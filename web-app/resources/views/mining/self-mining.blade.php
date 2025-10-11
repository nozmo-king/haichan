@extends('layout')

@section('title', 'Mine Yourself - Personal 21e8')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #F5F5DC, #E6D2AB); border: 2px solid #708B75; border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: #3D315B; margin: 0 0 10px 0;">
            ⛏️ Mine Yourself
        </h1>
        <p style="color: #6B7A6B; font-size: 16px; margin: 0;">
            Every user has their own unique 21e8 hash waiting to be discovered
        </p>
    </div>

    @if(session('bitcoin_auth_id'))
    <!-- User Mining Card -->
    <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; padding: 30px; margin-bottom: 20px;">
        <div style="text-align: center; margin-bottom: 25px;">
            <div style="font-size: 48px; margin-bottom: 10px;">👤</div>
            <h2 style="font-size: 24px; color: #3D315B; margin: 0 0 5px 0;">{{ session('bitcoin_auth_user')->username }}</h2>
            <p style="color: #6B7A6B; font-size: 14px;">User #{{ session('bitcoin_auth_id') }}/256</p>
        </div>

        <!-- Personal Hash Target -->
        <div style="background: rgba(112, 139, 117, 0.1); border: 1px solid #708B75; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #3D315B; font-size: 16px;">Your Personal Mining Target:</h3>
            <div id="personal-target" style="font-family: monospace; font-size: 14px; color: #708B75; word-break: break-all;">
                <!-- Will be generated from user data -->
            </div>
        </div>

        <!-- Mining Status -->
        <div id="mining-status" style="text-align: center; margin-bottom: 20px;">
            <div id="status-text" style="font-size: 18px; color: #6B7A6B; margin-bottom: 10px;">
                Ready to mine your personal 21e8
            </div>
            <div id="hash-display" style="font-family: monospace; font-size: 12px; color: #999; min-height: 20px;">
                <!-- Current hash being tested -->
            </div>
        </div>

        <!-- Mining Stats -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
            <div style="background: rgba(112, 139, 117, 0.1); border: 1px solid #708B75; border-radius: 8px; padding: 15px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75;" id="hashrate">0 H/s</div>
                <div style="font-size: 12px; color: #6B7A6B;">Hashrate</div>
            </div>
            <div style="background: rgba(112, 139, 117, 0.1); border: 1px solid #708B75; border-radius: 8px; padding: 15px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75;" id="total-hashes">0</div>
                <div style="font-size: 12px; color: #6B7A6B;">Total Hashes</div>
            </div>
            <div style="background: rgba(112, 139, 117, 0.1); border: 1px solid #708B75; border-radius: 8px; padding: 15px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75;" id="best-zeros">0</div>
                <div style="font-size: 12px; color: #6B7A6B;">Best Leading Zeros</div>
            </div>
        </div>

        <!-- Mining Button -->
        <button id="mine-button" onclick="toggleMining()" style="width: 100%; padding: 15px; background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; transition: all 0.3s;">
            Start Mining Yourself
        </button>

        <!-- Success Display -->
        <div id="success-display" style="display: none; margin-top: 20px; background: linear-gradient(135deg, #FFD700, #FFA500); border: 2px solid #FFD700; border-radius: 8px; padding: 20px; text-align: center;">
            <h3 style="color: #1a1a1a; margin: 0 0 10px 0;">🎉 21e8 FOUND!</h3>
            <div id="winning-hash" style="font-family: monospace; font-size: 14px; color: #1a1a1a; word-break: break-all; margin-bottom: 10px;"></div>
            <div id="mining-time" style="color: #1a1a1a; font-size: 14px;"></div>
            <div style="margin-top: 15px; font-size: 20px; color: #1a1a1a;">+1000 ⚡ Points!</div>
        </div>

        <!-- Previous Success -->
        @if(session('bitcoin_auth_user')->personal_21e8_hash)
        <div style="margin-top: 20px; background: rgba(255, 215, 0, 0.1); border: 1px solid #FFD700; border-radius: 8px; padding: 15px;">
            <h4 style="color: #3D315B; margin: 0 0 10px 0;">Your Previous 21e8:</h4>
            <div style="font-family: monospace; font-size: 12px; color: #708B75; word-break: break-all;">
                {{ session('bitcoin_auth_user')->personal_21e8_hash }}
            </div>
            <div style="font-size: 12px; color: #6B7A6B; margin-top: 5px;">
                Found on: {{ session('bitcoin_auth_user')->personal_21e8_found_at }}
            </div>
        </div>
        @endif
    </div>

    <!-- Leaderboard -->
    <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; padding: 20px;">
        <h3 style="color: #3D315B; margin: 0 0 20px 0;">🏆 Self-Mining Leaderboard</h3>
        <div id="leaderboard">
            Loading...
        </div>
    </div>

    @else
    <!-- Not logged in -->
    <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; padding: 40px; text-align: center;">
        <p style="color: #6B7A6B; font-size: 18px; margin-bottom: 20px;">
            Login to mine your personal 21e8 hash
        </p>
        <a href="/auth/login" style="display: inline-block; padding: 12px 30px; background: #708B75; color: #F5F5DC; text-decoration: none; border-radius: 8px; font-weight: bold;">
            Login Now
        </a>
    </div>
    @endif
