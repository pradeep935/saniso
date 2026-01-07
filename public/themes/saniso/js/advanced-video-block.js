/**
 * Advanced Video Block JavaScript - Optimized for Performance
 * Features: Lazy loading, modal management, accessibility, error handling
 */

class AdvancedVideoBlock {
    constructor() {
        this.modals = new Map();
        this.intersectionObserver = null;
        this.resizeObserver = null;
        this.init();
    }

    init() {
        this.setupLazyLoading();
        this.setupModalHandlers();
        this.setupKeyboardNavigation();
        this.setupResponsiveHandling();
        this.optimizeSliders();
    }

    // Lazy Loading Implementation
    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            this.intersectionObserver = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.loadVideoThumbnail(entry.target);
                            this.intersectionObserver.unobserve(entry.target);
                        }
                    });
                },
                {
                    rootMargin: '50px 0px',
                    threshold: 0.1
                }
            );

            // Observe all video thumbnails
            document.querySelectorAll('.video-thumbnail[data-src]').forEach(img => {
                img.classList.add('video-loading');
                this.intersectionObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            this.loadAllThumbnails();
        }
    }

    loadVideoThumbnail(img) {
        const src = img.dataset.src;
        if (!src) return;

        // Create a new image to test loading
        const testImg = new Image();
        
        testImg.onload = () => {
            img.src = src;
            img.classList.remove('video-loading');
            img.classList.add('loaded');
        };
        
        testImg.onerror = () => {
            this.handleThumbnailError(img);
        };
        
        testImg.src = src;
    }

    loadAllThumbnails() {
        document.querySelectorAll('.video-thumbnail[data-src]').forEach(img => {
            this.loadVideoThumbnail(img);
        });
    }

    handleThumbnailError(img) {
        const wrapper = img.closest('.video-thumbnail-wrapper');
        if (wrapper) {
            wrapper.innerHTML = `
                <div class="video-error">
                    <div>
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        <div>Thumbnail not available</div>
                    </div>
                </div>
            `;
        }
    }

    // Modal Management
    setupModalHandlers() {
        // Create modal if it doesn't exist
        if (!document.getElementById('videoModal')) {
            this.createModal();
        }

        // Setup event listeners
        document.addEventListener('click', this.handleModalClick.bind(this));
        document.addEventListener('keydown', this.handleKeyDown.bind(this));
        
        // Setup modal close handlers
        const modal = document.getElementById('videoModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeVideoModal();
                }
            });

            const closeBtn = modal.querySelector('.video-modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.closeVideoModal());
            }
        }
    }

    createModal() {
        const modal = document.createElement('div');
        modal.id = 'videoModal';
        modal.className = 'video-modal';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', 'videoModalTitle');
        
        modal.innerHTML = `
            <div class="video-modal-content">
                <div class="video-modal-header">
                    <h3 class="video-modal-title" id="videoModalTitle"></h3>
                    <button class="video-modal-close" aria-label="Close video modal" title="Close (Esc)">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="video-modal-body" role="main">
                    <!-- Video content will be inserted here -->
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    handleModalClick(e) {
        const playButton = e.target.closest('.video-play-button');
        if (playButton) {
            e.preventDefault();
            const videoItem = playButton.closest('.video-item');
            this.openVideoFromItem(videoItem);
        }
    }

    openVideoFromItem(videoItem) {
        if (!videoItem) return;

        const title = videoItem.querySelector('.video-title')?.textContent || 'Video';
        const videoData = this.extractVideoData(videoItem);
        
        if (videoData) {
            this.openVideoModal(title, videoData.html, videoData.type);
        }
    }

    extractVideoData(videoItem) {
        // Extract video data from data attributes or other sources
        const thumbnail = videoItem.querySelector('.video-thumbnail');
        const playButton = videoItem.querySelector('.video-play-button');
        
        if (!thumbnail || !playButton) return null;

        // Try to get video data from onclick attribute (if present)
        const onclickAttr = playButton.getAttribute('onclick');
        if (onclickAttr) {
            const match = onclickAttr.match(/openVideoModal\('([^']+)',\s*`([^`]+)`/);
            if (match) {
                return {
                    html: match[2],
                    type: this.detectVideoType(match[2])
                };
            }
        }

        return null;
    }

    detectVideoType(html) {
        if (html.includes('youtube.com') || html.includes('youtu.be')) return 'youtube';
        if (html.includes('vimeo.com')) return 'vimeo';
        if (html.includes('<video')) return 'video';
        return 'external';
    }

    openVideoModal(title, videoHtml, type = 'video') {
        const modal = document.getElementById('videoModal');
        const modalTitle = modal.querySelector('.video-modal-title');
        const modalBody = modal.querySelector('.video-modal-body');
        
        if (!modal || !modalTitle || !modalBody) return;

        // Set title
        modalTitle.textContent = title;
        
        // Set video content
        modalBody.innerHTML = videoHtml;
        
        // Show modal with animation
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Trigger animation
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });

        // Focus management for accessibility
        modal.focus();
        this.trapFocus(modal);

        // Store the previously focused element
        this.previouslyFocused = document.activeElement;
    }

    closeVideoModal() {
        const modal = document.getElementById('videoModal');
        if (!modal) return;

        // Stop any playing videos
        const modalBody = modal.querySelector('.video-modal-body');
        if (modalBody) {
            modalBody.innerHTML = '';
        }

        // Hide modal with animation
        modal.classList.remove('show');
        
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Restore focus
            if (this.previouslyFocused) {
                this.previouslyFocused.focus();
                this.previouslyFocused = null;
            }
        }, 300);
    }

    // Keyboard Navigation
    setupKeyboardNavigation() {
        document.addEventListener('keydown', this.handleKeyDown.bind(this));
    }

    handleKeyDown(e) {
        const modal = document.getElementById('videoModal');
        
        if (e.key === 'Escape' && modal && modal.style.display === 'block') {
            e.preventDefault();
            this.closeVideoModal();
        }
        
        // Handle Enter key on play buttons
        if (e.key === 'Enter' && e.target.classList.contains('video-play-button')) {
            e.preventDefault();
            e.target.click();
        }
    }

    trapFocus(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        modal.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
        });
    }

    // Responsive Handling
    setupResponsiveHandling() {
        if ('ResizeObserver' in window) {
            this.resizeObserver = new ResizeObserver(
                this.debounce(this.handleResize.bind(this), 250)
            );
            
            document.querySelectorAll('.advanced-video-block').forEach(block => {
                this.resizeObserver.observe(block);
            });
        } else {
            window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
        }
    }

    handleResize() {
        // Re-initialize sliders if needed
        this.optimizeSliders();
        
        // Adjust modal size
        this.adjustModalSize();
    }

    adjustModalSize() {
        const modal = document.getElementById('videoModal');
        const modalContent = modal?.querySelector('.video-modal-content');
        
        if (modalContent && modal.style.display === 'block') {
            const windowHeight = window.innerHeight;
            const windowWidth = window.innerWidth;
            
            modalContent.style.maxHeight = (windowHeight * 0.9) + 'px';
            modalContent.style.maxWidth = (windowWidth * 0.9) + 'px';
        }
    }

    // Slider Optimization
    optimizeSliders() {
        document.querySelectorAll('.video-slider').forEach(slider => {
            if (slider.classList.contains('slick-initialized')) {
                // Refresh existing slider
                $(slider).slick('refresh');
            }
        });
    }

    // Utility Functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Performance Monitoring
    reportPerformance() {
        if ('performance' in window && 'PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry) => {
                    if (entry.name.includes('video') && entry.duration > 100) {
                        console.warn(`Slow video operation: ${entry.name} took ${entry.duration}ms`);
                    }
                });
            });
            
            observer.observe({ entryTypes: ['measure', 'navigation'] });
        }
    }

    // Cleanup
    destroy() {
        if (this.intersectionObserver) {
            this.intersectionObserver.disconnect();
        }
        
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
        
        // Remove event listeners
        document.removeEventListener('click', this.handleModalClick);
        document.removeEventListener('keydown', this.handleKeyDown);
    }
}

