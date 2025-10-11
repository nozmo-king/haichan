@extends('layout')

@section('title', 'Mining Test')

@section('content')
<div style="padding: 20px;">
    <h1>Mining System Test</h1>
    
    <div id="test-results" style="margin: 20px 0; padding: 20px; background: #f5f5dc; border: 2px solid #708B75; border-radius: 8px; font-family: monospace;">
        <h3>Test Results:</h3>
        <div id="results-content">Testing...</div>
    </div>
    
    <button onclick="runMiningTest()" style="padding: 10px 20px; background: #708B75; color: white; border: none; border-radius: 4px; cursor: pointer;">
        Run Mining Test
    </button>
</div>

<script>
async function runMiningTest() {
    const results = document.getElementById('results-content');
    let output = '';
    
    // Test 1: Check if mining systems are loaded
    output += '<strong>1. Mining Systems Check:</strong><br>';
    output += `- HaichanMiningBrain: ${!!window.HaichanMiningBrain ? '✅ Loaded' : '❌ Not loaded'}<br>`;
    output += `- haichanMiningBrain instance: ${!!window.haichanMiningBrain ? '✅ Available' : '❌ Not available'}<br>`;
    output += `- SimplePoW: ${!!window.simplePoW ? '✅ Available' : '❌ Not available'}<br>`;
    output += `- FallbackMining: ${!!window.fallbackMining ? '✅ Available' : '❌ Not available'}<br>`;
    output += '<br>';
    
    // Test 2: Try to acquire a proof
    output += '<strong>2. Proof Acquisition Test:</strong><br>';
    
    if (window.haichanMiningBrain && typeof window.haichanMiningBrain.acquireProofFor === 'function') {
        try {
            output += 'Attempting to mine proof-of-work...<br>';
            results.innerHTML = output + '<span style="color: orange;">⛏️ Mining in progress...</span>';
            
            const startTime = Date.now();
            const proof = await window.haichanMiningBrain.acquireProofFor({
                board_code: 'test',
                target_type: 'test',
                target_id: '1',
                action: 'test',
                difficulty: '21e8'
            });
            
            const miningTime = Date.now() - startTime;
            
            output += `✅ Proof acquired in ${miningTime}ms<br>`;
            output += `- Nonce: ${proof.nonce}<br>`;
            output += `- Hash: ${proof.hash}<br>`;
            output += `- Challenge ID: ${proof.challenge_id}<br>`;
            output += `- Valid 21e8: ${proof.hash.toLowerCase().startsWith('21e8') ? '✅ Yes' : '❌ No'}<br>`;
        } catch (error) {
            output += `❌ Mining failed: ${error.message}<br>`;
            console.error('Mining test error:', error);
        }
    } else {
        output += '❌ No mining system available with acquireProofFor method<br>';
    }
    
    results.innerHTML = output;
}

// Run test automatically after page load
window.addEventListener('load', () => {
    setTimeout(runMiningTest, 2000);
});
</script>
@endsection