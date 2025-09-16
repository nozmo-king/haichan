@extends('layout')

@section('title', 'Create Thread - ' . $board->code)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > 
    <a href="{{ route('forum.board', $board->code) }}">{{ $board->code }}</a> > 
    Create Thread
</div>

<h2>Create Thread in /{{ $board->code }}/</h2>

<form action="{{ route('forum.store', $board->code) }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="form-group">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required maxlength="255" value="{{ old('title') }}" 
               style="width: 100%; padding: 5px; margin: 5px 0;">
        @error('title')
            <div style="color: red; font-size: 12px;">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="content">Content:</label>
        <textarea name="content" id="content" rows="8" required maxlength="2000" 
                  style="width: 100%; padding: 5px; margin: 5px 0;">{{ old('content') }}</textarea>
        @error('content')
            <div style="color: red; font-size: 12px;">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="image">Image (optional):</label>
        <input type="file" name="image" id="image" accept="image/*" 
               style="width: 100%; padding: 5px; margin: 5px 0;">
        <small style="color: #666; font-size: 12px;">Max size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</small>
        @error('image')
            <div style="color: red; font-size: 12px;">{{ $message }}</div>
        @enderror
    </div>
    
    
    <!-- Proof of Work Mining Interface -->
    <div class="pow-mining-container" style="background: #F5F5DC; border: 2px solid #708B75; padding: 20px; margin: 20px 0; border-radius: 8px;">
        <h3 style="color: #444B6E; margin-bottom: 15px;">⛏️ Proof of Work Required</h3>
        
        <div class="mining-status" style="background: #FFFFEE; padding: 15px; border: 1px solid #708B75; margin-bottom: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-weight: bold; color: #444B6E;">Mining Status:</span>
                <span id="mining-status" style="color: #dc3545; font-weight: bold;">⛔ Not Started</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span>Hash Rate:</span>
                <span id="hash-rate" style="font-family: 'Courier New', monospace;">0 H/s</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span>Target Pattern:</span>
                <span id="target-pattern" style="font-family: 'Courier New', monospace; color: #708B75;">21e8</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Current Hash:</span>
                <span id="current-hash" style="font-family: 'Courier New', monospace; font-size: 10px; color: #666;">None</span>
            </div>
        </div>
        
        <div class="mining-controls" style="text-align: center; margin-bottom: 15px;">
            <button type="button" id="start-mining" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; margin-right: 10px;">🚀 Start Mining</button>
            <button type="button" id="stop-mining" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px;" disabled>⛔ Stop Mining</button>
        </div>
        
        <div class="mining-progress" style="width: 100%; height: 20px; background: #ddd; border-radius: 10px; overflow: hidden; margin-bottom: 15px;">
            <div id="mining-progress-bar" style="height: 100%; width: 0%; background: linear-gradient(90deg, #708B75, #5A7B5F); transition: width 0.3s ease;"></div>
        </div>
        
        <div class="mining-log" style="background: #FFFFEE; border: 1px solid #708B75; padding: 10px; height: 120px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 11px;">
            <div style="color: #708B75;">[SYSTEM] Mining engine ready. Click "Start Mining" to begin proof of work.</div>
        </div>
        
        <div style="color: #666; font-size: 12px; margin-top: 10px;">
            <strong>How it works:</strong> Your browser will mine SHA256 hashes until it finds one starting with "21e8". 
            This proves you've done computational work before creating the thread. Typical mining time: 30 seconds - 2 minutes.
        </div>
    </div>
    
    <!-- Hidden PoW fields -->
    <input type="hidden" name="pow_nonce" id="pow_nonce" required>
    <input type="hidden" name="pow_hash" id="pow_hash" required>
    <input type="hidden" name="pow_challenge_id" id="pow_challenge_id" required>
    
    <div class="form-group">
        <button type="submit" id="submit-thread" style="padding: 8px 16px; margin: 10px 0; background: #ccc; color: #666; border: none; border-radius: 4px;" disabled>Create Thread (Complete PoW First)</button>
        <a href="{{ route('forum.board', $board->code) }}" style="margin-left: 10px;">Cancel</a>
    </div>
</form>

<script>
class ThreadCreationMiner {
    constructor() {
        this.isMining = false;
        this.nonce = 0;
        this.challengeId = this.generateChallenge();
        this.hashCount = 0;
        this.startTime = 0;
        this.pattern = '21e8';
        this.miningInterval = null;
        this.statsInterval = null;
        
        // Set challenge ID
        document.getElementById('pow_challenge_id').value = this.challengeId;
        
        // Bind events
        document.getElementById('start-mining').addEventListener('click', () => this.startMining());
        document.getElementById('stop-mining').addEventListener('click', () => this.stopMining());
        
        // Prevent form submission until PoW is complete
        document.querySelector('form').addEventListener('submit', (e) => {
            if (!document.getElementById('pow_hash').value) {
                e.preventDefault();
                alert('Please complete the proof of work mining first!');
            }
        });
    }
    