</div>

<script>
@if(session('bitcoin_auth_id'))
// User data for mining
const userData = {
    id: {{ session('bitcoin_auth_id') }},
    username: '{{ session('bitcoin_auth_user')->username }}',
    publicKey: '{{ session('bitcoin_auth_user')->public_key }}',
    address: '{{ session('bitcoin_auth_user')->address }}'
};

// Mining state
let isMining = false;
let miningWorker = null;
let startTime = null;
let totalHashes = 0;
let bestZeros = 0;
let hashrate = 0;
let statsInterval = null;

// Generate personal mining target
function generatePersonalTarget() {
    const target = userData.username + ':' + userData.id + ':' + userData.address.substring(0, 10);
    document.getElementById('personal-target').textContent = target;
    return target;
}

// Initialize
const miningTarget = generatePersonalTarget();

// Toggle mining
function toggleMining() {
    if (isMining) {
        stopMining();
    } else {
        startMining();
    }
}

// Start mining
async function startMining() {
    isMining = true;
    startTime = Date.now();
    totalHashes = 0;
    bestZeros = 0;
    
    const button = document.getElementById('mine-button');
    button.textContent = 'Stop Mining';
    button.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
    
    document.getElementById('status-text').textContent = 'Mining in progress...';
    document.getElementById('success-display').style.display = 'none';
    
    // Start hash rate tracking
    let lastHashes = 0;
    let lastTime = Date.now();
    
    statsInterval = setInterval(() => {
        const currentTime = Date.now();
        const timeDiff = (currentTime - lastTime) / 1000;
        hashrate = Math.round((totalHashes - lastHashes) / timeDiff);
        
        document.getElementById('hashrate').textContent = hashrate + ' H/s';
        document.getElementById('total-hashes').textContent = totalHashes.toLocaleString();
        document.getElementById('best-zeros').textContent = bestZeros;
        
        lastHashes = totalHashes;
        lastTime = currentTime;
    }, 1000);
    
    // Start mining loop
    mine();
}

// Stop mining
function stopMining() {
    isMining = false;
    
    if (statsInterval) {
        clearInterval(statsInterval);
        statsInterval = null;
    }
    
    const button = document.getElementById('mine-button');
    button.textContent = 'Start Mining Yourself';
    button.style.background = 'linear-gradient(135deg, #708B75, #5a7860)';
    
    document.getElementById('status-text').textContent = 'Mining stopped';
}

// Mining function
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
        
        // Count leading zeros
        let zeros = 0;
        for (let i = 0; i < hash.length; i++) {
            if (hash[i] === '0') zeros++;
            else break;
        }
        
        if (zeros > bestZeros) {
            bestZeros = zeros;
        }
        
        // Update display every 100 hashes
        if (totalHashes % 100 === 0) {
            document.getElementById('hash-display').textContent = hash;
        }
        
        // Check for 21e8
        if (hash.startsWith('00000000')) { // 21e8 in decimal = 00000000 in hex (8 zeros)
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

// Handle successful mining
async function handleSuccess(hash, nonce) {
    const miningTime = ((Date.now() - startTime) / 1000).toFixed(2);
    
    // Display success
    document.getElementById('success-display').style.display = 'block';
    document.getElementById('winning-hash').textContent = hash;
    document.getElementById('mining-time').textContent = `Found in ${miningTime} seconds with ${totalHashes.toLocaleString()} hashes`;
    document.getElementById('status-text').textContent = '🎉 You found your 21e8!';
    
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
            const data = await response.json();
            console.log('21e8 submitted successfully:', data);
            
            // Refresh leaderboard
            loadLeaderboard();
        }
    } catch (error) {
        console.error('Failed to submit 21e8:', error);
    }
}

// Load leaderboard
async function loadLeaderboard() {
    try {
        const response = await fetch('/api/self-mining/leaderboard');
        const data = await response.json();
        
        const leaderboardDiv = document.getElementById('leaderboard');
        
        if (data.leaders && data.leaders.length > 0) {
            let html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
            
            data.leaders.forEach((leader, index) => {
                const isCurrentUser = leader.user_id == userData.id;
                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: ${isCurrentUser ? 'rgba(255, 215, 0, 0.2)' : 'rgba(112, 139, 117, 0.1)'}; border: 1px solid ${isCurrentUser ? '#FFD700' : '#708B75'}; border-radius: 6px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 18px; font-weight: bold; color: #708B75;">#${index + 1}</span>
                            <span style="font-weight: ${isCurrentUser ? 'bold' : 'normal'};">${leader.username}</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #6B7A6B;">${leader.mining_time}s</div>
                            <div style="font-size: 11px; color: #999;">${leader.total_hashes} hashes</div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            leaderboardDiv.innerHTML = html;
        } else {
            leaderboardDiv.innerHTML = '<p style="color: #999; text-align: center;">No one has found their 21e8 yet!</p>';
        }
    } catch (error) {
        console.error('Failed to load leaderboard:', error);
    }
}

// Load leaderboard on page load
loadLeaderboard();
@endif
</script>
@endsection