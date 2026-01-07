'use strict'

/**
 * Affiliate Commission Info JavaScript
 * Handles copy functionality for affiliate links on product pages
 */

class AffiliateCommissionInfo {
    constructor() {
        this.initEvents()
    }

    /**
     * Initialize event listeners
     */
    initEvents() {
        // Use event delegation for copy buttons
        $(document).on('click', '[data-affiliate-copy-link]', (e) => {
            e.preventDefault()
            const productId = $(e.currentTarget).data('affiliate-copy-link')
            this.copyAffiliateLink(productId, e.currentTarget)
        })
    }

    /**
     * Copy affiliate link to clipboard
     * @param {string} productId - The product ID
     * @param {HTMLElement} button - The button element that was clicked
     */
    copyAffiliateLink(productId, button) {
        const linkInput = document.getElementById('affiliate-link-' + productId)

        if (!linkInput) {
            console.error('Affiliate link input not found for product ID:', productId)
            return
        }

        linkInput.select()
        linkInput.setSelectionRange(0, 99999) // For mobile devices

        try {
            document.execCommand('copy')

            // Show success feedback
            const $button = $(button)
            const originalHtml = $button.html()

            $button.html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>')
            $button.removeClass('btn-outline-secondary').addClass('btn-success')

            setTimeout(() => {
                $button.html(originalHtml)
                $button.removeClass('btn-success').addClass('btn-outline-secondary')
            }, 2000)

        } catch (err) {
            console.error('Failed to copy link:', err)

            // Show error feedback
            const $button = $(button)
            const originalHtml = $button.html()

            $button.html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>')
            $button.removeClass('btn-outline-secondary').addClass('btn-danger')

            setTimeout(() => {
                $button.html(originalHtml)
                $button.removeClass('btn-danger').addClass('btn-outline-secondary')
            }, 2000)
        }
    }
}

// Initialize when document is ready
$(document).ready(function() {
    new AffiliateCommissionInfo()
})