// Global video play function with inline/modal support
window.playVideo = function(videoId, title, videoHtml, playMode = 'inline') {
    const videoItem = document.getElementById(videoId);
    if (!videoItem) return;
    
    if (playMode === 'inline') {
        // Play video inline (replace thumbnail)
        const thumbnailContainer = videoItem.querySelector('.video-thumbnail-container');
        const playerContainer = videoItem.querySelector('.video-player-container');
        const playButton = videoItem.querySelector('.video-play-button');
        
        if (thumbnailContainer && playerContainer && playButton) {
            // Hide thumbnail and play button
            thumbnailContainer.style.display = 'none';
            playButton.style.display = 'none';
            
            // Show video player
            playerContainer.style.display = 'block';
            playerContainer.innerHTML = videoHtml;
            
            // Focus on the video for accessibility
            const iframe = playerContainer.querySelector('iframe');
            const video = playerContainer.querySelector('video');
            if (iframe) iframe.focus();
            else if (video) video.focus();
        }
    } else {
        // Play video in modal
        if (window.videoBlockInstance) {
            window.videoBlockInstance.openVideoModal(title, videoHtml);
        }
    }
};

// Simplified function that reads from button data attributes
window.playVideoFromButton = function(button) {
    const videoId = button.getAttribute('data-video-id');
    const title = button.getAttribute('data-video-title');
    const videoHtmlBase64 = button.getAttribute('data-video-html');
    const playMode = button.getAttribute('data-play-mode') || 'inline';
    
    // Decode the base64 encoded HTML
    let videoHtml = '';
    try {
        videoHtml = atob(videoHtmlBase64);
    } catch (e) {
        console.error('Failed to decode video HTML:', e);
        return;
    }
    
    playVideo(videoId, title, videoHtml, playMode);
};

// Global functions for backward compatibility
window.openVideoModal = function(title, videoHtml) {
    if (window.videoBlockInstance) {
        window.videoBlockInstance.openVideoModal(title, videoHtml);
    }
};

window.closeVideoModal = function() {
    if (window.videoBlockInstance) {
        window.videoBlockInstance.closeVideoModal();
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.videoBlockInstance = new AdvancedVideoBlock();
    
    // Performance monitoring in development
    if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev')) {
        window.videoBlockInstance.reportPerformance();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.videoBlockInstance) {
        window.videoBlockInstance.destroy();
    }
});
