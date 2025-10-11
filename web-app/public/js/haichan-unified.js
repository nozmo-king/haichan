/**
 * HAICHAN CANONICAL MINING SYSTEM v2.0
 * Single unified implementation combining best features from all mining systems
 * Ultra-high performance mining with Web Workers and optimized algorithms
 */

class HaichanCanonicalMining {
    constructor() {
        this.isInitialized = false;
        this.isActive = true;
        
        // Central state management
        this.state = {
            power: 5, // 0-10 power levels
            mode: 'mouseover', // mouseover, manual, background, idle
            currentTarget: null,
            
            // Session statistics
            sessionStats: {
                startTime: Date.now(),
                totalHashes: 0,
                totalProofs: 0,
                totalPoints: 0,
                currentHashrate: 0
            },
            
            // Active mining targets
            activeTargets: new Map(),
            
            // Performance metrics
            performance: {
                avgHashTime: 0,
                cpuUsage: 0
            }
        };

        // Mining configurations - ULTRA HIGH PERFORMANCE
        this.configs = {
            patterns: {
                '21': { points: 0.1, difficulty: 'Trivial' },
                '21e': { points: 0.5, difficulty: 'Easy' },
                '21e8': { points: 100, difficulty: 'Standard' },
                '21e80': { points: 500, difficulty: 'Hard' },
                '21e800': { points: 2500, difficulty: 'Very Hard' },
                '21e8000': { points: 10000, difficulty: 'Extreme' }
            },
            rare: {
                'deadbeef': { points: 5000, rarity: '🏆 LEGENDARY' },
                '1337': { points: 2500, rarity: '👑 ELITE' },
                '777': { points: 777, rarity: '🍀 LUCKY' },
                '666': { points: 666, rarity: '😈 CURSED' },
                '000': { points: 500, rarity: '⚡ RARE' },
                '111': { points: 400, rarity: '⚡ RARE' }
            },
            powerLevels: {
                0: { name: 'Disabled', batchSize: 0, interval: 0 },
                1: { name: 'Minimal', batchSize: 100000, interval: 5 },
                2: { name: 'Low', batchSize: 250000, interval: 3 },
                3: { name: 'Light', batchSize: 500000, interval: 2 },
                4: { name: 'Below Average', batchSize: 1000000, interval: 1 },
                5: { name: 'Standard', batchSize: 2000000, interval: 1 },
                6: { name: 'Above Average', batchSize: 3000000, interval: 1 },
                7: { name: 'High', batchSize: 5000000, interval: 1 },
                8: { name: 'Very High', batchSize: 7500000, interval: 1 },
                9: { name: 'Extreme', batchSize: 10000000, interval: 1 },
                10: { name: 'MAXIMUM POWER', batchSize: 15000000, interval: 1 }
            }
        };

        // Web Workers for multithreaded mining
        this.workers = {
            webWorkers: [],
            mouseover: null,
            manual: null,
            background: null
        };

        this.intervals = {
            stats: null,
            performance: null
        };

        this.init();
    }

    init() {
        if (this.isInitialized) return;
        this.isInitialized = true;

        console.log('🧠 CANONICAL MINING: Initializing ultra-high performance mining system...');

        // Disable old systems
        this.disableOldSystems();

        // Initialize Web Workers
        this.initializeWebWorkers();

        // Setup mining modes
        this.setupMouseoverMining();
        this.setupFormMining();
        this.setupToolbarControls();

        // Start performance monitoring
        this.startPerformanceMonitoring();
        this.startUIUpdates();

        // Load saved state
        this.loadState();

        console.log('🧠 CANONICAL MINING: System fully operational');
    }

