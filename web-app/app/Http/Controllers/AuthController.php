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

    public function showSimpleRegister()
    {
        $remainingSlots = InviteCode::getRemainingSlots();

        if ($remainingSlots <= 0) {
            return redirect('/auth/login')->withErrors(['message' => 'Registration is full. 256 user limit reached.']);
        }

        return view('auth.simple-register', compact('remainingSlots'));
    }

    public function showRegisterForm()
    {
        return view('auth.register-form');
    }

    public function validateFriendCode(Request $request)
    {
        $request->validate([
            'friend_code' => 'required|string|size:32'
        ]);

        $inviteCode = InviteCode::where('code', $request->friend_code)
            ->where('uses_remaining', '>', 0)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$inviteCode) {
            return back()->withErrors(['friend_code' => 'Invalid or expired friend code.']);
        }

        // Redirect to registration with the friend code
        return redirect(route('auth.register', $request->friend_code));
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
        // Check if it's the superadmin code before validation
        $isSuperadminCode = str_starts_with($request->friend_code, 'SUPERADMIN-');
        
        // Apply different validation rules for superadmin
        $rules = [
            'friend_code' => 'required|string',
            'password' => 'required|string|min:8',
            'private_key' => 'required|string|min:64',
            'public_key' => 'required|string|min:64',
            'address' => 'required|string|min:26',
            'ssh_key' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048'
        ];
        
        // For superadmin, skip username validation since we'll force it to 'jcb'
        if (!$isSuperadminCode) {
            $rules['username'] = 'required|string|min:3|max:20|unique:bitcoin_auth,username|regex:/^[a-zA-Z0-9_]+$/';
        }
        
        $request->validate($rules);

        try {
            // $isSuperadminCode is already defined above
            
            if ($isSuperadminCode && $request->friend_code === 'SUPERADMIN-3FuiKyZDg28GWoBcaKMCCgUK') {
                // Force username to 'jcb' for this special code
                $request->merge(['username' => 'jcb']);
            }
            
            // Check remaining slots
            if (!$isSuperadminCode && !InviteCode::canRegister()) {
                return back()->withErrors(['message' => 'Registration is full. 256 user limit reached.']);
            }

            // Verify invite code (skip for superadmin code)
            $inviteCode = null;
            if (!$isSuperadminCode) {
                $inviteCode = InviteCode::where('code', $request->friend_code)
                    ->where('uses_remaining', '>', 0)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->first();

                if (!$inviteCode) {
                    return back()->withErrors(['friend_code' => 'Invalid or expired friend code.']);
                }
            }

            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // Create salted password hash
            $salt = bin2hex(random_bytes(32));
            $hashData = $request->password . $salt . $request->public_key;
            $passwordHash = hash('sha256', $hashData);

            // Create user
            $user = BitcoinAuth::create([
                'public_key' => $request->public_key,
                'private_key_hash' => hash('sha256', $request->private_key),
                'address' => $request->address,
                'username' => $request->username,
                'invite_code' => strtoupper(bin2hex(random_bytes(6))),
                'password_hash' => $passwordHash,
                'password_salt' => $salt,
                'last_login' => now(),
                'mining_power' => 1.0,
                'total_pow_points' => 0,
                'level' => 1,
                'invited_by' => $request->friend_code,
                'ssh_key' => $request->ssh_key,
                'avatar_path' => $avatarPath,
                'is_admin' => $isSuperadminCode ? 1 : 0,
                'is_moderator' => $isSuperadminCode ? 1 : 0,
            ]);

            // Use the invite code (skip for superadmin)
            if (!$isSuperadminCode && $inviteCode) {
                $inviteCode->useCode($user->id);
            }

            // Generate Haichan.txt file content
            $keyFileContent = "# HAICHAN KEYS AND CREDENTIALS\n";
            $keyFileContent .= "# Generated: " . now() . "\n";
            $keyFileContent .= "# User ID: {$user->id}/256\n";
            $keyFileContent .= "# DO NOT SHARE THIS FILE - IT CONTAINS YOUR PRIVATE KEYS\n\n";
            $keyFileContent .= "[BITCOIN_AUTH]\n";
            $keyFileContent .= "PRIVATE_KEY={$request->private_key}\n";
            $keyFileContent .= "PUBLIC_KEY={$request->public_key}\n";
            $keyFileContent .= "BITCOIN_ADDRESS={$request->address}\n\n";
            $keyFileContent .= "[USER_INFO]\n";
            $keyFileContent .= "USERNAME={$user->username}\n";
            $keyFileContent .= "USER_ID={$user->id}\n";
            $keyFileContent .= "INVITE_CODE={$user->invite_code}\n";
            $keyFileContent .= "REGISTRATION_DATE=" . now() . "\n\n";
            if ($request->ssh_key) {
                $keyFileContent .= "[SSH_KEY]\n";
                $keyFileContent .= $request->ssh_key . "\n\n";
            }
            $keyFileContent .= "[LOGIN_METHODS]\n";
            $keyFileContent .= "1. Standard: Username + Password\n";
            $keyFileContent .= "2. Bitcoin Address: {$request->address} + Password\n";
            $keyFileContent .= "3. Backup: Private Key only (no password needed)\n";

            // Log user in
            session()->regenerate();
            session([
                'bitcoin_auth_id' => $user->id,
                'bitcoin_auth_user' => $user,
            ]);
            session()->save();

            return redirect('/')
                ->with('success', "Welcome to Haichan, {$user->username}! You are user #{$user->id}/256.")
                ->with('download_key', base64_encode($keyFileContent))
                ->with('download_filename', 'Haichan.txt');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Registration failed: '.$e->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_identifier' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // Try to find user by username first, then by address
        $user = BitcoinAuth::where('username', $request->login_identifier)
                          ->orWhere('address', $request->login_identifier)
                          ->first();

        if (!$user) {
            return back()->withErrors(['login_identifier' => 'Invalid username/address or user not found.']);
        }

        // Verify password using appropriate hash method based on user's password structure
        $isValidPassword = false;
        
        // Try new simplified hash method (for users registered with simpleRegister)
        $hashData = $request->password . $user->password_salt . $user->public_key;
        $passwordHash = hash('sha256', $hashData);
        
        if ($passwordHash === $user->password_hash) {
            $isValidPassword = true;
        } else {
            // Try old complex hash method (for legacy users)
            $hashData = $request->password . $user->password_salt . $user->public_key . $user->private_key_hash . $user->address;
            $passwordHash = hash('sha256', $hashData);
            
            if ($passwordHash === $user->password_hash) {
                $isValidPassword = true;
            }
        }

        if (!$isValidPassword) {
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

        // Log user in with secure session handling
        session()->regenerate();
        session([
            'bitcoin_auth_id' => $user->id,
            'bitcoin_auth_user' => $user->fresh(),
        ]);
        session()->save();

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

        return view('user.profile-simple', compact('user', 'stats'));
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

    public function simpleRegister(Request $request)
    {
        try {
            $request->validate([
                'invite_code' => 'required|string|min:8',
                'username' => 'required|string|min:3|max:20|unique:bitcoin_auth,username|regex:/^[a-zA-Z0-9_]+$/',
                'password' => 'required|string|min:6',
                'mouse_entropy' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            \Log::info('SimpleRegister: Starting registration process');
            
            // Check remaining slots
            if (!InviteCode::canRegister()) {
                return back()->withErrors(['message' => 'Registration is full. 256 user limit reached.']);
            }
            \Log::info('SimpleRegister: Slots available');

            // Verify invite code
            $inviteCode = InviteCode::where('code', $request->invite_code)
                ->where('uses_remaining', '>', 0)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();

            if (!$inviteCode) {
                return back()->withErrors(['invite_code' => 'Invalid or expired invite code.']);
            }
            \Log::info('SimpleRegister: Invite code valid');

            // Handle entropy data - make it optional for testing
            $entropySource = '';
            if ($request->has('mouse_entropy') && !empty($request->mouse_entropy)) {
                $entropyData = json_decode($request->mouse_entropy, true);
                if ($entropyData && is_array($entropyData) && count($entropyData) > 0) {
                    $entropySource = serialize($entropyData);
                    \Log::info('SimpleRegister: Using mouse entropy with ' . count($entropyData) . ' points');
                }
            }
            
            // If no valid entropy provided, use system entropy
            if (empty($entropySource)) {
                $entropySource = microtime(true) . getmypid() . memory_get_usage() . uniqid('', true);
                \Log::info('SimpleRegister: Using system entropy fallback');
            }
            
            // Generate cryptographic keys with multiple entropy sources
            $privateKey = hash('sha256', 
                $request->username . 
                $request->password . 
                $entropySource . 
                microtime(true) . 
                bin2hex(random_bytes(32)) .
                session_id()
            );
            \Log::info('SimpleRegister: Private key generated');
            
            $publicKey = hash('sha256', $privateKey);
            \Log::info('SimpleRegister: Public key generated');
            
            $address = BitcoinAuth::generateAddress($publicKey);
            \Log::info('SimpleRegister: Address generated: ' . substr($address, 0, 10) . '...');

            // Create salted password hash (simplified for better compatibility)
            $salt = bin2hex(random_bytes(32));
            $hashData = $request->password . $salt . $publicKey;
            $passwordHash = hash('sha256', $hashData);
            \Log::info('SimpleRegister: Password hash created');

            // Create user with username
            $user = BitcoinAuth::create([
                'public_key' => $publicKey,
                'private_key_hash' => hash('sha256', $privateKey),
                'address' => $address,
                'invite_code' => strtoupper(bin2hex(random_bytes(6))), // Generate unique invite code for this user
                'username' => $request->username,
                'password_hash' => $passwordHash,
                'password_salt' => $salt,
                'last_login' => now(),
                'mining_power' => 1.0,
                'total_pow_points' => 0,
                'level' => 1,
                'invited_by' => $request->invite_code,
            ]);
            \Log::info('SimpleRegister: User created with ID: ' . $user->id);

            // Use the invite code
            $inviteCode->useCode($user->id);

            // Log user in with secure session handling
            session()->regenerate();
            session([
                'bitcoin_auth_id' => $user->id,
                'bitcoin_auth_user' => $user->fresh(),
            ]);
            session()->save();

            \Log::info('SimpleRegister: User logged in successfully');

            // Generate downloadable key file content for user
            $keyFileContent = "# HAICHAN BACKUP KEYS\n# Generated: " . now() . "\n# Username: {$user->username}\n# Address: {$address}\n# User ID: {$user->id}\n\nPRIVATE_KEY={$privateKey}\nPUBLIC_KEY={$publicKey}\nADDRESS={$address}\nUSERNAME={$user->username}\nINVITE_CODE={$user->invite_code}\n\n# Save this file securely! Use the private key for backup login.\n# Regular login: Use your username + password\n# Backup login: Use your private key only";

            return redirect('/')->with('success', "Welcome to Haichan, {$user->username}! You are user #{$user->id}/256. Your invite code: {$user->invite_code}")
                                ->with('download_key', base64_encode($keyFileContent))
                                ->with('username', $user->username)
                                ->with('user_id', $user->id);

        } catch (\Exception $e) {
            \Log::error('SimpleRegister failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['message' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Generate extremely random private key from mouse entropy
     */
    private function generatePrivateKeyFromEntropy(array $entropyData, string $username, string $password): string
    {
        try {
            // Collect multiple sources of entropy
            $entropyPool = '';
            
            // 1. Mouse movement entropy - position, timing, velocity
            $lastPoint = null;
            foreach ($entropyData as $point) {
                if (!is_array($point)) continue;
                
                $entropyPool .= ($point['x'] ?? 0) . ':' . ($point['y'] ?? 0) . ':' . ($point['timestamp'] ?? 0) . ':';
                $entropyPool .= ($point['deltaX'] ?? 0) . ':' . ($point['deltaY'] ?? 0) . '|';
                
                // Add microsecond timing variations
                $entropyPool .= microtime(true) . ':';
                
                // Add velocity calculations
                if ($lastPoint && is_array($lastPoint)) {
                    $distance = sqrt(pow(($point['x'] ?? 0) - ($lastPoint['x'] ?? 0), 2) + pow(($point['y'] ?? 0) - ($lastPoint['y'] ?? 0), 2));
                    $timeDiff = max(($point['timestamp'] ?? 0) - ($lastPoint['timestamp'] ?? 0), 0.001);
                    $velocity = $distance / $timeDiff;
                    $entropyPool .= $velocity . ':';
                }
                $lastPoint = $point;
            }
            
            // 2. Add system entropy sources
            $entropyPool .= getmypid() . ':' . memory_get_usage() . ':' . memory_get_peak_usage() . ':';
            $entropyPool .= microtime(true) . ':' . (function_exists('hrtime') ? hrtime(true) : time()) . ':';
            
            // 3. Add user-specific entropy
            $entropyPool .= $username . ':' . $password . ':' . session_id() . ':';
            $entropyPool .= $_SERVER['HTTP_USER_AGENT'] ?? 'unknown' . ':';
            $entropyPool .= $_SERVER['REMOTE_ADDR'] ?? 'unknown' . ':';
            
            // 4. Add additional randomness
            for ($i = 0; $i < 16; $i++) {
                $entropyPool .= bin2hex(random_bytes(4)) . ':' . uniqid('', true) . ':';
            }
            
            // 5. Mix entropy using multiple hash rounds with different salts
            $mixed = $entropyPool;
            $salts = ['haichan_salt_1', 'mouse_entropy_v2', 'private_key_gen', 'bitcoin_addr_2024'];
            
            foreach ($salts as $salt) {
                $mixed = hash('sha512', $mixed . $salt . microtime(true));
                if (function_exists('hash') && in_array('whirlpool', hash_algos())) {
                    $mixed = hash('whirlpool', $mixed . $entropyPool . $salt);
                }
                if (function_exists('hash') && in_array('sha3-512', hash_algos())) {
                    $mixed = hash('sha3-512', $mixed . bin2hex(random_bytes(32)) . $salt);
                } else {
                    $mixed = hash('sha512', $mixed . bin2hex(random_bytes(32)) . $salt);
                }
            }
            
            // 6. Final private key generation - ensure it's valid for Bitcoin
            $attempts = 0;
            do {
                $keyData = hash('sha256', $mixed . $attempts . microtime(true) . bin2hex(random_bytes(16)));
                $attempts++;
                
                // Simple validation - ensure not all zeros
                $allZeros = str_repeat('0', 64);
                $valid = ($keyData !== $allZeros && strlen($keyData) === 64);
                
                // More advanced validation if GMP is available
                if ($valid && extension_loaded('gmp')) {
                    $keyInt = gmp_init('0x' . $keyData, 16);
                    $curveOrder = gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
                    $valid = (gmp_cmp($keyInt, gmp_init(1)) > 0 && gmp_cmp($keyInt, $curveOrder) < 0);
                }
                
            } while (!$valid && $attempts < 100);
            
            if ($attempts >= 100) {
                throw new \Exception('Failed to generate valid private key after 100 attempts');
            }
            
            return $keyData;
            
        } catch (\Exception $e) {
            // Fallback to simpler entropy if complex method fails
            $simple = hash('sha256', 
                serialize($entropyData) . 
                $username . 
                $password . 
                microtime(true) . 
                bin2hex(random_bytes(32))
            );
            return $simple;
        }
    }
}
