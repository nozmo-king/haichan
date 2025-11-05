@extends('layout')

@section('title', 'Create Doodle - ' . $board->code)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('boards.index') }}">Boards</a> >
    <a href="{{ route('forum.board', $board->code) }}">{{ $board->code }}</a> >
    Create Doodle
</div>

<h2>Create Doodle in /{{ $board->code }}/</h2>

<form action="{{ route('forum.store', $board->code) }}" method="POST" id="doodle-form">
    @csrf

    <div class="form-group">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required maxlength="255" value="{{ old('title') }}"
               style="width: 100%; padding: 5px; margin: 5px 0;">
        @error('title')
            <div style="color: red; font-size: 12px;">{{ $message }}</div>
        @enderror
    </div>

    <!-- Doodle Canvas Area -->
    <div class="doodle-container" style="background: #f8f9fa; border: 2px solid #708B75; padding: 20px; margin: 20px 0; border-radius: 8px;">
        <h3 style="color: #444B6E; margin-bottom: 15px;">🎨 Doodle Canvas</h3>

        <!-- Color Palette -->
        <div class="color-palette" style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
            @php
                $colors = json_decode($board->doodle_config, true)['colors'] ?? [
                    '#000000', '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF'
                ];
            @endphp
            @foreach($colors as $index => $color)
                <div class="color-btn {{ $index === 0 ? 'active' : '' }}"
                     data-color="{{ $color }}"
                     style="width: 40px; height: 40px; background: {{ $color }}; border: 3px solid {{ $index === 0 ? '#333' : '#ccc' }}; border-radius: 50%; cursor: pointer; transition: all 0.2s;">
                </div>
            @endforeach
        </div>

        <!-- Canvas Controls -->
        <div class="canvas-controls" style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button type="button" id="clear-canvas" style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px;">🗑️ Clear</button>
            <button type="button" id="redo-btn" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px;" disabled>↶ Redo (0/3)</button>
            <span style="color: #666; font-size: 14px;">Brush Size:</span>
            <input type="range" id="brush-size" min="2" max="20" value="5" style="width: 100px;">
            <span id="brush-size-display" style="color: #666; font-size: 14px;">5px</span>
        </div>

        <!-- Canvas -->
        <div class="canvas-wrapper" style="position: relative; background: white; border: 2px solid #ccc; border-radius: 4px; overflow: hidden;">
            <canvas id="doodle-canvas"
                    style="display: block; cursor: crosshair; touch-action: none;"
                    width="800"
                    height="600">
                Your browser doesn't support canvas. Please use a modern browser.
            </canvas>
        </div>

        <div style="color: #666; font-size: 12px; margin-top: 10px;">
            <strong>Instructions:</strong> Use your mouse or finger to draw on the canvas. Select colors from the palette above.
            You can redo your last 3 drawing actions using the Redo button.
        </div>
    </div>

    <!-- Hidden field for doodle data -->
    <input type="hidden" name="doodle_data" id="doodle_data" required>

    @error('doodle')
        <div style="color: red; font-size: 12px; margin: 10px 0;">{{ $message }}</div>
    @enderror

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
            This proves you've done computational work before creating the doodle. Typical mining time: 30 seconds - 2 minutes.
        </div>
    </div>

    <!-- Hidden PoW fields -->
    <input type="hidden" name="pow_nonce" id="pow_nonce" required>
    <input type="hidden" name="pow_hash" id="pow_hash" required>
    <input type="hidden" name="pow_challenge_id" id="pow_challenge_id" required>

    <div class="form-group">
        <button type="submit" id="submit-thread" style="padding: 8px 16px; margin: 10px 0; background: #ccc; color: #666; border: none; border-radius: 4px;" disabled>Create Doodle (Complete PoW First)</button>
        <a href="{{ route('forum.board', $board->code) }}" style="margin-left: 10px;">Cancel</a>
    </div>
</form>

<script nonce="{{ app('csp_nonce') }}">
class DoodleCanvas {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.isDrawing = false;
        this.currentColor = '#000000';
        this.brushSize = 5;
        this.lastX = 0;
        this.lastY = 0;
        this.redoStack = [];
        this.maxRedoSteps = 3;

