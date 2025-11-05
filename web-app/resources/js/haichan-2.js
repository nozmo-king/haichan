/**
 * HAICHAN 2.0 - QUANTUM MINING SYSTEM
 * Next-generation consciousness-enhanced mining with dimensional traversal
 */

class Haichan2 extends HaichanComplete {
    constructor() {
        super();
        this.quantumEnabled = false;
        this.quantumState = {
            energy: 100,
            neural_sync: 0,
            quantum_coherence: 1.0,
            dimensional_phase: 0,
            active_mechanic: null
        };
        this.quantumMechanics = {
            'quantum_mining': { 
                name: 'Quantum Mining', 
                icon: '⚛️', 
                description: 'Enhanced mining algorithm with 2.5x speed boost',
                cost: 15,
                duration: 60
            },
            'neural_sync': { 
                name: 'Neural Sync', 
                icon: '🧠', 
                description: 'AI-assisted pattern recognition system',
                cost: 25,
                duration: 120
            },
            'hash_cascade': { 
                name: 'Hash Cascade', 
                icon: '💫', 
                description: 'Parallel processing for chain hash discovery',
                cost: 20,
                duration: 90
            },
            'proof_fusion': { 
                name: 'Proof Fusion', 
                icon: '🔮', 
                description: 'Merge multiple hash calculations for bonus points',
                cost: 30,
                duration: 45
            },
            'dimensional_shift': { 
                name: 'Dimensional Shift', 
                icon: '🌀', 
                description: 'Advanced threading for parallel mining operations',
                cost: 50,
                duration: 180
            }
        };
        this.dimensionalLayers = [];
        this.neuralEvolution = {
            level: 1,
            consciousness: 100,
            neural_pathways: 10,
            evolution_points: 0
        };
        this.initializeQuantum();
    }

    async initializeQuantum() {
        await this.checkQuantumAccess();
        if (this.quantumEnabled) {
            this.setupQuantumUI();
            this.setupQuantumEventListeners();
            this.startQuantumAnimations();
            this.initializeNeuralNetwork();
        }
    }

    async checkQuantumAccess() {
        try {
            const response = await fetch('/api/quantum/initialize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.quantumEnabled = true;
                this.quantumState = data.quantum_system;
                this.showQuantumActivation();
                this.updateQuantumUI(data);
            }
        } catch (error) {
            console.warn('Quantum access check failed:', error);
        }
    }

    setupQuantumUI() {
        // Create quantum interface
        const dashboard = document.getElementById('mining-dashboard');
        if (!dashboard) return;

        const quantumSection = document.createElement('div');
        quantumSection.className = 'quantum-section';
        quantumSection.innerHTML = `
            <div class="quantum-header">
                <h3>🌌 HAICHAN 2.0 - QUANTUM SYSTEM</h3>
                <div class="quantum-status" id="quantum-status">ONLINE</div>
            </div>
            
            <div class="quantum-energy-bar">
                <div class="energy-label">Quantum Energy</div>
                <div class="energy-bar">
                    <div class="energy-fill" id="quantum-energy-fill"></div>
                    <div class="energy-text" id="quantum-energy-text">100/100</div>
                </div>
            </div>

            <div class="quantum-mechanics" id="quantum-mechanics">
                <h4>⚛️ Quantum Mechanics</h4>
                <div class="mechanics-grid" id="mechanics-grid"></div>
            </div>

            <div class="neural-evolution" id="neural-evolution">
                <h4>🧠 Neural Evolution</h4>
                <div class="evolution-stats">
                    <div class="stat">
                        <span>Consciousness Level:</span>
                        <span id="consciousness-level">1</span>
                    </div>
                    <div class="stat">
                        <span>Neural Pathways:</span>
                        <span id="neural-pathways">10</span>
                    </div>
                    <div class="stat">
                        <span>Evolution Points:</span>
                        <span id="evolution-points">0</span>
                    </div>
                </div>
            </div>

            <div class="dimensional-interface" id="dimensional-interface" style="display: none;">
                <h4>🌀 Dimensional Interface</h4>
                <div class="dimension-selector" id="dimension-selector"></div>
                <button class="dimensional-shift-btn" id="dimensional-shift-btn">
                    🌀 ACCESS DIMENSIONAL LAYERS
                </button>
            </div>

            <div class="quantum-visualizer" id="quantum-visualizer">
                <canvas id="quantum-canvas" width="300" height="200"></canvas>
            </div>
        `;

        dashboard.appendChild(quantumSection);
        this.renderQuantumMechanics();
        this.updateQuantumEnergyBar();
    }