    generateChallenge() {
        const array = new Uint8Array(16);
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }
    
    async startMining() {
        if (this.isMining) return;
        
        // Get form data for challenge
        const title = document.getElementById('title').value;
        const boardCode = '{{ $board->code }}';
        
        if (!title.trim()) {
            alert('Please enter a thread title before starting mining!');
            return;
        }
        
        this.isMining = true;
        this.startTime = Date.now();
        this.hashCount = 0;
        this.nonce = Math.floor(Math.random() * 1000000);
        
        // Update UI
        document.getElementById('start-mining').disabled = true;
        document.getElementById('stop-mining').disabled = false;
        document.getElementById('mining-status').innerHTML = '🔥 Mining Active';
        document.getElementById('mining-status').style.color = '#28a745';
        
        // Start mining
        this.mine();
        
        // Start stats updater
        this.statsInterval = setInterval(() => {
            this.updateStats();
        }, 1000);
        
        this.log('🚀 Started mining for thread creation...');
    }
    
    async mine() {
        const title = document.getElementById('title').value;
        const boardCode = '{{ $board->code }}';
        const challengeData = `thread:${boardCode}:${title}:${this.challengeId}`;
        
        while (this.isMining) {
            const testData = `${challengeData}:${this.nonce}`;
            const hash = await this.sha256(testData);
            
            this.hashCount++;
            document.getElementById('current-hash').textContent = hash;
            
            // Update progress bar based on hash attempts
            const progress = (this.hashCount % 1000) / 10;
            document.getElementById('mining-progress-bar').style.width = progress + '%';
            
            if (hash.startsWith(this.pattern)) {
                this.foundProof(hash);
                break;
            }
            
            this.nonce++;
            
            // Yield control occasionally to prevent browser freeze
            if (this.hashCount % 1000 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
    }
    
    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    
    foundProof(hash) {
        this.isMining = false;
        
        // Store PoW data
        document.getElementById('pow_nonce').value = this.nonce;
        document.getElementById('pow_hash').value = hash;
        
        // Update UI
        document.getElementById('mining-status').innerHTML = '✅ Proof Found!';
        document.getElementById('mining-status').style.color = '#28a745';
        document.getElementById('mining-progress-bar').style.width = '100%';
        document.getElementById('mining-progress-bar').style.background = 'linear-gradient(90deg, #28a745, #20c997)';
        
        // Enable submit button
        const submitBtn = document.getElementById('submit-thread');
        submitBtn.disabled = false;
        submitBtn.style.background = '#007bff';
        submitBtn.style.color = 'white';
        submitBtn.textContent = 'Create Thread';
        
        // Clear intervals
        if (this.statsInterval) {
            clearInterval(this.statsInterval);
        }
        
        this.log(`🎯 PROOF FOUND! Hash: ${hash}`);
        this.log(`✅ Thread creation unlocked! Click "Create Thread" to proceed.`);
        
        // Visual celebration
        this.celebrate();
    }
    
    celebrate() {
        // Flash the container
        const container = document.querySelector('.pow-mining-container');
        container.style.animation = 'flash 0.5s ease-in-out 3';
        
        // Add flash keyframes if not already present
        if (!document.querySelector('#flash-animation')) {
            const style = document.createElement('style');
            style.id = 'flash-animation';
            style.textContent = `
                @keyframes flash {
                    0%, 100% { background: #F5F5DC; }
                    50% { background: #e6f7e6; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    stopMining() {
        this.isMining = false;
        
        document.getElementById('start-mining').disabled = false;
        document.getElementById('stop-mining').disabled = true;
        document.getElementById('mining-status').innerHTML = '⛔ Mining Stopped';
        document.getElementById('mining-status').style.color = '#dc3545';
        
        if (this.statsInterval) {
            clearInterval(this.statsInterval);
        }
        
        this.log('⛔ Mining stopped by user');
    }
    
    updateStats() {
        if (this.startTime === 0) return;
        
        const elapsed = (Date.now() - this.startTime) / 1000;
        const hashRate = Math.floor(this.hashCount / elapsed);
        
        document.getElementById('hash-rate').textContent = `${hashRate.toLocaleString()} H/s`;
    }
    
    log(message) {
        const logContainer = document.querySelector('.mining-log');
        const entry = document.createElement('div');
        entry.style.color = message.includes('FOUND') ? '#28a745' : 
                          message.includes('stopped') ? '#dc3545' : '#708B75';
        entry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
        
        logContainer.appendChild(entry);
        logContainer.scrollTop = logContainer.scrollHeight;
        
        // Keep only last 20 entries
        while (logContainer.children.length > 20) {
            logContainer.removeChild(logContainer.firstChild);
        }
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.threadMiner = new ThreadCreationMiner();
});
</script>
@endsection