'use strict'

$(document).ready(function() {
    // Initialize affiliate-related JS functionality

    // Handle affiliate registration form
    $(document).on('click', '#affiliate-register-button', function(e) {
        e.preventDefault()
        let $form = $(this).closest('form')

        $.ajax({
            type: 'POST',
            cache: false,
            url: $form.prop('action'),
            data: $form.serialize(),
            beforeSend: () => {
                $(this).prop('disabled', true).addClass('button-loading')
            },
            success: res => {
                if (!res.error) {
                    $form.find('input[type=text], input[type=email], textarea').val('')
                    Botble.showNotice('success', res.message)
                } else {
                    Botble.showNotice('error', res.message)
                }
            },
            error: res => {
                Botble.handleError(res)
            },
            complete: () => {
                $(this).prop('disabled', false).removeClass('button-loading')
            },
        })
    })

    // Handle withdrawal request form
    $(document).on('click', '#affiliate-withdrawal-button', function(e) {
        e.preventDefault()
        let $form = $(this).closest('form')

        $.ajax({
            type: 'POST',
            cache: false,
            url: $form.prop('action'),
            data: $form.serialize(),
            beforeSend: () => {
                $(this).prop('disabled', true).addClass('button-loading')
            },
            success: res => {
                if (!res.error) {
                    $form.find('input[type=text], input[type=number], textarea').val('')
                    Botble.showNotice('success', res.message)
                } else {
                    Botble.showNotice('error', res.message)
                }
            },
            error: res => {
                Botble.handleError(res)
            },
            complete: () => {
                $(this).prop('disabled', false).removeClass('button-loading')
            },
        })
    })
})
