<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\PowChallenge;
use App\Models\PowCommit;
use App\Models\Post;
use App\Models\OpReceipt;
use Illuminate\Support\Str;

class PowSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_pow_params()
    {
        $response = $this->getJson('/api/pow/params');

        $response->assertStatus(200)
            ->assertJson([
                'mode' => 'vanity_prefix',
                'default_prefix' => '21e8',
                'min_miner_version' => 1,
            ]);
    }

    public function test_thread_begin_creates_challenge()
    {
        $pubkey = '02a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2';
        $client_op_id = (string) Str::uuid();

        $response = $this->postJson('/api/thread.begin', [
            'client_op_id' => $client_op_id,
            'post_draft' => [
                'title' => 'Test Thread',
                'body' => 'Test body',
                'attachments' => [],
                'refs' => [],
            ],
        ], ['X-Pubkey' => $pubkey]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'challenge_id',
                'required_prefix_hex',
                'challenge_version',
                'op_id',
                'expires_at',
                'post_bytes_hash',
            ]);

        $this->assertDatabaseHas('pow_challenges', [
            'id' => $response->json('challenge_id'),
            'user_pubkey_hex' => $pubkey,
            'scope' => 'thread',
        ]);

        $this->assertDatabaseHas('users', [
            'pubkey_hex' => $pubkey,
        ]);
    }

    public function test_thread_begin_is_idempotent()
    {
        $pubkey = '02a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2';
        $client_op_id = (string) Str::uuid();

        $response1 = $this->postJson('/api/thread.begin', [
            'client_op_id' => $client_op_id,
            'post_draft' => [
                'title' => 'Test Thread',
                'body' => 'Test body',
                'attachments' => [],
                'refs' => [],
            ],
        ], ['X-Pubkey' => $pubkey]);

        $response2 = $this->postJson('/api/thread.begin', [
            'client_op_id' => $client_op_id,
            'post_draft' => [
                'title' => 'Test Thread',
                'body' => 'Test body',
                'attachments' => [],
                'refs' => [],
            ],
        ], ['X-Pubkey' => $pubkey]);

        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_thread_commit_with_valid_pow()
    {
        $pubkey = '02a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2';
        $client_op_id = (string) Str::uuid();
        $post_draft = [
            'title' => 'First post',
            'body' => 'Hello world',
            'attachments' => [],
            'refs' => [],
        ];

        $beginResponse = $this->postJson('/api/thread.begin', [
            'client_op_id' => $client_op_id,
            'post_draft' => $post_draft,
        ], ['X-Pubkey' => $pubkey]);

        $challenge_id = $beginResponse->json('challenge_id');

        $commitResponse = $this->postJson('/api/thread.commit', [
            'op_id' => $client_op_id,
            'challenge_id' => $challenge_id,
            'post_draft' => $post_draft,
            'proof' => [
                'nonce_u64' => 3759,
                'miner_version' => 1,
                'timestamp_i64' => 1700000000,
            ],
        ], ['X-Pubkey' => $pubkey]);

        $commitResponse->assertStatus(200)
            ->assertJsonStructure(['thread_id']);

        $this->assertDatabaseHas('posts', [
            'author_pubkey_hex' => $pubkey,
            'title' => 'First post',
            'body' => 'Hello world',
        ]);

        $this->assertDatabaseHas('pow_commits', [
            'challenge_id' => $challenge_id,
            'accepted' => true,
        ]);
    }

    public function test_thread_commit_rejects_expired_challenge()
    {
        $pubkey = '02a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2';
        $user = User::firstOrCreate(['pubkey_hex' => $pubkey]);

        $post_draft = [
            'title' => 'Test',
            'body' => 'Test',
            'attachments' => [],
            'refs' => [],
        ];

        $post_json = json_encode([
            'attachments' => [],
            'body' => 'Test',
            'refs' => [],
            'title' => 'Test',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $challenge = PowChallenge::create([
            'user_pubkey_hex' => $pubkey,
            'scope' => 'thread',
            'thread_id' => 0,
            'parent_id' => 0,
            'post_bytes_hash' => hash('sha256', $post_json, true),
            'required_prefix_hex' => '21e8',
            'challenge_version' => 1,
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->postJson('/api/thread.commit', [
            'op_id' => (string) Str::uuid(),
            'challenge_id' => $challenge->id,
            'post_draft' => $post_draft,
            'proof' => [
                'nonce_u64' => 12345,
                'miner_version' => 1,
                'timestamp_i64' => 1700000000,
            ],
        ], ['X-Pubkey' => $pubkey]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Challenge expired']);
    }

    public function test_thread_commit_rejects_mutated_draft()
    {
        $pubkey = '02a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2';
        $client_op_id = (string) Str::uuid();

        $original_draft = [
            'title' => 'Original',
            'body' => 'Original body',
            'attachments' => [],
            'refs' => [],
        ];

        $beginResponse = $this->postJson('/api/thread.begin', [
            'client_op_id' => $client_op_id,
            'post_draft' => $original_draft,
        ], ['X-Pubkey' => $pubkey]);

        $challenge_id = $beginResponse->json('challenge_id');

        $modified_draft = [
            'title' => 'Original',
            'body' => 'Modified body',
            'attachments' => [],
            'refs' => [],
        ];

        $commitResponse = $this->postJson('/api/thread.commit', [
            'op_id' => $client_op_id,
            'challenge_id' => $challenge_id,
            'post_draft' => $modified_draft,
            'proof' => [
                'nonce_u64' => 12345,
                'miner_version' => 1,
                'timestamp_i64' => 1700000000,
            ],
        ], ['X-Pubkey' => $pubkey]);

        $commitResponse->assertStatus(400)
            ->assertJson(['error' => 'Post draft mutated']);
    }

    public function test_thread_commit_rejects_pubkey_mismatch()
    {
        $pubkey1 = '02a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2';
        $pubkey2 = '03b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3';
        $client_op_id = (string) Str::uuid();

        $post_draft = [
            'title' => 'Test',
            'body' => 'Test',
            'attachments' => [],
            'refs' => [],
        ];

        $beginResponse = $this->postJson('/api/thread.begin', [
            'client_op_id' => $client_op_id,
            'post_draft' => $post_draft,
        ], ['X-Pubkey' => $pubkey1]);

        $challenge_id = $beginResponse->json('challenge_id');

        $commitResponse = $this->postJson('/api/thread.commit', [
            'op_id' => $client_op_id,
            'challenge_id' => $challenge_id,
            'post_draft' => $post_draft,
            'proof' => [
                'nonce_u64' => 12345,
                'miner_version' => 1,
                'timestamp_i64' => 1700000000,
            ],
        ], ['X-Pubkey' => $pubkey2]);

        $commitResponse->assertStatus(403)
            ->assertJson(['error' => 'Pubkey mismatch']);
    }

    public function test_reply_begin_and_commit_flow()
    {
        $pubkey = '02a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2';
        
        $thread = Post::create([
            'author_pubkey_hex' => $pubkey,
            'title' => 'Thread',
            'body' => 'Thread body',
        ]);
        $thread->update(['thread_id' => $thread->id]);

        $client_op_id = (string) Str::uuid();
        $reply_draft = [
            'title' => '',
            'body' => 'Reply body',
            'attachments' => [],
            'refs' => [],
        ];

        $beginResponse = $this->postJson('/api/reply.begin', [
            'client_op_id' => $client_op_id,
            'thread_id' => $thread->id,
            'parent_id' => $thread->id,
            'post_draft' => $reply_draft,
        ], ['X-Pubkey' => $pubkey]);

        $beginResponse->assertStatus(200);
        $challenge_id = $beginResponse->json('challenge_id');

        $this->assertDatabaseHas('pow_challenges', [
            'id' => $challenge_id,
            'scope' => 'reply',
            'thread_id' => $thread->id,
            'parent_id' => $thread->id,
        ]);
    }
}
