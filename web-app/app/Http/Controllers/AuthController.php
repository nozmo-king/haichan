<?php

namespace App\Http\Controllers;

use App\Models\BitcoinAuth;
use App\Models\InviteCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\BitcoinAddressGenerator;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register-form');
    }

    public function showRegister(Request $request, $friendCode = null)
    {
        $remainingSlots = 256 - \App\Models\BitcoinAuth::count();
        return view('auth.register', ['friendCode' => $friendCode, 'remainingSlots' => $remainingSlots]);
    }
    

    public function generateKeys()
    {
        // This is handled client-side now
        return response()->json([
            'error' => 'Key generation must be done client-side for security'
        ], 400);
    }

    // Emergency login using private key with optional password reset
    public function backupLogin(Request $request)
    {
        $request->validate([
            'private_key' => 'required|string',
            'new_password' => 'nullable|string|min:8',
            'confirm_password' => 'nullable|string|same:new_password'
        ]);

        try {
            // Clean up the private key input (remove whitespace, newlines, etc.)
            $privateKey = trim(preg_replace('/\s+/', '', $request->private_key));
            
            // Handle both fake and real private keys
            $publicKey = BitcoinAddressGenerator::derivePublicKey($privateKey);
            
            // If this was a fake key, we now have the real public key derived from it
            $realCredentials = null;
            if (!preg_match('/^[a-fA-F0-9]{64}$/', $privateKey)) {
                // This was a fake key, get the real credentials
                $realCredentials = BitcoinAddressGenerator::generateRealFromFake($privateKey);
            }
            
        } catch (\Exception $e) {
            return back()->withErrors(['private_key' => 'Invalid private key. Please check your backup file and try again.']);
        }
        
        // Find user by public key (should work for both old fake-derived and real keys)
        $user = BitcoinAuth::where('public_key', $publicKey)->first();
        
        if (!$user) {
            return back()->withErrors(['private_key' => 'No account found with this private key.']);
        }

        if ($user->is_banned) {
            $banMessage = "Account banned: {$user->ban_reason}";
            if ($user->banned_until) {
                $banMessage .= " Until: {$user->banned_until->format('Y-m-d H:i')}";
            }
            return back()->withErrors(['message' => $banMessage]);
        }

        // Upgrade to real Bitcoin address if needed and we derived real credentials
        $needsAddressUpgrade = false;
        $backupContent = null;
        
        if ($realCredentials && (!$user->address || !BitcoinAddressGenerator::isRealBitcoinAddress($user->address))) {
            // Update user with real Bitcoin credentials
            $user->update([
                'address' => $realCredentials['address'],
                'public_key' => $realCredentials['public_key']
            ]);

            // Create backup content for the user
            $backupContent = "🔐 HAICHAN BITCOIN WALLET BACKUP - EMERGENCY LOGIN UPGRADE\n";
            $backupContent .= "=====================================================\n\n";
            $backupContent .= "Username: {$user->username}\n";
            $backupContent .= "Private Key (Hex): {$realCredentials['private_key_hex']}\n";
            $backupContent .= "Public Key: {$realCredentials['public_key']}\n";
            $backupContent .= "NEW Bitcoin Address: {$realCredentials['address']}\n";
            $backupContent .= "Invite Code: {$user->invite_code}\n";
            $backupContent .= "Upgrade Date: " . now()->format('Y-m-d H:i:s') . "\n\n";
            $backupContent .= "🚨 IMPORTANT UPGRADE NOTICE:\n";
            $backupContent .= "- Your account was upgraded to use REAL Bitcoin addresses!\n";
            $backupContent .= "- Your new address: {$realCredentials['address']}\n";
            $backupContent .= "- This is a REAL Bitcoin address that works with Bitcoin wallets\n";
            $backupContent .= "- Keep your private key secure - it controls real Bitcoin!\n";
            $backupContent .= "- Your login credentials remain the same\n\n";
            
            if ($request->new_password) {
                $backupContent .= "- New password has been set for easier future logins\n\n";
            }
            
            $backupContent .= "Generated: " . now()->format('Y-m-d H:i:s T') . "\n";

            $needsAddressUpgrade = true;
            
            \Log::info("User upgraded to real Bitcoin address via emergency login", [
                'user_id' => $user->id,
                'username' => $user->username,
                'new_address' => $realCredentials['address']
            ]);
        }

        // Set new password if provided
        if ($request->new_password) {
            $user->password_hash = Hash::make($request->new_password);
            $user->save();
            \Log::info("Password reset via emergency login for user: {$user->username}");
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

        // Store backup content in session if we upgraded
        if ($needsAddressUpgrade && $backupContent) {
            session(['bitcoin_upgrade_backup' => $backupContent]);
            session(['bitcoin_upgrade_credentials' => $realCredentials]);
        }

        $successMessage = "Emergency login successful! Welcome back, {$user->username}!";
        
        if ($request->new_password) {
            $successMessage .= " Your password has been updated.";
        }
        
        if ($needsAddressUpgrade) {
            $successMessage .= " Your account has been upgraded to use real Bitcoin addresses.";
            return redirect()->route('bitcoin.upgrade')->with('success', $successMessage);
        }

        return redirect('/')->with('success', $successMessage);
    }

    // Cryptographic login using public key and signature
    public function cryptographicLogin(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string|size:64',
            'signature' => 'required|string',
            'message' => 'required|string', // The message that was signed
        ]);

        $publicKey = $request->public_key;
        $signature = $request->signature;
        $message = $request->message;

        $user = BitcoinAuth::where('public_key', $publicKey)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found with this public key.'], 404);
        }

        // In a real application, you would use a robust secp256k1 library to verify the signature.
        // For this example, we'll use a simplified (and insecure) verification.
        // The BitcoinAuth model has a placeholder verifySignature method.
        if (!$user->verifySignature($message, $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature.'], 401);
        }

        if ($user->is_banned) {
            $banMessage = "Account banned: {$user->ban_reason}";
            if ($user->banned_until) {
                $banMessage .= " Until: {$user->banned_until->format('Y-m-d H:i')}";
            }
            return response()->json(['success' => false, 'message' => $banMessage], 403);
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

        return response()->json([
            'success' => true,
            'message' => "Welcome back, {$user->username}!",
            'user_pubkey' => $user->public_key, // Return public key for frontend storage
            'username' => $user->username,
        ]);
    }

    // Standard username/password login
    public function login(Request $request)
    {
        Log::info('Login attempt', [
            'identifier' => $request->login_identifier,
            'has_password' => !empty($request->password)
        ]);
        
        $request->validate([
            'login_identifier' => 'required|string',
            'password' => 'required|string'
        ]);

        // Find user by username or Bitcoin address
        $user = BitcoinAuth::where('username', $request->login_identifier)
            ->orWhere('address', $request->login_identifier)
            ->first();
        
        Log::info('User lookup', ['found' => $user ? true : false, 'username' => $user?->username]);
        
        if (!$user) {
            Log::info('User not found');
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
            // Generate secure password hash using Laravel's Hash facade
            $hash = Hash::make($request->password);
            
            // Update user with password hash for future logins
            $user->password_hash = $hash;
            $user->save();
            
            Log::info("Password hash created for user: {$user->username}");
        } else {
            // Check if this is a legacy SHA256 hash (64 characters)
            if (strlen($user->password_hash) === 64) {
                // Legacy SHA256 + salt verification
                $salt = $user->password_salt ?? '';
                $legacy_hash = hash('sha256', $request->password . $salt);
                
                Log::info('Legacy password check', [
                    'hash_length' => strlen($user->password_hash),
                    'salt' => $salt,
                    'generated_hash' => $legacy_hash,
                    'stored_hash' => $user->password_hash,
                    'match' => $legacy_hash === $user->password_hash
                ]);
                
                if ($legacy_hash !== $user->password_hash) {
                    Log::info('Legacy password mismatch');
                    return back()->withErrors(['login_identifier' => 'Invalid password. Try using the private key backup login if you forgot your password.']);
                }
                
                // Upgrade to Bcrypt for future logins
                $user->password_hash = Hash::make($request->password);
                $user->password_salt = null; // Clear the old salt
                $user->save();
                
                Log::info("Upgraded legacy password hash to Bcrypt for user: {$user->username}");
            } else {
                // Modern Bcrypt verification
                if (!Hash::check($request->password, $user->password_hash)) {
                    return back()->withErrors(['login_identifier' => 'Invalid password. Try using the private key backup login if you forgot your password.']);
                }
            }
        }

        // Update last login
        $user->last_login = now();
        $user->mining_streak++;
        $user->save();

        // Check if user needs Bitcoin address upgrade to real address
        $needsAddressUpgrade = false;
        if ($user->address && !BitcoinAddressGenerator::isRealBitcoinAddress($user->address)) {
            try {
                // Generate new real Bitcoin credentials
                $newCredentials = BitcoinAddressGenerator::generateKeyPair();
                
                // Update user with real Bitcoin address
                $user->update([
                    'address' => $newCredentials['address'],
                    'public_key' => $newCredentials['public_key']
                ]);

                // Create backup content for the user
                $backupContent = "🔐 HAICHAN BITCOIN WALLET BACKUP - ADDRESS UPGRADE\n";
                $backupContent .= "================================================\n\n";
                $backupContent .= "Username: {$user->username}\n";
                $backupContent .= "Private Key (Hex): {$newCredentials['private_key_hex']}\n";
                $backupContent .= "Public Key: {$newCredentials['public_key']}\n";
                $backupContent .= "NEW Bitcoin Address: {$newCredentials['address']}\n";
                $backupContent .= "Invite Code: {$user->invite_code}\n";
                $backupContent .= "Upgrade Date: " . now()->format('Y-m-d H:i:s') . "\n\n";
                $backupContent .= "🚨 IMPORTANT UPGRADE NOTICE:\n";
                $backupContent .= "- Your account was upgraded to use REAL Bitcoin addresses!\n";
                $backupContent .= "- Your new address: {$newCredentials['address']}\n";
                $backupContent .= "- This is a REAL Bitcoin address that works with Bitcoin wallets\n";
                $backupContent .= "- Keep your private key secure - it controls real Bitcoin!\n";
                $backupContent .= "- Your login credentials remain the same\n\n";
                $backupContent .= "Generated: " . now()->format('Y-m-d H:i:s T') . "\n";

                // Store in session for the upgrade page
                session(['bitcoin_upgrade_credentials' => $newCredentials]);
                session(['bitcoin_upgrade_backup' => $backupContent]);
                
                $needsAddressUpgrade = true;
                
                \Log::info("User upgraded to real Bitcoin address on login", [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'new_address' => $newCredentials['address']
                ]);
                
            } catch (\Exception $e) {
                \Log::error("Failed to upgrade user address on login", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Log user in
        session()->regenerate();
        session([
            'bitcoin_auth_id' => $user->id,
            'bitcoin_auth_user' => $user->fresh(),
        ]);

        // Redirect to upgrade page if needed, otherwise home
        if ($needsAddressUpgrade) {
            return redirect()->route('bitcoin.upgrade')->with('success', "Welcome back! Your account has been upgraded to use real Bitcoin addresses.");
        }

        return redirect('/')->with('success', "Welcome back, {$user->username}!");
    }

    // API endpoint to get current user's public key
    public function getUserPublicKey()
    {
        $userId = session('bitcoin_auth_id');
        if (!$userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = BitcoinAuth::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user_pubkey' => $user->public_key,
            'username' => $user->username,
        ]);
    }



    public function register(Request $request)
    {
        // Check for genesis code first
        $isGenesisCode = in_array($request->friend_code, ['GENESIS2025', 'SUPERADMIN-3FuiKyZDg28GWoBcaKMCCgUK']);
        
        $request->validate([
            'friend_code' => 'required|string',
            'username' => 'required|string|min:3|max:20|unique:bitcoin_auth,username|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:8',
        ]);

        // Auto-generate Bitcoin credentials using our service
        $credentials = BitcoinAddressGenerator::generateKeyPair();

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
            
            // Generate secure password hash using Laravel's Hash facade (bcrypt/Argon2)
            $passwordHash = Hash::make($request->password);
            
            // Create user with auto-generated Bitcoin credentials
            $user = BitcoinAuth::create([
                'public_key' => $credentials['public_key'],
                'address' => $credentials['address'],
                'username' => $request->username,
                'password_hash' => $passwordHash,
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
                
                // Special handling for 21E8 legendary code - preload points instead of bonus
                if ($inviteCode->code === '21E80000000000') {
                    $user->total_pow_points = 210000;
                    $user->level = 210; // Set level to 210 but don't let it affect mining power
                    $user->mining_power = 2.1; // Fixed 2.1x mining power, not level-based
                    $user->save();
                    
                    \Log::info('21E8 legendary code used', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'preloaded_points' => 210000,
                        'level' => 210,
                        'mining_power' => 2.1
                    ]);
                }
            }

            // Log user in
            session()->regenerate();
            session([
                'bitcoin_auth_id' => $user->id,
                'bitcoin_auth_user' => $user->fresh(),
            ]);

            // Calculate remaining slots
            $totalUsers = BitcoinAuth::count();
            $remainingSlots = 256 - $totalUsers;

            // Create backup content for download
            $backupContent = "🔐 HAICHAN BITCOIN WALLET BACKUP\n";
            $backupContent .= "===================================\n\n";
            $backupContent .= "Username: {$user->username}\n";
            $backupContent .= "Password: {$request->password}\n";
            $backupContent .= "Private Key (Hex): {$credentials['private_key_hex']}\n";
            $backupContent .= "Public Key: {$credentials['public_key']}\n";
            $backupContent .= "Bitcoin Address: {$credentials['address']}\n";
            $backupContent .= "Invite Code: {$user->invite_code}\n";
            $backupContent .= "Registration Date: " . $user->created_at->format('Y-m-d H:i:s') . "\n\n";
            $backupContent .= "🚨 CRITICAL SECURITY NOTES:\n";
            $backupContent .= "- This file contains your PRIVATE KEY - keep it absolutely secure!\n";
            $backupContent .= "- Anyone with your private key can control your Bitcoin address\n";
            $backupContent .= "- Never share your private key with anyone\n";
            $backupContent .= "- Store multiple copies in safe locations\n";
            $backupContent .= "- Use this private key to recover your account if needed\n";
            $backupContent .= "- Share your invite code with friends to invite them\n\n";
            $backupContent .= "Generated: " . now()->format('Y-m-d H:i:s T') . "\n";

            // Show success page
            return view('auth.registration-success', [
                'user' => $user,
                'credentials' => $credentials,
                'remainingSlots' => $remainingSlots,
                'backupContent' => $backupContent
            ]);

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        // Clear all session data
        session()->forget(['bitcoin_auth_id', 'bitcoin_auth_user']);
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

    public function validateFriendCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = $request->code;
        
        // Check if it's a genesis code
        $isGenesisCode = in_array($code, ['GENESIS2025', 'SUPERADMIN-3FuiKyZDg28GWoBcaKMCCgUK']);
        
        if ($isGenesisCode) {
            return response()->json([
                'valid' => true,
                'message' => 'Valid genesis code',
                'code' => $code
            ]);
        }
        
        // Check in invite_codes table
        $inviteCode = InviteCode::where('code', $code)
            ->where('uses_remaining', '>', 0)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
        
        if ($inviteCode) {
            return response()->json([
                'valid' => true,
                'message' => 'Valid invite code',
                'code' => $code,
                'uses_remaining' => $inviteCode->uses_remaining
            ]);
        }
        
        // Check legacy friend_codes table
        $friendCode = \DB::table('friend_codes')
            ->where('code', $code)
            ->where('is_used', 0)
            ->first();
        
        if ($friendCode) {
            return response()->json([
                'valid' => true,
                'message' => 'Valid friend code',
                'code' => $code
            ]);
        }
        
        return response()->json([
            'valid' => false,
            'message' => 'Invalid or expired friend code'
        ], 422);
    }
}