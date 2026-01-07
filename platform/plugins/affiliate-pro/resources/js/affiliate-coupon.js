'use strict'

$(document).ready(function() {
    // Handle discount type change
    $(document).on('change', '#discount-type', function() {
        let discountType = $(this).val()
        let discountAmountInput = $('#discount-amount')

        if (discountType === 'percentage') {
            discountAmountInput.attr('max', 100)
            if (parseFloat(discountAmountInput.val()) > 100) {
                discountAmountInput.val(100)
            }
        } else {
            discountAmountInput.removeAttr('max')
        }
    })
})
