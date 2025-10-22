<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PowChallenge;
use App\Models\PowCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class PowSystemTest extends TestCase
{
    use RefreshDatabase;

    private function createTestUser()
    {
        return User::factory()->create([
            'pubkey_hex' => '02' . str_repeat('a', 64),
        ]);
    }

    private function mineNonce(string $canonicalBytes, string $requiredPrefix, int $maxIters = 1000000): ?array
    {
        for ($nonce = 0; $nonce < $maxIters; $nonce++) {
            $powInput = $canonicalBytes . pack('P', $nonce);
            $hash = hash('sha256', $powInput, true);
            $hashHex = bin2hex($hash);
            
            if (str_starts_with($hashHex, $requiredPrefix)) {
                return [
                    'nonce_u64' => $nonce,
                    'miner_version' => 1,
                    'timestamp_i64' => (int)(microtime(true) * 1000),
                    'hash_hex' => $hashHex,
                ];
            }
        }
        return null;
    }

    public function test_pow_params_endpoint()
    {
        $response = $this->getJson('/api/pow.params');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'mode',
                'default_prefix',
                'min_miner_version',
                'suggested_prefix_by_load',
            ]);
    }

    public function test_thread_begin_creates_challenge()
    {
        $user = $this->createTestUser();
        $this->actingAs($user, 'sanctum');

        $postDraft = [
            'title' => 'Test Thread',
            'body' => 'Test body',
            'attachments' => [],
            'refs' => [],
        ];

        $response = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => Str::uuid()->toString(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'challenge_id',
                'required_prefix_hex',
                'challenge_version',
                'op_id',
                'expires_at',
                'post_bytes_hash',
                'canonical_bytes',
            ]);

        $this->assertDatabaseHas('pow_challenges', [
            'id' => $response->json('challenge_id'),
            'user_pubkey_hex' => $user->pubkey_hex,
            'scope' => 'thread',
        ]);
    }

    public function test_thread_commit_with_valid_proof()
    {
        $user = $this->createTestUser();
        $this->actingAs($user, 'sanctum');

        $postDraft = [
            'title' => 'Test Thread',
            'body' => 'Test body',
            'attachments' => [],
            'refs' => [],
        ];

        // Begin
        $beginResponse = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => Str::uuid()->toString(),
        ]);

        $beginResponse->assertStatus(200);
        $challengeId = $beginResponse->json('challenge_id');
        $opId = $beginResponse->json('op_id');
        $requiredPrefix = $beginResponse->json('required_prefix_hex');
        $canonicalBytes = hex2bin($beginResponse->json('canonical_bytes'));

        // Mine a valid nonce
        $proof = $this->mineNonce($canonicalBytes, $requiredPrefix);
        $this->assertNotNull($proof, 'Could not mine a valid nonce');

        // Commit
        $commitResponse = $this->postJson('/api/thread.commit', [
            'op_id' => $opId,
            'challenge_id' => $challengeId,
            'post_draft' => $postDraft,
            'proof' => [
                'nonce_u64' => $proof['nonce_u64'],
                'miner_version' => $proof['miner_version'],
                'timestamp_i64' => $proof['timestamp_i64'],
            ],
        ]);

        $commitResponse->assertStatus(200)
            ->assertJsonStructure(['thread_id', 'hash_hex']);

        $this->assertDatabaseHas('pow_commits', [
            'challenge_id' => $challengeId,
            'nonce_u64' => $proof['nonce_u64'],
            'accepted' => true,
        ]);
    }

    public function test_thread_commit_rejects_invalid_nonce()
    {
        $user = $this->createTestUser();
        $this->actingAs($user, 'sanctum');

        $postDraft = [
            'title' => 'Test Thread',
            'body' => 'Test body',
            'attachments' => [],
            'refs' => [],
        ];

        // Begin
        $beginResponse = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => Str::uuid()->toString(),
        ]);

        $challengeId = $beginResponse->json('challenge_id');
        $opId = $beginResponse->json('op_id');

        // Commit with invalid nonce
        $commitResponse = $this->postJson('/api/thread.commit', [
            'op_id' => $opId,
            'challenge_id' => $challengeId,
            'post_draft' => $postDraft,
            'proof' => [
                'nonce_u64' => 999999,
                'miner_version' => 1,
                'timestamp_i64' => (int)(microtime(true) * 1000),
            ],
        ]);

        $commitResponse->assertStatus(422)
            ->assertJson(['error' => fn($msg) => str_contains($msg, 'Invalid PoW')]);

        $this->assertDatabaseHas('pow_commits', [
            'challenge_id' => $challengeId,
            'accepted' => false,
        ]);
    }

    public function test_thread_commit_rejects_mutated_draft()
    {
        $user = $this->createTestUser();
        $this->actingAs($user, 'sanctum');

        $postDraft = [
            'title' => 'Original Title',
            'body' => 'Original body',
            'attachments' => [],
            'refs' => [],
        ];

        // Begin
        $beginResponse = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => Str::uuid()->toString(),
        ]);

        $challengeId = $beginResponse->json('challenge_id');
        $opId = $beginResponse->json('op_id');

        // Try to commit with modified draft
        $mutatedDraft = $postDraft;
        $mutatedDraft['title'] = 'Modified Title';

        $commitResponse = $this->postJson('/api/thread.commit', [
            'op_id' => $opId,
            'challenge_id' => $challengeId,
            'post_draft' => $mutatedDraft,
            'proof' => [
                'nonce_u64' => 12345,
                'miner_version' => 1,
                'timestamp_i64' => (int)(microtime(true) * 1000),
            ],
        ]);

        $commitResponse->assertStatus(422)
            ->assertJson(['error' => 'Post draft mismatch']);
    }

    public function test_idempotent_thread_begin()
    {
        $user = $this->createTestUser();
        $this->actingAs($user, 'sanctum');

        $postDraft = [
            'title' => 'Test Thread',
            'body' => 'Test body',
            'attachments' => [],
            'refs' => [],
        ];

        $opId = Str::uuid()->toString();

        // First call
        $response1 = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => $opId,
        ]);

        // Second call with same op_id
        $response2 = $this->postJson('/api/thread.begin', [
            'post_draft' => $postDraft,
            'client_op_id' => $opId,
        ]);

        // Should return identical response
        $this->assertEquals($response1->json(), $response2->json());
    }
}
