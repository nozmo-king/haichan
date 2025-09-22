<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\AllowedPublicKey;
use App\Services\FriendCodeService;

class AuthApiController extends Controller
{
    public function __construct(
        private FriendCodeService $friendCodeService
    ) {}
    public function getChallenge(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string'
        ]);

        Log::info('Challenge request received', [
            'public_key' => $request->public_key,
            'public_key_length' => strlen($request->public_key),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $user = User::findOrCreateForPublicKey($request->public_key);

        if (!$user) {
            Log::warning('Public key not authorized for challenge', [
                'public_key' => $request->public_key,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Public key not authorized'], 403);
        }

        $challenge = $user->generateChallenge();

        Log::info('Challenge generated', [
            'user_id' => $user->id,
            'public_key' => $request->public_key,
            'challenge' => $challenge,
            'challenge_expires_at' => $user->challenge_expires_at
        ]);

        return response()->json([
            'challenge' => $challenge,
            'user_id' => $user->id
        ]);
    }

    public function login(Request $request)
    {
        Log::info('API login request received', [
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'has_signature' => !empty($request->signature),
            'has_challenge' => !empty($request->challenge),
            'has_user_id' => !empty($request->user_id),
            'signature_length' => strlen($request->signature ?? ''),
            'challenge_length' => strlen($request->challenge ?? ''),
            'request_data_keys' => array_keys($request->all())
        ]);

        $request->validate([
            'signature' => 'required|string',
            'challenge' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        Log::info('API login validation passed', [
            'user_id' => $request->user_id,
            'signature_sample' => substr($request->signature, 0, 20) . '...',
            'challenge_sample' => substr($request->challenge, 0, 20) . '...'
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            Log::warning('User not found for login', [
                'requested_user_id' => $request->user_id,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'User not found'], 404);
        }

        Log::info('User found for login attempt', [
            'user_id' => $user->id,
            'has_allowed_public_key' => !empty($user->allowedPublicKey),
            'public_key' => $user->allowedPublicKey->public_key ?? 'not_loaded',
            'last_challenge_exists' => !empty($user->last_challenge),
            'challenge_expires_at' => $user->challenge_expires_at
        ]);

        // Verify signature using your existing verification method
        $signatureValid = $user->verifySignature($request->signature, $request->challenge);

        Log::info('Signature verification completed', [
            'user_id' => $user->id,
            'signature_valid' => $signatureValid,
            'about_to_return_result' => true
        ]);

        if (!$signatureValid) {
            Log::warning('Authentication failed - invalid signature', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Clear the challenge to prevent replay attacks
        $user->clearChallenge();

        // Check if this is a web browser request (vs mobile app)
        $userAgent = $request->userAgent();
        $isWebBrowser = str_contains($userAgent, 'Mozilla') || str_contains($userAgent, 'Chrome') || str_contains($userAgent, 'Firefox') || str_contains($userAgent, 'Safari');

        if ($isWebBrowser) {
            // For web browsers: create session-based authentication
            Auth::login($user, true); // Remember user

            // Store the public key in session for easy access
            session(['authenticated_public_key' => $user->allowedPublicKey->public_key]);
            session(['user_id' => $user->id]);

            Log::info('Authentication successful (web session)', [
                'user_id' => $user->id,
                'session_created' => true,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'session_created' => true,
                'user' => [
                    'id' => $user->id,
                    'public_key' => $user->allowedPublicKey->public_key
                ],
                'redirect_url' => route('dashboard')
            ]);
        } else {
            // For mobile apps: create API token
            $token = $user->createToken('api-token')->plainTextToken;

            Log::info('Authentication successful (API token)', [
                'user_id' => $user->id,
                'token_created' => !empty($token),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'public_key' => $user->allowedPublicKey->public_key
                ]
            ]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function validateFriendCode(Request $request)
    {
        $request->validate([
            'friend_code' => 'required|string',
            'public_key' => 'required|string|size:66'
        ]);

        $friendCode = $this->friendCodeService->validateFriendCode($request->friend_code);

        if (!$friendCode) {
            return response()->json(['error' => 'Invalid or expired friend code'], 400);
        }

        // Check if public key already exists
        $existingKey = AllowedPublicKey::where('public_key', $request->public_key)->first();

        if ($existingKey) {
            // Check if there's already a user with this public key
            $existingUser = User::where('allowed_public_key_id', $existingKey->id)->first();

            if ($existingUser) {
                return response()->json(['error' => 'This public key is already registered'], 409);
            }

            // Key exists but no user - reuse the existing key
            $allowedKey = $existingKey;
        } else {
            // Create new allowed key
            $allowedKey = AllowedPublicKey::create([
                'public_key' => $request->public_key,
                'label' => 'User registered via mobile app',
                'is_active' => true,
            ]);
        }

        $user = User::create([
            'allowed_public_key_id' => $allowedKey->id,
            'username' => $request->public_key,
        ]);

        $this->friendCodeService->useFriendCode($request->friend_code, $user);

        // Generate a friend code for the new user
        $this->friendCodeService->generateFriendCode($user);

        return response()->json([
            'message' => 'Registration successful',
            'user_id' => $user->id,
            'public_key' => $allowedKey->public_key
        ]);
    }

    /**
     * Test endpoint for debugging signature verification
     * This should be removed or protected in production
     */
    public function debugSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
            'challenge' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        Log::info('DEBUG SIGNATURE TEST STARTED', [
            'user_id' => $user->id,
            'public_key' => $user->allowedPublicKey->public_key,
            'challenge' => $request->challenge,
            'signature' => $request->signature,
            'challenge_length' => strlen($request->challenge),
            'signature_length' => strlen($request->signature)
        ]);

        // Don't actually verify - just test the signature verification logic
        $verificationResult = $user->verifySignature($request->signature, $request->challenge);

        return response()->json([
            'verification_result' => $verificationResult,
            'user_id' => $user->id,
            'challenge_provided' => $request->challenge,
            'signature_provided' => $request->signature,
            'debug_complete' => true,
            'message' => 'Check logs for detailed verification process'
        ]);
    }

    /**
     * Create a test challenge without updating the user's stored challenge
     * Useful for testing signature generation on iOS side
     */
    public function createTestChallenge(Request $request)
    {
        $testChallenge = bin2hex(random_bytes(32)) . time();

        return response()->json([
            'test_challenge' => $testChallenge,
            'message' => 'Use this challenge to generate a signature for testing',
            'note' => 'This challenge is not stored - use debugSignature endpoint to test'
        ]);
    }

    /**
     * Compare iOS vs Website data step-by-step
     */
    public function compareImplementations(Request $request)
    {
        // Support both iOS format (combined signature) and website format (separate components)
        if ($request->has('signature') && !$request->has('signature_full')) {
            // iOS format - extract components from combined signature
            $request->validate([
                'platform' => 'required|string',
                'private_key' => 'required|string|size:64',
                'public_key' => 'required|string',
                'challenge' => 'required|string',
                'challenge_hash' => 'required|string',
                'signature' => 'required|string|size:128'
            ]);

            $signature = $request->signature;
            $signature_r = substr($signature, 0, 64);
            $signature_s = substr($signature, 64, 64);
            $signature_full = $signature;

            // Calculate if S was high and normalized
            $curveOrder = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
            $halfOrder = gmp_div($curveOrder, 2);
            $sValue = gmp_init($signature_s, 16);
            $s_was_high = gmp_cmp($sValue, $halfOrder) > 0;
            $normalizedS = $s_was_high ? gmp_sub($curveOrder, $sValue) : $sValue;
            $s_normalized = str_pad(gmp_strval($normalizedS, 16), 64, '0', STR_PAD_LEFT);

        } else {
            // Website format - use provided components
            $request->validate([
                'platform' => 'required|string|in:ios,website',
                'private_key' => 'required|string|size:64',
                'public_key' => 'required|string',
                'challenge' => 'required|string',
                'challenge_hash' => 'required|string',
                'signature_r' => 'required|string',
                'signature_s' => 'required|string',
                'signature_full' => 'required|string',
                's_was_high' => 'required|boolean',
                's_normalized' => 'required|string'
            ]);

            $signature_r = $request->signature_r;
            $signature_s = $request->signature_s;
            $signature_full = $request->signature_full;
            $s_was_high = $request->s_was_high;
            $s_normalized = $request->s_normalized;
        }

        Log::info('COMPARISON TEST - ' . strtoupper($request->platform) . ' DATA', [
            'platform' => $request->platform,
            'private_key_sample' => substr($request->private_key, 0, 8) . '...',
            'public_key' => $request->public_key,
            'challenge' => $request->challenge,
            'challenge_hash' => $request->challenge_hash,
            'signature_r' => $signature_r,
            'signature_s' => $signature_s,
            'signature_full' => $signature_full,
            's_was_high' => $s_was_high,
            's_normalized' => $s_normalized
        ]);

        // Verify the data step by step
        $verification = [];

        // Step 1: Verify public key generation
        try {
            $generator = \Mdanter\Ecc\EccFactory::getSecgCurves()->generator256k1();
            $privateKeyInt = gmp_init($request->private_key, 16);
            $publicKeyPoint = $generator->mul($privateKeyInt);
            $x = $publicKeyPoint->getX();
            $y = $publicKeyPoint->getY();
            $prefix = gmp_strval(gmp_mod($y, 2)) == '0' ? '02' : '03';
            $xHex = str_pad(gmp_strval($x, 16), 64, '0', STR_PAD_LEFT);
            $serverPublicKey = $prefix . $xHex;

            $verification['public_key'] = [
                'provided' => $request->public_key,
                'server_generated' => $serverPublicKey,
                'matches' => $request->public_key === $serverPublicKey
            ];
        } catch (\Exception $e) {
            $verification['public_key'] = ['error' => $e->getMessage()];
        }

        // Step 2: Verify challenge hash
        $serverChallengeHash = hash('sha256', $request->challenge);
        $verification['challenge_hash'] = [
            'provided' => $request->challenge_hash,
            'server_generated' => $serverChallengeHash,
            'matches' => $request->challenge_hash === $serverChallengeHash
        ];

        // Step 3: Verify S-value normalization
        try {
            $curveOrder = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
            $halfOrder = gmp_div($curveOrder, 2);
            $sValue = gmp_init($signature_s, 16);
            $isHighS = gmp_cmp($sValue, $halfOrder) > 0;
            $normalizedS = $isHighS ? gmp_sub($curveOrder, $sValue) : $sValue;
            $normalizedSHex = str_pad(gmp_strval($normalizedS, 16), 64, '0', STR_PAD_LEFT);

            $verification['s_normalization'] = [
                'provided_s' => $signature_s,
                'provided_s_was_high' => $s_was_high,
                'provided_s_normalized' => $s_normalized,
                'server_calculated_high' => $isHighS,
                'server_calculated_normalized' => $normalizedSHex,
                'high_matches' => $s_was_high === $isHighS,
                'normalized_matches' => $s_normalized === $normalizedSHex
            ];
        } catch (\Exception $e) {
            $verification['s_normalization'] = ['error' => $e->getMessage()];
        }

        // Step 4: Test signature verification
        try {
            // Find user by public key for testing
            $allowedKey = \App\Models\AllowedPublicKey::where('public_key', $request->public_key)->first();
            if ($allowedKey) {
                $user = \App\Models\User::where('allowed_public_key_id', $allowedKey->id)->first();
                if ($user) {
                    // Set test challenge
                    $user->update([
                        'last_challenge' => $request->challenge,
                        'challenge_expires_at' => \Carbon\Carbon::now()->addMinutes(5)
                    ]);

                    // Test signature verification
                    $verificationResult = $user->verifySignature($signature_full, $request->challenge);
                    $verification['signature_verification'] = [
                        'result' => $verificationResult,
                        'user_id' => $user->id
                    ];
                } else {
                    $verification['signature_verification'] = ['error' => 'No user found for public key'];
                }
            } else {
                $verification['signature_verification'] = ['error' => 'Public key not in allowed list'];
            }
        } catch (\Exception $e) {
            $verification['signature_verification'] = ['error' => $e->getMessage()];
        }

        return response()->json([
            'platform' => $request->platform,
            'verification_results' => $verification,
            'summary' => [
                'public_key_ok' => $verification['public_key']['matches'] ?? false,
                'challenge_hash_ok' => $verification['challenge_hash']['matches'] ?? false,
                's_normalization_ok' => $verification['s_normalization']['normalized_matches'] ?? false,
                'signature_verifies' => $verification['signature_verification']['result'] ?? false
            ]
        ]);
    }
}