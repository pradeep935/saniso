'use strict'
import forEach from 'lodash/forEach'

class FrontendProductOption {
    constructor() {
        this.priceSale = $('.product-details-content .product-price-sale .js-product-price')
        this.priceOriginal = $('.product-details-content .product-price-original .js-product-price')
        this.priceWithTax = $('.product-price-with-tax')
        this.basePrice = 0
        this.taxPercentage = 0
        this.extraPrice = {}
        
        // Initialize price from data attributes if available
        const priceData = this.priceWithTax.data();
        if (priceData) {
            this.basePrice = priceData.basePrice || 0
            this.taxPercentage = priceData.taxPercentage || 0
        }
        
        this.eventListeners()
        
        // Use locale-specific currency formatting
        const currency = document.documentElement.getAttribute('data-currency') || 'EUR'
        this.formatter = new Intl.NumberFormat(document.documentElement.lang || 'nl-NL', {
            style: 'currency',
            currency: currency
        })
    }

    eventListeners() {
        // Handle variation changes
        $(document).on('change', '.product-variation-form select, .product-variation-form input[type="radio"]', (e) => {
            const $form = $(e.target).closest('form')
            const data = $form.serialize()
            
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: data,
                success: (res) => {
                    if (res.error) {
                        return
                    }
                    
                    const product = res.data
                    // Get the correct tax percentage from the variation or product
                    let taxPercentage = 0
                    if (product.tax_info) {
                        taxPercentage = product.tax_info.percentage
                    } else if (product.total_taxes_percentage) {
                        taxPercentage = product.total_taxes_percentage
                    }
                    
                    // Update prices with new variation data
                    this.updatePrices(
                        product.price,
                        product.sale_price || product.price,
                        taxPercentage
                    )
                }
            })
        })

        // Handle product options
        $('.product-option .form-radio input').change((e) => {
            const name = $(e.target).attr('name')
            this.extraPrice[name] = parseFloat($(e.target).attr('data-extra-price'))
            this.changeDisplayedPrice()
        })

        $('.product-option .form-checkbox input').change((e) => {
            const name = $(e.target).attr('name')
            const extraPrice = parseFloat($(e.target).attr('data-extra-price'))
            if (typeof this.extraPrice[name] == 'undefined') {
                this.extraPrice[name] = []
            }
            this.extraPrice[name].push(extraPrice)
            this.changeDisplayedPrice()
        })
    }

    updatePrices(price, salePrice, taxPercentage) {
        this.basePrice = salePrice || price
        this.taxPercentage = parseFloat(taxPercentage || 0)
        
        // Calculate prices with tax
        const baseExclTax = this.basePrice
        const baseInclTax = this.taxPercentage > 0 
            ? baseExclTax + (baseExclTax * (this.taxPercentage / 100))
            : baseExclTax
        
        // Update price display elements
        $('.price-amount.bb-product-price-text').text(this.formatter.format(baseInclTax))
        $('.price-excl-tax .price-amount').text(this.formatter.format(baseExclTax))
        
        // Update tax info
        const $taxInfo = $('.tax-info')
        if (product && product.tax_info) {
            const taxTitle = product.tax_info.title
            const taxPercentage = product.tax_info.percentage
            if (taxPercentage > 0) {
                const taxText = `${taxTitle} (${taxPercentage}%)`
                $taxInfo.each(function() {
                    const isIncl = $(this).closest('.price-incl-tax').length > 0
                    $(this).text((isIncl ? 'Incl. ' : 'Excl. ') + taxText)
                })
                $taxInfo.show()
            } else {
                $taxInfo.hide()
            }
        } else {
            $taxInfo.hide()
        }
        
        // If there's a sale price, update original price display
        if (price !== salePrice) {
            const origExclTax = price
            const origInclTax = this.taxPercentage > 0
                ? origExclTax + (origExclTax * (this.taxPercentage / 100))
                : origExclTax
            $('.original-price-incl-tax del').text(this.formatter.format(origInclTax))
        }
    }

    changeDisplayedPrice() {
        let extraPrice = 0
        forEach(this.extraPrice, (value) => {
            if (typeof value === 'number') {
                extraPrice += value
            } else if (Array.isArray(value)) {
                extraPrice += value.reduce((sum, val) => sum + (parseFloat(val) || 0), 0)
            }
        })
        
        const totalPrice = this.basePrice + extraPrice
        this.updatePrices(totalPrice, totalPrice, this.taxPercentage)
    }
}

$(() => {
    new FrontendProductOption()
})
