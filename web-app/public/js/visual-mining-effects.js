/**
 * HAICHAN VISUAL MINING EFFECTS ENGINE
 * Sophisticated particle systems, hash discovery animations, and progress indicators
 * Creates a premium mining experience with cinematic visual feedback
 */

class VisualMiningEffects {
    constructor() {
        this.particles = new Map();
        this.activeAnimations = new Set();
        this.canvas = null;
        this.ctx = null;
        this.particlePool = [];
        this.maxParticles = 200;
        this.isRunning = false;
        
        this.setupCanvas();
        this.setupStyles();
        this.startRenderLoop();
        
        console.log('🎨 Visual Mining Effects Engine initialized');
    }
    
    setupCanvas() {
        // Create background canvas for particle effects
        this.canvas = document.createElement('canvas');
        this.canvas.id = 'mining-effects-canvas';
        this.canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 9998;
            opacity: 0.8;
        `;
        
        this.ctx = this.canvas.getContext('2d');
        document.body.appendChild(this.canvas);
        
        this.resizeCanvas();
        window.addEventListener('resize', () => this.resizeCanvas());
    }
    
    resizeCanvas() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }
    
    setupStyles() {
        if (document.getElementById('visual-mining-effects-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'visual-mining-effects-styles';
        styles.textContent = `
            /* Mining Element Enhancements */
            .mining-active {
                position: relative;
                overflow: visible;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .mining-active::before {
                content: '';
                position: absolute;
                inset: -4px;
                background: linear-gradient(45deg, 
                    rgba(0, 169, 165, 0.3), 
                    rgba(144, 194, 231, 0.3),
                    rgba(0, 255, 165, 0.2),
                    rgba(0, 169, 165, 0.3)
                );
                background-size: 400% 400%;
                border-radius: inherit;
                z-index: -1;
                animation: mining-border-flow 3s ease-in-out infinite;
                filter: blur(2px);
            }
            
            .mining-active::after {
                content: '';
                position: absolute;
                inset: -2px;
                background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                border-radius: inherit;
                z-index: -1;
                animation: mining-shimmer 2s linear infinite;
            }
            
            @keyframes mining-border-flow {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }
            
            @keyframes mining-shimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            
            /* Hash Discovery Effects */
            .hash-discovery-legendary {
                animation: legendary-hash-pulse 2s ease-out;
                position: relative;
                z-index: 1000;
            }
            
            @keyframes legendary-hash-pulse {
                0% { 
                    transform: scale(1);
                    box-shadow: 0 0 0 rgba(255, 215, 0, 0);
                }
                20% { 
                    transform: scale(1.02);
                    box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
                }
                50% { 
                    transform: scale(1.05);
                    box-shadow: 
                        0 0 40px rgba(255, 215, 0, 1),
                        inset 0 0 20px rgba(255, 215, 0, 0.3);
                }
                80% { 
                    transform: scale(1.02);
                    box-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
                }
                100% { 
                    transform: scale(1);
                    box-shadow: 0 0 0 rgba(255, 215, 0, 0);
                }
            }
            
            .hash-discovery-epic {
                animation: epic-hash-pulse 1.5s ease-out;
            }
            
            @keyframes epic-hash-pulse {
                0% { 
                    transform: scale(1);
                    box-shadow: 0 0 0 rgba(0, 169, 165, 0);
                }
                30% { 
                    transform: scale(1.03);
                    box-shadow: 0 0 25px rgba(0, 169, 165, 0.8);
                }
                70% { 
                    transform: scale(1.01);
                    box-shadow: 0 0 15px rgba(144, 194, 231, 0.6);
                }
                100% { 
                    transform: scale(1);
                    box-shadow: 0 0 0 rgba(0, 169, 165, 0);
                }
            }
            
            /* Progress Indicators */
            .mining-progress-ring {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 60px;
                height: 60px;
                z-index: 1001;
                pointer-events: none;
            }
            
            .mining-progress-ring circle {
                fill: none;
                stroke: rgba(0, 169, 165, 0.8);
                stroke-width: 3;
                stroke-linecap: round;
                transform-origin: 50% 50%;
                transform: rotate(-90deg);
                animation: progress-ring-rotate 2s linear infinite;
            }
            
            @keyframes progress-ring-rotate {
                0% { stroke-dasharray: 0 188.4; }
                50% { stroke-dasharray: 94.2 94.2; }
                100% { stroke-dasharray: 188.4 0; }
            }
            
            /* Floating Numbers */
            .mining-float-number {
                position: absolute;
                font-size: 14px;
                font-weight: 700;
                color: #00ffa5;
                pointer-events: none;
                z-index: 1002;
                text-shadow: 0 0 8px rgba(0, 255, 165, 0.8);
                animation: float-number 2s ease-out forwards;
            }
            
            @keyframes float-number {
                0% {
                    opacity: 0;
                    transform: translateY(0) scale(0.5);
                }
                20% {
                    opacity: 1;
                    transform: translateY(-10px) scale(1.2);
                }
                100% {
                    opacity: 0;
                    transform: translateY(-50px) scale(1);
                }
            }
            
            /* Intensity Visual Styles */
            .mining-intensity-casual { filter: brightness(0.8) saturate(0.7); }
            .mining-intensity-active { filter: brightness(1) saturate(1); }
            .mining-intensity-elite { filter: brightness(1.2) saturate(1.3); }
            .mining-intensity-legendary { 
                filter: brightness(1.5) saturate(1.5) hue-rotate(30deg);
                animation: legendary-glow 3s ease-in-out infinite;
            }
            
            @keyframes legendary-glow {
                0%, 100% { filter: brightness(1.5) saturate(1.5) hue-rotate(30deg); }
                50% { filter: brightness(1.8) saturate(1.8) hue-rotate(60deg); }
            }
        `;
        document.head.appendChild(styles);
    }
    
    startRenderLoop() {
        if (this.isRunning) return;
        this.isRunning = true;
        
        const render = () => {
            if (!this.isRunning) return;
            
            // Clear canvas
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            // Render all particles
            this.renderParticles();
            
            requestAnimationFrame(render);
        };
        
        render();
    }
    
    // Main mining visualization methods
    startMiningVisualization(element, intensity = 'ELITE') {
        if (!element) return;
        
        element.classList.add('mining-active');
        element.classList.add(`mining-intensity-${intensity.toLowerCase()}`);
        
        // Start particle system
        this.startElementParticles(element, intensity);
        
        // Add progress indicator for high-intensity mining
        if (intensity === 'LEGENDARY' || intensity === 'ELITE') {
            this.addProgressIndicator(element);
        }
        
        // Store reference for cleanup
        this.particles.set(element, {
            intensity,
            startTime: Date.now(),
            particleEmitters: []
        });
    }
    
    stopMiningVisualization(element) {
        if (!element) return;
        
        element.classList.remove('mining-active');
        element.classList.remove('mining-intensity-casual', 'mining-intensity-active', 'mining-intensity-elite', 'mining-intensity-legendary');
        
        // Remove progress indicator
        const progressRing = element.querySelector('.mining-progress-ring');
        if (progressRing) progressRing.remove();
        
        // Clean up particles
        if (this.particles.has(element)) {
            const particleData = this.particles.get(element);
            particleData.particleEmitters.forEach(emitter => {
                emitter.active = false;
            });
            this.particles.delete(element);
        }
    }
    
    showHashDiscovery(element, difficulty, hash) {
        if (!element) return;
        
        const isLegendary = difficulty === '21e8';
        const isEpic = difficulty === '21e' || difficulty === '21';
        
        // Apply discovery animation class
        if (isLegendary) {
            element.classList.add('hash-discovery-legendary');
            this.createLegendaryEffect(element, hash);
        } else if (isEpic) {
            element.classList.add('hash-discovery-epic');
            this.createEpicEffect(element, hash);
        }
        
        // Create explosion of particles
        this.createHashDiscoveryExplosion(element, difficulty);
        
        // Show floating hash preview
        this.showFloatingHash(element, hash);
        
        // Clean up animations
        setTimeout(() => {
            element.classList.remove('hash-discovery-legendary', 'hash-discovery-epic');
        }, isLegendary ? 2000 : 1500);
    }
    
    updateMiningProgress(element, progress) {
        if (!element) return;
        
        const progressRing = element.querySelector('.mining-progress-ring circle');
        if (progressRing) {
            const circumference = 2 * Math.PI * 30; // radius = 30
            const offset = circumference - (progress / 100) * circumference;
            progressRing.style.strokeDasharray = `${circumference} ${circumference}`;
            progressRing.style.strokeDashoffset = offset;
        }
        
        // Add more intense particles as progress increases
        if (progress > 50) {
            this.boostParticleIntensity(element, progress / 100);
        }
    }
    
    // Particle system methods
    startElementParticles(element, intensity) {
        const rect = element.getBoundingClientRect();
        const particleData = this.particles.get(element);
        
        if (!particleData) return;
        
        // Create particle emitter
        const emitter = {
            element,
            x: rect.left + rect.width / 2,
            y: rect.top + rect.height / 2,
            width: rect.width,
            height: rect.height,
            intensity: this.getIntensityLevel(intensity),
            active: true,
            particles: [],
            lastEmit: Date.now()
        };
        
        particleData.particleEmitters.push(emitter);
        this.updateEmitterPosition(emitter);
    }
    
    updateEmitterPosition(emitter) {
        const rect = emitter.element.getBoundingClientRect();
        emitter.x = rect.left + rect.width / 2;
        emitter.y = rect.top + rect.height / 2;
        emitter.width = rect.width;
        emitter.height = rect.height;
    }
    
    renderParticles() {
        const now = Date.now();
        
        // Update all emitters and their particles
        for (const [element, data] of this.particles) {
            data.particleEmitters.forEach(emitter => {
                if (!emitter.active) return;
                
                // Update emitter position
                this.updateEmitterPosition(emitter);
                
                // Emit new particles
                if (now - emitter.lastEmit > 100) { // Emit every 100ms
                    this.emitParticles(emitter);
                    emitter.lastEmit = now;
                }
                
                // Update and render existing particles
                this.updateParticles(emitter, now);
                this.drawParticles(emitter);
            });
        }
    }
    
    emitParticles(emitter) {
        const particleCount = Math.floor(emitter.intensity * 2);
        
        for (let i = 0; i < particleCount; i++) {
            const particle = {
                x: emitter.x + (Math.random() - 0.5) * emitter.width * 0.8,
                y: emitter.y + (Math.random() - 0.5) * emitter.height * 0.8,
                vx: (Math.random() - 0.5) * 2,
                vy: (Math.random() - 0.5) * 2 - 1, // Slight upward bias
                size: Math.random() * 3 + 1,
                life: 1.0,
                maxLife: Math.random() * 2000 + 1000, // 1-3 seconds
                color: this.getParticleColor(emitter.intensity),
                type: Math.random() < 0.1 ? 'spark' : 'normal', // 10% sparks
                createdAt: Date.now()
            };
            
            emitter.particles.push(particle);
        }
        
        // Limit particles per emitter
        if (emitter.particles.length > 50) {
            emitter.particles.splice(0, emitter.particles.length - 50);
        }
    }
    
    updateParticles(emitter, now) {
        emitter.particles = emitter.particles.filter(particle => {
            const age = now - particle.createdAt;
            particle.life = 1.0 - (age / particle.maxLife);
            
            if (particle.life <= 0) return false;
            
            // Update position
            particle.x += particle.vx;
            particle.y += particle.vy;
            
            // Apply gravity and air resistance
            particle.vy += 0.02; // gravity
            particle.vx *= 0.99; // air resistance
            particle.vy *= 0.99;
            
            return true;
        });
    }
    
    drawParticles(emitter) {
        emitter.particles.forEach(particle => {
            const alpha = particle.life * 0.8;
            
            this.ctx.save();
            this.ctx.globalAlpha = alpha;
            
            if (particle.type === 'spark') {
                this.drawSpark(particle);
            } else {
                this.drawNormalParticle(particle);
            }
            
            this.ctx.restore();
        });
    }
    
    drawNormalParticle(particle) {
        const gradient = this.ctx.createRadialGradient(
            particle.x, particle.y, 0,
            particle.x, particle.y, particle.size
        );
        
        gradient.addColorStop(0, particle.color);
        gradient.addColorStop(1, 'transparent');
        
        this.ctx.fillStyle = gradient;
        this.ctx.beginPath();
        this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
        this.ctx.fill();
    }
    
    drawSpark(particle) {
        this.ctx.strokeStyle = particle.color;
        this.ctx.lineWidth = 2;
        this.ctx.lineCap = 'round';
        
        this.ctx.beginPath();
        this.ctx.moveTo(particle.x - particle.vx * 3, particle.y - particle.vy * 3);
        this.ctx.lineTo(particle.x, particle.y);
        this.ctx.stroke();
    }
    
    // Special effects methods
    createLegendaryEffect(element, hash) {
        // Create golden ripples
        this.createRippleEffect(element, '#FFD700', 5);
        
        // Create hash particles
        this.createHashParticles(element, hash);
        
        // Add screen flash effect
        this.createScreenFlash('#FFD700', 0.1);
    }
    
    createEpicEffect(element, hash) {
        // Create blue-cyan ripples
        this.createRippleEffect(element, '#00A9A5', 3);
        
        // Create smaller hash particles
        this.createHashParticles(element, hash, 0.7);
    }
    
    createRippleEffect(element, color, count) {
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        for (let i = 0; i < count; i++) {
            setTimeout(() => {
                this.createSingleRipple(centerX, centerY, color, i * 0.2);
            }, i * 200);
        }
    }
    
    createSingleRipple(x, y, color, delay) {
        const ripple = document.createElement('div');
        ripple.style.cssText = `
            position: fixed;
            left: ${x}px;
            top: ${y}px;
            width: 10px;
            height: 10px;
            border: 2px solid ${color};
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            pointer-events: none;
            z-index: 9999;
            animation: ripple-expand ${2 + delay}s ease-out forwards;
        `;
        
        // Add ripple animation if not exists
        if (!document.getElementById('ripple-animations')) {
            const style = document.createElement('style');
            style.id = 'ripple-animations';
            style.textContent = `
                @keyframes ripple-expand {
                    0% {
                        transform: translate(-50%, -50%) scale(0);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(-50%, -50%) scale(10);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), (2 + delay) * 1000);
    }
    
    createHashParticles(element, hash, scale = 1) {
        const rect = element.getBoundingClientRect();
        const particleCount = Math.floor(20 * scale);
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.textContent = hash.charAt(i % hash.length);
            particle.style.cssText = `
                position: fixed;
                left: ${rect.left + Math.random() * rect.width}px;
                top: ${rect.top + Math.random() * rect.height}px;
                font-size: ${8 + Math.random() * 6}px;
                color: ${this.getRandomHashColor()};
                font-family: monospace;
                font-weight: bold;
                pointer-events: none;
                z-index: 10000;
                opacity: 0;
                animation: hash-particle-float ${2 + Math.random()}s ease-out forwards;
            `;
            
            document.body.appendChild(particle);
            
            setTimeout(() => particle.remove(), 3000);
        }
        
        // Add hash particle animation
        if (!document.getElementById('hash-particle-animations')) {
            const style = document.createElement('style');
            style.id = 'hash-particle-animations';
            style.textContent = `
                @keyframes hash-particle-float {
                    0% {
                        opacity: 0;
                        transform: translateY(0) rotate(0deg);
                    }
                    20% {
                        opacity: 1;
                    }
                    100% {
                        opacity: 0;
                        transform: translateY(-80px) rotate(360deg);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    createScreenFlash(color, opacity) {
        const flash = document.createElement('div');
        flash.style.cssText = `
            position: fixed;
            inset: 0;
            background: ${color};
            opacity: 0;
            pointer-events: none;
            z-index: 10001;
            animation: screen-flash 0.3s ease-out forwards;
        `;
        
        if (!document.getElementById('flash-animations')) {
            const style = document.createElement('style');
            style.id = 'flash-animations';
            style.textContent = `
                @keyframes screen-flash {
                    0% { opacity: 0; }
                    50% { opacity: ${opacity}; }
                    100% { opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(flash);
        setTimeout(() => flash.remove(), 300);
    }
    
    createHashDiscoveryExplosion(element, difficulty) {
        const rect = element.getBoundingClientRect();
        const intensity = this.getDifficultyIntensity(difficulty);
        
        // Create temporary emitter for explosion
        const emitter = {
            element,
            x: rect.left + rect.width / 2,
            y: rect.top + rect.height / 2,
            width: rect.width,
            height: rect.height,
            intensity: intensity * 3, // Boost intensity for explosion
            active: true,
            particles: [],
            lastEmit: 0
        };
        
        // Emit burst of particles
        for (let i = 0; i < 50; i++) {
            const angle = (Math.PI * 2 * i) / 50;
            const speed = 3 + Math.random() * 2;
            
            const particle = {
                x: emitter.x,
                y: emitter.y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                size: Math.random() * 4 + 2,
                life: 1.0,
                maxLife: 1500,
                color: this.getExplosionColor(difficulty),
                type: Math.random() < 0.3 ? 'spark' : 'normal',
                createdAt: Date.now()
            };
            
            emitter.particles.push(particle);
        }
        
        // Render explosion particles for limited time
        const explosionId = Date.now();
        this.particles.set(explosionId, { particleEmitters: [emitter] });
        
        setTimeout(() => {
            this.particles.delete(explosionId);
        }, 2000);
    }
    
    showFloatingHash(element, hash) {
        const rect = element.getBoundingClientRect();
        const floatingHash = document.createElement('div');
        
        floatingHash.className = 'mining-float-number';
        floatingHash.textContent = `${hash.substring(0, 8)}...`;
        floatingHash.style.left = `${rect.left + rect.width / 2}px`;
        floatingHash.style.top = `${rect.top}px`;
        
        document.body.appendChild(floatingHash);
        
        setTimeout(() => floatingHash.remove(), 2000);
    }
    
    addProgressIndicator(element) {
        if (element.querySelector('.mining-progress-ring')) return; // Already exists
        
        const progressRing = document.createElement('div');
        progressRing.className = 'mining-progress-ring';
        progressRing.innerHTML = `
            <svg width="60" height="60">
                <circle cx="30" cy="30" r="25"></circle>
            </svg>
        `;
        
        element.style.position = element.style.position || 'relative';
        element.appendChild(progressRing);
    }
    
    boostParticleIntensity(element, multiplier) {
        if (!this.particles.has(element)) return;
        
        const particleData = this.particles.get(element);
        particleData.particleEmitters.forEach(emitter => {
            emitter.intensity = Math.min(emitter.intensity * (1 + multiplier * 0.5), 10);
        });
    }
    
    // Utility methods
    getIntensityLevel(intensity) {
        const levels = {
            'CASUAL': 1,
            'ACTIVE': 2,
            'ELITE': 4,
            'LEGENDARY': 6
        };
        return levels[intensity] || 4;
    }
    
    getDifficultyIntensity(difficulty) {
        const intensities = {
            '2': 2,
            '21': 3,
            '21e': 5,
            '21e8': 8
        };
        return intensities[difficulty] || 3;
    }
    
    getParticleColor(intensity) {
        const colors = [
            'rgba(0, 169, 165, 0.8)',
            'rgba(144, 194, 231, 0.8)',
            'rgba(0, 255, 165, 0.8)',
            'rgba(144, 238, 144, 0.8)'
        ];
        
        const index = Math.floor(Math.random() * colors.length);
        return colors[index];
    }
    
    getExplosionColor(difficulty) {
        const colorMaps = {
            '21e8': ['#FFD700', '#FFA500', '#FF8C00'],
            '21e': ['#00A9A5', '#90C2E7', '#5DADE2'],
            '21': ['#48C9B0', '#58D68D', '#52BE80'],
            '2': ['#AED6F1', '#A9CCE3', '#85C1E9']
        };
        
        const colors = colorMaps[difficulty] || colorMaps['21'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    getRandomHashColor() {
        const colors = ['#00ffa5', '#00d4aa', '#00a9a5', '#90c2e7', '#5dade2'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    // Public API methods
    destroy() {
        this.isRunning = false;
        this.particles.clear();
        this.activeAnimations.clear();
        
        if (this.canvas && this.canvas.parentNode) {
            this.canvas.parentNode.removeChild(this.canvas);
        }
    }
    
    setQuality(quality) {
        // Adjust particle count and effects based on quality
        const multipliers = {
            'low': 0.5,
            'medium': 1.0,
            'high': 1.5,
            'ultra': 2.0
        };
        
        this.maxParticles = Math.floor(200 * (multipliers[quality] || 1.0));
    }
}

// Initialize and make globally available
window.VisualMiningEffects = VisualMiningEffects;
window.visualMiningEffects = new VisualMiningEffects();

console.log('🎨 Visual Mining Effects Engine loaded');
console.log('💫 Premium particle systems, hash discovery animations, and progress indicators ready');