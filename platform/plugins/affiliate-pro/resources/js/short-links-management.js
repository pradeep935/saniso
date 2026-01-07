'use strict'

/**
 * Short Links Management JavaScript
 * Handles short link creation, deletion, and copy functionality
 */

class ShortLinksManagement {
    constructor() {
        this.initEvents()
    }

    /**
     * Initialize event listeners
     */
    initEvents() {
        // Copy buttons are handled by the core clipboard system with data-bb-toggle="clipboard"
        // Form submission is now handled by the partial view's JavaScript

        // Handle delete button clicks
        $(document).on('click', '.delete-link-btn', (e) => {
            e.preventDefault()
            const linkId = $(e.currentTarget).data('id')
            this.handleDeleteLink(linkId)
        })

        // Listen for custom shortLinkCreated events from the partial forms
        window.addEventListener('shortLinkCreated', (e) => {
            this.handleShortLinkCreated(e.detail)
        })
    }

    /**
     * Handle short link created event from partial forms
     * @param {object} detail - Event detail containing shortLink data and formId
     */
    handleShortLinkCreated(detail) {
        const { shortLink, formId } = detail

        // If we're on the short-links page, we might want to refresh the list
        // or add the new link to the existing list
        if (window.location.pathname.includes('/short-links')) {
            // Optionally refresh the page or dynamically add the new link
            // For now, we'll just show a success message
            if (typeof Theme !== 'undefined' && Theme.showNotice) {
                Theme.showSuccess(window.affiliateTranslations?.shortLinkCreated || 'Short link created successfully!')
            }
        }
    }



    /**
     * Handle delete link action
     * @param {number} linkId - The link ID to delete
     */
    handleDeleteLink(linkId) {
        const $linkCard = $(`#short-link-${linkId}`)

        if (confirm(this.getTranslation('deleteConfirm'))) {
            $.ajax({
                type: 'DELETE',
                url: `/customer/affiliate/short-links/${linkId}`,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: (res) => {
                    if (!res.error) {
                        if (Theme !== 'undefined' && Theme.showNotice) {
                            Theme.showSuccess(res.message)
                        }

                        $linkCard.fadeOut(300, function() {
                            $(this).remove()

                            // Check if no links remain
                            if ($('.short-link-item').length === 0) {
                                ShortLinksManagement.showEmptyState()
                            }
                        })
                    } else {
                        if (Theme !== 'undefined' && Theme.showNotice) {
                            Theme.showError(res.message)
                        }
                    }
                },
                error: (res) => {
                    if (Theme !== 'undefined' && Theme.showNotice) {
                        Theme.showError(res.responseJSON?.message || this.getTranslation('errorOccurred'))
                    }
                },
            })
        }
    }

    /**
     * Show empty state when no links exist
     */
    static showEmptyState() {
        const emptyStateTemplate = document.getElementById('short-links-empty-state-template')
        if (emptyStateTemplate) {
            $('.short-links-list').html(emptyStateTemplate.innerHTML)
        }
    }

    /**
     * Get translation text
     * @param {string} key - Translation key
     * @returns {string} - Translated text
     */
    getTranslation(key) {
        return window.affiliateTranslations?.[key] || key
    }
}

// Initialize when document is ready - with jQuery availability check
function initShortLinksManagement() {
    if (typeof $ !== 'undefined' && typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            new ShortLinksManagement()
        })
    } else {
        // If jQuery is not available yet, wait and try again
        setTimeout(initShortLinksManagement, 100)
    }
}

// Start initialization
initShortLinksManagement()
