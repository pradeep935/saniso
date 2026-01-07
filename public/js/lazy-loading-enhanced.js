/**
 * Enhanced Lazy Loading Implementation for Saniso
 * Optimizes image loading performance with intersection observer
 */

document.addEventListener('DOMContentLoaded', function() {
    // Lazy loading configuration
    const lazyImageConfig = {
        root: null,
        rootMargin: '50px',
        threshold: 0.01
    };

    // Initialize lazy loading observer
    if ('IntersectionObserver' in window) {
        const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src || img.dataset.original;
                    
                    if (src) {
                        // Create new image to preload
                        const imageLoader = new Image();
                        
                        imageLoader.onload = function() {
                            img.src = src;
                            img.classList.remove('lazy-loading');
                            img.classList.add('lazy-loaded');
                            
                            // Fade in effect
                            img.style.opacity = '0';
                            img.style.transition = 'opacity 0.3s ease-in-out';
                            setTimeout(() => {
                                img.style.opacity = '1';
                            }, 50);
                        };
                        
                        imageLoader.onerror = function() {
                            img.classList.add('lazy-error');
                        };
                        
                        imageLoader.src = src;
                        observer.unobserve(img);
                    }
                }
            });
        }, lazyImageConfig);

        // Observe all lazy images
        const lazyImages = document.querySelectorAll('img[data-src], img[data-original]');
        lazyImages.forEach(function(img) {
            img.classList.add('lazy-loading');
            lazyImageObserver.observe(img);
        });

        // Product gallery lazy loading
        const productImages = document.querySelectorAll('.product-image, .bb-product-gallery img, .product-item img');
        productImages.forEach(function(img) {
            if (!img.complete && img.naturalHeight === 0) {
                img.classList.add('lazy-loading');
                lazyImageObserver.observe(img);
            }
        });
    }

    // Fallback for older browsers
    else {
        const lazyImages = document.querySelectorAll('img[data-src], img[data-original]');
        lazyImages.forEach(function(img) {
            const src = img.dataset.src || img.dataset.original;
            if (src) {
                img.src = src;
            }
        });
    }

    // Preload critical images
    const criticalImages = document.querySelectorAll('.hero-image, .main-banner img, .featured-product img');
    criticalImages.forEach(function(img) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = img.src || img.dataset.src;
        document.head.appendChild(link);
    });
});

// CSS for lazy loading effects
const lazyLoadingCSS = `
<style>
    .lazy-loading {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        min-height: 200px;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    .lazy-loaded {
        animation: none;
        background: none;
    }
    
    .lazy-error {
        background: #f5f5f5 url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect width="40" height="40" fill="%23ddd"/><text x="50%" y="50%" text-anchor="middle" dy="0.3em" font-family="Arial" font-size="12" fill="%23999">Image</text></svg>') center/contain no-repeat;
    }
</style>
`;

document.head.insertAdjacentHTML('beforeend', lazyLoadingCSS);