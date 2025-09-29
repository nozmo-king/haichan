<?php

namespace App\Http\Controllers;

use App\Models\BitcoinAuth;
use App\Models\InviteCode;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        $remainingSlots = InviteCode::getRemainingSlots();

        if ($remainingSlots <= 0) {
            return redirect('/auth/login')->withErrors(['message' => 'Registration is full. 256 user limit reached.']);
        }

        return view('auth.register', compact('remainingSlots'));
    }

    public function generateKeys()
    {
        // Generate a simple key pair for demo purposes
        $privateKey = bin2hex(random_bytes(32));
        $publicKey = hash('sha256', $privateKey);
        $address = BitcoinAuth::generateAddress($publicKey);

        return response()->json([
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'address' => $address,
            'warning' => 'SAVE YOUR PRIVATE KEY! This is the only way to access your account.',
        ]);
    }

    public function generateAddress(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string|min:64',
        ]);

        try {
            $address = BitcoinAuth::generateAddress($request->public_key);

            return response()->json([
                'address' => $address,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate address: '.$e->getMessage(),
            ], 400);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string',
            'public_key' => 'required|string|min:64',
            'private_key' => 'required|string|min:64',
            'password' => 'required|string|min:8',
            'address' => 'required|string|min:26',
        ]);

        try {
            // Check remaining slots
            if (! InviteCode::canRegister()) {
                return back()->withErrors(['message' => 'Registration is full. 256 user limit reached.']);
            }

            // Verify invite code
            $inviteCode = InviteCode::where('code', $request->invite_code)
                ->where('uses_remaining', '>', 0)
                ->first();

            if (! $inviteCode) {
                return back()->withErrors(['invite_code' => 'Invalid or expired invite code.']);
            }

            // Verify key pair
            $expectedPublicKey = hash('sha256', $request->private_key);
            if ($expectedPublicKey !== $request->public_key) {
                return back()->withErrors(['message' => 'Invalid key pair.']);
            }

            // Verify address matches public key
            $expectedAddress = BitcoinAuth::generateAddress($request->public_key);
            if ($expectedAddress !== $request->address) {
                return back()->withErrors(['message' => 'Address does not match public key.']);
            }

            // Create salted password hash using cryptographic keys
            $salt = bin2hex(random_bytes(32));
            $hashData = $request->password.$salt.$request->public_key.$request->private_key.$request->address;
            $passwordHash = hash('sha256', $hashData);

            // Create user
            $user = BitcoinAuth::createUser(
                $request->public_key,
                'verified', // Simplified signature
                $request->invite_code,
                $passwordHash,
                $salt
            );

            // Use the invite code
            $inviteCode->useCode($user->id);

            // Log user in
            session([
                'bitcoin_auth_id' => $user->id,
                'bitcoin_auth_user' => $user,
            ]);

            return redirect('/')->with('success', "Welcome to Haichan! You are user #{$user->id}/256. Your invite code: {$user->invite_code}");

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Registration failed: '.$e->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        \Log::info('Login attempt', ['address' => $request->address, 'has_password' => ! empty($request->password)]);

        $request->validate([
            'address' => 'required|string|min:26',
            'password' => 'required|string|min:8',
        ]);

        // Find user by address
        $user = BitcoinAuth::where('address', $request->address)->first();

        if (! $user) {
            \Log::info('Login failed: User not found', ['address' => $request->address]);

            return back()->withErrors(['address' => 'Invalid address or user not found.']);
        }

        // Verify password using stored salt and cryptographic keys
        $hashData = $request->password.$user->password_salt.$user->public_key.$user->private_key_hash.$user->address;
        $passwordHash = hash('sha256', $hashData);

        \Log::info('Password verification', [
            'calculated_hash' => $passwordHash,
            'stored_hash' => $user->password_hash,
            'match' => $passwordHash === $user->password_hash,
        ]);

        if ($passwordHash !== $user->password_hash) {
            \Log::info('Login failed: Invalid password');

            return back()->withErrors(['password' => 'Invalid password.']);
        }

        if ($user->is_banned) {
            $banMessage = "Account banned: {$user->ban_reason}";
            if ($user->banned_until) {
                $banMessage .= " Until: {$user->banned_until->format('Y-m-d H:i')}";
            }

            return back()->withErrors(['message' => $banMessage]);
        }

        // Update last login
        $user->last_login = now();
        $user->mining_streak++; // Daily login bonus
        $user->save();

        // Log user in
        session([
            'bitcoin_auth_id' => $user->id,
            'bitcoin_auth_user' => $user,
        ]);

        \Log::info('Login successful', [
            'user_id' => $user->id,
            'username' => $user->username,
            'session_id' => session()->getId(),
        ]);

        return redirect('/')->with('success', "Welcome back, {$user->username}! Mining streak: {$user->mining_streak} days");
    }

    public function backupLogin(Request $request)
    {
        $request->validate([
            'private_key' => 'required|string|min:64',
        ]);

        // Find user by matching public key (derived from private key)
        $publicKey = hash('sha256', $request->private_key);
        $user = BitcoinAuth::where('public_key', $publicKey)->first();

        if (! $user) {
            return back()->withErrors(['private_key' => 'Invalid private key or user not found.']);
        }

        if ($user->is_banned) {
            $banMessage = "Account banned: {$user->ban_reason}";
            if ($user->banned_until) {
                $banMessage .= " Until: {$user->banned_until->format('Y-m-d H:i')}";
            }

            return back()->withErrors(['message' => $banMessage]);
        }

        // Update last login
        $user->last_login = now();
        $user->mining_streak++; // Daily login bonus
        $user->save();

        // Log user in
        session([
            'bitcoin_auth_id' => $user->id,
            'bitcoin_auth_user' => $user,
        ]);

        return redirect('/')->with('success', "Welcome back via backup login, {$user->username}! Mining streak: {$user->mining_streak} days");
    }

    public function logout()
    {
        session()->forget(['bitcoin_auth_id', 'bitcoin_auth_user']);

        return redirect('/auth/login')->with('success', 'Logged out successfully.');
    }

    public function showDashboard()
    {
        if (! session('bitcoin_auth_id')) {
            return redirect('/auth/login');
        }

        $user = BitcoinAuth::findOrFail(session('bitcoin_auth_id'));

        // Get user stats
        $stats = [
            'posts' => \DB::table('posts')->where('user_id', $user->id)->count(),
            'threads' => \DB::table('threads')->where('user_id', $user->id)->count(),
            'messages' => 0, // Will implement messages later
        ];

        // Get recent messages (empty for now)
        $messages = collect();

        return view('user.dashboard', compact('user', 'stats', 'messages'));
    }

    public function showEditProfile()
    {
        if (! session('bitcoin_auth_id')) {
            return redirect('/auth/login');
        }

        $user = BitcoinAuth::findOrFail(session('bitcoin_auth_id'));

        return view('user.profile-edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        if (! session('bitcoin_auth_id')) {
            return redirect('/auth/login');
        }

        $user = BitcoinAuth::findOrFail(session('bitcoin_auth_id'));

        $request->validate([
            'display_name' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'timezone' => 'nullable|string|max:50',
            'signature' => 'nullable|string|max:500',
            'show_email' => 'boolean',
            'profile_public' => 'boolean',
            'social_links' => 'nullable|json',
        ]);

        // Parse social links JSON
        $socialLinks = null;
        if ($request->has('social_twitter') || $request->has('social_github') || $request->has('social_discord')) {
            $socialLinks = [];
            if ($request->social_twitter) {
                $socialLinks['twitter'] = $request->social_twitter;
            }
            if ($request->social_github) {
                $socialLinks['github'] = $request->social_github;
            }
            if ($request->social_discord) {
                $socialLinks['discord'] = $request->social_discord;
            }
        }

        $user->update([
            'display_name' => $request->display_name ?: null,
            'bio' => $request->bio ?: null,
            'location' => $request->location ?: null,
            'website' => $request->website ?: null,
            'email' => $request->email ?: null,
            'timezone' => $request->timezone ?: 'UTC',
            'signature' => $request->signature ?: null,
            'show_email' => $request->has('show_email'),
            'profile_public' => $request->has('profile_public'),
            'social_links' => $socialLinks,
        ]);

        // Update session user data
        session(['bitcoin_auth_user' => $user->fresh()]);

        return redirect('/user/profile/edit')->with('success', 'Profile updated successfully!');
    }

    public function showUserProfile($userId)
    {
        $user = BitcoinAuth::findOrFail($userId);

        // Get user stats
        $stats = [
            'posts' => \DB::table('posts')->where('user_id', $user->id)->count(),
            'threads' => \DB::table('threads')->where('user_id', $user->id)->count(),
        ];

        return view('user.profile', compact('user', 'stats'));
    }
}
