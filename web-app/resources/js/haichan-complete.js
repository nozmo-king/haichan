/**
 * HAICHAN COMPLETE - Advanced Mining System
 * Enhanced mining with rare patterns and multipliers
 */

class HaichanComplete extends HaichanMiner {
    constructor() {
        super();
        this.completeEnabled = false;
        this.completePattterns = {
            'deadbeef': { points: 5000, rarity: '🏆 LEGENDARY', color: '#FFD700' },
            '1337': { points: 2500, rarity: '👑 ELITE', color: '#FF6B35' },
            'c0ffee': { points: 2000, rarity: '☕ CAFFEINE', color: '#8B4513' },
            'facade': { points: 1800, rarity: '🏗️ ARCHITECT', color: '#4169E1' },
            'defaced': { points: 1500, rarity: '💀 HACKER', color: '#DC143C' },
            'babe': { points: 1200, rarity: '💖 CHARM', color: '#FF69B4' },
            'beef': { points: 1000, rarity: '💪 POWER', color: '#B22222' }
        };
        this.initializeComplete();
    }

    initializeComplete() {
        this.checkCompleteAccess();
        this.setupCompleteUI();
        this.setupCompleteEventListeners();
    }

    async checkCompleteAccess() {
        try {
            const response = await fetch('/api/complete/progression', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            this.completeEnabled = data.complete_status.enabled;
            
            if (this.completeEnabled) {
                this.showCompleteActivation();
                this.updateCompleteUI(data);
            }
        } catch (error) {
            console.warn('Complete access check failed:', error);
        }
    }

    setupCompleteUI() {
        // Enhance existing mining dashboard with Complete features
        const dashboard = document.getElementById('mining-dashboard');
        if (!dashboard) return;

        const completeSection = document.createElement('div');
        completeSection.className = 'complete-section';
        completeSection.innerHTML = `
            <div class="complete-header">
                <h3>🚀 HAICHAN COMPLETE</h3>
                <div class="complete-status" id="complete-status">Checking access...</div>
            </div>
            <div class="complete-stats" id="complete-stats" style="display: none;">
                <div class="stat-item">
                    <span class="stat-label">Complete Rank:</span>
                    <span class="stat-value" id="complete-rank">-</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Legendary Finds:</span>
                    <span class="stat-value" id="legendary-finds">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Next Milestone:</span>
                    <span class="stat-value" id="next-milestone">-</span>
                </div>
            </div>
            <div class="complete-patterns" id="complete-patterns" style="display: none;">
                <h4>🎯 Target Patterns</h4>
                <div class="pattern-grid" id="pattern-grid"></div>
            </div>
        `;

        dashboard.appendChild(completeSection);
        this.renderCompletePatterns();
    }

    renderCompletePatterns() {
        const patternGrid = document.getElementById('pattern-grid');
        if (!patternGrid) return;

        patternGrid.innerHTML = '';
        
        Object.entries(this.completePattterns).forEach(([pattern, data]) => {
            const patternElement = document.createElement('div');
            patternElement.className = 'pattern-target';
            patternElement.innerHTML = `
                <div class="pattern-code" style="color: ${data.color};">${pattern.toUpperCase()}</div>
                <div class="pattern-rarity">${data.rarity}</div>
                <div class="pattern-points">${data.points.toLocaleString()} pts</div>
            `;
            patternGrid.appendChild(patternElement);
        });
    }

    setupCompleteEventListeners() {
        // Enhanced pattern detection for Complete patterns
        this.originalCheckRarePattern = this.checkRarePattern.bind(this);
        this.checkRarePattern = this.checkCompletePattern.bind(this);
    }

    checkCompletePattern(hash) {
        // First check original rare patterns
        const originalResult = this.originalCheckRarePattern(hash);
        if (originalResult) return originalResult;

        // Check Complete patterns
        for (const [pattern, data] of Object.entries(this.completePattterns)) {
            if (hash.toLowerCase().startsWith(pattern)) {
                this.showCompleteDiscovery(pattern, hash, data);
                return { pattern, ...data };
            }
        }

        return null;
    }

    async submitCompleteProof(proof, target, type) {
        try {
            const response = await fetch('/api/complete/proof', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    hash: proof.hash,
                    nonce: proof.nonce,
                    data: proof.data,
                    pattern: proof.pattern,
                    thread_id: target
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.handleCompleteSuccess(result);
            }

            return result;
        } catch (error) {
            console.error('Complete proof submission failed:', error);
            return { success: false };
        }
    }

    showCompleteActivation() {
        const statusElement = document.getElementById('complete-status');
        if (statusElement) {
            statusElement.innerHTML = '✅ COMPLETE ACCESS GRANTED';
            statusElement.style.color = '#28a745';
        }

        // Show Complete sections
        const completeStats = document.getElementById('complete-stats');
        const completePatterns = document.getElementById('complete-patterns');
        
        if (completeStats) completeStats.style.display = 'block';
        if (completePatterns) completePatterns.style.display = 'block';

        // Show activation notification
        this.showSystemNotification('🚀 HAICHAN COMPLETE ACTIVATED', 'You now have access to legendary patterns and enhanced rewards!');
    }

    updateCompleteUI(data) {
        // Update Complete rank
        const rankElement = document.getElementById('complete-rank');
        if (rankElement) {
            rankElement.textContent = data.complete_status.rank;
        }

        // Update milestones
        const milestoneElement = document.getElementById('next-milestone');
        if (milestoneElement && data.progression.next_milestone) {
            milestoneElement.textContent = `${data.progression.next_milestone.points.toLocaleString()} pts`;
        }
    }

    showCompleteDiscovery(pattern, hash, data) {
        // Enhanced visual effects for Complete discoveries
        this.triggerCompleteVisualEffect(pattern, data);
        this.showCompleteNotification(pattern, hash, data);
        this.updateCompleteAchievements(pattern);
    }

    triggerCompleteVisualEffect(pattern, data) {
        // Screen flash effect for legendary discoveries
        if (data.points >= 2500) {
            this.triggerScreenFlash(data.color);
        }

        // Particle explosion effect
        this.createCompleteParticleEffect(data.color);
        
        // Neural canvas enhancement
        if (this.triggerVisualization) {
            this.triggerVisualization(pattern, 'legendary');
        }
    }

    triggerScreenFlash(color) {
        const flash = document.createElement('div');
        flash.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: ${color};
            opacity: 0.3;
            z-index: 999999;
            pointer-events: none;
            animation: completeFlash 0.5s ease-out;
        `;

        // Add CSS animation if not exists
        if (!document.querySelector('#complete-animations')) {
            const style = document.createElement('style');
            style.id = 'complete-animations';
            style.textContent = `
                @keyframes completeFlash {
                    0% { opacity: 0.5; }
                    50% { opacity: 0.8; }
                    100% { opacity: 0; }
                }
                @keyframes completeParticle {
                    0% { transform: translate(0, 0) scale(1); opacity: 1; }
                    100% { transform: translate(var(--dx), var(--dy)) scale(0); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(flash);
        setTimeout(() => flash.remove(), 500);
    }

    createCompleteParticleEffect(color) {
        const particleCount = 20;
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: fixed;
                left: 50%;
                top: 50%;
                width: 4px;
                height: 4px;
                background: ${color};
                border-radius: 50%;
                z-index: 10000;
                pointer-events: none;
                --dx: ${(Math.random() - 0.5) * 400}px;
                --dy: ${(Math.random() - 0.5) * 400}px;
                animation: completeParticle 1s ease-out forwards;
            `;
            document.body.appendChild(particle);
            setTimeout(() => particle.remove(), 1000);
        }
    }

    showCompleteNotification(pattern, hash, data) {
        const notification = document.createElement('div');
        notification.className = 'complete-notification';
        notification.innerHTML = `
            <div class="complete-notification-content">
                <div class="complete-pattern" style="color: ${data.color};">
                    ${pattern.toUpperCase()}
                </div>
                <div class="complete-rarity">${data.rarity}</div>
                <div class="complete-points">+${data.points.toLocaleString()} POINTS</div>
                <div class="complete-hash">${hash.substring(0, 20)}...</div>
            </div>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, ${data.color}22, ${data.color}11);
            border: 2px solid ${data.color};
            border-radius: 12px;
            padding: 20px;
            color: white;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            z-index: 10001;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 20px ${data.color}44;
            animation: completeSlideIn 0.5s ease-out;
            max-width: 300px;
        `;

        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'completeSlideOut 0.5s ease-in forwards';
            setTimeout(() => notification.remove(), 500);
        }, 5000);

        // Add slide animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes completeSlideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes completeSlideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        if (!document.querySelector('#complete-slide-animations')) {
            style.id = 'complete-slide-animations';
            document.head.appendChild(style);
        }
    }

    updateCompleteAchievements(pattern) {
        const legendaryFinds = document.getElementById('legendary-finds');
        if (legendaryFinds) {
            const current = parseInt(legendaryFinds.textContent) || 0;
            legendaryFinds.textContent = current + 1;
        }

        // Store achievement locally
        const achievements = JSON.parse(localStorage.getItem('haichan-complete-achievements') || '{}');
        achievements[pattern] = (achievements[pattern] || 0) + 1;
        achievements.total = Object.values(achievements).reduce((a, b) => typeof b === 'number' ? a + b : a, 0);
        localStorage.setItem('haichan-complete-achievements', JSON.stringify(achievements));
    }

    handleCompleteSuccess(result) {
        // Update UI with Complete results
        if (result.unlocks && result.unlocks.length > 0) {
            this.showUnlockNotification(result.unlocks);
        }

        if (result.framework_bonus > 0) {
            this.showFrameworkBonusNotification(result.framework_bonus);
        }

        // Update progression display
        this.updateProgressionDisplay(result);
    }

    showUnlockNotification(unlocks) {
        unlocks.forEach(unlock => {
            const notification = document.createElement('div');
            notification.innerHTML = `
                <div style="background: linear-gradient(45deg, #FFD700, #FFA500); color: black; padding: 15px; border-radius: 8px; margin: 10px; font-weight: bold; text-align: center;">
                    🔓 UNLOCK: ${unlock.toUpperCase()}
                </div>
            `;
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 10002;
                animation: completeSlideIn 0.5s ease-out;
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 4000);
        });
    }

    showFrameworkBonusNotification(bonus) {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="background: linear-gradient(45deg, #00FF00, #32CD32); color: black; padding: 10px; border-radius: 6px; font-weight: bold; text-align: center;">
                💰 FRAMEWORK BONUS: +${bonus} pts
            </div>
        `;
        notification.style.cssText = `
            position: fixed;
            top: 180px;
            right: 20px;
            z-index: 10002;
            animation: completeSlideIn 0.5s ease-out;
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    updateProgressionDisplay(result) {
        // Update rank display
        const rankElement = document.getElementById('complete-rank');
        if (rankElement && result.complete_tier) {
            rankElement.textContent = result.complete_tier;
            rankElement.style.color = '#FFD700';
        }
    }

    showSystemNotification(title, message) {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 20px; border-radius: 12px; border: 1px solid #4a90e2; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">${title}</div>
                <div style="font-size: 14px; opacity: 0.9;">${message}</div>
            </div>
        `;
        notification.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10003;
            max-width: 400px;
            animation: completeSlideIn 0.5s ease-out;
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 6000);
    }

    // Override original mining to use Complete submission when available
    async submitProof(proof, target, type) {
        if (this.completeEnabled && this.completePattterns[proof.pattern]) {
            return await this.submitCompleteProof(proof, target, type);
        }
        return await super.submitProof(proof, target, type);
    }
}

// Replace global miner with Complete version
function initializeCompleteSystem() {
    if (window.haichanMiner) {
        // Upgrade existing miner to Complete
        window.haichanComplete = new HaichanComplete();
        window.haichanMiner = window.haichanComplete;
    }
}

// Auto-initialize Complete system
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCompleteSystem);
} else {
    initializeCompleteSystem();
}