/**
 * CHAOS ENGINE - Dynamic Visual Effects System
 * Creates particles, matrix rain, glitch effects, and other chaos
 */

class ChaosEngine {
    constructor() {
        this.isInitialized = false;
        this.particles = [];
        this.matrixChars = [];
        this.glitchElements = [];
        this.animationFrame = null;
        
        this.init();
    }
    
    init() {
        if (this.isInitialized) return;
        this.isInitialized = true;
        
        console.log('🌪️ CHAOS ENGINE - Initializing reality distortion...');
        
        this.createParticleSystem();
        this.createMatrixRain();
        this.createCyberGrid();
        this.setupGlitchText();
        this.setupChaosEffects();
        this.startAnimationLoop();
        
        console.log('✅ CHAOS ENGINE - Reality distorted successfully');
    }
    
    createParticleSystem() {
        const container = document.createElement('div');
        container.className = 'particle-container';
        container.id = 'chaos-particles';
        document.body.appendChild(container);
        
        // Create initial particles
        for (let i = 0; i < 50; i++) {
            this.createParticle();
        }
        
        // Continuously spawn particles
        setInterval(() => {
            if (this.particles.length < 100) {
                this.createParticle();
            }
        }, 200);
    }
    
    createParticle() {
        const container = document.getElementById('chaos-particles');
        if (!container) return;
        
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Random starting position
        const startX = Math.random() * window.innerWidth;
        const startY = window.innerHeight + 10;
        
        // Random properties
        const size = Math.random() * 4 + 1;
        const duration = Math.random() * 15 + 10; // 10-25 seconds
        const drift = Math.random() * 200 - 100; // -100 to 100px drift
        
        particle.style.left = startX + 'px';
        particle.style.top = startY + 'px';
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.animationDuration = duration + 's';
        particle.style.setProperty('--drift', drift + 'px');
        
        // Random color
        const colors = ['var(--grunge-neon)', 'var(--grunge-accent)', 'var(--grunge-secondary)', 'var(--grunge-purple)'];
        particle.style.background = colors[Math.floor(Math.random() * colors.length)];
        
        container.appendChild(particle);
        this.particles.push(particle);
        
        // Remove particle after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
                const index = this.particles.indexOf(particle);
                if (index > -1) this.particles.splice(index, 1);
            }
        }, duration * 1000);
    }
    
    createMatrixRain() {
        const container = document.createElement('div');
        container.className = 'matrix-rain';
        container.id = 'matrix-rain';
        document.body.appendChild(container);
        
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZハイチャン日本語カタカナひらがな';
        
        // Create columns of falling characters
        const columns = Math.floor(window.innerWidth / 20);
        
        for (let i = 0; i < columns; i++) {
            this.createMatrixColumn(i * 20, chars);
        }
    }
    
    createMatrixColumn(x, chars) {
        const container = document.getElementById('matrix-rain');
        if (!container) return;
        
        const createChar = () => {
            const char = document.createElement('div');
            char.className = 'matrix-char';
            char.textContent = chars[Math.floor(Math.random() * chars.length)];
            char.style.left = x + 'px';
            char.style.top = '-20px';
            char.style.animationDuration = (Math.random() * 10 + 5) + 's';
            
            container.appendChild(char);
            
            // Remove char after animation
            setTimeout(() => {
                if (char.parentNode) {
                    char.parentNode.removeChild(char);
                }
            }, 15000);
        };
        
        // Initial delay
        setTimeout(() => {
            createChar();
            // Continue creating chars for this column
            setInterval(createChar, Math.random() * 2000 + 1000);
        }, Math.random() * 2000);
    }
    
    createCyberGrid() {
        const grid = document.createElement('div');
        grid.className = 'cyber-grid';
        grid.id = 'cyber-grid';
        document.body.appendChild(grid);
    }
    
    setupGlitchText() {
        // Add glitch effect to headers and important text
        const headers = document.querySelectorAll('h1, h2, h3, .tui-title');
        headers.forEach(header => {
            if (!header.dataset.text) {
                header.dataset.text = header.textContent;
                header.classList.add('glitch-container');
                
                // Random glitch activation
                setInterval(() => {
                    if (Math.random() < 0.1) { // 10% chance every interval
                        header.style.animation = 'glitchTitle 0.5s ease-in-out';
                        setTimeout(() => {
                            header.style.animation = '';
                        }, 500);
                    }
                }, 2000);
            }
        });
    }
    
    setupChaosEffects() {
        // Add chaos effects to various elements
        this.setupHoverChaos();
        this.setupRandomGlitches();
        this.setupNeonPulses();
        this.setupHologramEffects();
    }
    
    setupHoverChaos() {
        // Add chaos effects on hover
        const chaosElements = document.querySelectorAll('.tui-window, .tui-btn, .thread-item');
        
        chaosElements.forEach(element => {
            element.addEventListener('mouseenter', () => {
                this.addChaosEffect(element);
            });
            
            element.addEventListener('mouseleave', () => {
                this.removeChaosEffect(element);
            });
        });
    }
    
    addChaosEffect(element) {
        element.classList.add('quantum-interference');
        
        // Create temporary sparkles
        for (let i = 0; i < 5; i++) {
            setTimeout(() => {
                this.createSparkle(element);
            }, i * 100);
        }
    }
    
    removeChaosEffect(element) {
        setTimeout(() => {
            element.classList.remove('quantum-interference');
        }, 300);
    }
    
    createSparkle(element) {
        const sparkle = document.createElement('div');
        sparkle.innerHTML = '✦';
        sparkle.style.position = 'absolute';
        sparkle.style.color = 'var(--grunge-neon)';
        sparkle.style.fontSize = Math.random() * 20 + 10 + 'px';
        sparkle.style.pointerEvents = 'none';
        sparkle.style.zIndex = '1000';
        
        const rect = element.getBoundingClientRect();
        sparkle.style.left = rect.left + Math.random() * rect.width + 'px';
        sparkle.style.top = rect.top + Math.random() * rect.height + 'px';
        
        sparkle.style.animation = 'sparkle 1s ease-out forwards';
        
        document.body.appendChild(sparkle);
        
        setTimeout(() => {
            if (sparkle.parentNode) {
                sparkle.parentNode.removeChild(sparkle);
            }
        }, 1000);
    }
    
    setupRandomGlitches() {
        setInterval(() => {
            const elements = document.querySelectorAll('.tui-window, .tui-btn, .thread-item');
            const randomElement = elements[Math.floor(Math.random() * elements.length)];
            
            if (randomElement && Math.random() < 0.05) { // 5% chance
                this.triggerGlitch(randomElement);
            }
        }, 1000);
    }
    
    triggerGlitch(element) {
        element.classList.add('tv-static');
        element.style.filter = 'hue-rotate(' + (Math.random() * 360) + 'deg)';
        
        setTimeout(() => {
            element.classList.remove('tv-static');
            element.style.filter = '';
        }, 200);
    }
    
    setupNeonPulses() {
        const neonElements = document.querySelectorAll('.tui-badge, .tui-btn-primary');
        neonElements.forEach(element => {
            element.classList.add('neon-pulse');
        });
    }
    
    setupHologramEffects() {
        const headers = document.querySelectorAll('.header h1 a');
        headers.forEach(header => {
            header.classList.add('hologram');
            header.dataset.text = header.textContent;
        });
    }
    
    startAnimationLoop() {
        const animate = () => {
            // Update particle positions if needed
            this.updateChaosEffects();
            this.animationFrame = requestAnimationFrame(animate);
        };
        
        animate();
    }
    
    updateChaosEffects() {
        // Random screen distortions
        if (Math.random() < 0.001) { // Very rare
            this.screenGlitch();
        }
        
        // Random color shifts
        if (Math.random() < 0.005) { // Rare
            this.colorShift();
        }
    }
    
    screenGlitch() {
        document.body.style.animation = 'chaosDistortion 0.3s ease-in-out';
        setTimeout(() => {
            document.body.style.animation = '';
        }, 300);
    }
    
    colorShift() {
        const hue = Math.random() * 360;
        document.documentElement.style.filter = `hue-rotate(${hue}deg)`;
        
        setTimeout(() => {
            document.documentElement.style.filter = '';
        }, 1000);
    }
    
    // Public methods for external use
    explode(element) {
        // Create explosion effect at element
        for (let i = 0; i < 20; i++) {
            setTimeout(() => {
                this.createExplosionParticle(element);
            }, i * 50);
        }
    }
    
    createExplosionParticle(element) {
        const particle = document.createElement('div');
        particle.innerHTML = ['✦', '◆', '●', '▲', '■'][Math.floor(Math.random() * 5)];
        particle.style.position = 'absolute';
        particle.style.color = ['var(--grunge-accent)', 'var(--grunge-neon)', 'var(--grunge-secondary)'][Math.floor(Math.random() * 3)];
        particle.style.fontSize = Math.random() * 20 + 10 + 'px';
        particle.style.pointerEvents = 'none';
        particle.style.zIndex = '1001';
        
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        particle.style.left = centerX + 'px';
        particle.style.top = centerY + 'px';
        
        const angle = Math.random() * Math.PI * 2;
        const velocity = Math.random() * 200 + 50;
        const deltaX = Math.cos(angle) * velocity;
        const deltaY = Math.sin(angle) * velocity;
        
        particle.style.animation = `explosionParticle 1s ease-out forwards`;
        particle.style.setProperty('--deltaX', deltaX + 'px');
        particle.style.setProperty('--deltaY', deltaY + 'px');
        
        document.body.appendChild(particle);
        
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 1000);
    }
    
    // Cleanup method
    destroy() {
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
        }
        
        const cleanup = [
            'chaos-particles',
            'matrix-rain',
            'cyber-grid'
        ];
        
        cleanup.forEach(id => {
            const element = document.getElementById(id);
            if (element && element.parentNode) {
                element.parentNode.removeChild(element);
            }
        });
    }
}

// CSS for dynamic effects
const chaosCSS = `
@keyframes sparkle {
    0% { 
        transform: scale(0) rotate(0deg);
        opacity: 1;
    }
    50% { 
        transform: scale(1) rotate(180deg);
        opacity: 1;
    }
    100% { 
        transform: scale(0) rotate(360deg);
        opacity: 0;
    }
}

@keyframes explosionParticle {
    0% {
        transform: translate(0, 0) scale(1) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translate(var(--deltaX), var(--deltaY)) scale(0) rotate(720deg);
        opacity: 0;
    }
}

@keyframes matrixFall {
    0% { 
        transform: translateY(-100vh);
        opacity: 1;
    }
    90% { 
        opacity: 1;
    }
    100% { 
        transform: translateY(100vh);
        opacity: 0;
    }
}

@keyframes particleFloat {
    0% {
        transform: translateY(100vh) translateX(0px) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(-10vh) translateX(var(--drift, 100px)) rotate(360deg);
        opacity: 0;
    }
}
`;

// Inject CSS
const style = document.createElement('style');
style.textContent = chaosCSS;
document.head.appendChild(style);

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.chaosEngine = new ChaosEngine();
    });
} else {
    window.chaosEngine = new ChaosEngine();
}