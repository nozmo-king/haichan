/**
 * Content Processor for Haichan
 * Handles greentexting, post links, and other text formatting
 */

class HaichanContentProcessor {
    constructor() {
        this.init();
    }
    
    init() {
        // Process existing content
        this.processAllContent();
        
        // Setup observer for new content
        this.setupContentObserver();
        
        console.log('🔧 Content Processor initialized');
    }
    
    processAllContent() {
        // Process all post content
        document.querySelectorAll('.post-content, .reply-text').forEach(element => {
            this.processContent(element);
        });
    }
    
    setupContentObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Process new posts
                        const postElements = node.querySelectorAll ? 
                            node.querySelectorAll('.post-content, .reply-text') : [];
                        postElements.forEach(element => this.processContent(element));
                        
                        // Process the element itself if it's a post
                        if (node.classList && (node.classList.contains('post-content') || node.classList.contains('reply-text'))) {
                            this.processContent(node);
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
    }
    
    processContent(element) {
        if (element.dataset.processed) return; // Already processed
        
        let content = element.textContent || element.innerText;
        if (!content) return;
        
        // Process the content
        let processedHTML = this.processText(content);
        
        // Update the element
        element.innerHTML = processedHTML;
        element.dataset.processed = 'true';
    }
    
    processText(text) {
        // Split by lines to preserve structure
        const lines = text.split('\n');
        
        return lines.map(line => {
            // Process each line
            let processedLine = line;
            
            // 1. Process greentexting (lines starting with >)
            if (processedLine.trim().startsWith('>') && !processedLine.trim().startsWith('>>')) {
                processedLine = `<span class="greentext">${this.escapeHtml(processedLine)}</span>`;
            }
            // 2. Process post links (>>123)
            else if (processedLine.includes('>>')) {
                processedLine = this.processPostLinks(processedLine);
            }
            // 3. Process regular text
            else {
                processedLine = this.escapeHtml(processedLine);
            }
            
            return processedLine;
        }).join('<br>');
    }
    
    processPostLinks(text) {
        // Escape HTML first
        let escaped = this.escapeHtml(text);
        
        // Find >>postNumber patterns
        const postLinkRegex = /&gt;&gt;(\d+)/g;
        
        return escaped.replace(postLinkRegex, (match, postId) => {
            return `<a href="#" class="post-link" data-post-id="${postId}" onclick="highlightPost(${postId}); return false;">&gt;&gt;${postId}</a>`;
        });
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Post highlighting and navigation functions
window.highlightPost = function(postId) {
    // Remove existing highlights
    document.querySelectorAll('.post-highlighted, .reply-post-highlighted').forEach(el => {
        el.classList.remove('post-highlighted', 'reply-post-highlighted');
    });
    
    // Find and highlight the target post
    const targetPost = document.querySelector(`[data-post-id="${postId}"], .post[data-thread-id="${postId}"]`);
    
    if (targetPost) {
        // Add highlight class
        if (targetPost.classList.contains('reply-post')) {
            targetPost.classList.add('reply-post-highlighted');
        } else {
            targetPost.classList.add('post-highlighted');
        }
        
        // Scroll to post with smooth animation
        targetPost.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        
        // Show notification
        showPostLinkNotification(`Highlighted post #${postId}`);
        
        // Remove highlight after 5 seconds
        setTimeout(() => {
            targetPost.classList.remove('post-highlighted', 'reply-post-highlighted');
        }, 5000);
    } else {
        // Post not found on current page
        showPostLinkNotification(`Post #${postId} not found on this page`, 'warning');
    }
};

function showPostLinkNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `post-link-notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 50px;
        right: 20px;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        z-index: 10001;
        font-family: 'Courier New', monospace;
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    `;
    
    // Set colors based on type
    switch (type) {
        case 'warning':
            notification.style.background = '#ff6b35';
            notification.style.color = 'white';
            break;
        case 'info':
        default:
            notification.style.background = 'var(--ib-accent, #d4af37)';
            notification.style.color = 'white';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-in forwards';
        setTimeout(() => notification.remove(), 300);
    }, 2500);
}

// Add CSS styles for greentexting and post links
function addContentProcessorStyles() {
    if (document.getElementById('content-processor-styles')) return;
    
    const style = document.createElement('style');
    style.id = 'content-processor-styles';
    style.textContent = `
        /* Greentext styling */
        .greentext {
            color: #789922 !important;
            font-weight: normal;
        }
        
        /* Post link styling */
        .post-link {
            color: var(--ib-accent, #d4af37) !important;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s ease;
            cursor: pointer;
        }
        
        .post-link:hover {
            color: var(--ib-text, #444b6e) !important;
            text-decoration: underline;
        }
        
        .post-link:visited {
            color: var(--ib-accent, #d4af37) !important;
        }
        
        /* Post highlighting */
        .post-highlighted {
            background: rgba(212, 175, 55, 0.1) !important;
            border: 2px solid var(--ib-accent, #d4af37) !important;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
        }
        
        .reply-post-highlighted {
            background: rgba(212, 175, 55, 0.1) !important;
            border: 2px solid var(--ib-accent, #d4af37) !important;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
        }
        
        /* Animations */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    
    document.head.appendChild(style);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    addContentProcessorStyles();
    window.haichanContentProcessor = new HaichanContentProcessor();
    console.log('🎨 Content processing system loaded');
});

// Also initialize if DOM already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        addContentProcessorStyles();
        window.haichanContentProcessor = new HaichanContentProcessor();
    });
} else {
    addContentProcessorStyles();
    window.haichanContentProcessor = new HaichanContentProcessor();
}