    disableOldSystems() {
        // Disable all conflicting mining systems
        const oldSystems = [
            'mouseoverMiningV2', 'mouseoverMining', 'enhancedMiningDashboard',
            'haichanMiner', 'emergencyMiner', 'haichanUnified', 'haichan2',
            'simpleMining', 'miningBrain'
        ];

        oldSystems.forEach(system => {
            if (window[system]) {
                console.log(`🧠 CANONICAL MINING: Disabling old system: ${system}`);
                try {
                    if (typeof window[system].disable === 'function') window[system].disable();
                    if (typeof window[system].stop === 'function') window[system].stop();
                    if (typeof window[system].destroy === 'function') window[system].destroy();
                } catch (e) {
                    console.log(`🧠 CANONICAL MINING: Could not disable ${system}:`, e);
                }
            }
        });

        // Clear all mining intervals
        for (let i = 0; i < 10000; i++) {
            clearInterval(i);
        }
    }

    // Initialize Web Workers for maximum performance
    initializeWebWorkers() {
        const numWorkers = Math.min(navigator.hardwareConcurrency || 4, 8);
        console.log(`🧠 CANONICAL MINING: Initializing ${numWorkers} Web Workers`);

        const workerScript = `
            // Ultra-high performance SHA-256 mining worker
            async function sha256(message) {
                const msgBuffer = new TextEncoder().encode(message);
                const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            // Ultra-fast hash for initial screening
            function ultraFastHash(str) {
                let hash = 0x811c9dc5; // FNV-1a
                for (let i = 0; i < str.length; i++) {
                    hash ^= str.charCodeAt(i);
                    hash = Math.imul(hash, 0x01000193);
                }
                const hex = Math.abs(hash).toString(16).padStart(8, '0');
                return hex + '0'.repeat(56);
            }

            self.onmessage = async function(e) {
                const { target, batchSize, workerId, taskId } = e.data;
                let results = [];
                let hashes = 0;

                for (let i = 0; i < batchSize; i++) {
                    const nonce = Math.floor(Math.random() * 4294967295);
                    const data = \`\${target.type}-\${target.id}-\${Date.now()}-\${nonce}\`;
                    
                    // Quick screening with fast hash
                    const fastHash = ultraFastHash(data);
                    if (fastHash.startsWith('21') || fastHash.includes('777') || 
                        fastHash.includes('666') || fastHash.includes('000') || 
                        fastHash.includes('111') || fastHash.includes('deadbe') || 
                        fastHash.includes('1337')) {
                        
                        // Verify with real SHA-256
                        const realHash = await sha256(data);
                        results.push({ hash: realHash, nonce, data });
                    }
                    
                    hashes++;
                    
                    // Progress updates for large batches
                    if (i > 0 && i % 100000 === 0) {
                        self.postMessage({
                            type: 'progress',
                            workerId,
                            taskId,
                            completed: i,
                            total: batchSize
                        });
                    }
                }

                self.postMessage({
                    type: 'complete',
                    workerId,
                    taskId,
                    results,
                    hashes
                });
            };
        `;

        for (let i = 0; i < numWorkers; i++) {
            try {
                const workerBlob = new Blob([workerScript], { type: 'application/javascript' });
                const workerUrl = URL.createObjectURL(workerBlob);
                const worker = new Worker(workerUrl);

                worker.onmessage = (e) => this.handleWorkerMessage(e, i);
                worker.onerror = (e) => console.error(`WebWorker ${i} error:`, e);

                this.workers.webWorkers.push({
                    worker,
                    id: i,
                    busy: false,
                    url: workerUrl
                });
            } catch (e) {
                console.warn('WebWorker initialization failed, falling back to main thread:', e);
                break;
            }
        }
    }

    // Setup mouseover mining
    setupMouseoverMining() {
        if (this.state.mode !== 'mouseover') return;

        console.log('🧠 CANONICAL MINING: Setting up mouseover mining');

        document.removeEventListener('mouseover', this.handleMouseover);
        document.removeEventListener('mouseout', this.handleMouseout);

        this.handleMouseover = this.handleMouseover.bind(this);
        this.handleMouseout = this.handleMouseout.bind(this);

        document.addEventListener('mouseover', this.handleMouseover);
        document.addEventListener('mouseout', this.handleMouseout);
    }

