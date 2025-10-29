<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class BitcoinAuth extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'bitcoin_auth';

    protected $fillable = [
        'public_key',
        'address',
        'username',
        'bio',
        'location',
        'website',
        'avatar_hash',
        'avatar_filename',
        'display_name',
        'tripcode',
        'social_links',
        'show_email',
        'email',
        'timezone',
        'signature',
        'profile_public',
        'mining_power',
        'total_pow_points',
        'invite_code',
        'invited_by',
        'last_login',
        'mining_streak',
        'level',
        'password_hash',
        'password_salt',
        'private_key_hash',
        'is_banned',
        'ban_reason',
        'banned_until',
        'personal_21e8_hash',
        'personal_21e8_nonce',
        'personal_21e8_total_hashes',
        'personal_21e8_mining_time',
        'personal_21e8_found_at',
        'is_admin',
        'is_moderator',
        'ssh_key',
        'avatar_path',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'banned_until' => 'datetime',
        'personal_21e8_found_at' => 'datetime',
        'mining_power' => 'decimal:2',
        'total_pow_points' => 'integer',
        'mining_streak' => 'integer',
        'level' => 'integer',
        'personal_21e8_nonce' => 'integer',
        'personal_21e8_total_hashes' => 'integer',
        'personal_21e8_mining_time' => 'float',
        'is_banned' => 'boolean',
        'is_admin' => 'boolean',
        'is_moderator' => 'boolean',
        'admin_level' => 'integer',
        'social_links' => 'json',
        'show_email' => 'boolean',
        'profile_public' => 'boolean',
    ];

    /**
     * Generate a REAL Bitcoin address from public key
     */
    public static function generateAddress($publicKey)
    {
        // Real Bitcoin address generation process

        // Step 1: SHA256 hash of public key
        $sha256 = hash('sha256', hex2bin($publicKey), true);

        // Step 2: RIPEMD160 hash of SHA256 result
        $ripemd160 = hash('ripemd160', $sha256, true);

        // Step 3: Add version byte (0x00 for Bitcoin mainnet)
        $versionedPayload = "\x00".$ripemd160;

        // Step 4: Double SHA256 for checksum
        $checksum = hash('sha256', hash('sha256', $versionedPayload, true), true);
        $checksum = substr($checksum, 0, 4);

        // Step 5: Concatenate version + payload + checksum
        $binaryAddress = $versionedPayload.$checksum;

        // Step 6: Base58 encode
        return self::base58encode($binaryAddress);
    }

    /**
     * Proper Base58 encoding for Bitcoin addresses
     */
    private static function base58encode($data)
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);

        // Convert binary to integer
        $num = gmp_init('0x'.bin2hex($data), 16);

        $encoded = '';
        while (gmp_cmp($num, 0) > 0) {
            [$num, $remainder] = gmp_div_qr($num, $base);
            $encoded = $alphabet[gmp_intval($remainder)].$encoded;
        }

        // Add leading '1's for leading zero bytes
        $leadingZeros = 0;
        for ($i = 0; $i < strlen($data) && $data[$i] === "\x00"; $i++) {
            $leadingZeros++;
        }

        return str_repeat('1', $leadingZeros).$encoded;
    }

    /**
     * Generate a cryptographically secure invite code
     */
    public static function generateInviteCode()
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';

        for ($i = 0; $i < 12; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $code;
    }

    /**
     * Create new Bitcoin auth user
     */
    public static function createUser($publicKey, $signature, $inviteCode = null, $passwordHash = null, $passwordSalt = null)
    {
        // Verify invite code if required
        if ($inviteCode && ! InviteCode::where('code', $inviteCode)->where('uses_remaining', '>', 0)->exists()) {
            throw new \Exception('Invalid invite code');
        }

        // Generate address
        $address = self::generateAddress($publicKey);

        // Hash private key for storage (we don't store the actual private key)
        $privateKeyHash = hash('sha256', $signature); // Using signature as private key stand-in

        // Create user
        $user = self::create([
            'public_key' => $publicKey,
            'address' => $address,
            'username' => 'Anon'.substr($address, -8),
            'mining_power' => 1.0,
            'total_pow_points' => 0,
            'invite_code' => self::generateInviteCode(),
            'invited_by' => $inviteCode,
            'last_login' => now(),
            'mining_streak' => 0,
            'level' => 1,
            'password_hash' => $passwordHash,
            'password_salt' => $passwordSalt,
            'private_key_hash' => $privateKeyHash,
            'is_banned' => false,
            'is_admin' => false,
            'admin_level' => 0,
        ]);

        // Check if this should be the first admin (user #1)
        if ($user->id === 1) {
            $user->is_admin = true;
            $user->admin_level = 5; // Administrator
            $user->username = 'Administrator';
            $user->mining_power = 10.0; // Admin bonus
            $user->save();
        }

        // Check if this should be a moderator (user #2)
        if ($user->id === 2) {
            $user->is_admin = true;
            $user->admin_level = 3; // Moderator
            $user->username = 'Moderator';
            $user->mining_power = 5.0; // Mod bonus
            $user->save();
        }

        // Generate 5 invite codes for new user
        for ($i = 0; $i < 5; $i++) {
            InviteCode::create([
                'code' => self::generateInviteCode(),
                'created_by' => $user->id,
                'uses_remaining' => 1,
            ]);
        }

        return $user;
    }

    /**
     * Verify Bitcoin signature
     */
    public function verifySignature($message, $signature)
    {
        // In production, use proper secp256k1 signature verification
        // For now, simplified verification
        $expectedHash = hash('sha256', $message.$this->public_key);

        return hash('sha256', $signature) === $expectedHash;
    }

    /**
     * Award mining points and update level
     */
    public function awardMiningPoints($points)
    {
        $this->total_pow_points += $points;

        // Update level based on total points
        $newLevel = 1 + floor($this->total_pow_points / 1000);

        if ($newLevel > $this->level) {
            $this->level = $newLevel;
            $this->mining_power = 1.0 + ($newLevel * 0.1); // 10% bonus per level
        }

        $this->save();

        return $newLevel > $this->level;
    }

    /**
     * Get invitation codes owned by this user
     */
    public function inviteCodes()
    {
        return $this->hasMany(InviteCode::class, 'created_by');
    }

    /**
     * Get users invited by this user
     */
    public function invitedUsers()
    {
        return $this->hasMany(self::class, 'invited_by', 'invite_code');
    }

    /**
     * Get mining leaderboard
     */
    public static function getLeaderboard($limit = 50)
    {
        return self::orderBy('total_pow_points', 'desc')
            ->orderBy('level', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate challenge for signature verification
     */
    public function generateChallenge()
    {
        $challenge = bin2hex(random_bytes(32));

        // Store challenge in session or cache
        session(['auth_challenge' => $challenge]);

        return $challenge;
    }

    /**
     * Check if user has admin privileges
     */
    public function isAdmin($requiredLevel = 1)
    {
        return $this->is_admin && $this->admin_level >= $requiredLevel;
    }

    /**
     * Promote user to admin
     */
    public function promoteToAdmin($level = 1)
    {
        $this->is_admin = true;
        $this->admin_level = $level;
        $this->save();
    }

    /**
     * Revoke admin privileges
     */
    public function revokeAdmin()
    {
        $this->is_admin = false;
        $this->admin_level = 0;
        $this->save();
    }

    /**
     * Ban user with reason
     */
    public function banUser($reason, $until = null)
    {
        $this->is_banned = true;
        $this->ban_reason = $reason;
        $this->banned_until = $until;
        $this->save();
    }

    /**
     * Generate tripcode from Bitcoin address
     */
    public function getTripcode()
    {
        if (! $this->address) {
            return 'Anonymous';
        }

        // Take last 8 characters of bitcoin address for tripcode
        $code = substr($this->address, -8);

        return "!{$code}";
    }

    /**
     * Get display name with tripcode
     */
    public function getDisplayName()
    {
        if ($this->username && $this->username !== 'Anonymous') {
            return $this->username.' '.$this->getTripcode();
        }

        return $this->getTripcode();
    }

    /**
     * Unban user
     */
    public function unbanUser()
    {
        $this->is_banned = false;
        $this->ban_reason = null;
        $this->banned_until = null;
        $this->save();
    }

    /**
     * User attestations relationship
     */
    public function attestations()
    {
        return $this->hasMany(UserAttestation::class, 'user_id');
    }

    /**
     * User proof of work submissions
     */
    public function proofOfWork()
    {
        return $this->hasMany(ProofOfWork::class, 'user_id');
    }
}
