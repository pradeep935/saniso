'use strict'

/**
 * Affiliate Pro Front-end JavaScript
 * This file contains all the JavaScript functions needed for the customer-facing affiliate pages
 */

class AffiliateFront {
    constructor() {
        this.initEvents()
    }

    /**
     * Initialize all event listeners
     */
    initEvents() {
        // Use proper event delegation instead of inline handlers
        $(document).on('click', '[data-copy-affiliate-link]', (e) => {
            e.preventDefault()
            this.copyAffiliateLink()
        })

        $(document).on('click', '[data-copy-affiliate-code]', (e) => {
            e.preventDefault()
            this.copyAffiliateCode()
        })

        $(document).on('click', '[data-copy-text-link]', (e) => {
            e.preventDefault()
            const index = $(e.currentTarget).data('copy-text-link')
            this.copyTextLink(index)
        })

        $(document).on('click', '[data-copy-banner-html]', (e) => {
            e.preventDefault()
            const index = $(e.currentTarget).data('copy-banner-html')
            this.copyBannerHtml(index)
        })

        $(document).on('click', '[data-copy-coupon-code]', (e) => {
            e.preventDefault()
            const code = $(e.currentTarget).data('copy-coupon-code')
            this.copyCouponCode(code)
        })

        // Handle core copy component (same as admin panel)
        $(document).on('click', '[data-bb-toggle="clipboard"]', (e) => {
            e.preventDefault()
            this.handleCoreClipboard(e)
        })

        // Handle banner image errors
        this.initBannerImageErrorHandling()
    }

    /**
     * Copy affiliate link to clipboard
     */
    async copyAffiliateLink() {
        const copyText = document.getElementById('affiliate-link')
        if (!copyText) return

        await this.copyToClipboard(copyText.value)
        this.showSuccessMessage(this.getTranslation('copied_to_clipboard'))
    }

    /**
     * Copy affiliate code to clipboard
     */
    async copyAffiliateCode() {
        const copyText = document.getElementById('affiliate-code')
        if (!copyText) return

        await this.copyToClipboard(copyText.value)
        this.showSuccessMessage(this.getTranslation('copied_to_clipboard'))
    }

    /**
     * Copy text link HTML to clipboard
     * @param {number} index - The index of the text link to copy
     */
    async copyTextLink(index) {
        const copyText = document.getElementById('text-link-' + index)
        if (!copyText) return

        await this.copyToClipboard(copyText.value)
        this.showSuccessMessage(this.getTranslation('html_copied'))
    }

    /**
     * Copy banner HTML to clipboard
     * @param {number} index - The index of the banner to copy
     */
    async copyBannerHtml(index) {
        const copyText = document.getElementById('banner-html-' + index)
        if (!copyText) return

        await this.copyToClipboard(copyText.value)
        this.showSuccessMessage(this.getTranslation('html_copied'))
    }

    /**
     * Copy coupon code to clipboard
     * @param {string} code - The coupon code to copy
     */
    async copyCouponCode(code) {
        await this.copyToClipboard(code)
        this.showSuccessMessage(this.getTranslation('coupon_copied'))
    }

    /**
     * Handle core clipboard component (same as admin panel core.js)
     * @param {Event} e - The click event
     */
    async handleCoreClipboard(e) {
        const target = $(e.currentTarget)
        const copiedMessage = target.data('clipboard-message')
        const action = target.data('clipboard-action') || 'copy'
        const isCut = action.toLowerCase() === 'cut'
        const iconClipboard = target.find('[data-clipboard-icon]')
        const iconClipboardSuccess = target.find('[data-clipboard-success-icon]')
        const clipboardParent = target.data('clipboard-parent')
        const clipboardParentTarget = clipboardParent ? document.querySelector(clipboardParent) : undefined

        let text = target.data('clipboard-text')

        if (!text) {
            const copyTarget = $(target.data('clipboard-target'))

            if (copyTarget.length > 0) {
                text = copyTarget.val()

                isCut && copyTarget.val('')
            }
        }

        await this.copyToClipboard(text, clipboardParentTarget)

        if (copiedMessage) {
            this.showSuccessMessage(copiedMessage)
        }

        // Update icons
        iconClipboard.addClass('d-none')
        iconClipboardSuccess.removeClass('d-none')

        setTimeout(() => {
            iconClipboard.removeClass('d-none')
            iconClipboardSuccess.addClass('d-none')
        }, 1000)
    }

    /**
     * Universal copy to clipboard method (same as core.js)
     * @param {string} textToCopy - The text to copy
     * @param {Element} parentTarget - The parent element for fallback
     */
    async copyToClipboard(textToCopy, parentTarget) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(textToCopy)
        } else {
            this.unsecuredCopyToClipboard(textToCopy, parentTarget)
        }
    }

    /**
     * Fallback copy method for non-secure contexts (same as core.js)
     * @param {string} textToCopy - The text to copy
     * @param {Element} parentTarget - The parent element
     */
    unsecuredCopyToClipboard(textToCopy, parentTarget) {
        parentTarget = parentTarget || document.body
        const textArea = document.createElement('textarea')
        textArea.value = textToCopy
        textArea.style.position = 'absolute'
        textArea.style.left = '-999999px'
        parentTarget.append(textArea)
        textArea.select()

        try {
            document.execCommand('copy')
        } catch (error) {
            console.error('Unable to copy to clipboard', error)
        }

        parentTarget.removeChild(textArea)
    }

    /**
     * Show success message using available notification system
     * @param {string} message - The message to show
     */
    showSuccessMessage(message) {
        // Try different notification systems
        if (typeof Theme !== 'undefined' && Theme.showNotice) {
            Theme.showSuccess(message)
        } else if (typeof Botble !== 'undefined' && Botble.showSuccess) {
            Botble.showSuccess(message)
        } else if (typeof toastr !== 'undefined') {
            toastr.success(message)
        } else {
            // Fallback to alert
            alert(message)
        }
    }

    /**
     * Initialize banner image error handling
     */
    initBannerImageErrorHandling() {
        $(document).on('error', '.banner-image', function() {
            $(this).hide()
        })
    }

    /**
     * Get translation text
     * @param {string} key - Translation key
     * @returns {string} - Translated text
     */
    getTranslation(key) {
        const translations = {
            'copied_to_clipboard': 'Copied to clipboard!',
            'html_copied': 'HTML copied to clipboard!',
            'coupon_copied': 'Coupon code copied to clipboard!',
            'copy_failed': 'Failed to copy. Please try again.'
        }

        return window.affiliateTranslations?.[key] ||
               window.trans?.['plugins/affiliate-pro::affiliate.' + key] ||
               translations[key] ||
               key
    }
}

// Initialize the AffiliateFront class when the document is ready
$(document).ready(function() {
    new AffiliateFront()
})