    handleMouseover(event) {
        if (this.state.mode !== 'mouseover' || this.state.power === 0) return;

        const target = this.getMineableTarget(event.target);
        if (target && !this.state.activeTargets.has(target.id)) {
            this.startMining(target);
        }
    }

    handleMouseout(event) {
        const target = this.getMineableTarget(event.target);
        if (target && this.state.activeTargets.has(target.id)) {
            setTimeout(() => {
                if (!event.target.matches(':hover')) {
                    this.stopMining(target);
                }
            }, 100);
        }
    }

    getMineableTarget(element) {
        let current = element;
        for (let i = 0; i < 6; i++) {
            if (!current) break;

            // Check for images
            if (current.tagName === 'IMG' && current.src) {
                return {
                    id: current.src,
                    type: 'image',
                    element: current,
                    displayName: `🖼️ Image`,
                    points: 25
                };
            }

            // Check for posts
            if (current.classList && current.classList.contains('post')) {
                const postNo = current.querySelector('.post-no');
                if (postNo) {
                    const match = postNo.textContent.match(/No\.(\d+)/);
                    if (match) {
                        return {
                            id: `post-${match[1]}`,
                            type: 'post',
                            element: current,
                            displayName: `💬 Post #${match[1]}`,
                            points: 20
                        };
                    }
                }
            }

            // Check for threads
            if (current.classList && current.classList.contains('catalog-thread')) {
                const threadId = current.dataset.threadId;
                if (threadId) {
                    return {
                        id: `thread-${threadId}`,
                        type: 'thread',
                        element: current,
                        displayName: `🧵 Thread #${threadId}`,
                        points: 22
                    };
                }
            }

            current = current.parentElement;
        }
        return null;
    }

    async startMining(target) {
        console.log(`🧠 CANONICAL MINING: Starting ultra-fast mining on ${target.displayName}`);

        this.state.currentTarget = target;
        this.state.activeTargets.set(target.id, {
            target,
            startTime: Date.now(),
            hashes: 0,
            worker: null
        });

        this.addMiningVisual(target.element);
        this.updateDashboardTarget(target.displayName);

        // Start mining worker
        const session = this.state.activeTargets.get(target.id);
        session.worker = setInterval(() => {
            this.performMining(target);
        }, this.configs.powerLevels[this.state.power].interval);
    }

    stopMining(target) {
        console.log(`🧠 CANONICAL MINING: Stopping mining on ${target.displayName}`);

        const session = this.state.activeTargets.get(target.id);
        if (session) {
            clearInterval(session.worker);
            this.state.activeTargets.delete(target.id);
            this.removeMiningVisual(target.element);
        }

        if (this.state.currentTarget && this.state.currentTarget.id === target.id) {
            this.state.currentTarget = null;
            this.updateDashboardTarget('Hover over content to begin mining');
        }
    }

    async performMining(target) {
        const batchSize = this.configs.powerLevels[this.state.power].batchSize;
        const startTime = performance.now();

        // Use Web Workers for maximum performance if available
        const result = this.workers.webWorkers.length > 0
            ? await this.performWebWorkerMining(target, batchSize)
            : await this.performHighSpeedMining(target, batchSize);

        if (result.found) {
            this.handleProofFound(result.hash, result.nonce, result.data, result.pattern, result.points, target);
        }

        this.state.sessionStats.totalHashes += result.hashes;
        this.updatePerformanceMetrics(startTime, result.hashes);
    }

