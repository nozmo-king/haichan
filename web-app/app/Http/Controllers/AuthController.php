<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AllowedPublicKey;
use App\Services\FriendCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use kornrunner\Secp256k1;

class AuthController extends Controller
{
    public function __construct(
        private FriendCodeService $friendCodeService
    ) {}
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('forum.index');
        }
        
        return view('auth.login');
    }

    public function getChallenge(Request $request)
    {
        Log::info('Challenge request received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $request->validate([
            'public_key' => 'required|string|size:66' // compressed secp256k1 public key
        ]);

        Log::info('Challenge request for public key', [
            'public_key' => $request->public_key,
            'ip' => $request->ip()
        ]);

        // Check if public key is allowed
        if (!AllowedPublicKey::isAllowed($request->public_key)) {
            Log::warning('Unauthorized public key attempted access', [
                'public_key' => $request->public_key,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['error' => 'Public key not authorized'], 403);
        }

        Log::info('Public key authorized, finding user', [
            'public_key' => $request->public_key
        ]);

        // Find or create user for this public key
        $user = User::findOrCreateForPublicKey($request->public_key);
        if (!$user) {
            Log::error('Failed to find or create user for authorized public key', [
                'public_key' => $request->public_key
            ]);
            return response()->json(['error' => 'Public key not authorized'], 403);
        }

        Log::info('User found/created, generating challenge', [
            'user_id' => $user->id,
            'public_key' => $request->public_key
        ]);

        // Generate challenge
        $challenge = $user->generateChallenge();
        
        Log::info('Challenge generated successfully', [
            'user_id' => $user->id,
            'challenge_length' => strlen($challenge)
        ]);
        
        return response()->json([
            'challenge' => $challenge,
            'user_id' => $user->id
        ]);
    }

    public function login(Request $request)
    {
        Log::info('Login attempt received', [
            'user_id' => $request->user_id,
            'has_challenge' => !empty($request->challenge),
            'has_signature' => !empty($request->signature),
            'ip' => $request->ip()
        ]);

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'challenge' => 'required|string',
            'signature' => 'required|string'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            Log::info('User found for login attempt', [
                'user_id' => $user->id,
                'public_key' => $user->allowedPublicKey->public_key,
                'challenge_length' => strlen($request->challenge),
                'signature_length' => strlen($request->signature)
            ]);
            
            // Verify signature
            if (!$user->verifySignature($request->signature, $request->challenge)) {
                Log::warning('Failed signature verification', [
                    'user_id' => $user->id,
                    'public_key' => $user->allowedPublicKey->public_key,
                    'challenge' => $request->challenge,
                    'signature' => $request->signature,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return back()->withErrors(['signature' => 'Invalid signature']);
            }
            
            Log::info('Signature verification successful', [
                'user_id' => $user->id
            ]);
            
            // Clear challenge to prevent replay
            $user->clearChallenge();
            
            // Login user
            Auth::login($user);
            
            Log::info('Successful authentication', [
                'user_id' => $user->id,
                'public_key' => $user->allowedPublicKey->public_key,
                'ip' => $request->ip()
            ]);
            
            return redirect()->intended(route('forum.index'));
        } catch (\Exception $e) {
            Log::error('Login exception', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
                'ip' => $request->ip()
            ]);
            return back()->withErrors(['signature' => 'Authentication failed']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register-form');
    }

    public function showRegister(Request $request)
    {
        $friendCode = $request->get('validated_friend_code');
        
        return view('auth.register', compact('friendCode'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string|size:66',
            'friend_code' => 'required|string',
        ]);

        $friendCode = $this->friendCodeService->validateFriendCode($request->friend_code);
        
        if (!$friendCode) {
            return back()->withErrors(['friend_code' => 'Invalid or expired friend code.']);
        }

        // Check if public key already exists
        $existingKey = AllowedPublicKey::where('public_key', $request->public_key)->first();
        
        if ($existingKey) {
            // Check if there's already a user with this public key
            $existingUser = User::where('allowed_public_key_id', $existingKey->id)->first();
            
            if ($existingUser) {
                return back()->withErrors(['public_key' => 'This public key is already registered to an existing account.']);
            }
            
            // Key exists but no user - reuse the existing key
            $allowedKey = $existingKey;
        } else {
            // Create new allowed key
            $allowedKey = AllowedPublicKey::create([
                'public_key' => $request->public_key,
                'label' => 'User registered via friend code',
                'is_active' => true,
            ]);
        }

        $user = User::create([
            'allowed_public_key_id' => $allowedKey->id,
        ]);

        $this->friendCodeService->useFriendCode($request->friend_code, $user);

        // Generate a friend code for the new user
        $this->friendCodeService->generateFriendCode($user);

        // Automatically log in the user after registration
        Auth::login($user);

        return redirect()->route('subscription.plans')
            ->with('success', 'Account created successfully! You are now logged in. Please choose a subscription plan to continue.')
            ->with('registered_public_key', $request->public_key);
    }
}
