// Doodle-based Proof of Work System
class DoodlePoW {
    constructor(canvasId, options = {}) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.isDrawing = false;
        this.strokes = [];
        this.currentStroke = [];
        this.entropy = 0;
        this.miningActive = false;
        
        // Options
        this.options = {
            width: options.width || 400,
            height: options.height || 300,
            strokeColor: options.strokeColor || '#708B75',
            strokeWidth: options.strokeWidth || 3,
            backgroundColor: options.backgroundColor || '#FFFACD',
            difficulty: options.difficulty || '21e8',
            onProofFound: options.onProofFound || null,
            onEntropyUpdate: options.onEntropyUpdate || null
        };
        
        this.setupCanvas();
        this.bindEvents();
    }
    
    setupCanvas() {
        // Set actual canvas dimensions
        this.canvas.width = this.options.width;
        this.canvas.height = this.options.height;
        
        // Also set CSS dimensions to match
        this.canvas.style.width = this.options.width + 'px';
        this.canvas.style.height = this.options.height + 'px';
        
        this.clearCanvas();
        
        // Style the canvas
        this.canvas.style.border = '2px solid #708B75';
        this.canvas.style.borderRadius = '8px';
        this.canvas.style.cursor = 'crosshair';
        this.canvas.style.touchAction = 'none'; // Prevent scrolling on touch
        this.canvas.style.display = 'block'; // Ensure it's visible
        
        console.log('Canvas setup complete:', this.canvas.width, 'x', this.canvas.height);
    }
    
    clearCanvas() {
        this.ctx.fillStyle = this.options.backgroundColor;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        this.strokes = [];
        this.entropy = 0;
        this.updateEntropy();
    }
    
    bindEvents() {
        // Mouse events
        this.canvas.addEventListener('mousedown', this.startDrawing.bind(this));
        this.canvas.addEventListener('mousemove', this.draw.bind(this));
        this.canvas.addEventListener('mouseup', this.stopDrawing.bind(this));
        this.canvas.addEventListener('mouseleave', this.stopDrawing.bind(this));
        
        // Touch events
        this.canvas.addEventListener('touchstart', this.handleTouch.bind(this));
        this.canvas.addEventListener('touchmove', this.handleTouch.bind(this));
        this.canvas.addEventListener('touchend', this.stopDrawing.bind(this));
    }
    
    handleTouch(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const rect = this.canvas.getBoundingClientRect();
        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;
        
        if (e.type === 'touchstart') {
            this.startDrawing({ offsetX: x, offsetY: y });
        } else if (e.type === 'touchmove') {
            this.draw({ offsetX: x, offsetY: y });
        }
    }
    
    startDrawing(e) {
        console.log('Start drawing at:', e.offsetX, e.offsetY);
        this.isDrawing = true;
        this.currentStroke = [{
            x: e.offsetX,
            y: e.offsetY,
            time: Date.now()
        }];
        
        this.ctx.beginPath();
        this.ctx.moveTo(e.offsetX, e.offsetY);
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth = this.options.strokeWidth;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
    }
    
    draw(e) {
        if (!this.isDrawing) return;
        
        this.ctx.lineTo(e.offsetX, e.offsetY);
        this.ctx.stroke();
        
        this.currentStroke.push({
            x: e.offsetX,
            y: e.offsetY,
            time: Date.now()
        });
        
        this.updateEntropy();
    }
    
    stopDrawing() {
        if (!this.isDrawing) return;
        
        this.isDrawing = false;
        if (this.currentStroke.length > 1) {
            this.strokes.push(this.currentStroke);
            this.currentStroke = [];
            this.updateEntropy(); // Make sure to update entropy after stroke
            
            console.log('Stroke completed. Total strokes:', this.strokes.length, 'Entropy:', this.entropy);
            
            // Start mining if we have enough entropy
            if (this.entropy > 50 && !this.miningActive) {
                console.log('Starting mining with entropy:', this.entropy);
                this.startMining();
            }
        }
    }
    
    calculateEntropy() {
        let entropy = 0;
        
        for (const stroke of this.strokes) {
            if (stroke.length < 2) continue;
            
            // Calculate path complexity
            let pathLength = 0;
            let curvature = 0;
            let velocity = 0;
            
            for (let i = 1; i < stroke.length; i++) {
                const dx = stroke[i].x - stroke[i-1].x;
                const dy = stroke[i].y - stroke[i-1].y;
                const dt = stroke[i].time - stroke[i-1].time;
                
                // Path length
                pathLength += Math.sqrt(dx * dx + dy * dy);
                
                // Velocity changes
                if (dt > 0) {
                    velocity += Math.sqrt(dx * dx + dy * dy) / dt;
                }
                
                // Angle changes (curvature)
                if (i > 1) {
                    const dx2 = stroke[i-1].x - stroke[i-2].x;
                    const dy2 = stroke[i-1].y - stroke[i-2].y;
                    
                    const angle1 = Math.atan2(dy, dx);
                    const angle2 = Math.atan2(dy2, dx2);
                    curvature += Math.abs(angle1 - angle2);
                }
            }
            
            // Combine factors for entropy
            entropy += pathLength * 0.1 + curvature * 10 + velocity * 100;
        }
        
        return Math.floor(entropy);
    }
    
    updateEntropy() {
        this.entropy = this.calculateEntropy();
        console.log('Entropy updated:', this.entropy);
        
        if (this.options.onEntropyUpdate) {
            this.options.onEntropyUpdate(this.entropy);
        }
    }
    
    async startMining() {
        if (this.miningActive) return;
        
        console.log('Mining started!');
        this.miningActive = true;
        
        // Generate seed from doodle data
        const doodleSeed = this.generateDoodleSeed();
        
        // Get challenge
        const challengeResp = await fetch('/api/mining/challenges', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                board_code: 'ddl',
                target_type: 'thread',
                action: 'create',
                difficulty: this.options.difficulty,
                doodle_entropy: this.entropy
            })
        });
        
        const challenge = await challengeResp.json();
        if (!challenge.success) {
            console.error('Challenge request failed:', challenge.error);
            this.miningActive = false;
            return;
        }
        
        // Mine with doodle-enhanced algorithm
        const encoder = new TextEncoder();
        const challengeData = JSON.stringify(challenge.canonical_payload);
        let nonce = 0;
        let found = false;
        
        while (!found && this.miningActive) {
            // Incorporate doodle seed into mining
            const data = challengeData + ':' + doodleSeed + ':' + nonce;
            const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(data));
            const hashArray = new Uint8Array(hashBuffer);
            const hash = Array.from(hashArray).map(b => b.toString(16).padStart(2, '0')).join('');
            
            if (hash.startsWith(this.options.difficulty)) {
                found = true;
                
                const proof = {
                    nonce: nonce.toString(),
                    hash: hash,
                    challenge_id: challenge.token,
                    doodle_data: this.exportDoodle(),
                    entropy: this.entropy
                };
                
                if (this.options.onProofFound) {
                    this.options.onProofFound(proof);
                }
            }
            
            nonce++;
            
            // Use entropy to skip nonces (makes doodles matter more)
            if (this.entropy > 100) {
                nonce += Math.floor(this.entropy / 100);
            }
            
            // UI update
            if (nonce % 1000 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        this.miningActive = false;
    }
    
    generateDoodleSeed() {
        // Create a unique seed from the doodle strokes
        let seed = '';
        
        for (const stroke of this.strokes) {
            for (let i = 0; i < stroke.length; i += 5) { // Sample every 5th point
                if (stroke[i]) {
                    seed += Math.floor(stroke[i].x) + ',' + Math.floor(stroke[i].y) + ';';
                }
            }
        }
        
        // Add canvas image data for extra uniqueness
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        const pixels = imageData.data;
        
        // Sample pixels at regular intervals
        for (let i = 0; i < pixels.length; i += 1000) {
            seed += pixels[i].toString(16);
        }
        
        return seed;
    }
    
    exportDoodle() {
        return {
            strokes: this.strokes,
            entropy: this.entropy,
            canvas: this.canvas.toDataURL('image/png'),
            timestamp: Date.now()
        };
    }
    
    stopMining() {
        this.miningActive = false;
    }
    
    // Set drawing color
    setColor(color) {
        this.options.strokeColor = color;
    }
    
    // Drawing preset shapes
    drawSpiral() {
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const maxRadius = Math.min(centerX, centerY) - 20;
        
        this.ctx.beginPath();
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth = this.options.strokeWidth;
        
        this.currentStroke = [];
        for (let angle = 0; angle < Math.PI * 8; angle += 0.1) {
            const radius = (angle / (Math.PI * 8)) * maxRadius;
            const x = centerX + radius * Math.cos(angle);
            const y = centerY + radius * Math.sin(angle);
            
            if (angle === 0) {
                this.ctx.moveTo(x, y);
            } else {
                this.ctx.lineTo(x, y);
            }
            
            this.currentStroke.push({ x, y, time: Date.now() });
        }
        
        this.ctx.stroke();
        this.strokes.push(this.currentStroke);
        this.currentStroke = [];
        this.updateEntropy();
    }
    
    drawStar() {
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const spikes = 5;
        const outerRadius = Math.min(centerX, centerY) - 20;
        const innerRadius = outerRadius / 2;
        
        this.ctx.beginPath();
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth = this.options.strokeWidth;
        
        this.currentStroke = [];
        for (let i = 0; i < spikes * 2; i++) {
            const radius = i % 2 === 0 ? outerRadius : innerRadius;
            const angle = (i * Math.PI) / spikes;
            const x = centerX + Math.cos(angle - Math.PI/2) * radius;
            const y = centerY + Math.sin(angle - Math.PI/2) * radius;
            
            if (i === 0) {
                this.ctx.moveTo(x, y);
            } else {
                this.ctx.lineTo(x, y);
            }
            
            this.currentStroke.push({ x, y, time: Date.now() });
        }
        
        this.ctx.closePath();
        this.ctx.stroke();
        this.strokes.push(this.currentStroke);
        this.currentStroke = [];
        this.updateEntropy();
    }
    
    drawSmiley() {
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const radius = Math.min(centerX, centerY) - 30;
        
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth = this.options.strokeWidth;
        
        // Face circle
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        this.ctx.stroke();
        
        // Eyes
        const eyeY = centerY - radius / 3;
        const eyeOffset = radius / 3;
        
        this.ctx.beginPath();
        this.ctx.arc(centerX - eyeOffset, eyeY, 5, 0, Math.PI * 2);
        this.ctx.fill();
        
        this.ctx.beginPath();
        this.ctx.arc(centerX + eyeOffset, eyeY, 5, 0, Math.PI * 2);
        this.ctx.fill();
        
        // Smile
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, radius * 0.6, 0.2 * Math.PI, 0.8 * Math.PI);
        this.ctx.stroke();
        
        // Generate stroke data
        this.currentStroke = [];
        for (let angle = 0; angle < Math.PI * 2; angle += 0.1) {
            const x = centerX + radius * Math.cos(angle);
            const y = centerY + radius * Math.sin(angle);
            this.currentStroke.push({ x, y, time: Date.now() });
        }
        
        this.strokes.push(this.currentStroke);
        this.currentStroke = [];
        this.updateEntropy();
    }
    
    draw3DSquare() {
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const size = Math.min(this.canvas.width, this.canvas.height) / 3;
        const depth = size / 3;
        
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth = this.options.strokeWidth;
        
        // Front face
        this.ctx.beginPath();
        this.ctx.rect(centerX - size/2, centerY - size/2, size, size);
        this.ctx.stroke();
        
        // Back face
        this.ctx.beginPath();
        this.ctx.rect(centerX - size/2 + depth, centerY - size/2 - depth, size, size);
        this.ctx.stroke();
        
        // Connect corners
        this.ctx.beginPath();
        // Top left
        this.ctx.moveTo(centerX - size/2, centerY - size/2);
        this.ctx.lineTo(centerX - size/2 + depth, centerY - size/2 - depth);
        // Top right
        this.ctx.moveTo(centerX + size/2, centerY - size/2);
        this.ctx.lineTo(centerX + size/2 + depth, centerY - size/2 - depth);
        // Bottom left
        this.ctx.moveTo(centerX - size/2, centerY + size/2);
        this.ctx.lineTo(centerX - size/2 + depth, centerY + size/2 - depth);
        // Bottom right
        this.ctx.moveTo(centerX + size/2, centerY + size/2);
        this.ctx.lineTo(centerX + size/2 + depth, centerY + size/2 - depth);
        this.ctx.stroke();
        
        // Generate stroke data
        this.currentStroke = [];
        const points = [
            [centerX - size/2, centerY - size/2],
            [centerX + size/2, centerY - size/2],
            [centerX + size/2, centerY + size/2],
            [centerX - size/2, centerY + size/2],
            [centerX - size/2, centerY - size/2]
        ];
        
        points.forEach(([x, y]) => {
            this.currentStroke.push({ x, y, time: Date.now() });
        });
        
        this.strokes.push(this.currentStroke);
        this.currentStroke = [];
        this.updateEntropy();
    }
    
    drawCat() {
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const size = Math.min(this.canvas.width, this.canvas.height) / 3;
        
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth = this.options.strokeWidth;
        
        // Head (circle)
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, size, 0, Math.PI * 2);
        this.ctx.stroke();
        
        // Ears (triangles)
        this.ctx.beginPath();
        // Left ear
        this.ctx.moveTo(centerX - size * 0.7, centerY - size * 0.7);
        this.ctx.lineTo(centerX - size * 0.9, centerY - size * 1.3);
        this.ctx.lineTo(centerX - size * 0.3, centerY - size * 0.9);
        // Right ear
        this.ctx.moveTo(centerX + size * 0.7, centerY - size * 0.7);
        this.ctx.lineTo(centerX + size * 0.9, centerY - size * 1.3);
        this.ctx.lineTo(centerX + size * 0.3, centerY - size * 0.9);
        this.ctx.stroke();
        
        // Eyes
        this.ctx.beginPath();
        this.ctx.arc(centerX - size * 0.3, centerY - size * 0.2, size * 0.1, 0, Math.PI * 2);
        this.ctx.arc(centerX + size * 0.3, centerY - size * 0.2, size * 0.1, 0, Math.PI * 2);
        this.ctx.fill();
        
        // Nose (triangle)
        this.ctx.beginPath();
        this.ctx.moveTo(centerX, centerY);
        this.ctx.lineTo(centerX - size * 0.1, centerY + size * 0.2);
        this.ctx.lineTo(centerX + size * 0.1, centerY + size * 0.2);
        this.ctx.closePath();
        this.ctx.fill();
        
        // Whiskers
        this.ctx.beginPath();
        // Left whiskers
        this.ctx.moveTo(centerX - size * 0.5, centerY);
        this.ctx.lineTo(centerX - size * 1.2, centerY - size * 0.1);
        this.ctx.moveTo(centerX - size * 0.5, centerY + size * 0.1);
        this.ctx.lineTo(centerX - size * 1.2, centerY + size * 0.1);
        // Right whiskers
        this.ctx.moveTo(centerX + size * 0.5, centerY);
        this.ctx.lineTo(centerX + size * 1.2, centerY - size * 0.1);
        this.ctx.moveTo(centerX + size * 0.5, centerY + size * 0.1);
        this.ctx.lineTo(centerX + size * 1.2, centerY + size * 0.1);
        this.ctx.stroke();
        
        // Generate entropy
        this.currentStroke = [];
        for (let i = 0; i < 50; i++) {
            this.currentStroke.push({ 
                x: centerX + (Math.random() - 0.5) * size * 2, 
                y: centerY + (Math.random() - 0.5) * size * 2, 
                time: Date.now() 
            });
        }
        
        this.strokes.push(this.currentStroke);
        this.currentStroke = [];
        this.updateEntropy();
    }
    
    drawFrog() {
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const size = Math.min(this.canvas.width, this.canvas.height) / 3;
        
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth = this.options.strokeWidth;
        this.ctx.fillStyle = this.options.strokeColor;
        
        // Body (wide ellipse)
        this.ctx.beginPath();
        this.ctx.ellipse(centerX, centerY + size * 0.3, size * 1.2, size * 0.8, 0, 0, Math.PI * 2);
        this.ctx.stroke();
        
        // Head (wider at top)
        this.ctx.beginPath();
        this.ctx.moveTo(centerX - size * 0.8, centerY);
        this.ctx.quadraticCurveTo(centerX - size * 0.9, centerY - size * 0.8, centerX, centerY - size * 0.9);
        this.ctx.quadraticCurveTo(centerX + size * 0.9, centerY - size * 0.8, centerX + size * 0.8, centerY);
        this.ctx.stroke();
        
        // Eyes (big circles on top)
        this.ctx.beginPath();
        this.ctx.arc(centerX - size * 0.4, centerY - size * 0.7, size * 0.25, 0, Math.PI * 2);
        this.ctx.stroke();
        this.ctx.beginPath();
        this.ctx.arc(centerX + size * 0.4, centerY - size * 0.7, size * 0.25, 0, Math.PI * 2);
        this.ctx.stroke();
        
        // Eye pupils
        this.ctx.beginPath();
        this.ctx.arc(centerX - size * 0.4, centerY - size * 0.7, size * 0.1, 0, Math.PI * 2);
        this.ctx.fill();
        this.ctx.beginPath();
        this.ctx.arc(centerX + size * 0.4, centerY - size * 0.7, size * 0.1, 0, Math.PI * 2);
        this.ctx.fill();
        
        // Mouth (wide smile)
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY - size * 0.2, size * 0.6, 0.2 * Math.PI, 0.8 * Math.PI);
        this.ctx.stroke();
        
        // Front legs
        this.ctx.beginPath();
        this.ctx.moveTo(centerX - size * 0.5, centerY + size * 0.3);
        this.ctx.lineTo(centerX - size * 0.6, centerY + size * 0.8);
        this.ctx.moveTo(centerX + size * 0.5, centerY + size * 0.3);
        this.ctx.lineTo(centerX + size * 0.6, centerY + size * 0.8);
        this.ctx.stroke();
        
        // Generate high entropy for our amphibious friend
        this.currentStroke = [];
        for (let i = 0; i < 100; i++) {
            const angle = (i / 100) * Math.PI * 2;
            const wobble = Math.sin(angle * 8) * 10;
            this.currentStroke.push({ 
                x: centerX + Math.cos(angle) * (size + wobble), 
                y: centerY + Math.sin(angle) * (size + wobble), 
                time: Date.now() 
            });
        }
        
        this.strokes.push(this.currentStroke);
        this.currentStroke = [];
        this.updateEntropy();
    }
}

// Export for use
window.DoodlePoW = DoodlePoW;