        this.setupCanvas();
        this.bindEvents();
        this.initializeCanvas();
    }

    setupCanvas() {
        // Make canvas responsive while maintaining aspect ratio
        this.resizeCanvas();

        // Set canvas drawing properties
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.imageSmoothingEnabled = true;

        window.addEventListener('resize', () => this.resizeCanvas());
    }

    resizeCanvas() {
        const container = this.canvas.parentElement;
        const containerWidth = container.clientWidth - 4; // Account for border
        const aspectRatio = 4/3; // 800/600

        let canvasWidth = Math.min(containerWidth, 800);
        let canvasHeight = canvasWidth / aspectRatio;

        // Ensure minimum size for mobile
        if (canvasWidth < 300) {
            canvasWidth = 300;
            canvasHeight = 225;
        }

        this.canvas.width = canvasWidth;
        this.canvas.height = canvasHeight;
        this.canvas.style.width = canvasWidth + 'px';
        this.canvas.style.height = canvasHeight + 'px';

        this.initializeCanvas();
    }

    initializeCanvas() {
        // Fill with white background
        this.ctx.fillStyle = '#FFFFFF';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        this.saveState();
    }

    bindEvents() {
        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
        this.canvas.addEventListener('mousemove', (e) => this.draw(e));
        this.canvas.addEventListener('mouseup', () => this.stopDrawing());
        this.canvas.addEventListener('mouseout', () => this.stopDrawing());

        // Touch events for mobile
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            this.startDrawing(this.getTouchPos(e));
        });
        this.canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            this.draw(this.getTouchPos(e));
        });
        this.canvas.addEventListener('touchend', (e) => {
            e.preventDefault();
            this.stopDrawing();
        });

        // Color palette
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', () => this.selectColor(btn));
        });

        // Controls
        document.getElementById('clear-canvas').addEventListener('click', () => this.clearCanvas());
        document.getElementById('redo-btn').addEventListener('click', () => this.redo());
        document.getElementById('brush-size').addEventListener('input', (e) => this.setBrushSize(e.target.value));
    }

    getMousePos(e) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;

        return {
            x: (e.clientX - rect.left) * scaleX,
            y: (e.clientY - rect.top) * scaleY
        };
    }

    getTouchPos(e) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;
        const touch = e.touches[0];

        return {
            x: (touch.clientX - rect.left) * scaleX,
            y: (touch.clientY - rect.top) * scaleY
        };
    }

    startDrawing(e) {
        this.isDrawing = true;
        const pos = e.x !== undefined ? e : this.getMousePos(e);
        [this.lastX, this.lastY] = [pos.x, pos.y];
    }

    draw(e) {
        if (!this.isDrawing) return;

        const pos = e.x !== undefined ? e : this.getMousePos(e);

        this.ctx.globalCompositeOperation = 'source-over';
        this.ctx.strokeStyle = this.currentColor;
        this.ctx.lineWidth = this.brushSize;

        this.ctx.beginPath();
        this.ctx.moveTo(this.lastX, this.lastY);
        this.ctx.lineTo(pos.x, pos.y);
        this.ctx.stroke();

        [this.lastX, this.lastY] = [pos.x, pos.y];
    }

    stopDrawing() {
        if (this.isDrawing) {
            this.isDrawing = false;
            this.saveState();
        }
    }

    selectColor(colorBtn) {
        // Remove active class from all color buttons
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.border = '3px solid #ccc';
        });

        // Add active class to selected button
        colorBtn.classList.add('active');
        colorBtn.style.border = '3px solid #333';

        this.currentColor = colorBtn.dataset.color;
    }

    setBrushSize(size) {
        this.brushSize = parseInt(size);
        document.getElementById('brush-size-display').textContent = size + 'px';
    }

    clearCanvas() {
        this.ctx.fillStyle = '#FFFFFF';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        this.saveState();
    }

    saveState() {
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        this.redoStack.push(imageData);

        // Keep only the last maxRedoSteps states
        if (this.redoStack.length > this.maxRedoSteps) {
            this.redoStack.shift();
        }

        this.updateRedoButton();
    }

    redo() {
        if (this.redoStack.length > 1) {
            this.redoStack.pop(); // Remove current state
            const previousState = this.redoStack[this.redoStack.length - 1];
            this.ctx.putImageData(previousState, 0, 0);
            this.updateRedoButton();
        }
    }

    updateRedoButton() {
        const redoBtn = document.getElementById('redo-btn');
        const availableRedos = Math.max(0, this.redoStack.length - 1);
        redoBtn.disabled = availableRedos === 0;
        redoBtn.textContent = `↶ Redo (${availableRedos}/${this.maxRedoSteps})`;
        redoBtn.style.background = availableRedos > 0 ? '#6c757d' : '#ccc';
    }

    getCanvasDataURL() {
        return this.canvas.toDataURL('image/png');
    }
}

