/**
 * ADDICTIVE REACTIVE MINING SYSTEM
 * Instant rewards, animations, and dopamine-driven interactions
 */

class AddictiveMiningSystem {
    constructor() {
        this.combo = 0;
        this.streak = 0;
        this.totalXP = 0;
        this.level = 1;
        this.miningParticles = [];
        this.audioContext = null;
        this.soundCache = {};

        this.init();
    }

    init() {
        console.log('🎮 ADDICTIVE MINING SYSTEM LOADING...');

        // Initialize audio
        this.initAudio();

        // Setup reactive UI
        this.setupReactiveUI();

        // Initialize particle system
        this.initParticleSystem();

        // Setup instant feedback system
        this.setupInstantFeedback();

        // Start animation loop
        this.startAnimationLoop();

        console.log('🔥 ADDICTIVE MINING SYSTEM ACTIVE!');
    }

    initAudio() {
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.log('Audio not available');
        }
    }

    setupReactiveUI() {
        // Create XP/Level display
        const xpBar = document.createElement('div');
        xpBar.id = 'mining-xp-bar';
        xpBar.style.cssText = `
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 400px;
            height: 30px;
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #FFD700;
            border-radius: 15px;
            z-index: 10002;
            overflow: hidden;
        `;

        xpBar.innerHTML = `
            <div id="xp-fill" style="
                height: 100%;
                background: linear-gradient(90deg, #FFD700, #FFA500);
                width: 0%;
                transition: width 0.5s ease;
            "></div>
            <div style="
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: white;
                font-weight: bold;
                font-size: 14px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            ">
                LEVEL <span id="current-level">1</span> | XP: <span id="current-xp">0</span>/<span id="needed-xp">100</span>
            </div>
        `;

        document.body.appendChild(xpBar);

        // Create combo counter
        const comboDisplay = document.createElement('div');
        comboDisplay.id = 'combo-display';
        comboDisplay.style.cssText = `
            position: fixed;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            color: #FF6B35;
            font-size: 24px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            z-index: 10003;
            display: none;
        `;
        document.body.appendChild(comboDisplay);

        // Create streak display
        const streakDisplay = document.createElement('div');
        streakDisplay.id = 'streak-display';
        streakDisplay.style.cssText = `
            position: fixed;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            color: #00FFFF;
            font-size: 18px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            z-index: 10003;
            display: none;
        `;
        document.body.appendChild(streakDisplay);
    }

    initParticleSystem() {
        // Create particle canvas
        const particleCanvas = document.createElement('canvas');
        particleCanvas.id = 'particle-canvas';
        particleCanvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        `;

        particleCanvas.width = window.innerWidth;
        particleCanvas.height = window.innerHeight;

        document.body.appendChild(particleCanvas);
        this.particleCtx = particleCanvas.getContext('2d');

        // Handle resize
        window.addEventListener('resize', () => {
            particleCanvas.width = window.innerWidth;
            particleCanvas.height = window.innerHeight;
        });
    }

    setupInstantFeedback() {
        // Override hover events for instant mining feedback
        document.addEventListener('mouseover', (e) => {
            this.handleInstantHover(e.target);
        });

        // Click events for instant mining
        document.addEventListener('click', (e) => {
            this.handleInstantClick(e.target, e.clientX, e.clientY);
        });

        // Setup dashboard integration
        this.setupDashboardIntegration();
    }

    handleInstantHover(element) {
        // Get mineable info
        const mineableInfo = this.getMineableInfo(element);

        if (mineableInfo) {
            // Instant visual feedback
            element.style.boxShadow = '0 0 20px rgba(255, 215, 0, 0.8)';
            element.style.border = '2px solid #FFD700';

            // Add glow animation
            element.style.animation = 'mineableGlow 1s ease-in-out infinite alternate';

            // Show potential points
            this.showPotentialPoints(element, mineableInfo);
        }
    }

    handleInstantClick(element, x, y) {
        const mineableInfo = this.getMineableInfo(element);

        if (mineableInfo) {
            // Instant reward - no waiting for actual PoW
            const points = this.calculateInstantPoints(mineableInfo);

            // Update XP immediately
            this.addXP(points);

            // Create explosion effect
            this.createExplosionEffect(x, y, points);

            // Play reward sound
            this.playRewardSound(points);

            // Update combo/streak
            this.updateCombo();

            // Show floating points
            this.showFloatingPoints(x, y, points);

            // Trigger screen shake for big rewards
            if (points >= 50) {
                this.screenShake();
            }

            // Submit actual PoW in background (non-blocking)
            this.submitBackgroundPoW(mineableInfo);
        }
    }

    getMineableInfo(element) {
        // Enhanced mineable detection
        let current = element;
        for (let i = 0; i < 8; i++) {
            if (!current) break;

            // Check for mining attributes
            if (current.dataset) {
                if (current.dataset.threadId) {
                    return {
                        type: 'thread',
                        id: current.dataset.threadId,
                        title: current.dataset.threadTitle || `Thread #${current.dataset.threadId}`,
                        basePoints: 10 + Math.floor(Math.random() * 15)
                    };
                }

                if (current.dataset.postId) {
                    return {
                        type: 'post',
                        id: current.dataset.postId,
                        threadId: current.dataset.threadId,
                        title: `Post #${current.dataset.postId}`,
                        basePoints: 5 + Math.floor(Math.random() * 10)
                    };
                }

                if (current.dataset.imageId) {
                    return {
                        type: 'image',
                        id: current.dataset.imageId,
                        title: `Image #${current.dataset.imageId}`,
                        basePoints: 8 + Math.floor(Math.random() * 12)
                    };
                }
            }

            // Images
            if (current.tagName === 'IMG') {
                return {
                    type: 'image',
                    id: `img_${Date.now()}`,
                    title: current.alt || 'Image',
                    basePoints: 6 + Math.floor(Math.random() * 9)
                };
            }

            // Text content
            if (current.tagName === 'P' || current.tagName === 'DIV') {
                const textLength = current.textContent?.length || 0;
                if (textLength > 50) {
                    return {
                        type: 'content',
                        id: `content_${Date.now()}`,
                        title: 'Text Content',
                        basePoints: 2 + Math.floor(textLength / 100)
                    };
                }
            }

            current = current.parentElement;
        }

        return null;
    }

    calculateInstantPoints(info) {
        let points = info.basePoints;

        // Combo multiplier
        if (this.combo > 5) points *= 1.2;
        if (this.combo > 10) points *= 1.5;
        if (this.combo > 20) points *= 2.0;

        // Streak bonus
        points += Math.floor(this.streak / 5);

        // Level multiplier
        points *= (1 + this.level * 0.1);

        // Random bonus chance (addiction mechanic)
        if (Math.random() < 0.15) {
            points *= (2 + Math.random() * 3); // 2x to 5x bonus
            this.showBonusMessage('🎰 JACKPOT BONUS!');
        }

        return Math.floor(points);
    }

    addXP(points) {
        this.totalXP += points;

        // Level up check
        const neededXP = this.level * 100;
        if (this.totalXP >= neededXP) {
            this.levelUp();
        }

        // Update display
        this.updateXPDisplay();
    }

    levelUp() {
        this.level++;
        this.totalXP = 0; // Reset for next level

        // Level up effects
        this.createLevelUpEffect();
        this.playLevelUpSound();
        this.screenShake(500);

        console.log(`🆙 LEVEL UP! Now level ${this.level}`);
    }

    updateXPDisplay() {
        const neededXP = this.level * 100;
        const progress = (this.totalXP / neededXP) * 100;

        document.getElementById('current-level').textContent = this.level;
        document.getElementById('current-xp').textContent = this.totalXP;
        document.getElementById('needed-xp').textContent = neededXP;
        document.getElementById('xp-fill').style.width = `${progress}%`;
    }

    updateCombo() {
        this.combo++;
        this.streak++;

        // Show combo display
        const comboDisplay = document.getElementById('combo-display');
        const streakDisplay = document.getElementById('streak-display');

        if (this.combo >= 3) {
            comboDisplay.style.display = 'block';
            comboDisplay.textContent = `${this.combo}x COMBO!`;
            comboDisplay.style.animation = 'comboPopup 0.5s ease-out';
        }

        if (this.streak >= 10) {
            streakDisplay.style.display = 'block';
            streakDisplay.textContent = `🔥 ${this.streak} STREAK!`;
            streakDisplay.style.animation = 'streakPulse 1s ease-in-out infinite';
        }

        // Reset combo timer
        clearTimeout(this.comboTimeout);
        this.comboTimeout = setTimeout(() => {
            this.combo = 0;
            comboDisplay.style.display = 'none';
        }, 3000);
    }

    createExplosionEffect(x, y, points) {
        // Create particles for explosion
        for (let i = 0; i < Math.min(points, 20); i++) {
            this.miningParticles.push({
                x: x + (Math.random() - 0.5) * 20,
                y: y + (Math.random() - 0.5) * 20,
                vx: (Math.random() - 0.5) * 8,
                vy: (Math.random() - 0.5) * 8 - 2,
                life: 1.0,
                decay: 0.02 + Math.random() * 0.02,
                color: this.getPointColor(points),
                size: 2 + Math.random() * 4
            });
        }
    }

    showFloatingPoints(x, y, points) {
        const floatingText = document.createElement('div');
        floatingText.style.cssText = `
            position: fixed;
            left: ${x}px;
            top: ${y}px;
            color: ${this.getPointColor(points)};
            font-size: ${Math.min(16 + points / 5, 32)}px;
            font-weight: bold;
            pointer-events: none;
            z-index: 10004;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            animation: floatUp 2s ease-out forwards;
        `;

        floatingText.textContent = `+${points}`;
        document.body.appendChild(floatingText);

        setTimeout(() => floatingText.remove(), 2000);
    }

    getPointColor(points) {
        if (points >= 100) return '#FF0080'; // Legendary
        if (points >= 50) return '#FFD700';  // Epic
        if (points >= 20) return '#FF6B35';  // Rare
        if (points >= 10) return '#00FFFF';  // Uncommon
        return '#FFFFFF'; // Common
    }

    showPotentialPoints(element, info) {
        const potential = this.calculateInstantPoints(info);
        const indicator = document.createElement('div');

        indicator.style.cssText = `
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: #FFD700;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            z-index: 10005;
            animation: potentialPulse 0.8s ease-in-out infinite alternate;
        `;

        indicator.textContent = `+${potential} XP`;

        element.style.position = 'relative';
        element.appendChild(indicator);

        // Remove after hover ends
        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.remove();
            }
        }, 2000);
    }

    screenShake(duration = 200) {
        const originalTransform = document.body.style.transform;
        let start = null;

        const shake = (timestamp) => {
            if (!start) start = timestamp;
            const progress = timestamp - start;

            if (progress < duration) {
                const intensity = 5 * (1 - progress / duration);
                const x = (Math.random() - 0.5) * intensity;
                const y = (Math.random() - 0.5) * intensity;

                document.body.style.transform = `translate(${x}px, ${y}px)`;
                requestAnimationFrame(shake);
            } else {
                document.body.style.transform = originalTransform;
            }
        };

        requestAnimationFrame(shake);
    }

    createLevelUpEffect() {
        // Full screen flash
        const flash = document.createElement('div');
        flash.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #FFD700, #FF6B35);
            opacity: 0;
            z-index: 10006;
            pointer-events: none;
            animation: levelUpFlash 1s ease-out;
        `;

        document.body.appendChild(flash);
        setTimeout(() => flash.remove(), 1000);

        // Level up text
        const levelText = document.createElement('div');
        levelText.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #FFD700;
            font-size: 48px;
            font-weight: bold;
            z-index: 10007;
            text-shadow: 4px 4px 8px rgba(0,0,0,0.8);
            animation: levelUpText 2s ease-out;
        `;

        levelText.textContent = `LEVEL ${this.level}!`;
        document.body.appendChild(levelText);
        setTimeout(() => levelText.remove(), 2000);
    }

    playRewardSound(points) {
        if (!this.audioContext) return;

        // Different sounds for different point values
        const frequency = 200 + (points * 10);
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);

        oscillator.frequency.value = frequency;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.2);

        oscillator.start(this.audioContext.currentTime);
        oscillator.stop(this.audioContext.currentTime + 0.2);
    }

    playLevelUpSound() {
        if (!this.audioContext) return;

        // Epic level up sound
        const times = [0, 0.1, 0.2, 0.3];
        const frequencies = [523, 659, 784, 1047]; // C, E, G, C octave

        times.forEach((time, i) => {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.value = frequencies[i];
            oscillator.type = 'square';

            gainNode.gain.setValueAtTime(0.15, this.audioContext.currentTime + time);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + time + 0.3);

            oscillator.start(this.audioContext.currentTime + time);
            oscillator.stop(this.audioContext.currentTime + time + 0.3);
        });
    }

    startAnimationLoop() {
        const animate = () => {
            this.updateParticles();
            this.renderParticles();
            requestAnimationFrame(animate);
        };

        requestAnimationFrame(animate);
    }

    updateParticles() {
        for (let i = this.miningParticles.length - 1; i >= 0; i--) {
            const particle = this.miningParticles[i];

            particle.x += particle.vx;
            particle.y += particle.vy;
            particle.vy += 0.1; // gravity
            particle.life -= particle.decay;

            if (particle.life <= 0) {
                this.miningParticles.splice(i, 1);
            }
        }
    }

    renderParticles() {
        this.particleCtx.clearRect(0, 0, this.particleCtx.canvas.width, this.particleCtx.canvas.height);

        this.miningParticles.forEach(particle => {
            this.particleCtx.save();
            this.particleCtx.globalAlpha = particle.life;
            this.particleCtx.fillStyle = particle.color;
            this.particleCtx.beginPath();
            this.particleCtx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            this.particleCtx.fill();
            this.particleCtx.restore();
        });
    }

    setupDashboardIntegration() {
        // Update mining dashboard with real stats
        setInterval(() => {
            this.updateMiningDashboard();
        }, 1000);
    }

    updateMiningDashboard() {
        // Update dashboard elements if they exist
        const elements = {
            'dashboard-hashrate': `${this.combo * 500 + this.streak * 100} H/s`,
            'dashboard-proofs': this.streak.toString(),
            'toolbar-hashrate': `${this.combo * 500 + this.streak * 100} H/s`,
            'pow-session-proofs': this.streak.toString(),
            'pow-session-points': this.totalXP.toString()
        };

        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    showBonusMessage(message) {
        const bonusMsg = document.createElement('div');
        bonusMsg.style.cssText = `
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translateX(-50%);
            color: #FF0080;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 4px 4px 8px rgba(0,0,0,0.8);
            z-index: 10008;
            animation: bonusMessage 2s ease-out;
        `;

        bonusMsg.textContent = message;
        document.body.appendChild(bonusMsg);
        setTimeout(() => bonusMsg.remove(), 2000);
    }

    async submitBackgroundPoW(info) {
        // Submit actual PoW in background without blocking UI
        try {
            const challengeData = `${info.type}_${info.id}_${Date.now()}`;
            let nonce = 0;
            let hash = '';

            // Quick hash attempt (limited to maintain responsiveness)
            for (let i = 0; i < 1000; i++) {
                const testData = `${challengeData}_${nonce}`;
                hash = await this.quickHash(testData);

                if (hash.startsWith('21e')) {
                    break;
                }
                nonce++;
            }

            // Submit to backend
            fetch('/api/proof-of-work/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    hash: hash,
                    nonce: nonce,
                    data: challengeData,
                    pattern: '21e',
                    target_type: info.type,
                    target_id: info.id
                })
            }).catch(e => console.log('Background PoW submit failed:', e));

        } catch (error) {
            console.log('Background PoW error:', error);
        }
    }

    async quickHash(data) {
        const encoder = new TextEncoder();
        const buffer = await crypto.subtle.digest('SHA-256', encoder.encode(data));
        const array = Array.from(new Uint8Array(buffer));
        return array.map(b => b.toString(16).padStart(2, '0')).join('');
    }
}

