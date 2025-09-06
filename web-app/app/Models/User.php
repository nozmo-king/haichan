<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Crypto\Signature\Signer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'allowed_public_key_id',
        'last_challenge',
        'challenge_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'last_challenge',
    ];
    
    protected $casts = [
        'challenge_expires_at' => 'datetime',
    ];

    public function allowedPublicKey(): BelongsTo
    {
        return $this->belongsTo(AllowedPublicKey::class);
    }

    /**
     * Get the username for authentication
     */
    public function getUsername()
    {
        return $this->allowedPublicKey->public_key;
    }

    /**
     * Generate a challenge for authentication
     */
    public function generateChallenge(): string
    {
        $challenge = bin2hex(random_bytes(32)) . time();
        
        $this->update([
            'last_challenge' => $challenge,
            'challenge_expires_at' => Carbon::now()->addMinutes(5),
        ]);
        
        return $challenge;
    }

    /**
     * Verify signature against challenge
     */
    public function verifySignature(string $signature, string $challenge): bool
    {
        // Verify challenge is still valid and matches
        if (!$this->last_challenge || 
            $this->last_challenge !== $challenge ||
            $this->challenge_expires_at < Carbon::now()) {
            Log::warning('Challenge validation failed', [
                'has_last_challenge' => !empty($this->last_challenge),
                'challenge_matches' => $this->last_challenge === $challenge,
                'challenge_expired' => $this->challenge_expires_at < Carbon::now(),
                'expires_at' => $this->challenge_expires_at
            ]);
            return false;
        }
        
        Log::info('Challenge validation passed, starting signature verification');
        
        try {
            $generator = EccFactory::getSecgCurves()->generator256k1();
            $signer = new Signer($generator->getAdapter());
            
            $publicKeyHex = $this->allowedPublicKey->public_key;
            
            // Parse compressed public key
            $publicKeyData = hex2bin($publicKeyHex);
            $pubKeySerializer = new DerPublicKeySerializer();
            
            // For compressed format, need to reconstruct the full point
            $prefix = ord($publicKeyData[0]);
            $xCoord = gmp_init(bin2hex(substr($publicKeyData, 1)), 16);
            
            // Reconstruct the point from compressed format
            $curve = $generator->getCurve();
            $math = $generator->getAdapter();
            $ySquared = $math->add(
                $math->add(
                    $math->mul($curve->getA(), $xCoord),
                    $curve->getB()
                ),
                $math->pow($xCoord, 3)
            );
            $p = $curve->getPrime();
            
            // Calculate y coordinate
            $y = $math->powmod($ySquared, $math->div($math->add($p, gmp_init(1)), gmp_init(4)), $p);
            
            if ($math->mod($y, gmp_init(2)) != ($prefix - 2)) {
                $y = $math->sub($p, $y);
            }
            
            $point = $curve->getPoint($xCoord, $y);
            $publicKey = $generator->getPublicKeyFrom($xCoord, $y);
            
            // Hash the challenge
            $challengeHash = hash('sha256', $challenge, true);
            $hashInt = gmp_init(bin2hex($challengeHash), 16);
            
            // Parse signature - expecting raw hex format (r||s, 64 bytes each)
            if (strlen($signature) === 128) {
                // Raw hex format: first 64 chars = r, next 64 chars = s
                $rHex = substr($signature, 0, 64);
                $sHex = substr($signature, 64, 64);
                $r = gmp_init($rHex, 16);
                $s = gmp_init($sHex, 16);
                $sig = new \Mdanter\Ecc\Crypto\Signature\Signature($r, $s);
            } else {
                // Try DER format as fallback
                $sigSerializer = new DerSignatureSerializer();
                $sig = $sigSerializer->parse($signature);
            }
            
            Log::info('About to verify signature', [
                'challenge_hash' => bin2hex($challengeHash),
                'hash_int' => gmp_strval($hashInt, 16),
                'signature_r' => gmp_strval($sig->getR(), 16),
                'signature_s' => gmp_strval($sig->getS(), 16)
            ]);
            
            // Verify signature
            $result = $signer->verify($publicKey, $sig, $hashInt);
            
            Log::info('Signature verification result', ['result' => $result]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Signature verification exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Clear used challenge to prevent replay
     */
    public function clearChallenge(): void
    {
        $this->update([
            'last_challenge' => null,
            'challenge_expires_at' => null,
        ]);
    }

    /**
     * Create or find user for allowed public key
     */
    public static function findOrCreateForPublicKey(string $publicKey): ?self
    {
        $allowedKey = AllowedPublicKey::active()->where('public_key', $publicKey)->first();
        
        if (!$allowedKey) {
            return null;
        }
        
        return self::firstOrCreate(
            ['allowed_public_key_id' => $allowedKey->id]
        );
    }

    public function friendCodes(): HasMany
    {
        return $this->hasMany(FriendCode::class);
    }

    public function usedFriendCode(): HasOne
    {
        return $this->hasOne(FriendCode::class, 'used_by_user_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->currentSubscription()->exists();
    }
}