class DoodleMiner {
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
        document.getElementById('doodle-form').addEventListener('submit', (e) => {
            if (!document.getElementById('pow_hash').value) {
                e.preventDefault();
                alert('Please complete the proof of work mining first!');
                return;
            }

            // Save doodle data before submission
            const doodleData = window.doodleCanvas.getCanvasDataURL();
            document.getElementById('doodle_data').value = doodleData;

            if (!doodleData || doodleData === 'data:,') {
                e.preventDefault();
                alert('Please create a doodle before submitting!');
                return;
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
            alert('Please enter a doodle title before starting mining!');
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

        this.log('🚀 Started mining for doodle creation...');
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
            
            // Update global mining state for toolbar
            if (window.haichanGlobalState) {
                window.haichanGlobalState.setState('mining.totalHashes', this.hashCount);
                window.haichanGlobalState.setState('mining.isActive', this.isMining);
                
                const elapsed = (Date.now() - this.startTime) / 1000;
                const currentHashRate = elapsed > 0 ? Math.floor(this.hashCount / elapsed) : 0;
                window.haichanGlobalState.setState('mining.hashrate', currentHashRate);
            }
            
            // Expose mining data globally for toolbar tracking
            const elapsed = (Date.now() - this.startTime) / 1000;
            window.currentMiner = {
                hashCount: this.hashCount,
                hashrate: elapsed > 0 ? Math.floor(this.hashCount / elapsed) : 0,
                isActive: this.isMining
            };

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
        submitBtn.textContent = 'Create Doodle';

        // Clear intervals
        if (this.statsInterval) {
            clearInterval(this.statsInterval);
        }

        // Update global mining state - mining completed
        if (window.haichanGlobalState) {
            window.haichanGlobalState.setState('mining.isActive', false);
            // Keep final hashrate and total for display
        }
        
        // Update global mining data - completed
        if (window.currentMiner) {
            window.currentMiner.isActive = false;
        }

        this.log(`🎯 PROOF FOUND! Hash: ${hash}`);
        this.log(`✅ Doodle creation unlocked! Click "Create Doodle" to proceed.`);

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

        // Update global mining state
        if (window.haichanGlobalState) {
            window.haichanGlobalState.setState('mining.isActive', false);
            window.haichanGlobalState.setState('mining.hashrate', 0);
        }
        
        // Clear global mining data
        if (window.currentMiner) {
            window.currentMiner.isActive = false;
            window.currentMiner.hashrate = 0;
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
    window.doodleCanvas = new DoodleCanvas('doodle-canvas');
    window.doodleMiner = new DoodleMiner();
});
</script>

<style>
/* Mobile optimizations */
@media (max-width: 768px) {
    .doodle-container {
        padding: 10px;
        margin: 10px 0;
    }

    .color-palette {
        justify-content: center;
    }

    .color-btn {
        width: 35px !important;
        height: 35px !important;
    }

    .canvas-controls {
        justify-content: center;
        flex-direction: column;
        gap: 5px;
    }

    .canvas-controls > * {
        margin: 2px;
    }

    .pow-mining-container {
        padding: 10px;
        margin: 10px 0;
    }

    .mining-status {
        padding: 10px;
    }

    .mining-status > div {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}

/* Touch-friendly improvements */
@media (pointer: coarse) {
    .color-btn {
        width: 45px !important;
        height: 45px !important;
    }

    button {
        min-height: 44px;
        padding: 12px 16px;
    }

    input[type="range"] {
        height: 30px;
    }
}
</style>
<script nonce="{{ app('csp_nonce') }}" src="/js/pow-emergency-fallback.js" defer></script>
@endsection
