// Optimized tooltip disabler with passive event listeners
(function() {
    'use strict';

    // Disable Bootstrap tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const originalTooltip = bootstrap.Tooltip;
        bootstrap.Tooltip = function() {
            return {
                show: function() {},
                hide: function() {},
                toggle: function() {},
                dispose: function() {},
                enable: function() {},
                disable: function() {},
                toggleEnabled: function() {},
                update: function() {}
            };
        };
        bootstrap.Tooltip.getInstance = function() { return null; };
        bootstrap.Tooltip.getOrCreateInstance = function() { return new bootstrap.Tooltip(); };
    }

    // Disable jQuery tooltips if present
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $.fn.tooltip = function() { return this; };
    }

    // Function to remove all title attributes
    function removeTitleAttributes() {
        try {
            const elementsWithTitle = document.querySelectorAll('[title]');
            elementsWithTitle.forEach(function(element) {
                const title = element.getAttribute('title');
                if (title) {
                    element.setAttribute('data-original-title', title);
                    element.removeAttribute('title');
                }
            });
        } catch (e) {
            // Silently handle errors
        }
    }

    // Function to disable noUISlider tooltips
    function disableNoUISliderTooltips() {
        if (typeof noUiSlider !== 'undefined') {
            const originalCreate = noUiSlider.create;
            noUiSlider.create = function(target, options) {
                if (options && options.tooltips) {
                    options.tooltips = false;
                }
                return originalCreate.call(this, target, options);
            };
        }
    }

    // Function to destroy existing tooltips
    function destroyExistingTooltips() {
        try {
            // Remove Bootstrap tooltip elements
            const tooltipElements = document.querySelectorAll('.tooltip, .popover, [class*="bs-tooltip"], [class*="bs-popover"]');
            tooltipElements.forEach(function(element) {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });

            // Destroy jQuery tooltips
            if (typeof $ !== 'undefined') {
                $('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]').tooltip('dispose').off();
            }
        } catch (e) {
            // Silently handle errors
        }
    }

    // Throttled cleanup function
    let cleanupTimeout;
    function throttledCleanup() {
        clearTimeout(cleanupTimeout);
        cleanupTimeout = setTimeout(function() {
            removeTitleAttributes();
            destroyExistingTooltips();
        }, 100);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            removeTitleAttributes();
            destroyExistingTooltips();
            disableNoUISliderTooltips();
        }, { passive: true });
    } else {
        removeTitleAttributes();
        destroyExistingTooltips();
        disableNoUISliderTooltips();
    }

    // Optimized MutationObserver for dynamic content
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            let shouldCleanup = false;
            
            for (let i = 0; i < mutations.length; i++) {
                const mutation = mutations[i];
                
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    shouldCleanup = true;
                    break;
                }
                
                if (mutation.type === 'attributes' && mutation.attributeName === 'title') {
                    shouldCleanup = true;
                    break;
                }
            }
            
            if (shouldCleanup) {
                throttledCleanup();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['title']
        });
    }

    // Disable tooltip-related event handlers with passive listeners
    const eventTypes = ['mouseenter', 'mouseover', 'focus'];
    eventTypes.forEach(function(eventType) {
        document.addEventListener(eventType, function(e) {
            if (e.target && e.target.hasAttribute && e.target.hasAttribute('title')) {
                const title = e.target.getAttribute('title');
                if (title) {
                    e.target.setAttribute('data-original-title', title);
                    e.target.removeAttribute('title');
                }
            }
        }, { passive: true, capture: true });
    });

    // Periodic cleanup (reduced frequency)
    setInterval(throttledCleanup, 5000);

    console.log('✅ Tooltips disabled with performance optimizations');
})();