    async performHighSpeedMining(target, maxHashes) {
        const startTime = performance.now();
        let totalHashes = 0;

        while (totalHashes < maxHashes) {
            const batchSize = Math.min(50000, maxHashes - totalHashes);

            for (let i = 0; i < batchSize; i++) {
                const nonce = Math.floor(Math.random() * 4294967295);
                const data = `${target.type}-${target.id}-${Date.now()}-${nonce}`;

                // Ultra-fast screening
                const fastHash = this.ultraFastHash(data);
                if (this.fastPatternCheck(fastHash)) {
                    // Verify with real SHA-256
                    const realHash = await this.realSha256(data);

                    // Check for patterns
                    const result = this.checkAllPatterns(realHash);
                    if (result.found) {
                        return {
                            found: true,
                            hash: realHash,
                            nonce: nonce,
                            data: data,
                            pattern: result.pattern,
                            points: result.points,
                            hashes: totalHashes + i + 1
                        };
                    }
                }
                totalHashes++;
            }

            // Yield control
            await new Promise(resolve => setTimeout(resolve, 0));
        }

        return { found: false, hashes: totalHashes };
    }

    async performWebWorkerMining(target, maxHashes) {
        if (this.workers.webWorkers.length === 0) {
            return await this.performHighSpeedMining(target, maxHashes);
        }

        const availableWorkers = this.workers.webWorkers.filter(w => !w.busy);
        if (availableWorkers.length === 0) {
            return await this.performHighSpeedMining(target, maxHashes);
        }

        // Distribute work across workers
        const hashesPerWorker = Math.ceil(maxHashes / availableWorkers.length);
        const promises = [];
        const taskId = Date.now();

        for (let i = 0; i < availableWorkers.length; i++) {
            const worker = availableWorkers[i];
            worker.busy = true;

            const promise = new Promise((resolve) => {
                const timeout = setTimeout(() => {
                    resolve({ found: false, hashes: 0, worker: worker.id });
                }, 30000);

                const handler = (e) => {
                    if (e.data.workerId === worker.id && e.data.taskId === taskId && e.data.type === 'complete') {
                        clearTimeout(timeout);
                        worker.worker.removeEventListener('message', handler);
                        worker.busy = false;

                        // Check results for patterns
                        for (const result of e.data.results) {
                            const found = this.checkAllPatterns(result.hash);
                            if (found.found) {
                                resolve({
                                    found: true,
                                    ...found,
                                    ...result,
                                    hashes: e.data.hashes,
                                    worker: worker.id
                                });
                                return;
                            }
                        }

                        resolve({
                            found: false,
                            hashes: e.data.hashes,
                            worker: worker.id
                        });
                    }
                };

                worker.worker.addEventListener('message', handler);
            });

            worker.worker.postMessage({
                target,
                batchSize: hashesPerWorker,
                workerId: worker.id,
                taskId
            });

            promises.push(promise);
        }

        const results = await Promise.allSettled(promises);

        // Find first successful result
        for (const result of results) {
            if (result.status === 'fulfilled' && result.value.found) {
                return result.value;
            }
        }

        // Calculate total hashes
        const totalHashes = results
            .filter(r => r.status === 'fulfilled')
            .reduce((sum, r) => sum + r.value.hashes, 0);

        return { found: false, hashes: totalHashes };
    }

    ultraFastHash(str) {
        let hash = 0x811c9dc5; // FNV-1a
        for (let i = 0; i < str.length; i++) {
            hash ^= str.charCodeAt(i);
            hash = Math.imul(hash, 0x01000193);
        }
        const hex = Math.abs(hash).toString(16).padStart(8, '0');
        return hex + '0'.repeat(56);
    }

    fastPatternCheck(hash) {
        const start = hash.substring(0, 4);
        return start.startsWith('21') ||
               hash.includes('777') ||
               hash.includes('666') ||
               hash.includes('000') ||
               hash.includes('111') ||
               hash.includes('deadbe') ||
               hash.includes('1337');
    }

    async realSha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    checkAllPatterns(hash) {
        // Check rare patterns first
        for (const [pattern, data] of Object.entries(this.configs.rare)) {
            if (hash.toLowerCase().includes(pattern.toLowerCase())) {
                return { found: true, pattern, points: data.points };
            }
        }

        // Check standard patterns
        for (const [pattern, config] of Object.entries(this.configs.patterns)) {
            if (hash.startsWith(pattern)) {
                return { found: true, pattern, points: config.points };
            }
        }

        return { found: false };
    }