    renderQuantumMechanics() {
        const mechanicsGrid = document.getElementById('mechanics-grid');
        if (!mechanicsGrid) return;

        mechanicsGrid.innerHTML = '';
        
        Object.entries(this.quantumMechanics).forEach(([key, mechanic]) => {
            const mechanicElement = document.createElement('div');
            mechanicElement.className = 'quantum-mechanic';
            mechanicElement.innerHTML = `
                <div class="mechanic-icon">${mechanic.icon}</div>
                <div class="mechanic-name">${mechanic.name}</div>
                <div class="mechanic-cost">Energy: ${mechanic.cost}</div>
                <button class="activate-mechanic-btn" data-mechanic="${key}">
                    ACTIVATE
                </button>
            `;
            mechanicsGrid.appendChild(mechanicElement);
        });
    }

    setupQuantumEventListeners() {
        // Quantum mechanic activation
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('activate-mechanic-btn')) {
                const mechanic = e.target.dataset.mechanic;
                this.activateQuantumMechanic(mechanic);
            }
        });

        // Dimensional shift button
        const dimensionalBtn = document.getElementById('dimensional-shift-btn');
        if (dimensionalBtn) {
            dimensionalBtn.addEventListener('click', () => this.accessDimensionalMining());
        }

        // Neural synthesis triggers
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Q' && e.ctrlKey) {
                e.preventDefault();
                this.performQuantumBoost();
            }
            if (e.key === 'N' && e.ctrlKey) {
                e.preventDefault();
                this.triggerNeuralSynthesis();
            }
        });
    }

    async activateQuantumMechanic(mechanicKey) {
        const mechanic = this.quantumMechanics[mechanicKey];
        if (!mechanic) return;

        if (this.quantumState.energy < mechanic.cost) {
            this.showQuantumNotification('⚠️ Insufficient Quantum Energy', `Need ${mechanic.cost} energy, have ${this.quantumState.energy}`, '#FF6B35');
            return;
        }

        try {
            const response = await fetch('/api/quantum/activate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ mechanic: mechanicKey })
            });

            const result = await response.json();
            
            if (result.success) {
                this.quantumState.energy = result.quantum_mechanic.energy_remaining;
                this.quantumState.active_mechanic = mechanicKey;
                this.updateQuantumEnergyBar();
                this.showQuantumMechanicActivation(mechanic, result.quantum_mechanic.duration);
                this.startQuantumMechanicEffect(mechanicKey, result.quantum_mechanic.duration);
            }
        } catch (error) {
            console.error('Quantum mechanic activation failed:', error);
        }
    }

    startQuantumMechanicEffect(mechanicKey, duration) {
        // Visual effects for active quantum mechanic
        const effectElement = document.createElement('div');
        effectElement.className = 'active-quantum-effect';
        effectElement.innerHTML = `
            <div class="quantum-effect-icon">${this.quantumMechanics[mechanicKey].icon}</div>
            <div class="quantum-effect-name">${this.quantumMechanics[mechanicKey].name}</div>
            <div class="quantum-effect-timer" id="quantum-timer-${mechanicKey}">${duration}s</div>
        `;

        effectElement.style.cssText = `
            position: fixed;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 12px;
            border: 2px solid #9d50bb;
            box-shadow: 0 0 20px rgba(157, 80, 187, 0.5);
            z-index: 10000;
            font-family: 'Courier New', monospace;
            animation: quantumPulse 2s infinite;
        `;

        document.body.appendChild(effectElement);

        // Countdown timer
        let timeLeft = duration;
        const timer = setInterval(() => {
            timeLeft--;
            const timerElement = document.getElementById(`quantum-timer-${mechanicKey}`);
            if (timerElement) {
                timerElement.textContent = `${timeLeft}s`;
            }

            if (timeLeft <= 0) {
                clearInterval(timer);
                effectElement.remove();
                this.quantumState.active_mechanic = null;
                this.showQuantumNotification('⏰ Quantum Mechanic Expired', `${this.quantumMechanics[mechanicKey].name} effect ended`, '#FFA500');
            }
        }, 1000);

        // Add quantum pulse animation
        if (!document.querySelector('#quantum-pulse-animation')) {
            const style = document.createElement('style');
            style.id = 'quantum-pulse-animation';
            style.textContent = `
                @keyframes quantumPulse {
                    0%, 100% { box-shadow: 0 0 20px rgba(157, 80, 187, 0.5); }
                    50% { box-shadow: 0 0 40px rgba(157, 80, 187, 0.8); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    async submitQuantumProof(proof, target, type) {
        if (!this.quantumEnabled) {
            return await super.submitProof(proof, target, type);
        }

        try {
            const quantumSignature = await this.generateQuantumSignature(proof);
            
            const response = await fetch('/api/quantum/proof', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    hash: proof.hash,
                    nonce: proof.nonce,
                    data: proof.data,
                    quantum_signature: quantumSignature,
                    dimensional_phase: this.quantumState.dimensional_phase,
                    neural_enhancement: this.quantumState.neural_sync > 0
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.handleQuantumProofSuccess(result);
                this.updateNeuralEvolution(result.quantum_result.quantum_xp_earned);
            }

            return result;
        } catch (error) {
            console.error('Quantum proof submission failed:', error);
            return { success: false };
        }
    }

    async generateQuantumSignature(proof) {
        const data = proof.hash + proof.nonce + 'quantum';
        const encoder = new TextEncoder();
        const dataBuffer = encoder.encode(data);
        const hashBuffer = await crypto.subtle.digest('SHA-256', dataBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    handleQuantumProofSuccess(result) {
        const quantumResult = result.quantum_result;
        
        // Show quantum success animation
        this.triggerQuantumSuccessEffect(quantumResult);
        
        // Display reality shift information
        if (result.reality_shift) {
            this.showRealityShiftNotification(result.reality_shift);
        }

        // Update quantum stats
        this.updateQuantumStats(quantumResult);
    }

    triggerQuantumSuccessEffect(quantumResult) {
        // Create quantum success visual
        const effect = document.createElement('div');
        effect.innerHTML = `
            <div class="quantum-success">
                <div class="quantum-points">+${quantumResult.total_points} QUANTUM POINTS</div>
                <div class="quantum-multiplier">Multiplier: ${quantumResult.quantum_multiplier}x</div>
                <div class="dimensional-bonus">Dimensional: +${quantumResult.dimensional_bonus}</div>
                <div class="neural-bonus">Neural: +${quantumResult.neural_bonus}</div>
            </div>
        `;

        effect.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(45deg, #667eea, #764ba2, #667eea);
            background-size: 300% 300%;
            color: white;
            padding: 25px;
            border-radius: 15px;
            border: 3px solid #9d50bb;
            box-shadow: 0 0 50px rgba(157, 80, 187, 0.7);
            z-index: 10001;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-align: center;
            animation: quantumSuccess 3s ease-out;
        `;

        document.body.appendChild(effect);

        // Add quantum success animation
        if (!document.querySelector('#quantum-success-animation')) {
            const style = document.createElement('style');
            style.id = 'quantum-success-animation';
            style.textContent = `
                @keyframes quantumSuccess {
                    0% { 
                        transform: translate(-50%, -50%) scale(0) rotate(0deg); 
                        opacity: 0; 
                        background-position: 0% 50%;
                    }
                    20% { 
                        transform: translate(-50%, -50%) scale(1.2) rotate(180deg); 
                        opacity: 1; 
                        background-position: 50% 50%;
                    }
                    80% { 
                        transform: translate(-50%, -50%) scale(1) rotate(360deg); 
                        opacity: 1; 
                        background-position: 100% 50%;
                    }
                    100% { 
                        transform: translate(-50%, -50%) scale(0) rotate(540deg); 
                        opacity: 0; 
                        background-position: 0% 50%;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => effect.remove(), 3000);
    }

    showRealityShiftNotification(realityShift) {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div class="reality-shift-notification">
                <div class="shift-title">🌌 REALITY SHIFT DETECTED</div>
                <div class="shift-details">
                    <div>Magnitude: ${realityShift.magnitude.toFixed(3)}</div>
                    <div>Direction: ${realityShift.direction}</div>
                    <div>Frequency: ${realityShift.resonance_frequency}</div>
                    <div>Status: ${realityShift.stability}</div>
                </div>
            </div>
        `;

        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #4a90e2;
            box-shadow: 0 0 30px rgba(74, 144, 226, 0.5);
            z-index: 10000;
            font-family: 'Courier New', monospace;
            max-width: 300px;
            animation: realityShimmer 2s infinite;
        `;

        document.body.appendChild(notification);

        // Add shimmer effect
        if (!document.querySelector('#reality-shimmer-animation')) {
            const style = document.createElement('style');
            style.id = 'reality-shimmer-animation';
            style.textContent = `
                @keyframes realityShimmer {
                    0%, 100% { filter: hue-rotate(0deg); }
                    33% { filter: hue-rotate(120deg); }
                    66% { filter: hue-rotate(240deg); }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => notification.remove(), 5000);
    }

    updateQuantumEnergyBar() {
        const energyFill = document.getElementById('quantum-energy-fill');
        const energyText = document.getElementById('quantum-energy-text');
        
        if (energyFill && energyText) {
            const percentage = this.quantumState.energy;
            energyFill.style.width = `${percentage}%`;
            energyFill.style.background = `linear-gradient(90deg, 
                ${percentage > 75 ? '#00ff00' : percentage > 50 ? '#ffff00' : percentage > 25 ? '#ffa500' : '#ff0000'}, 
                ${percentage > 75 ? '#32cd32' : percentage > 50 ? '#ffd700' : percentage > 25 ? '#ff8c00' : '#dc143c'})`;
            energyText.textContent = `${this.quantumState.energy}/100`;
        }
    }

    updateNeuralEvolution(xpGained) {
        this.neuralEvolution.evolution_points += xpGained;
        
        // Check for level up
        const newLevel = Math.floor(this.neuralEvolution.evolution_points / 1000) + 1;
        if (newLevel > this.neuralEvolution.level) {
            this.neuralEvolution.level = newLevel;
            this.neuralEvolution.consciousness += 50;
            this.neuralEvolution.neural_pathways += 5;
            this.showNeuralEvolutionNotification(newLevel);
        }

        // Update UI
        this.updateNeuralUI();
    }

    updateNeuralUI() {
        const consciousnessElement = document.getElementById('consciousness-level');
        const pathwaysElement = document.getElementById('neural-pathways');
        const evolutionElement = document.getElementById('evolution-points');

        if (consciousnessElement) consciousnessElement.textContent = this.neuralEvolution.level;
        if (pathwaysElement) pathwaysElement.textContent = this.neuralEvolution.neural_pathways;
        if (evolutionElement) evolutionElement.textContent = this.neuralEvolution.evolution_points.toLocaleString();
    }

    showNeuralEvolutionNotification(newLevel) {
        this.showQuantumNotification(
            '🧠 NEURAL EVOLUTION', 
            `Consciousness Level ${newLevel} Achieved!\n+50 Consciousness, +5 Neural Pathways`,
            '#9d50bb'
        );
    }

    async accessDimensionalMining() {
        try {
            const response = await fetch('/api/quantum/dimensional', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();
            
            if (result.success) {
                this.showDimensionalInterface(result.dimensional_interface);
            } else {
                this.showQuantumNotification('🚫 Dimensional Access Denied', result.message, '#FF6B35');
            }
        } catch (error) {
            console.error('Dimensional access failed:', error);
        }
    }

    showDimensionalInterface(interfaceData) {
        const dimensionalInterface = document.getElementById('dimensional-interface');
        if (dimensionalInterface) {
            dimensionalInterface.style.display = 'block';
            
            // Show dimensional portal animation
            this.triggerDimensionalPortal();
            
            this.showQuantumNotification(
                '🌀 DIMENSIONAL PORTAL OPENED', 
                'Reality layers accessible. Consciousness expansion imminent.',
                '#9d50bb'
            );
        }
    }

    triggerDimensionalPortal() {
        const portal = document.createElement('div');
        portal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            width: 200px;
            height: 200px;
            transform: translate(-50%, -50%);
            border: 3px solid #9d50bb;
            border-radius: 50%;
            background: radial-gradient(circle, transparent 30%, rgba(157, 80, 187, 0.2) 70%);
            z-index: 10000;
            animation: dimensionalPortal 3s ease-out;
        `;

        document.body.appendChild(portal);

        // Add portal animation
        if (!document.querySelector('#dimensional-portal-animation')) {
            const style = document.createElement('style');
            style.id = 'dimensional-portal-animation';
            style.textContent = `
                @keyframes dimensionalPortal {
                    0% { 
                        width: 0; 
                        height: 0; 
                        opacity: 0;
                        transform: translate(-50%, -50%) rotate(0deg);
                    }
                    50% { 
                        width: 300px; 
                        height: 300px; 
                        opacity: 1;
                        transform: translate(-50%, -50%) rotate(180deg);
                    }
                    100% { 
                        width: 200px; 
                        height: 200px; 
                        opacity: 0;
                        transform: translate(-50%, -50%) rotate(360deg);
                    }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => portal.remove(), 3000);
    }

    startQuantumAnimations() {
        // Initialize quantum canvas visualization
        const canvas = document.getElementById('quantum-canvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const particles = [];

        // Create quantum particles
        for (let i = 0; i < 30; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 2,
                vy: (Math.random() - 0.5) * 2,
                size: Math.random() * 3 + 1,
                color: `hsl(${Math.random() * 360}, 70%, 60%)`,
                quantum_state: Math.random()
            });
        }

        const animateQuantum = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Draw quantum field background
            const gradient = ctx.createRadialGradient(canvas.width/2, canvas.height/2, 0, canvas.width/2, canvas.height/2, 100);
            gradient.addColorStop(0, 'rgba(157, 80, 187, 0.1)');
            gradient.addColorStop(1, 'rgba(102, 126, 234, 0.05)');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            particles.forEach(particle => {
                // Update quantum particle
                particle.x += particle.vx;
                particle.y += particle.vy;
                particle.quantum_state += 0.01;

                // Quantum tunneling (particles can phase through boundaries)
                if (particle.quantum_state > 1) {
                    particle.quantum_state = 0;
                    particle.x = Math.random() * canvas.width;
                    particle.y = Math.random() * canvas.height;
                }

                // Boundary wrapping
                if (particle.x < 0) particle.x = canvas.width;
                if (particle.x > canvas.width) particle.x = 0;
                if (particle.y < 0) particle.y = canvas.height;
                if (particle.y > canvas.height) particle.y = 0;

                // Draw particle with quantum glow
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                ctx.fillStyle = particle.color;
                ctx.globalAlpha = 0.8 * (0.5 + 0.5 * Math.sin(particle.quantum_state * Math.PI * 2));
                ctx.fill();
                
                // Quantum entanglement lines
                particles.forEach(otherParticle => {
                    const distance = Math.hypot(particle.x - otherParticle.x, particle.y - otherParticle.y);
                    if (distance < 50 && Math.random() > 0.98) {
                        ctx.beginPath();
                        ctx.moveTo(particle.x, particle.y);
                        ctx.lineTo(otherParticle.x, otherParticle.y);
                        ctx.strokeStyle = 'rgba(157, 80, 187, 0.3)';
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                });
            });

            ctx.globalAlpha = 1;
            requestAnimationFrame(animateQuantum);
        };

        animateQuantum();
    }

    initializeNeuralNetwork() {
        // Extend neural canvas from parent class with quantum enhancements
        if (this.triggerVisualization) {
            const originalTrigger = this.triggerVisualization;
            this.triggerVisualization = (hash, type = 'normal') => {
                originalTrigger(hash, type);
                
                if (this.quantumEnabled && type === 'legendary') {
                    this.triggerQuantumVisualization(hash);
                }
            };
        }
    }

    triggerQuantumVisualization(hash) {
        // Quantum-enhanced visualization
        const canvas = document.getElementById('neural-mining-canvas');
        if (!canvas) return;

        // Create quantum ripple effect
        const ripple = document.createElement('div');
        ripple.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 10px;
            border: 2px solid #9d50bb;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: quantumRipple 2s ease-out;
            z-index: 1000;
        `;

        canvas.parentElement.appendChild(ripple);

        // Add ripple animation
        if (!document.querySelector('#quantum-ripple-animation')) {
            const style = document.createElement('style');
            style.id = 'quantum-ripple-animation';
            style.textContent = `
                @keyframes quantumRipple {
                    0% { width: 10px; height: 10px; opacity: 1; }
                    100% { width: 200px; height: 200px; opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => ripple.remove(), 2000);
    }

    showQuantumActivation() {
        this.showQuantumNotification(
            '🌌 QUANTUM SYSTEM ONLINE', 
            'Haichan 2.0 activated. Reality matrix accessible. Consciousness expansion initiated.',
            '#667eea'
        );
    }

    showQuantumNotification(title, message, color = '#9d50bb') {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="font-size: 16px; font-weight: bold; margin-bottom: 8px;">${title}</div>
            <div style="font-size: 12px; opacity: 0.9;">${message}</div>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, ${color}ee, ${color}99);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            border: 1px solid ${color};
            box-shadow: 0 4px 20px ${color}44;
            z-index: 10002;
            font-family: 'Courier New', monospace;
            max-width: 400px;
            text-align: center;
            animation: quantumNotificationSlide 0.5s ease-out;
        `;

        document.body.appendChild(notification);

        // Add slide animation
        if (!document.querySelector('#quantum-notification-animation')) {
            const style = document.createElement('style');
            style.id = 'quantum-notification-animation';
            style.textContent = `
                @keyframes quantumNotificationSlide {
                    from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
                    to { transform: translateX(-50%) translateY(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => notification.remove(), 6000);
    }

    showQuantumMechanicActivation(mechanic, duration) {
        this.showQuantumNotification(
            `${mechanic.icon} ${mechanic.name.toUpperCase()} ACTIVATED`,
            `${mechanic.description}\nDuration: ${duration} seconds`,
            '#667eea'
        );
    }

    performQuantumBoost() {
        if (this.quantumState.energy >= 30) {
            this.quantumState.energy -= 30;
            this.updateQuantumEnergyBar();
            
            // Temporary mining boost
            this.showQuantumNotification('⚡ QUANTUM BOOST', 'Mining efficiency increased for 30 seconds', '#00ff00');
            
            // Visual effect for boost
            document.body.style.filter = 'hue-rotate(120deg) brightness(1.2)';
            setTimeout(() => {
                document.body.style.filter = '';
            }, 30000);
        }
    }

    async triggerNeuralSynthesis() {
        try {
            const response = await fetch('/api/quantum/neural-synthesis', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    synthesis_type: 'consciousness_expansion',
                    input_data: { 
                        level: this.neuralEvolution.level,
                        consciousness: this.neuralEvolution.consciousness 
                    }
                })
            });

            const result = await response.json();
            
            if (result.synthesis_result) {
                this.showQuantumNotification(
                    '🧠 NEURAL SYNTHESIS COMPLETE',
                    `Consciousness expanded by ${result.synthesis_result.consciousness_expansion}%\n+${result.synthesis_result.evolution_points} Evolution Points`,
                    '#9d50bb'
                );
                
                this.updateNeuralEvolution(result.synthesis_result.evolution_points);
            }
        } catch (error) {
            console.error('Neural synthesis failed:', error);
        }
    }

    // Override submitProof to use quantum system when available
    async submitProof(proof, target, type) {
        if (this.quantumEnabled) {
            return await this.submitQuantumProof(proof, target, type);
        }
        return await super.submitProof(proof, target, type);
    }
}

// Initialize Haichan 2.0 system
function initializeHaichan2() {
    if (typeof HaichanComplete !== 'undefined') {
        window.haichan2 = new Haichan2();
        window.haichanMiner = window.haichan2;
        console.log('🌌 Haichan 2.0 Quantum System Initialized');
    }
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHaichan2);
} else {
    initializeHaichan2();
}