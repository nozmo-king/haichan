/**
 * Haichan Mouseover Mining System v2.0
 * Complete rewrite focusing on threads, posts, and images
 * Uses 21e8 difficulty and proper SHA-256 hashes
 */

class MouseoverMiningSystemV2 {
    constructor() {
        this.isEnabled = true;
        this.activeMining = new Map();
        this.currentTarget = null;
        this.miningStats = {
            totalHashes: 0,
            totalProofs: 0,
            sessionStart: Date.now()
        };
        this.targetPattern = '21e8'; // Default difficulty
        
        this.init();
    }
    
    init() {
        console.log('🔥 Mouseover Mining System v2.0 initializing...');
        
        // Setup mouseover detection
        this.setupMouseoverDetection();
        
        // Setup mining visual feedback
        this.setupMiningStyles();
        
        // Connect to dashboard if available
        this.connectToDashboard();
        
        console.log('✅ Mouseover Mining System v2.0 ready');
    }
    
    setupMouseoverDetection() {
        // Remove any existing listeners
        document.removeEventListener('mouseover', this.handleMouseover);
        document.removeEventListener('mouseout', this.handleMouseout);
        
        // Add new listeners
        document.addEventListener('mouseover', (e) => this.handleMouseover(e));
        document.addEventListener('mouseout', (e) => this.handleMouseout(e));
    }
    
    handleMouseover(event) {
        if (!this.isEnabled) return;
        
        const target = event.target;
        const mineableData = this.getMineableData(target);
        
        if (mineableData) {
            this.startMining(target, mineableData);
        }
    }
    
    handleMouseout(event) {
        const target = event.target;
        this.stopMining(target);
    }
    