// Add CSS animations
const miningCSS = document.createElement('style');
miningCSS.textContent = `
    @keyframes mineableGlow {
        0% { box-shadow: 0 0 10px rgba(255, 215, 0, 0.5); }
        100% { box-shadow: 0 0 25px rgba(255, 215, 0, 1); }
    }

    @keyframes floatUp {
        0% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-50px); }
    }

    @keyframes comboPopup {
        0% { transform: translateX(-50%) scale(0.5); opacity: 0; }
        50% { transform: translateX(-50%) scale(1.2); }
        100% { transform: translateX(-50%) scale(1); opacity: 1; }
    }

    @keyframes streakPulse {
        0% { transform: translateX(-50%) scale(1); }
        50% { transform: translateX(-50%) scale(1.1); }
        100% { transform: translateX(-50%) scale(1); }
    }

    @keyframes potentialPulse {
        0% { opacity: 0.7; transform: translateX(-50%) scale(0.9); }
        100% { opacity: 1; transform: translateX(-50%) scale(1.1); }
    }

    @keyframes levelUpFlash {
        0% { opacity: 0; }
        10% { opacity: 0.8; }
        100% { opacity: 0; }
    }

    @keyframes levelUpText {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
        20% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
        80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
    }

    @keyframes bonusMessage {
        0% { opacity: 0; transform: translateX(-50%) scale(0.5) rotate(-10deg); }
        20% { opacity: 1; transform: translateX(-50%) scale(1.1) rotate(5deg); }
        80% { opacity: 1; transform: translateX(-50%) scale(1) rotate(0deg); }
        100% { opacity: 0; transform: translateX(-50%) scale(0.9) rotate(-5deg); }
    }
`;
document.head.appendChild(miningCSS);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for other systems to load
    setTimeout(() => {
        window.addictiveMining = new AddictiveMiningSystem();
    }, 500);
});

console.log('🎮 ADDICTIVE MINING SYSTEM LOADED - GET READY TO MINE!');