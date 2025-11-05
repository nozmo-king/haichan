/* THREAD REPLY PERSISTENCE & RETRY SYSTEM */
/* Ensures replies are never lost and can be retried if they fail */

class ThreadReplyPersistence {
    constructor() {
        this.storageKey = 'haichan_draft_replies';
        this.retryKey = 'haichan_pending_replies';
        this.maxRetries = 3;
        this.retryDelay = 5000; // 5 seconds
        
        this.init();
    }
    
    init() {
        console.log('🔄 ThreadReplyPersistence initialized');
        
        // Auto-save drafts every 5 seconds
        setInterval(() => {
            this.saveDrafts();
        }, 5000);
        
        // Check for pending retries on page load
        this.checkPendingRetries();
        
        // Restore drafts from storage
        this.restoreDrafts();
        
        // Hook into form submissions
        this.hookFormSubmissions();
        
        // Auto-save on content changes
        this.setupAutoSave();
    }
    
    /**
     * Save current draft replies to localStorage
     */
    saveDrafts() {
        const forms = document.querySelectorAll('.reply-form');
        const drafts = {};
        
        forms.forEach(form => {
            const textarea = form.querySelector('textarea[name="reply_content"]');
            const imageInput = form.querySelector('input[type="file"][name="image"]');
            
            if (textarea && textarea.value.trim()) {
                const threadId = this.getThreadIdFromForm(form);
                const key = `thread_${threadId}`;
                
                drafts[key] = {
                    content: textarea.value,
                    threadId: threadId,
                    timestamp: Date.now(),
                    hasImage: imageInput && imageInput.files.length > 0,
                    imageName: imageInput && imageInput.files[0] ? imageInput.files[0].name : null
                };
            }
        });
        
        if (Object.keys(drafts).length > 0) {
            localStorage.setItem(this.storageKey, JSON.stringify(drafts));
            console.log('💾 Saved drafts:', Object.keys(drafts).length);
        }
    }
    
    /**
     * Restore draft replies from localStorage
     */
    restoreDrafts() {
        const drafts = this.getDrafts();
        
        Object.entries(drafts).forEach(([key, draft]) => {
            const threadId = draft.threadId;
            const form = this.findReplyForm(threadId);
            
            if (form) {
                const textarea = form.querySelector('textarea[name="reply_content"]');
                if (textarea && !textarea.value.trim()) {
                    textarea.value = draft.content;
                    
                    // Trigger mining if content is long enough
                    const event = new Event('input', { bubbles: true });
                    textarea.dispatchEvent(event);
                    
                    this.showDraftNotification(form, draft);
                }
            }
        });
    }
    
    /**
     * Save reply for retry if submission fails
     */
    saveForRetry(replyData, error = null) {
        const pending = this.getPendingRetries();
        const key = `retry_${replyData.threadId}_${Date.now()}`;
        
        pending[key] = {
            ...replyData,
            retryCount: 0,
            lastError: error,
            timestamp: Date.now(),
            status: 'pending'
        };
        
        localStorage.setItem(this.retryKey, JSON.stringify(pending));
        console.log('🔄 Saved reply for retry:', key);
        
        // Schedule immediate retry
        setTimeout(() => {
            this.retryPendingReplies();
        }, this.retryDelay);
    }
    
    /**
     * Check for and retry pending replies
     */
    async checkPendingRetries() {
        const pending = this.getPendingRetries();
        
        if (Object.keys(pending).length > 0) {
            console.log('🔄 Found pending replies:', Object.keys(pending).length);
            this.showRetryNotification(Object.keys(pending).length);
            
            // Wait a bit then start retrying
            setTimeout(() => {
                this.retryPendingReplies();
            }, 2000);
        }
    }
    