    handleWorkerMessage(e, workerId) {
        if (e.data.type === 'progress') {
            console.log(`Worker ${workerId}: ${e.data.completed}/${e.data.total} hashes`);
        }
    }

    async handleProofFound(hash, nonce, data, pattern, points, target) {
        console.log(`🧠 CANONICAL MINING: 💎 PROOF FOUND! ${pattern} (+${points} points)`);

        this.state.sessionStats.totalProofs++;
        this.state.sessionStats.totalPoints += points;

        // Update enhanced dashboard with proof
        if (window.enhancedMiningDashboard) {
            window.enhancedMiningDashboard.addProof(hash, points);
        }

        // Show celebration
        this.showProofCelebration(target.element, points);

        // Submit proof
        try {
            await this.submitProof({
                hash,
                nonce,
                data,
                pattern,
                points,
                target_type: target.type,
                target_id: target.id
            });
        } catch (error) {
            console.error('🧠 CANONICAL MINING: Submit error:', error);
        }
    }

    async submitProof(proof) {
        const response = await fetch('/api/submit-proof', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(proof)
        });
        return await response.json();
    }

    updatePerformanceMetrics(startTime, hashCount) {
        const elapsed = performance.now() - startTime;
        this.state.performance.avgHashTime = elapsed / hashCount;

        const hashrate = elapsed > 0 ? hashCount / (elapsed / 1000) : 0;
        this.state.sessionStats.currentHashrate = Math.floor(hashrate);

        // Log high performance
        if (hashrate > 1000000) {
            console.log(`🧠 CANONICAL MINING: ULTRA HIGH PERFORMANCE - ${Math.floor(hashrate/1000000)}M H/s`);
        }
    }

    // Visual effects
    addMiningVisual(element) {
        if (!element) return;
        element.classList.add('mining-active');
        if (!document.getElementById('mining-active-styles')) {
            const style = document.createElement('style');
            style.id = 'mining-active-styles';
            style.textContent = `
                .mining-active {
                    box-shadow: 0 0 20px rgba(0, 255, 136, 0.8) !important;
                    border: 2px solid #00ff88 !important;
                    animation: miningGlow 1.5s infinite alternate;
                }
                @keyframes miningGlow {
                    0% { box-shadow: 0 0 20px rgba(0, 255, 136, 0.8); }
                    100% { box-shadow: 0 0 30px rgba(0, 255, 136, 1.0); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    removeMiningVisual(element) {
        if (!element) return;
        element.classList.remove('mining-active');
    }

    showProofCelebration(element, points) {
        if (!element) return;

        const celebration = document.createElement('div');
        celebration.textContent = `💎 +${points}!`;
        celebration.style.cssText = `
            position: fixed;
            color: #00ff88;
            font-weight: bold;
            font-size: 16px;
            z-index: 999999;
            pointer-events: none;
            animation: floatUp 2s ease-out forwards;
        `;

        const rect = element.getBoundingClientRect();
        celebration.style.left = (rect.left + rect.width/2) + 'px';
        celebration.style.top = (rect.top + rect.height/2) + 'px';

        document.body.appendChild(celebration);
        setTimeout(() => celebration.remove(), 2000);

        if (!document.getElementById('celebration-styles')) {
            const style = document.createElement('style');
            style.id = 'celebration-styles';
            style.textContent = `
                @keyframes floatUp {
                    0% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                    100% { opacity: 0; transform: translate(-50%, -150px) scale(1.5); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Setup toolbar controls (mini dashboard)
    setupToolbarControls() {
        const miningToggle = document.querySelector('#mining-toggle');
        if (miningToggle) {
            console.log('🧠 CANONICAL MINING: Setting up toolbar controls');
            
            miningToggle.addEventListener('click', () => {
                // Toggle between power 0 (off) and saved power level
                if (this.state.power === 0) {
                    const savedPower = parseInt(localStorage.getItem('canonical-mining-power')) || 5;
                    this.setPower(savedPower);
                    miningToggle.textContent = 'Auto-Mine: ON';
                    miningToggle.style.background = 'var(--accent-green)';
                } else {
                    localStorage.setItem('canonical-mining-power', this.state.power.toString());
                    this.setPower(0);
                    miningToggle.textContent = 'Auto-Mine: OFF';
                    miningToggle.style.background = 'var(--accent-red)';
                }
            });
            
            // Set initial state
            if (this.state.power === 0) {
                miningToggle.textContent = 'Auto-Mine: OFF';
                miningToggle.style.background = 'var(--accent-red)';
            } else {
                miningToggle.textContent = 'Auto-Mine: ON';
                miningToggle.style.background = 'var(--accent-green)';
            }
        }
    }

    // Form mining integration
    setupFormMining() {
        console.log('🧠 CANONICAL MINING: Setting up form mining for 21e8 pattern...');
        
        // Integrate with forms that need PoW
        const forms = document.querySelectorAll('form[id*="thread"], form[id*="post"], form[id*="chat"], form[id*="unified"]');
        forms.forEach(form => {
            const powFields = {
                nonce: form.querySelector('input[name="pow_nonce"]'),
                hash: form.querySelector('input[name="pow_hash"]'),
                challengeId: form.querySelector('input[name="pow_challenge_id"]')
            };

            const submitBtn = form.querySelector('button[type="submit"]');
            const miningStatus = form.querySelector('#mining-status');

            if (powFields.nonce && powFields.hash && submitBtn) {
                // Override form submission
                form.addEventListener('submit', async (e) => {
                    if (!powFields.hash.value) {
                        e.preventDefault();
                        await this.mineForForm(form, powFields, submitBtn, miningStatus);
                    }
                });

                // Also add click handler to submit button
                submitBtn.addEventListener('click', async (e) => {
                    if (!powFields.hash.value) {
                        e.preventDefault();
                        await this.mineForForm(form, powFields, submitBtn, miningStatus);
                    }
                });
            }
        });
    }

    async mineForForm(form, powFields, submitBtn, miningStatus) {
        console.log('🧠 CANONICAL MINING: Mining 21e8 proof for form submission...');
        
        // Update UI
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '⚡ Mining 21e8...';
            submitBtn.style.opacity = '0.7';
        }
        
        if (miningStatus) {
            miningStatus.innerHTML = '<span style="color: #708B75;">⚡ Mining proof-of-work pattern 21e8...</span>';
        }

        // Create mining target specifically for 21e8 pattern
        const formData = new FormData(form);
        const title = formData.get('title') || 'post';
        const content = formData.get('content') || 'content';
        
        const target = {
            id: `form-${Date.now()}`,
            type: 'form',
            displayName: 'Form Submission (21e8)',
            data: `${title}-${content}-${Date.now()}`
        };

        // Mine specifically for 21e8 pattern with higher attempt count
        const result = await this.mineSpecific21e8(target, 2000000);
        
        if (result.found) {
            console.log(`✅ Found 21e8 hash: ${result.hash}`);
            
            powFields.nonce.value = result.nonce;
            powFields.hash.value = result.hash;
            if (powFields.challengeId) {
                powFields.challengeId.value = Date.now();
            }
            
            // Update UI
            if (submitBtn) {
                submitBtn.textContent = '✅ Submitting...';
                submitBtn.style.opacity = '1';
            }
            
            if (miningStatus) {
                miningStatus.innerHTML = '<span style="color: #28a745;">✅ Mining complete! Submitting...</span>';
            }
            
            // Submit form after brief delay
            setTimeout(() => {
                form.submit();
            }, 500);
        } else {
            console.log('❌ Mining failed - no 21e8 pattern found');
            
            // Reset UI
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = '⚡ Mine & Post';
                submitBtn.style.opacity = '1';
            }
            
            if (miningStatus) {
                miningStatus.innerHTML = '<span style="color: #dc3545;">❌ Mining failed - try again</span>';
            }
        }
    }

    async mineSpecific21e8(target, maxAttempts) {
        console.log(`🧠 CANONICAL MINING: Mining 21e8 pattern, max attempts: ${maxAttempts.toLocaleString()}`);
        
        let attempts = 0;
        const startTime = performance.now();
        
        while (attempts < maxAttempts) {
            const nonce = Math.floor(Math.random() * 4294967295);
            const data = `${target.data}-${nonce}`;
            
            // Calculate SHA-256 hash
            const hash = await this.realSha256(data);
            attempts++;
            
            // Check specifically for 21e8 pattern
            if (hash.startsWith('21e8')) {
                const elapsed = performance.now() - startTime;
                console.log(`💎 FOUND 21e8! Hash: ${hash} (${attempts.toLocaleString()} attempts in ${Math.floor(elapsed)}ms)`);
                
                return {
                    found: true,
                    hash: hash,
                    nonce: nonce,
                    data: data,
                    pattern: '21e8',
                    points: 100,
                    attempts: attempts
                };
            }
            
            // Progress logging
            if (attempts % 100000 === 0) {
                const elapsed = performance.now() - startTime;
                const hashrate = Math.floor(attempts / (elapsed / 1000));
                console.log(`🧠 Mining progress: ${attempts.toLocaleString()} attempts, ${hashrate.toLocaleString()} H/s`);
            }
            
            // Yield control periodically
            if (attempts % 10000 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        const elapsed = performance.now() - startTime;
        console.log(`❌ Mining failed: ${attempts.toLocaleString()} attempts in ${Math.floor(elapsed)}ms, no 21e8 found`);
        
        return {
            found: false,
            attempts: attempts
        };
    }

    // Dashboard integration
    updateDashboardTarget(text) {
        const targetDisplay = document.querySelector('#current-target, [data-stat="current-target"]');
        if (targetDisplay) {
            targetDisplay.textContent = text;
        }
        
        // Update mining toolbar target (mini dashboard)
        const toolbarTarget = document.querySelector('#toolbar-target');
        if (toolbarTarget) {
            toolbarTarget.textContent = text;
        }
        
        // Update enhanced dashboard target
        if (window.enhancedMiningDashboard) {
            window.enhancedMiningDashboard.setTarget(text);
        }
    }

    startUIUpdates() {
        this.intervals.stats = setInterval(() => {
            this.updateDashboardStats();
        }, 1000);
    }

    updateDashboardStats() {
        const elapsed = (Date.now() - this.state.sessionStats.startTime) / 1000;
        const hashrate = elapsed > 0 ? Math.floor(this.state.sessionStats.totalHashes / elapsed) : 0;

        // Update dashboard elements (legacy compatibility)
        const elements = {
            hashRate: document.querySelector('#hash-rate, [data-stat="hashrate"]'),
            sessionHashes: document.querySelector('#session-hashes, [data-stat="hashes"]'),
            sessionProofs: document.querySelector('#session-proofs, [data-stat="proofs"]'),
            sessionPoints: document.querySelector('#session-points, [data-stat="points"]')
        };

        if (elements.hashRate) elements.hashRate.textContent = `${hashrate.toLocaleString()} H/s`;
        if (elements.sessionHashes) elements.sessionHashes.textContent = this.state.sessionStats.totalHashes.toLocaleString();
        if (elements.sessionProofs) elements.sessionProofs.textContent = this.state.sessionStats.totalProofs.toString();
        if (elements.sessionPoints) elements.sessionPoints.textContent = this.state.sessionStats.totalPoints.toFixed(1);

        // Update mining toolbar (mini dashboard) elements
        const toolbarHashRate = document.querySelector('.hash-rate');
        if (toolbarHashRate) {
            toolbarHashRate.textContent = `${hashrate.toLocaleString()} H/s`;
        }
        
        // Update mining indicator
        const miningIndicator = document.querySelector('#mining-indicator');
        if (miningIndicator) {
            miningIndicator.textContent = hashrate > 0 ? '⚡' : '💤';
        }

        // Update enhanced dashboard if available
        if (window.enhancedMiningDashboard) {
            window.enhancedMiningDashboard.updateHashrate(hashrate);
            window.enhancedMiningDashboard.updateHashCount(this.state.sessionStats.totalHashes);
            window.enhancedMiningDashboard.stats.sessionProofs = this.state.sessionStats.totalProofs;
            window.enhancedMiningDashboard.stats.sessionPoints = this.state.sessionStats.totalPoints;
        }

        this.state.sessionStats.currentHashrate = hashrate;
    }

    startPerformanceMonitoring() {
        setInterval(() => {
            if (performance.memory) {
                this.state.performance.memoryUsage = performance.memory.usedJSHeapSize / 1024 / 1024;
            }
        }, 10000);
    }

    // State management
    saveState() {
        const stateToSave = {
            power: this.state.power,
            mode: this.state.mode
        };
        localStorage.setItem('canonical-mining-state', JSON.stringify(stateToSave));
    }

    loadState() {
        try {
            const saved = localStorage.getItem('canonical-mining-state');
            if (saved) {
                const state = JSON.parse(saved);
                this.setPower(state.power || 5);
                this.setMode(state.mode || 'mouseover');
            }
        } catch (error) {
            console.error('🧠 CANONICAL MINING: Error loading state:', error);
        }
    }

    setPower(level) {
        this.state.power = Math.max(0, Math.min(10, level));
        console.log(`🧠 CANONICAL MINING: Power set to ${this.state.power}/10 - ${this.configs.powerLevels[this.state.power].name}`);
        
        // Update enhanced dashboard power level
        if (window.enhancedMiningDashboard) {
            window.enhancedMiningDashboard.powerLevel = this.state.power;
            const powerDisplay = document.getElementById('power-display');
            if (powerDisplay) {
                powerDisplay.textContent = `${this.state.power}/10`;
            }
            const powerDescription = document.getElementById('power-description');
            if (powerDescription) {
                powerDescription.textContent = this.configs.powerLevels[this.state.power].name;
            }
        }
        
        this.saveState();
    }

    setMode(mode) {
        const oldMode = this.state.mode;
        this.state.mode = mode;
        console.log(`🧠 CANONICAL MINING: Mode changed from ${oldMode} to ${mode}`);
        
        if (mode === 'mouseover') {
            this.setupMouseoverMining();
        }
        
        this.saveState();
    }

    // Public API
    enable() { this.isActive = true; }
    disable() { this.isActive = false; }
    
    destroy() {
        console.log('🧠 CANONICAL MINING: Shutting down');
        
        // Clear intervals
        Object.values(this.intervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });
        
        // Remove event listeners
        document.removeEventListener('mouseover', this.handleMouseover);
        document.removeEventListener('mouseout', this.handleMouseout);
        
        // Cleanup workers
        this.workers.webWorkers.forEach(worker => {
            worker.worker.terminate();
            URL.revokeObjectURL(worker.url);
        });
        
        this.isInitialized = false;
    }
}

// Initialize the canonical mining system
console.log('🧠 CANONICAL MINING: Loading ultra-high performance mining system...');

// Cleanup any existing instances
if (window.haichanCanonicalMining) {
    window.haichanCanonicalMining.destroy();
}

// Create new instance
window.haichanCanonicalMining = new HaichanCanonicalMining();

// Set as primary mining system
window.haichanMiner = window.haichanCanonicalMining;
window.haichanUnified = window.haichanCanonicalMining; // Backward compatibility

console.log('🧠 CANONICAL MINING: Ultra-high performance system operational');