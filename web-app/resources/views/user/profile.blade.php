@extends('layout')

@section('title', '@' . $user->username . ' - Haichan Profile')

@section('content')
<div style="max-width: 900px; margin: 40px auto;">
    <!-- Profile Header -->
    <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 32px rgba(112, 139, 117, 0.2); margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #708B75, #5a7860); padding: 30px;">
            <div style="display: flex; align-items: center; gap: 30px;">
                <!-- Avatar -->
                <div style="width: 100px; height: 100px; border: 3px solid #F5F5DC; border-radius: 50%; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
                    @if($user->avatar_filename && Storage::disk('public')->exists('avatars/' . $user->avatar_filename))
                        <img src="{{ Storage::disk('public')->url('avatars/' . $user->avatar_filename) }}" 
                             alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <div style="font-size: 48px;">👤</div>
                    @endif
                </div>
                
                <!-- User Info -->
                <div style="flex: 1;">
                    <h1 style="margin: 0 0 10px 0; color: #F5F5DC; font-family: 'Nova Cut', serif; font-size: 32px; display: flex; align-items: center; gap: 10px;">
                        {{ $user->username }}
                        @if($user->is_admin)
                            <span style="background: #FFD700; color: #333; padding: 2px 8px; font-size: 12px; border-radius: 4px; font-weight: bold;">ADMIN</span>
                        @elseif($user->is_moderator)
                            <span style="background: #9370DB; color: white; padding: 2px 8px; font-size: 12px; border-radius: 4px; font-weight: bold;">MOD</span>
                        @endif
                    </h1>
                    <div style="color: #E8F5E9; font-family: monospace; font-size: 14px; margin-bottom: 10px;">
                        {{ $user->getTripcode() }}
                    </div>
                    <div style="display: flex; gap: 30px; color: #F5F5DC; font-size: 14px;">
                        <div>
                            <strong>⚡ Points:</strong> {{ number_format($user->accumulated_points ?? 0) }}
                        </div>
                        <div>
                            <strong>📅 Joined:</strong> {{ $user->created_at->format('M j, Y') }}
                        </div>
                        <div>
                            <strong>🔨 Proofs:</strong> {{ $user->proofOfWork()->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bio/Description (if we add it later) -->
        @if(isset($user->bio) && $user->bio)
        <div style="padding: 20px; background: #FFFACD; border-top: 1px solid #708B75;">
            <p style="margin: 0; color: #3D315B; font-style: italic;">{{ $user->bio }}</p>
        </div>
        @endif
    </div>

    <!-- Attestations Grid -->
    <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; padding: 30px; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.1);">
        <h2 style="margin: 0 0 25px 0; color: #3D315B; font-family: 'Nova Cut', serif; font-size: 24px; text-align: center;">
            🔐 Verified Identities & Wallets
        </h2>
        
        @if($attestations->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                @foreach($attestations as $attestation)
                <div style="background: #FFFACD; border: 2px solid {{ $attestation->is_verified ? '#28a745' : '#ccc' }}; border-radius: 8px; padding: 20px; position: relative; transition: all 0.3s ease;"
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.1)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    
                    @if($attestation->is_verified)
                        <div style="position: absolute; top: 10px; right: 10px; color: #28a745; font-size: 20px;" title="Verified">✓</div>
                    @endif
                    
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="font-size: 32px; width: 40px; text-align: center;">
                            {{ $attestation->getPlatformIcon() }}
                        </div>
                        <div>
                            <h3 style="margin: 0; color: #3D315B; font-size: 16px;">
                                {{ $attestation->getPlatformName() }}
                            </h3>
                            <div style="color: #6B7A6B; font-size: 14px; margin-top: 2px; word-break: break-all;">
                                {{ Str::limit($attestation->identifier, 30) }}
                            </div>
                        </div>
                    </div>
                    
                    @if($attestation->proof_url)
                        <a href="{{ $attestation->proof_url }}" target="_blank" 
                           style="display: inline-block; color: #708B75; text-decoration: none; font-size: 12px; padding: 6px 12px; background: #F5F5DC; border: 1px solid #708B75; border-radius: 4px; transition: all 0.3s ease;"
                           onmouseover="this.style.background='#708B75'; this.style.color='#F5F5DC';"
                           onmouseout="this.style.background='#F5F5DC'; this.style.color='#708B75';">
                            View Proof →
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #6B7A6B;">
                <div style="font-size: 48px; margin-bottom: 20px;">🔒</div>
                <p style="margin: 0; font-size: 16px;">No verified identities yet</p>
            </div>
        @endif
    </div>

    <!-- Personal 21e8 Mining -->
    <div style="margin-top: 30px; background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; padding: 30px; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.1);">
        <h2 style="margin: 0 0 25px 0; color: #3D315B; font-family: 'Nova Cut', serif; font-size: 24px; text-align: center;">
            ⛏️ Personal 21e8
        </h2>
        
        @if($user->personal_21e8_hash)
            <!-- User has found their 21e8 -->
            <div style="background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">🏆</div>
                <h3 style="color: #1a1a1a; margin: 0 0 10px 0;">21e8 FOUND!</h3>
                <div style="font-family: monospace; font-size: 14px; color: #1a1a1a; word-break: break-all; margin-bottom: 10px;">
                    {{ $user->personal_21e8_hash }}
                </div>
                <div style="color: #1a1a1a; font-size: 13px;">
                    <div>Found on: {{ $user->personal_21e8_found_at->format('M j, Y g:i A') }}</div>
                    <div>Mining time: {{ number_format($user->personal_21e8_mining_time, 2) }}s</div>
                    <div>Total hashes: {{ number_format($user->personal_21e8_total_hashes) }}</div>
                </div>
            </div>
        @else
            <!-- User hasn't found their 21e8 yet -->
            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">💎</div>
                <p style="color: #6B7A6B; font-size: 16px; margin-bottom: 20px;">
                    This user hasn't discovered their personal 21e8 yet
                </p>
                @if(session('bitcoin_auth_id') && session('bitcoin_auth_id') == $user->id)
                    <button onclick="startPersonalMining()" id="mine-button" style="padding: 12px 30px; background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">
                        Start Mining Your 21e8
                    </button>
                    
                    <!-- Mining Progress (hidden by default) -->
                    <div id="mining-progress" style="display: none; margin-top: 20px;">
                        <div style="font-size: 14px; color: #6B7A6B; margin-bottom: 10px;">Mining in progress...</div>
                        <div id="hash-display" style="font-family: monospace; font-size: 12px; color: #999; margin-bottom: 10px; word-break: break-all;"></div>
                        <div style="display: flex; justify-content: center; gap: 30px;">
                            <div>
                                <div id="hashrate" style="font-size: 20px; font-weight: bold; color: #708B75;">0 H/s</div>
                                <div style="font-size: 12px; color: #6B7A6B;">Hashrate</div>
                            </div>
                            <div>
                                <div id="total-hashes" style="font-size: 20px; font-weight: bold; color: #708B75;">0</div>
                                <div style="font-size: 12px; color: #6B7A6B;">Hashes</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Recent Activity section disabled -->
    <div style="display: none;"></div>

    <!-- Navigation -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="/" style="color: #708B75; text-decoration: none; font-weight: 600; padding: 10px 20px; border: 2px solid #708B75; border-radius: 6px; transition: all 0.3s ease; margin: 0 10px;"
           onmouseover="this.style.background='#708B75'; this.style.color='#F5F5DC';"
           onmouseout="this.style.background='transparent'; this.style.color='#708B75';">
            ← Back to Boards
        </a>
        @if(session('bitcoin_auth_id') && session('bitcoin_auth_id') == $user->id)
            <a href="{{ route('profile.show') }}" style="color: #708B75; text-decoration: none; font-weight: 600; padding: 10px 20px; border: 2px solid #708B75; border-radius: 6px; transition: all 0.3s ease; margin: 0 10px;"
               onmouseover="this.style.background='#708B75'; this.style.color='#F5F5DC';"
               onmouseout="this.style.background='transparent'; this.style.color='#708B75';">
                Edit Profile
            </a>
        @endif
    </div>
</div>

<style>
/* Custom scrollbar for activity feed */
div[style*="overflow-y: auto"]::-webkit-scrollbar {
    width: 8px;
}

div[style*="overflow-y: auto"]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb {
    background: #708B75;
    border-radius: 4px;
}

div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb:hover {
    background: #5a7860;
}
</style>

@if(session('bitcoin_auth_id') && session('bitcoin_auth_id') == $user->id)
<script>
// Personal 21e8 Mining
const userData = {
    id: {{ $user->id }},
    username: '{{ $user->username }}',
    publicKey: '{{ $user->public_key }}',
    address: '{{ $user->address }}'
};

let isMining = false;
let miningWorker = null;
let startTime = null;
let totalHashes = 0;
let hashrate = 0;
let statsInterval = null;

function generatePersonalTarget() {
    return userData.username + ':' + userData.id + ':' + userData.address.substring(0, 10);
}

const miningTarget = generatePersonalTarget();

async function startPersonalMining() {
    if (isMining) return;
    
    isMining = true;
    startTime = Date.now();
    totalHashes = 0;
    
    const button = document.getElementById('mine-button');
    button.textContent = 'Stop Mining';
    button.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
    
    document.getElementById('mining-progress').style.display = 'block';
    
    // Start hash rate tracking
    let lastHashes = 0;
    let lastTime = Date.now();
    
    statsInterval = setInterval(() => {
        const currentTime = Date.now();
        const timeDiff = (currentTime - lastTime) / 1000;
        hashrate = Math.round((totalHashes - lastHashes) / timeDiff);
        
        document.getElementById('hashrate').textContent = hashrate + ' H/s';
        document.getElementById('total-hashes').textContent = totalHashes.toLocaleString();
        
        lastHashes = totalHashes;
        lastTime = currentTime;
    }, 1000);
    
    // Start mining loop
    mine();
}

async function mine() {
    let nonce = 0;
    const encoder = new TextEncoder();
    
    while (isMining) {
        const data = miningTarget + ':' + nonce;
        const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(data));
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hash = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        
        totalHashes++;
        nonce++;
        
        // Update display every 100 hashes
        if (totalHashes % 100 === 0) {
            document.getElementById('hash-display').textContent = hash;
        }
        
        // Check for 21e8 (8 leading zeros)
        if (hash.startsWith('00000000')) {
            // SUCCESS!
            await handleSuccess(hash, nonce);
            stopMining();
            return;
        }
        
        // Yield to prevent blocking
        if (totalHashes % 1000 === 0) {
            await new Promise(resolve => setTimeout(resolve, 1));
        }
    }
}

function stopMining() {
    isMining = false;
    
    if (statsInterval) {
        clearInterval(statsInterval);
        statsInterval = null;
    }
    
    const button = document.getElementById('mine-button');
    button.textContent = 'Start Mining Your 21e8';
    button.style.background = 'linear-gradient(135deg, #708B75, #5a7860)';
}

async function handleSuccess(hash, nonce) {
    const miningTime = ((Date.now() - startTime) / 1000).toFixed(2);
    
    // Submit to server
    try {
        const response = await fetch('/api/self-mining/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                hash: hash,
                nonce: nonce,
                target: miningTarget,
                hashes: totalHashes,
                time: miningTime
            })
        });
        
        if (response.ok) {
            // Reload page to show the found hash
            window.location.reload();
        }
    } catch (error) {
        console.error('Failed to submit 21e8:', error);
        alert('Found 21e8 but failed to save. Please try again.');
    }
}
</script>
@endif
@endsection