    /**
     * Retry all pending replies
     */
    async retryPendingReplies() {
        const pending = this.getPendingRetries();
        
        for (const [key, replyData] of Object.entries(pending)) {
            if (replyData.retryCount >= this.maxRetries) {
                console.warn('⚠️ Max retries reached for:', key);
                continue;
            }
            
            if (replyData.status === 'retrying') {
                continue; // Skip if already retrying
            }
            
            console.log('🔄 Retrying reply:', key);
            await this.attemptReply(key, replyData);
            
            // Wait between retries
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }
    
    /**
     * Attempt to submit a reply
     */
    async attemptReply(key, replyData) {
        const pending = this.getPendingRetries();
        
        // Mark as retrying
        pending[key] = { ...replyData, status: 'retrying', retryCount: replyData.retryCount + 1 };
        localStorage.setItem(this.retryKey, JSON.stringify(pending));
        
        try {
            // Find the form for this thread
            const form = this.findReplyForm(replyData.threadId);
            if (!form) {
                throw new Error('Reply form not found');
            }
            
            // Create FormData from saved reply data
            const formData = new FormData();
            formData.append('reply_content', replyData.content);
            formData.append('pow_nonce', replyData.powNonce);
            formData.append('pow_hash', replyData.powHash);
            formData.append('pow_challenge_id', replyData.powChallengeId);
            
            if (replyData.postAnonymous) {
                formData.append('post_anonymous', '1');
            }
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            // Submit the reply
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            if (response.ok) {
                // Success! Remove from pending retries
                delete pending[key];
                localStorage.setItem(this.retryKey, JSON.stringify(pending));
                
                console.log('✅ Reply retry successful:', key);
                this.showSuccessNotification('Reply posted successfully!');
                
                // Reload page to show the new reply
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                
            } else {
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
            }
            
        } catch (error) {
            console.error('❌ Reply retry failed:', error);
            
            // Update retry count and error
            pending[key] = {
                ...pending[key],
                status: 'failed',
                lastError: error.message,
                lastRetry: Date.now()
            };
            
            // If max retries reached, mark as permanent failure
            if (pending[key].retryCount >= this.maxRetries) {
                pending[key].status = 'max_retries_reached';
                this.showErrorNotification(`Reply failed after ${this.maxRetries} attempts. Content saved in drafts.`);
                
                // Move to drafts for manual recovery
                this.moveToDrafts(key, replyData);
            }
            
            localStorage.setItem(this.retryKey, JSON.stringify(pending));
        }
    }
    
    /**
     * Hook into form submissions to capture failures
     */
    hookFormSubmissions() {
        document.addEventListener('submit', async (event) => {
            const form = event.target;
            
            if (!form.classList.contains('reply-form')) {
                return;
            }
            
            // Capture reply data before submission
            const replyData = this.captureReplyData(form);
            if (!replyData) {
                return;
            }
            
            console.log('📤 Reply submission captured:', replyData.threadId);
            
            // Let the form submit normally, but monitor for failures
            setTimeout(() => {
                this.monitorSubmissionResult(replyData);
            }, 1000);
        });
    }
    
    /**
     * Monitor submission result and save for retry if failed
     */
    monitorSubmissionResult(replyData) {
        // Check if we're still on the same page (submission failed)
        const currentUrl = window.location.href;
        
        // Look for error messages
        const errorMessages = document.querySelectorAll('.alert-danger, .error-message, .validation-errors');
        
        if (errorMessages.length > 0) {
            console.log('❌ Reply submission failed, saving for retry');
            
            let errorText = '';
            errorMessages.forEach(el => errorText += el.textContent + ' ');
            
            this.saveForRetry(replyData, errorText.trim());
            this.showErrorNotification('Reply failed to post. Will retry automatically.');
        }
    }
    
    /**
     * Capture reply data from form
     */
    captureReplyData(form) {
        const textarea = form.querySelector('textarea[name="reply_content"]');
        const nonceInput = form.querySelector('input[name="pow_nonce"]');
        const hashInput = form.querySelector('input[name="pow_hash"]');
        const challengeInput = form.querySelector('input[name="pow_challenge_id"]');
        const anonCheckbox = form.querySelector('input[name="post_anonymous"]');
        
        if (!textarea || !textarea.value.trim()) {
            return null;
        }
        
        if (!nonceInput || !hashInput || !challengeInput) {
            console.warn('⚠️ Missing PoW data for reply');
            return null;
        }
        
        return {
            threadId: this.getThreadIdFromForm(form),
            content: textarea.value,
            powNonce: nonceInput.value,
            powHash: hashInput.value,
            powChallengeId: challengeInput.value,
            postAnonymous: anonCheckbox ? anonCheckbox.checked : false,
            timestamp: Date.now()
        };
    }
    
    /**
     * Setup auto-save on content changes
     */
    setupAutoSave() {
        document.addEventListener('input', (event) => {
            if (event.target.matches('textarea[name="reply_content"]')) {
                // Debounce auto-save
                clearTimeout(this.autoSaveTimeout);
                this.autoSaveTimeout = setTimeout(() => {
                    this.saveDrafts();
                }, 2000);
            }
        });
    }
    
    /**
     * Get thread ID from form
     */
    getThreadIdFromForm(form) {
        // Try to extract from form action URL
        const actionMatch = form.action.match(/\/(\d+)\/reply$/);
        if (actionMatch) {
            return parseInt(actionMatch[1]);
        }
        
        // Try to find in page URL
        const urlMatch = window.location.pathname.match(/\/thread\/(\d+)/);
        if (urlMatch) {
            return parseInt(urlMatch[1]);
        }
        
        // Try to find data attribute
        const threadElement = document.querySelector('[data-thread-id]');
        if (threadElement) {
            return parseInt(threadElement.dataset.threadId);
        }
        
        return null;
    }
    
    /**
     * Find reply form for specific thread
     */
    findReplyForm(threadId) {
        const forms = document.querySelectorAll('.reply-form');
        
        for (const form of forms) {
            if (this.getThreadIdFromForm(form) === threadId) {
                return form;
            }
        }
        
        return null;
    }
    
    /**
     * Get saved drafts
     */
    getDrafts() {
        try {
            return JSON.parse(localStorage.getItem(this.storageKey) || '{}');
        } catch (error) {
            console.error('Failed to parse drafts:', error);
            return {};
        }
    }
    
    /**
     * Get pending retries
     */
    getPendingRetries() {
        try {
            return JSON.parse(localStorage.getItem(this.retryKey) || '{}');
        } catch (error) {
            console.error('Failed to parse pending retries:', error);
            return {};
        }
    }
    
    /**
     * Move failed retry to drafts
     */
    moveToDrafts(retryKey, replyData) {
        const drafts = this.getDrafts();
        const draftKey = `thread_${replyData.threadId}`;
        
        drafts[draftKey] = {
            content: replyData.content,
            threadId: replyData.threadId,
            timestamp: Date.now(),
            recovered: true,
            originalError: replyData.lastError
        };
        
        localStorage.setItem(this.storageKey, JSON.stringify(drafts));
        
        // Remove from pending retries
        const pending = this.getPendingRetries();
        delete pending[retryKey];
        localStorage.setItem(this.retryKey, JSON.stringify(pending));
    }
    
    /**
     * Clear draft for thread
     */
    clearDraft(threadId) {
        const drafts = this.getDrafts();
        delete drafts[`thread_${threadId}`];
        localStorage.setItem(this.storageKey, JSON.stringify(drafts));
    }
    
    /**
     * Show notification messages
     */
    showDraftNotification(form, draft) {
        const notification = document.createElement('div');
        notification.className = 'draft-notification';
        notification.innerHTML = `
            <span>📝 Draft restored from ${new Date(draft.timestamp).toLocaleString()}</span>
            <button onclick="this.parentElement.remove()" style="margin-left: 10px;">×</button>
        `;
        
        form.insertBefore(notification, form.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => notification.remove(), 5000);
    }
    
    showRetryNotification(count) {
        const notification = document.createElement('div');
        notification.className = 'retry-notification';
        notification.innerHTML = `
            <span>🔄 Found ${count} pending reply${count > 1 ? 's' : ''}. Retrying automatically...</span>
        `;
        
        document.body.insertBefore(notification, document.body.firstChild);
        setTimeout(() => notification.remove(), 5000);
    }
    
    showSuccessNotification(message) {
        this.showNotification(message, 'success');
    }
    
    showErrorNotification(message) {
        this.showNotification(message, 'error');
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">×</button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-hide after 5 seconds
        setTimeout(() => notification.remove(), 5000);
    }
    
    /**
     * Get stats about persistence system
     */
    getStats() {
        const drafts = this.getDrafts();
        const pending = this.getPendingRetries();
        
        return {
            draftsCount: Object.keys(drafts).length,
            pendingCount: Object.keys(pending).length,
            totalFailures: Object.values(pending).filter(r => r.retryCount > 0).length
        };
    }
    
    /**
     * Clear all data (for debugging)
     */
    clearAll() {
        localStorage.removeItem(this.storageKey);
        localStorage.removeItem(this.retryKey);
        console.log('🧹 Cleared all persistence data');
    }
}

// Add CSS for notifications
const persistenceStyles = document.createElement('style');
persistenceStyles.innerHTML = `
    .draft-notification, .retry-notification, .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--accent-6);
        color: white;
        padding: 12px 16px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
    }
    
    .notification-error {
        background: #dc3545;
    }
    
    .notification-success {
        background: #28a745;
    }
    
    .draft-notification {
        background: #17a2b8;
        position: relative;
        margin-bottom: 10px;
        animation: none;
    }
    
    .retry-notification {
        background: #ffc107;
        color: #000;
    }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .notification button {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        font-size: 16px;
        padding: 0;
        margin: 0;
    }
`;

document.head.appendChild(persistenceStyles);

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.threadReplyPersistence = new ThreadReplyPersistence();
    });
} else {
    window.threadReplyPersistence = new ThreadReplyPersistence();
}

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThreadReplyPersistence;
}