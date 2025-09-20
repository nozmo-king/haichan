<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AllowedPublicKey;
use App\Services\FriendCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

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


    public function login(Request $request)
    {
        Log::info('Login attempt received', [
            'has_private_key' => !empty($request->private_key),
            'ip' => $request->ip()
        ]);

        $request->validate([
            'private_key' => 'required|string|size:64|regex:/^[a-fA-F0-9]{64}$/'
        ]);

        try {
            $privateKeyHex = $request->private_key;

            // Generate public key from private key using mdanter/ecc
            $generator = \Mdanter\Ecc\EccFactory::getSecgCurves()->generator256k1();
            $privateKeyInt = gmp_init($privateKeyHex, 16);
            $publicKeyPoint = $generator->mul($privateKeyInt);

            // Get compressed public key format (33 bytes: 02/03 prefix + 32 bytes x coordinate)
            $x = $publicKeyPoint->getX();
            $y = $publicKeyPoint->getY();
            $prefix = gmp_strval(gmp_mod($y, 2)) == '0' ? '02' : '03';
            $xHex = str_pad(gmp_strval($x, 16), 64, '0', STR_PAD_LEFT);
            $publicKeyHex = $prefix . $xHex;

            Log::info('Generated public key from private key', [
                'public_key' => $publicKeyHex,
                'ip' => $request->ip()
            ]);

            // Check if this public key is allowed
            if (!AllowedPublicKey::isAllowed($publicKeyHex)) {
                Log::warning('Unauthorized public key attempted access', [
                    'public_key' => $publicKeyHex,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return back()->withErrors(['authentication' => 'This private key is not authorized. Please register first with a friend code.']);
            }

            // Find or create user for this public key
            $user = User::findOrCreateForPublicKey($publicKeyHex);

            if (!$user) {
                Log::error('Failed to find or create user for authorized public key', [
                    'public_key' => $publicKeyHex
                ]);
                return back()->withErrors(['authentication' => 'Authentication failed. Please try again.']);
            }

            Log::info('User found/created for authentication', [
                'user_id' => $user->id,
                'public_key' => $publicKeyHex
            ]);

            // Login user and store public key in session
            Auth::login($user, true); // Remember user

            // Store the public key in session for easy access
            session(['authenticated_public_key' => $user->allowedPublicKey->public_key]);
            session(['user_id' => $user->id]);

            Log::info('Successful authentication', [
                'user_id' => $user->id,
                'public_key' => $user->allowedPublicKey->public_key,
                'ip' => $request->ip()
            ]);

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            Log::error('Login exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            return back()->withErrors(['authentication' => 'Authentication failed. Please check your private key and try again.']);
        }
    }

    public function logout(Request $request)
    {
        // Clear session data
        $request->session()->forget(['authenticated_public_key', 'user_id']);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showRegisterForm()
    {
        return view('auth.register-form');
    }

    public function validateFriendCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:32'
        ]);

        $friendCode = $this->friendCodeService->validateFriendCode($request->code);

        return response()->json([
            'valid' => (bool) $friendCode,
            'message' => $friendCode ? 'Friend code is valid' : 'Invalid or expired friend code'
        ]);
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
        Auth::login($user, true); // Remember user

        // Store the public key in session for easy access
        session(['authenticated_public_key' => $user->allowedPublicKey->public_key]);
        session(['user_id' => $user->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Account created successfully! You are now logged in. Welcome to Haichan!');
    }

    /**
     * Web-based cryptographic login using challenge-response
     * This endpoint supports sessions for web browsers
     */
    public function cryptographicLogin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'challenge' => 'required|string',
            'signature' => 'required|string'
        ]);

        Log::info('Web cryptographic login attempt', [
            'user_id' => $request->user_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            Log::warning('User not found for web login', [
                'requested_user_id' => $request->user_id,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'User not found'], 404);
        }

        Log::info('User found for web login attempt', [
            'user_id' => $user->id,
            'has_allowed_public_key' => !empty($user->allowedPublicKey),
            'public_key' => $user->allowedPublicKey->public_key ?? 'not_loaded'
        ]);

        // Verify signature using the same method as API
        $signatureValid = $user->verifySignature($request->signature, $request->challenge);

        Log::info('Web signature verification completed', [
            'user_id' => $user->id,
            'signature_valid' => $signatureValid
        ]);

        if (!$signatureValid) {
            Log::warning('Web authentication failed - invalid signature', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Clear the challenge to prevent replay attacks
        $user->clearChallenge();

        // Create web session (this is the key difference from API)
        Auth::login($user, true); // Remember user

        // Store additional session data
        session(['authenticated_public_key' => $user->allowedPublicKey->public_key]);
        session(['user_id' => $user->id]);

        // Regenerate session ID for security
        $request->session()->regenerate();

        Log::info('Web authentication successful', [
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'auth_check' => Auth::check(),
            'auth_user_id' => Auth::id(),
            'session_data' => [
                'authenticated_public_key' => session('authenticated_public_key'),
                'user_id' => session('user_id')
            ],
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
    }
}
