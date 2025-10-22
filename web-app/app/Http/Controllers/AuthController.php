<?php

namespace App\Http\Controllers;

use App\Models\BitcoinAuth;
use App\Models\InviteCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.login');
    }
    
    public function loginAnonymously()
    {
        // Set anonymous session
        session(['anonymous_mode' => true]);
        return redirect('/')->with('success', 'Browsing anonymously');
    }

    public function generateKeys()
    {
        // This is handled client-side now
        return response()->json([
            'error' => 'Key generation must be done client-side for security'
        ], 400);
    }

    // Simplified login using just private key
    public function backupLogin(Request $request)
    {
        $request->validate([
            'private_key' => 'required|string|size:64'
        ]);

        // Generate public key from private key (client should do this)
        $privateKey = $request->private_key;
        $publicKey = hash('sha256', $privateKey);
        
        // Find user by public key
        $user = BitcoinAuth::where('public_key', $publicKey)->first();
        
        if (!$user) {
            return back()->withErrors(['private_key' => 'Invalid private key']);
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
        $user->mining_streak++;
        $user->save();

        // Log user in
        session()->regenerate();
        session([
            'bitcoin_auth_id' => $user->id,
            'bitcoin_auth_user' => $user->fresh(),
        ]);

        return redirect('/')->with('success', "Welcome back, {$user->username}!");
    }

    // Standard username/password login
    public function login(Request $request)
    {
        $request->validate([
            'login_identifier' => 'required|string',
            'password' => 'required|string'
        ]);

        // Find user by username or Bitcoin address
        $user = BitcoinAuth::where('username', $request->login_identifier)
            ->orWhere('address', $request->login_identifier)
            ->first();
        
        if (!$user) {
            return back()->withErrors(['login_identifier' => 'User not found. Try using the private key backup login instead.']);
        }

        if ($user->is_banned) {
            $banMessage = "Account banned: {$user->ban_reason}";
            if ($user->banned_until) {
                $banMessage .= " Until: {$user->banned_until->format('Y-m-d H:i')}";
            }
            return back()->withErrors(['message' => $banMessage]);
        }

        // Check if user has password hash, if not, try to create one from provided password
        if (!$user->password_hash) {
            // Generate salt and hash for first-time password setup
            $salt = bin2hex(random_bytes(16));
            $hash = hash('sha256', $salt . $request->password);
            
            // Update user with password hash for future logins
            $user->password_salt = $salt;
            $user->password_hash = $hash;
            $user->save();
            
            Log::info("Password hash created for user: {$user->username}");
        } else {
            // Verify existing password
            $providedHash = hash('sha256', $user->password_salt . $request->password);
            
            if ($providedHash !== $user->password_hash) {
                return back()->withErrors(['login_identifier' => 'Invalid password. Try using the private key backup login if you forgot your password.']);
            }
        }

        // Update last login
        $user->last_login = now();
        $user->mining_streak++;
        $user->save();

        // Log user in
        session()->regenerate();
        session([
            'bitcoin_auth_id' => $user->id,
            'bitcoin_auth_user' => $user->fresh(),
        ]);

        return redirect('/')->with('success', "Welcome back, {$user->username}!");
    }

    public function showSimpleRegister(Request $request)
    {
        return view('auth.register-with-keys', [
            'friendCode' => $request->get('code')
        ]);
    }

    public function register(Request $request)
    {
        // Check for genesis code first
        $isGenesisCode = in_array($request->friend_code, ['GENESIS2025', 'SUPERADMIN-3FuiKyZDg28GWoBcaKMCCgUK']);
        
        $request->validate([
            'friend_code' => 'required|string',
            'username' => 'required|string|min:3|max:20|unique:bitcoin_auth,username|regex:/^[a-zA-Z0-9_]+$/',
            'public_key' => 'required|string|size:64',
            'address' => 'required|string|min:26',
            'private_key' => 'required|string|size:64', // Only for validation, not stored
        ]);

        try {
            // Check remaining slots
            if (!$isGenesisCode && !InviteCode::canRegister()) {
                return back()->withErrors(['message' => 'Registration is full. 256 user limit reached.']);
            }

            // Verify invite code
            $inviteCode = null;
            if (!$isGenesisCode) {
                // Check new invite codes table
                $inviteCode = InviteCode::where('code', $request->friend_code)
                    ->where('uses_remaining', '>', 0)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->first();

                if (!$inviteCode) {
                    // Check legacy friend_codes table
                    $friendCode = \DB::table('friend_codes')->where('code', $request->friend_code)
                        ->where('is_used', 0)
                        ->first();
                    
                    if (!$friendCode) {
                        return back()->withErrors(['friend_code' => 'Invalid or expired friend code.']);
                    }
                }
            }

            // Check if this is the first user
            $isFirstUser = BitcoinAuth::count() === 0;
            
            // Create user - NO private keys or passwords stored!
            $user = BitcoinAuth::create([
                'public_key' => $request->public_key,
                'address' => $request->address,
                'username' => $request->username,
                'invite_code' => strtoupper(bin2hex(random_bytes(6))),
                'last_login' => now(),
                'mining_power' => 1.0,
                'total_pow_points' => 0,
                'level' => 1,
                'invited_by' => $request->friend_code,
                'is_admin' => ($isGenesisCode || $isFirstUser) ? 1 : 0,
                'is_moderator' => ($isGenesisCode || $isFirstUser) ? 1 : 0,
            ]);

            // Use the invite code
            if ($inviteCode && !$isGenesisCode) {
                $inviteCode->useCode($user->id);
            }

            // Log user in
            session()->regenerate();
            session([
                'bitcoin_auth_id' => $user->id,
                'bitcoin_auth_user' => $user->fresh(),
            ]);

            // Generate backup file content
            $backupContent = "# HAICHAN BACKUP KEYS\n";
            $backupContent .= "# Generated: " . now() . "\n";
            $backupContent .= "# Username: {$user->username}\n";
            $backupContent .= "# Address: {$user->address}\n";
            $backupContent .= "# User ID: {$user->id}/256\n\n";
            $backupContent .= "PRIVATE_KEY={$request->private_key}\n";
            $backupContent .= "PUBLIC_KEY={$request->public_key}\n";
            $backupContent .= "ADDRESS={$request->address}\n";
            $backupContent .= "USERNAME={$user->username}\n";
            $backupContent .= "INVITE_CODE={$user->invite_code}\n\n";
            $backupContent .= "# IMPORTANT: Save this file securely!\n";
            $backupContent .= "# Use your private key for backup login";

            // Calculate remaining slots
            $totalUsers = BitcoinAuth::count();
            $remainingSlots = 256 - $totalUsers;

            // Show success page
            return view('auth.registration-success', [
                'user' => $user,
                'privateKey' => $request->private_key,
                'publicKey' => $request->public_key,
                'backupContent' => $backupContent,
                'remainingSlots' => $remainingSlots
            ]);

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        // Clear all session data
        session()->forget(['bitcoin_auth_id', 'bitcoin_auth_user', 'anonymous_mode']);
        session()->regenerate();

        return redirect('/login')->with('success', 'Logged out successfully');
    }

    public function checkUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:20'
        ]);

        $username = $request->username;
        $exists = BitcoinAuth::where('username', $username)->exists();

        return response()->json([
            'available' => !$exists,
            'username' => $username
        ]);
    }
}