    getMineableData(element) {\n        // Check if element or its parents are mineable
        let current = element;
        for (let i = 0; i < 5; i++) {
            if (!current) break;
            
            // 1. Check for thread containers
            if (current.dataset && current.dataset.threadId) {
                return {
                    type: 'thread',
                    id: current.dataset.threadId,
                    data: `thread_${current.dataset.threadId}`,
                    displayName: `Thread #${current.dataset.threadId}`,
                    points: 10
                };
            }
            
            // 2. Check for post containers  
            if (current.dataset && current.dataset.postId) {
                return {
                    type: 'post',
                    id: current.dataset.postId,
                    data: `post_${current.dataset.postId}`,
                    displayName: `Post #${current.dataset.postId}`,
                    points: 5
                };
            }
            
            // 3. Check for images with proper hash data\n            if (current.tagName === 'IMG') {\n                const imageHash = current.dataset.hash || current.dataset.imageHash;\n                if (imageHash && imageHash.length === 64) {\n                    return {\n                        type: 'image',\n                        id: imageHash,\n                        data: `image_${imageHash}`,\n                        displayName: `Image ${imageHash.substring(0, 8)}...`,\n                        hash: imageHash,\n                        points: 3\n                    };\n                }\n            }\n            \n            // 4. Check for .post divs (thread listings)\n            if (current.classList && current.classList.contains('post')) {\n                const threadId = current.querySelector('[data-thread-id]')?.dataset.threadId;\n                if (threadId) {\n                    return {\n                        type: 'thread-post',\n                        id: threadId,\n                        data: `thread_post_${threadId}`,\n                        displayName: `Thread Post #${threadId}`,\n                        points: 8\n                    };\n                }\n            }\n            \n            // 5. Check for catalog thread items\n            if (current.classList && current.classList.contains('catalog-thread')) {\n                const threadId = current.dataset.threadId;\n                if (threadId) {\n                    return {\n                        type: 'catalog-thread',\n                        id: threadId,\n                        data: `catalog_${threadId}`,\n                        displayName: `Catalog Thread #${threadId}`,\n                        points: 6\n                    };\n                }\n            }\n            \n            current = current.parentElement;\n        }\n        \n        return null;\n    }\n    \n    async startMining(element, mineableData) {\n        // Don't mine the same element twice\n        if (this.activeMining.has(element)) return;\n        \n        console.log(`⛏️ Starting mining: ${mineableData.displayName}`);\n        \n        // Visual feedback\n        this.addMiningVisual(element);\n        \n        // Update dashboard target display\n        this.updateDashboardTarget(mineableData);\n        \n        // Create mining session\n        const miningSession = {\n            element,\n            data: mineableData,\n            startTime: Date.now(),\n            hashes: 0,\n            isActive: true\n        };\n        \n        this.activeMining.set(element, miningSession);\n        this.currentTarget = mineableData;\n        \n        // Start the actual mining\n        this.performMining(miningSession);\n    }\n    \n    stopMining(element) {\n        const session = this.activeMining.get(element);\n        if (session) {\n            session.isActive = false;\n            this.activeMining.delete(element);\n            this.removeMiningVisual(element);\n            \n            console.log(`⏹️ Stopped mining: ${session.data.displayName}`);\n        }\n        \n        // Clear target if no active mining\n        if (this.activeMining.size === 0) {\n            this.currentTarget = null;\n            this.updateDashboardTarget(null);\n        }\n    }\n    \n    async performMining(session) {\n        const { data, element } = session;\n        let nonce = Math.floor(Math.random() * 1000000);\n        const batchSize = 500;\n        \n        while (session.isActive) {\n            for (let i = 0; i < batchSize && session.isActive; i++) {\n                const input = `${data.data}_${Date.now()}_${nonce}`;\n                const hash = await this.calculateSHA256(input);\n                \n                session.hashes++;\n                this.miningStats.totalHashes++;\n                \n                // Check if we found a proof\n                if (hash.startsWith(this.targetPattern)) {\n                    console.log(`💎 PROOF FOUND! ${data.displayName}: ${hash}`);\n                    \n                    // Stop mining this target\n                    session.isActive = false;\n                    \n                    // Show success animation\n                    this.showProofSuccess(element, hash, data.points, session.hashes);\n                    \n                    // Update stats\n                    this.miningStats.totalProofs++;\n                    \n                    // Submit proof if API available\n                    this.submitProof({\n                        type: data.type,\n                        targetId: data.id,\n                        hash: hash,\n                        nonce: nonce,\n                        input: input,\n                        attempts: session.hashes,\n                        points: data.points\n                    });\n                    \n                    break;\n                }\n                \n                nonce++;\n            }\n            \n            // Update mining display\n            this.updateMiningDisplay(session);\n            \n            // Yield control briefly\n            await new Promise(resolve => setTimeout(resolve, 1));\n        }\n    }\n    \n    async calculateSHA256(input) {\n        const encoder = new TextEncoder();\n        const data = encoder.encode(input);\n        const hashBuffer = await crypto.subtle.digest('SHA-256', data);\n        const hashArray = Array.from(new Uint8Array(hashBuffer));\n        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');\n    }\n    \n    addMiningVisual(element) {\n        element.classList.add('haichan-mining-active');\n        \n        // Add glow effect\n        element.style.boxShadow = '0 0 15px rgba(0, 212, 255, 0.6)';\n        element.style.border = '2px solid #00d4ff';\n        element.style.transition = 'all 0.3s ease';\n        \n        // Add mining indicator\n        if (!element.querySelector('.mining-indicator')) {\n            const indicator = document.createElement('div');\n            indicator.className = 'mining-indicator';\n            indicator.innerHTML = '⛏️';\n            indicator.style.cssText = `\n                position: absolute;\n                top: 5px;\n                right: 5px;\n                background: rgba(0, 212, 255, 0.9);\n                color: white;\n                padding: 2px 4px;\n                border-radius: 3px;\n                font-size: 10px;\n                z-index: 1000;\n                animation: pulse 1s infinite;\n            `;\n            \n            // Make parent relative if needed\n            if (getComputedStyle(element).position === 'static') {\n                element.style.position = 'relative';\n            }\n            \n            element.appendChild(indicator);\n        }\n    }\n    \n    removeMiningVisual(element) {\n        element.classList.remove('haichan-mining-active');\n        element.style.boxShadow = '';\n        element.style.border = '';\n        \n        // Remove mining indicator\n        const indicator = element.querySelector('.mining-indicator');\n        if (indicator) {\n            indicator.remove();\n        }\n    }\n    \n    showProofSuccess(element, hash, points, attempts) {\n        // Create floating success message\n        const success = document.createElement('div');\n        success.className = 'proof-success-float';\n        success.innerHTML = `\n            <div class=\"proof-title\">💎 PROOF FOUND!</div>\n            <div class=\"proof-hash\">${hash.substring(0, 16)}...</div>\n            <div class=\"proof-stats\">+${points} pts • ${attempts} attempts</div>\n        `;\n        \n        const rect = element.getBoundingClientRect();\n        success.style.cssText = `\n            position: fixed;\n            left: ${rect.left + rect.width/2}px;\n            top: ${rect.top + rect.height/2}px;\n            transform: translate(-50%, -50%);\n            background: linear-gradient(135deg, #FFD700, #FFA500);\n            color: #000;\n            padding: 12px 16px;\n            border-radius: 8px;\n            font-family: 'Courier New', monospace;\n            font-weight: bold;\n            font-size: 11px;\n            text-align: center;\n            z-index: 10002;\n            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.5);\n            animation: proofFloat 3s ease-out forwards;\n            border: 2px solid #FFD700;\n        `;\n        \n        document.body.appendChild(success);\n        \n        // Remove after animation\n        setTimeout(() => success.remove(), 3000);\n        \n        // Flash the element\n        element.style.animation = 'proofFlash 0.5s ease-in-out 3';\n        setTimeout(() => {\n            element.style.animation = '';\n        }, 1500);\n    }\n    \n    updateDashboardTarget(target) {\n        // Update enhanced dashboard if available\n        if (window.enhancedMiningDashboard) {\n            const targetElement = document.getElementById('current-target');\n            if (targetElement) {\n                if (target) {\n                    targetElement.textContent = target.displayName;\n                } else {\n                    targetElement.textContent = 'Hover over content to mine';\n                }\n            }\n        }\n    }\n    \n    updateMiningDisplay(session) {\n        const elapsed = (Date.now() - session.startTime) / 1000;\n        const hashrate = Math.floor(session.hashes / elapsed);\n        \n        // Update dashboard hashrate if available\n        if (window.enhancedMiningDashboard) {\n            const hashrateElement = document.getElementById('stat-hashrate');\n            if (hashrateElement) {\n                hashrateElement.textContent = hashrate;\n            }\n            \n            const hashesElement = document.getElementById('stat-hashes');\n            if (hashesElement) {\n                hashesElement.textContent = this.miningStats.totalHashes.toLocaleString();\n            }\n        }\n    }\n    \n    async submitProof(proof) {\n        try {\n            const response = await fetch('/api/mouseover-mining/submit', {\n                method: 'POST',\n                headers: {\n                    'Content-Type': 'application/json',\n                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || ''\n                },\n                body: JSON.stringify(proof)\n            });\n            \n            if (response.ok) {\n                const result = await response.json();\n                console.log('✅ Proof submitted:', result);\n            }\n        } catch (error) {\n            console.log('⚠️ Proof submission failed:', error);\n        }\n    }\n    \n    setupMiningStyles() {\n        if (document.getElementById('mouseover-mining-styles')) return;\n        \n        const style = document.createElement('style');\n        style.id = 'mouseover-mining-styles';\n        style.textContent = `\n            .haichan-mining-active {\n                cursor: crosshair !important;\n            }\n            \n            @keyframes pulse {\n                0%, 100% { opacity: 1; transform: scale(1); }\n                50% { opacity: 0.7; transform: scale(1.1); }\n            }\n            \n            @keyframes proofFloat {\n                0% {\n                    opacity: 1;\n                    transform: translate(-50%, -50%) scale(1);\n                }\n                50% {\n                    opacity: 1;\n                    transform: translate(-50%, -60%) scale(1.1);\n                }\n                100% {\n                    opacity: 0;\n                    transform: translate(-50%, -80%) scale(0.8);\n                }\n            }\n            \n            @keyframes proofFlash {\n                0%, 100% { background: inherit; }\n                50% { background: rgba(255, 215, 0, 0.3) !important; }\n            }\n            \n            .proof-success-float {\n                pointer-events: none;\n            }\n            \n            .proof-title {\n                font-size: 12px;\n                margin-bottom: 4px;\n            }\n            \n            .proof-hash {\n                font-size: 9px;\n                font-family: 'Courier New', monospace;\n                margin-bottom: 4px;\n                color: #333;\n            }\n            \n            .proof-stats {\n                font-size: 8px;\n                color: #666;\n            }\n        `;\n        \n        document.head.appendChild(style);\n    }\n    \n    connectToDashboard() {\n        // Set difficulty from dashboard if available\n        setTimeout(() => {\n            const prefixSelector = document.getElementById('prefix-selector');\n            if (prefixSelector) {\n                this.targetPattern = prefixSelector.value;\n                prefixSelector.addEventListener('change', (e) => {\n                    this.targetPattern = e.target.value;\n                    console.log(`🎯 Mining difficulty changed to: ${this.targetPattern}`);\n                });\n            }\n        }, 1000);\n    }\n    \n    // Public methods\n    enable() {\n        this.isEnabled = true;\n        console.log('✅ Mouseover mining enabled');\n    }\n    \n    disable() {\n        this.isEnabled = false;\n        // Stop all active mining\n        this.activeMining.forEach((session, element) => {\n            this.stopMining(element);\n        });\n        console.log('⏹️ Mouseover mining disabled');\n    }\n    \n    getStats() {\n        const elapsed = (Date.now() - this.miningStats.sessionStart) / 1000;\n        return {\n            totalHashes: this.miningStats.totalHashes,\n            totalProofs: this.miningStats.totalProofs,\n            sessionLength: elapsed,\n            averageHashrate: Math.floor(this.miningStats.totalHashes / elapsed),\n            activeMining: this.activeMining.size,\n            currentTarget: this.currentTarget?.displayName || 'None'\n        };\n    }\n}\n\n// Initialize the system\ndocument.addEventListener('DOMContentLoaded', function() {\n    // Replace any existing mining system\n    if (window.mouseoverMining) {\n        console.log('🔄 Replacing existing mouseover mining system');\n    }\n    \n    window.mouseoverMiningV2 = new MouseoverMiningSystemV2();\n    window.mouseoverMining = window.mouseoverMiningV2; // Backward compatibility\n    \n    console.log('🚀 Mouseover Mining System v2.0 loaded');\n});\n\n// Also initialize if DOM already loaded\nif (document.readyState !== 'loading') {\n    window.mouseoverMiningV2 = new MouseoverMiningSystemV2();\n    window.mouseoverMining = window.mouseoverMiningV2;\n}