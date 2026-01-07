'use strict'

class AffiliateActions {
    constructor() {
        this.initEvents()
    }

    initEvents() {
        // Handle approve/reject actions for commissions
        this.handleCommissionActions()

        // Handle approve/reject actions for withdrawals
        this.handleWithdrawalActions()

        // Handle approve/reject actions for pending affiliates
        this.handlePendingAffiliateActions()
    }

    handleCommissionActions() {
        $(document).on('click', '#approve-commission-button, #reject-commission-button', function(e) {
            e.preventDefault()

            const $this = $(this)
            const url = $this.data('url')
            const action = $this.data('action')
            const commissionId = $this.data('id')

            if (!url) {
                return
            }

            $this.addClass('button-loading').prop('disabled', true)

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: (res) => {
                    if (!res.error) {
                        Botble.showSuccess(res.message)

                        // Close the modal
                        $(`#${action}-commission-modal`).modal('hide')

                        // Update the status badge
                        setTimeout(() => {
                            window.location.reload()
                        }, 1000)
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                },
                complete: () => {
                    $this.removeClass('button-loading').prop('disabled', false)
                },
            })
        })
    }

    handleWithdrawalActions() {
        $(document).on('click', '#approve-withdrawal-button, #reject-withdrawal-button', function(e) {
            e.preventDefault()

            const $this = $(this)
            const url = $this.data('url')
            const action = $this.data('action')
            const withdrawalId = $this.data('id')

            if (!url) {
                return
            }

            $this.addClass('button-loading').prop('disabled', true)

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: (res) => {
                    if (!res.error) {
                        Botble.showSuccess(res.message)

                        // Close the modal
                        $(`#${action}-withdrawal-modal`).modal('hide')

                        // Update the status badge
                        setTimeout(() => {
                            window.location.reload()
                        }, 1000)
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                },
                complete: () => {
                    $this.removeClass('button-loading').prop('disabled', false)
                },
            })
        })
    }

    handlePendingAffiliateActions() {
        $(document).on('click', '#approve-affiliate-button, #reject-affiliate-button', function(e) {
            e.preventDefault()

            const $this = $(this)
            const url = $this.data('url')
            const action = $this.data('action')
            const affiliateId = $this.data('id')

            if (!url) {
                return
            }

            $this.addClass('button-loading').prop('disabled', true)

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: (res) => {
                    if (!res.error) {
                        Botble.showSuccess(res.message)

                        // Close the modal
                        $(`#${action}-affiliate-modal`).modal('hide')

                        // Redirect to the pending affiliates list
                        setTimeout(() => {
                            window.location.href = $this.data('redirect') || route('affiliate-pro.pending.index')
                        }, 1000)
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                },
                complete: () => {
                    $this.removeClass('button-loading').prop('disabled', false)
                },
            })
        })
    }
}

$(document).ready(function() {
    new AffiliateActions()
})
