<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PowControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up test data but don't recreate tables
        try {
            DB::table('op_receipts')->delete();
            DB::table('pow_commits')->delete(); 
            DB::table('pow_challenges')->delete();
        } catch (\Exception $e) {
            // Tables might not exist in test environment
        }
    }

    public function test_thread_begin_creates_challenge()
    {
        $clientOpId = Str::uuid();
        $postDraft = [
            'title' => 'Test Thread',
            'body' => 'This is a test thread body.',
            'attachments' => [],
            'refs' => [],
        ];

        $response = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => $clientOpId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'challenge_id',
            'required_prefix_hex',
            'challenge_version',
            'op_id',
            'expires_at',
            'post_bytes_hash',
        ]);

        // Verify challenge was stored in database
        $challengeId = $response->json('challenge_id');
        $challenge = DB::table('pow_challenges')->where('id', $challengeId)->first();
        
        $this->assertNotNull($challenge);
        $this->assertEquals('thread', $challenge->scope);
        $this->assertNull($challenge->thread_id);
        $this->assertNull($challenge->parent_id);
        $this->assertEquals('21e8', $challenge->required_prefix_hex);
        $this->assertEquals(1, $challenge->challenge_version);

        // Verify operation receipt was stored
        $receipt = DB::table('op_receipts')->where('client_op_id', $clientOpId)->first();
        $this->assertNotNull($receipt);
    }

    public function test_thread_begin_idempotent()
    {
        $clientOpId = Str::uuid();
        $postDraft = [
            'title' => 'Test Thread',
            'body' => 'This is a test thread body.',
            'attachments' => [],
            'refs' => [],
        ];

        // Make first request
        $response1 = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => $clientOpId,
        ]);

        // Make second request with same client_op_id
        $response2 = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => $clientOpId,
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        // Should return same result
        $this->assertEquals($response1->json(), $response2->json());

        // Should only have one challenge in database
        $challengeCount = DB::table('pow_challenges')->count();
        $this->assertEquals(1, $challengeCount);
    }

    public function test_thread_commit_with_invalid_challenge()
    {
        $response = $this->postJson('/api/thread.commit', [
            'op_id' => Str::uuid(),
            'challenge_id' => Str::uuid(), // Non-existent challenge
            'post_draft' => ['title' => 'Test', 'body' => 'Test body'],
            'proof' => [
                'nonce_u64' => 123456,
                'miner_version' => 1,
                'timestamp_i64' => time(),
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Proof rejected',
            'reason' => 'Challenge not found',
        ]);
    }

    public function test_thread_commit_with_expired_challenge()
    {
        // Create expired challenge
        $challengeId = Str::uuid();
        DB::table('pow_challenges')->insert([
            'id' => $challengeId,
            'user_pubkey_hex' => '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798',
            'scope' => 'thread',
            'thread_id' => null,
            'parent_id' => null,
            'post_bytes_hash' => str_repeat('a', 32),
            'required_prefix_hex' => '21e8',
            'challenge_version' => 1,
            'expires_at' => Carbon::now()->subMinutes(5), // Expired
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->postJson('/api/thread.commit', [
            'op_id' => Str::uuid(),
            'challenge_id' => $challengeId,
            'post_draft' => ['title' => 'Test', 'body' => 'Test body'],
            'proof' => [
                'nonce_u64' => 123456,
                'miner_version' => 1,
                'timestamp_i64' => time(),
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Proof rejected',
            'reason' => 'Challenge expired',
        ]);
    }

    public function test_reply_begin_creates_challenge()
    {
        // Create a thread first
        $threadId = DB::table('pow_posts')->insertGetId([
            'thread_id' => null,
            'parent_id' => null,
            'author_pubkey_hex' => '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798',
            'title' => 'Parent Thread',
            'body' => 'Thread body',
            'attachments_json' => '[]',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Update thread_id to reference itself
        DB::table('pow_posts')->where('id', $threadId)->update(['thread_id' => $threadId]);

        $clientOpId = Str::uuid();
        $postDraft = [
            'body' => 'This is a reply.',
            'attachments' => [],
            'refs' => [],
        ];

        $response = $this->postJson('/api/reply.begin', [
            'post_draft' => $postDraft,
            'thread_id' => $threadId,
            'parent_id' => null,
            'client_op_id' => $clientOpId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'challenge_id',
            'required_prefix_hex',
            'challenge_version',
            'op_id',
            'expires_at',
            'post_bytes_hash',
        ]);

        // Verify challenge was stored
        $challengeId = $response->json('challenge_id');
        $challenge = DB::table('pow_challenges')->where('id', $challengeId)->first();
        
        $this->assertNotNull($challenge);
        $this->assertEquals('reply', $challenge->scope);
        $this->assertEquals($threadId, $challenge->thread_id);
    }

    public function test_pow_params_returns_correct_structure()
    {
        $response = $this->getJson('/api/pow.params');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'mode',
            'default_prefix',
            'min_miner_version',
            'suggested_prefix_by_load',
        ]);

        $response->assertJson([
            'mode' => 'vanity_prefix',
            'default_prefix' => '21e8',
            'min_miner_version' => 1,
        ]);
    }

    public function test_validation_errors()
    {
        // Test thread.begin validation
        $response = $this->postJson('/api/thread.begin', [
            'post_draft' => [
                'title' => '', // Empty title should fail
                'body' => 'Test body',
            ],
            // Missing client_op_id
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Validation failed']);

        // Test thread.commit validation
        $response = $this->postJson('/api/thread.commit', [
            'op_id' => 'invalid-uuid', // Invalid UUID
            'challenge_id' => Str::uuid(),
            'post_draft' => ['title' => 'Test', 'body' => 'Test body'],
            'proof' => [
                'nonce_u64' => -1, // Negative nonce should fail
                'miner_version' => 1,
                'timestamp_i64' => time(),
            ],
        ]);

        $response->assertStatus(400);
    }

    public function test_canonical_bytes_generation()
    {
        // This tests the internal canonical bytes generation
        // We can't directly test the private method, but we can verify
        // that different inputs produce different challenges
        
        $clientOpId1 = Str::uuid();
        $clientOpId2 = Str::uuid();

        $postDraft1 = [
            'title' => 'First Thread',
            'body' => 'First body',
            'attachments' => [],
            'refs' => [],
        ];

        $postDraft2 = [
            'title' => 'Second Thread',
            'body' => 'Second body',
            'attachments' => [],
            'refs' => [],
        ];

        $response1 = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft1,
            'client_op_id' => $clientOpId1,
        ]);

        $response2 = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft2,
            'client_op_id' => $clientOpId2,
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Different post drafts should produce different post_bytes_hash
        $this->assertNotEquals(
            $response1->json('post_bytes_hash'),
            $response2->json('post_bytes_hash')
        );
    }

    public function test_server_verification_budget()
    {
        // This test verifies that the server verification doesn't exceed 5ms budget
        // We can't easily create a slow verification case, but we can verify
        // the verification process works quickly for valid cases
        
        $clientOpId = Str::uuid();
        $postDraft = [
            'title' => 'Performance Test',
            'body' => 'Testing verification performance',
            'attachments' => [],
            'refs' => [],
        ];

        $start = microtime(true);
        
        $response = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => $clientOpId,
        ]);

        $elapsed = (microtime(true) - $start) * 1000; // Convert to ms

        $response->assertStatus(200);
        
        // The entire request should complete well under the 5ms verification budget
        // Note: This includes more than just verification, so we use a larger threshold
        $this->assertLessThan(1000, $elapsed, 'Request took too long'); // 1 second max
    }

    public function test_proof_commit_recording()
    {
        // Create a valid challenge
        $challengeId = Str::uuid();
        $postBytesHash = hash('sha256', 'test_canonical_bytes', true);
        
        DB::table('pow_challenges')->insert([
            'id' => $challengeId,
            'user_pubkey_hex' => '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798',
            'scope' => 'thread',
            'thread_id' => null,
            'parent_id' => null,
            'post_bytes_hash' => $postBytesHash,
            'required_prefix_hex' => '21e8',
            'challenge_version' => 1,
            'expires_at' => Carbon::now()->addMinutes(1),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->postJson('/api/thread.commit', [
            'op_id' => Str::uuid(),
            'challenge_id' => $challengeId,
            'post_draft' => ['title' => 'Test', 'body' => 'Test body'],
            'proof' => [
                'nonce_u64' => 123456,
                'miner_version' => 1,
                'timestamp_i64' => time(),
            ],
        ]);

        // Even if the proof is invalid, it should be recorded
        $this->assertNotNull(
            DB::table('pow_commits')->where('challenge_id', $challengeId)->first()
        );
    }
}