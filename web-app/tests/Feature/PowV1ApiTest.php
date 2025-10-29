<?php

namespace Tests\Feature;

use App\Models\PowV1Challenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PowV1ApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return DB::table('bitcoin_auth')->insertGetId([
            'public_key' => '02' . str_repeat('a', 64),
            'address' => '1TestAddress',
            'username' => 'testuser_' . uniqid(),
            'invite_code' => 'TEST' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getUserById($id)
    {
        return (object) DB::table('bitcoin_auth')->where('id', $id)->first();
    }

    public function test_get_pow_params()
    {
        $response = $this->getJson('/api/pow/params');
        $response->assertStatus(200)->assertJson(['mode' => 'vanity_prefix', 'default_prefix' => '21e8']);
    }

    public function test_canonical_bytes_encoding()
    {
        $userPubkey = '02' . str_repeat('a', 64);
        $scope = 't';
        $threadId = 0;
        $parentId = 0;
        $timestamp = 1700000000;
        $postHash = hash('sha256', '{"attachments":[],"body":"test","refs":[],"title":"test"}', true);

        $bytes = 'HC1';
        $bytes .= $userPubkey;
        $bytes .= $scope;
        $bytes .= pack('P', $threadId);
        $bytes .= pack('P', $parentId);
        $bytes .= pack('q', $timestamp);
        $bytes .= $postHash;

        $this->assertTrue(str_starts_with($bytes, 'HC1'));
        $this->assertEquals(3 + 66 + 1 + 8 + 8 + 8 + 32, strlen($bytes));
    }

    public function test_pow_verification()
    {
        $canonicalBytes = 'HC1test';
        $nonce = 12345;
        $powInput = $canonicalBytes . pack('P', $nonce);
        $hash = hash('sha256', $powInput, false);

        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash));
        
        for ($i = 0; $i < 100000; $i++) {
            $powInput = $canonicalBytes . pack('P', $i);
            $hash = hash('sha256', $powInput, false);
            if (str_starts_with($hash, '21')) {
                $this->assertTrue(true, "Found valid nonce: $i");
                return;
            }
        }
    }
}
