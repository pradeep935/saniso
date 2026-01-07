// Disable all tooltips across the website
(function() {
    'use strict';

    // Disable Bootstrap tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        // Override Bootstrap Tooltip constructor to do nothing
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
        $.fn.tooltip = function() {
            return this;
        };
    }

    // Function to remove all title attributes that could trigger native tooltips
    function removeTitleAttributes() {
        const elementsWithTitle = document.querySelectorAll('[title]');
        elementsWithTitle.forEach(function(element) {
            // Store original title in a data attribute if needed for accessibility
            const title = element.getAttribute('title');
            if (title) {
                element.setAttribute('data-original-title', title);
                element.removeAttribute('title');
            }
        });
    }

    // Function to disable noUISlider tooltips
    function disableNoUISliderTooltips() {
        // Override noUiSlider options to disable tooltips
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

    // Function to prevent any tooltip initialization
    function preventTooltipInit() {
        // Disable data-bs-toggle="tooltip" functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggers = document.querySelectorAll('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]');
            tooltipTriggers.forEach(function(trigger) {
                trigger.removeAttribute('data-bs-toggle');
                trigger.removeAttribute('data-toggle');
                trigger.removeAttribute('data-bs-placement');
                trigger.removeAttribute('data-placement');
            });
        });
    }

    // Function to override any existing tooltip instances
    function destroyExistingTooltips() {
        // Remove any existing tooltip elements
        const tooltips = document.querySelectorAll('.tooltip, .bs-tooltip-auto, .bs-tooltip-top, .bs-tooltip-bottom, .bs-tooltip-start, .bs-tooltip-end');
        tooltips.forEach(function(tooltip) {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        });

        // Remove popover elements too
        const popovers = document.querySelectorAll('.popover, .bs-popover-auto, .bs-popover-top, .bs-popover-bottom, .bs-popover-start, .bs-popover-end');
        popovers.forEach(function(popover) {
            if (popover.parentNode) {
                popover.parentNode.removeChild(popover);
            }
        });
    }

    // Run immediately
    removeTitleAttributes();
    disableNoUISliderTooltips();
    preventTooltipInit();
    destroyExistingTooltips();

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            removeTitleAttributes();
            destroyExistingTooltips();
        });
    }

    // Periodically check for new elements with title attributes (for dynamic content)
    setInterval(function() {
        removeTitleAttributes();
        destroyExistingTooltips();
    }, 2000);

    // Override MutationObserver to catch dynamically added tooltips
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            // Remove title attributes from new elements
                            if (node.hasAttribute && node.hasAttribute('title')) {
                                const title = node.getAttribute('title');
                                node.setAttribute('data-original-title', title);
                                node.removeAttribute('title');
                            }
                            
                            // Remove title attributes from child elements
                            const childrenWithTitle = node.querySelectorAll ? node.querySelectorAll('[title]') : [];
                            childrenWithTitle.forEach(function(child) {
                                const title = child.getAttribute('title');
                                child.setAttribute('data-original-title', title);
                                child.removeAttribute('title');
                            });

                            // Remove tooltip elements
                            if (node.classList && (
                                node.classList.contains('tooltip') ||
                                node.classList.contains('popover') ||
                                node.className.includes('bs-tooltip') ||
                                node.className.includes('bs-popover')
                            )) {
                                if (node.parentNode) {
                                    node.parentNode.removeChild(node);
                                }
                            }
                        }
                    });
                }
                
                if (mutation.type === 'attributes' && mutation.attributeName === 'title') {
                    const element = mutation.target;
                    const title = element.getAttribute('title');
                    if (title) {
                        element.setAttribute('data-original-title', title);
                        element.removeAttribute('title');
                    }
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['title']
        });
    }

    console.log('All tooltips have been disabled across the website');
})();