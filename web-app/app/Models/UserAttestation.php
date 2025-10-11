<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAttestation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'identifier',
        'proof_url',
        'proof_content',
        'verification_hash',
        'is_verified',
        'verified_at'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Platform configuration
    public static $platforms = [
        'x' => [
            'name' => 'X (Twitter)',
            'icon' => '𝕏',
            'color' => '#000000',
            'proof_template' => 'Verifying myself on Haichan: I am {username} on Haichan with key {address}',
        ],
        'reddit' => [
            'name' => 'Reddit',
            'icon' => '🟠',
            'color' => '#FF4500',
            'proof_template' => 'Verifying for Haichan: I am {username} with Bitcoin address {address}',
        ],
        'github' => [
            'name' => 'GitHub',
            'icon' => '🐙',
            'color' => '#24292e',
            'proof_template' => 'haichan-proof: {username} = {address}',
        ],
        'ssh' => [
            'name' => 'SSH Key',
            'icon' => '🔑',
            'color' => '#2E7D32',
            'proof_template' => 'SSH public key fingerprint',
        ],
        'pgp' => [
            'name' => 'PGP Key',
            'icon' => '🔐',
            'color' => '#3F51B5',
            'proof_template' => 'PGP key fingerprint',
        ],
        'btc' => [
            'name' => 'Bitcoin',
            'icon' => '₿',
            'color' => '#F7931A',
            'proof_template' => 'Bitcoin address (sign message with key)',
        ],
        'zec' => [
            'name' => 'Zcash',
            'icon' => '🛡️',
            'color' => '#ECB244',
            'proof_template' => 'Zcash transparent address',
        ],
        'eth' => [
            'name' => 'Ethereum',
            'icon' => '⟠',
            'color' => '#627EEA',
            'proof_template' => 'Ethereum address (sign message)',
        ],
        'xrp' => [
            'name' => 'Ripple',
            'icon' => '💱',
            'color' => '#23292F',
            'proof_template' => 'XRP address',
        ],
        'xlm' => [
            'name' => 'Stellar',
            'icon' => '🚀',
            'color' => '#000000',
            'proof_template' => 'Stellar address',
        ],
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(BitcoinAuth::class, 'user_id');
    }

    // Helpers
    public function getPlatformConfig()
    {
        return self::$platforms[$this->platform] ?? null;
    }

    public function getPlatformName()
    {
        return $this->getPlatformConfig()['name'] ?? $this->platform;
    }

    public function getPlatformIcon()
    {
        return $this->getPlatformConfig()['icon'] ?? '❓';
    }

    public function getPlatformColor()
    {
        return $this->getPlatformConfig()['color'] ?? '#666666';
    }

    public function getProofTemplate()
    {
        $template = $this->getPlatformConfig()['proof_template'] ?? '';
        return str_replace(
            ['{username}', '{address}'],
            [$this->user->username, substr($this->user->address, 0, 20) . '...'],
            $template
        );
    }

    // Verification methods
    public function verify()
    {
        // Implement platform-specific verification logic
        $this->is_verified = true;
        $this->verified_at = now();
        $this->save();
        
        return true;
    }

    public function generateVerificationHash()
    {
        $data = $this->user_id . ':' . $this->platform . ':' . $this->identifier . ':' . time();
        $this->verification_hash = hash('sha256', $data);
        $this->save();
        
        return $this->verification_hash;
    }
}