/**
 * Mollie POS Terminal Integration for POS Pro
 * Extends the existing POS interface with Mollie Terminal payment functionality
 */
(function($) {
    'use strict';

    // Mollie POS Terminal Integration
    window.MolliePosIntegration = {
        currentOrderId: null,
        paymentInProgress: false,
        
        /**
         * Initialize Mollie POS integration
         */
        init: function() {
            this.bindEvents();
            this.extendCheckoutProcess();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            const self = this;
            
            // Listen for payment method changes
            $(document).on('change', 'input[name="payment_method"]', function() {
                const paymentMethod = $(this).val();
                self.updateCheckoutButtons(paymentMethod);
                
                if (paymentMethod === 'mollie_terminal') {
                    self.prepareTerminalPayment();
                }
            });

            // Handle Pay Now button for Mollie Terminal
            $(document).on('click', '#pay-now-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handlePayNowClick();
            });

            // Update checkout modal when opened
            $(document).on('shown.bs.modal', '#checkout-modal', function() {
                const selectedPaymentMethod = $('input[name="payment_method"]:checked').val();
                self.updateCheckoutModal(selectedPaymentMethod);
                self.updateCheckoutButtons(selectedPaymentMethod);
            });
        },

        /**
         * Update checkout buttons based on payment method
         */
        updateCheckoutButtons: function(paymentMethod) {
            if (paymentMethod === 'mollie_terminal') {
                $('#complete-order-btn').hide();
                $('#pay-now-btn').show();
            } else {
                $('#complete-order-btn').show();
                $('#pay-now-btn').hide();
            }
        },

        /**
         * Update checkout modal information
         */
        updateCheckoutModal: function(paymentMethod) {
            const $paymentInfo = $('#checkout-payment-method-info');
            const $paymentInput = $('#checkout-payment-method');
            
            if (paymentMethod === 'mollie_terminal') {
                $paymentInfo.html('<i class="ti ti-device-mobile me-1 text-success"></i>Mollie Terminal');
                $paymentInput.val('mollie_terminal');
            } else if (paymentMethod === 'card') {
                $paymentInfo.html('<i class="ti ti-credit-card me-1 text-primary"></i>Card');
                $paymentInput.val('card');
            } else if (paymentMethod === 'other') {
                $paymentInfo.html('<i class="ti ti-wallet me-1 text-warning"></i>Other');
                $paymentInput.val('other');
            } else {
                $paymentInfo.html('<i class="ti ti-cash me-1 text-success"></i>Cash');
                $paymentInput.val('cash');
            }
        },

        /**
         * Prepare terminal payment by validating configuration
         */
        prepareTerminalPayment: function() {
            const self = this;
            
            // Show loading indicator
            const $paymentSection = $('.form-selectgroup');
            $paymentSection.append('<div id="mollie-config-check" class="mt-2 text-center"><div class="spinner-border spinner-border-sm me-2"></div>Checking Mollie configuration...</div>');
            
            // Validate Mollie configuration
            $.get('/admin/mollie/pos/validate-config')
                .done(function(response) {
                    $('#mollie-config-check').remove();
                    if (response.error) {
                        self.showError('Mollie configuration error: ' + (response.message || 'Unknown error'));
                        // Switch back to previous payment method
                        $('input[name="payment_method"][value="cash"]').prop('checked', true);
                    } else {
                        self.showSuccess('Mollie Terminal ready for payment');
                    }
                })
                .fail(function(xhr) {
                    $('#mollie-config-check').remove();
                    self.showError('Failed to validate Mollie configuration');
                    $('input[name="payment_method"][value="cash"]').prop('checked', true);
                });
        },

        /**
         * Handle Pay Now button click
         */
        handlePayNowClick: function() {
            const self = this;
            
            if (self.paymentInProgress) {
                return;
            }

            // Create order first, then process terminal payment
            self.createOrderWithTerminalPayment();
        },

        /**
         * Create order and process terminal payment
         */
        createOrderWithTerminalPayment: function() {
            const self = this;
            const $payButton = $('#pay-now-btn');
            
            // Show loading on Pay Now button
            $payButton.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-2"></div>Creating order...');
            
            // Create order with pending payment status
            self.createOrderForTerminalPayment(function(orderData) {
                // Close checkout modal
                $('#checkout-modal').modal('hide');
                
                // Start terminal payment process
                self.processTerminalPaymentForOrder(orderData);
            }, function(error) {
                // Reset button on error
                $payButton.prop('disabled', false).html('<i class="ti ti-device-mobile me-1"></i>Pay Now');
                self.showError(error || 'Failed to create order');
            });
        },

        /**
         * Create order for terminal payment (with pending status)
         */
        createOrderForTerminalPayment: function(successCallback, errorCallback) {
            const self = this;
            const $form = $('#checkout-form');
            const $container = $('#pos-container');
            const checkoutUrl = $container.data('checkout-url');
            
            // Collect form data
            const formData = {
                customer_id: $('#checkout-customer-id').val() || null,
                delivery_option: $('input[name="delivery_option"]:checked').val(),
                payment_method: 'mollie_terminal', // Set correct payment method
                notes: $('textarea[name="notes"]').val() || null,
                _token: $('meta[name="csrf-token"]').attr('content'),
                create_pending: true // Flag to create order in pending status
            };

            // Add address data if shipping is selected
            if (formData.delivery_option === 'shipping') {
                const addressData = this.collectAddressData();
                Object.assign(formData, addressData);
            }

            // Create order
            $.post(checkoutUrl, formData)
                .done(function(response) {
                    if (response.error) {
                        errorCallback(response.message || 'Order creation failed');
                    } else {
                        successCallback(response.data);
                    }
                })
                .fail(function(xhr) {
                    errorCallback('Failed to create order');
                });
        },

        /**
         * Process terminal payment for created order
         */
        processTerminalPaymentForOrder: function(orderData) {
            const self = this;
            
            self.currentOrderId = orderData.order.id;
            self.paymentInProgress = true;
            
            // Show terminal payment modal
            self.showTerminalPaymentModal(orderData);

            // Start terminal payment process
            const paymentData = {
                order_id: orderData.order.id,
                payment_type: 'card',
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.post('/admin/mollie/pos/process-payment', paymentData)
                .done(function(response) {
                    if (response.error) {
                        self.showError(response.message || 'Terminal payment failed');
                        self.closeTerminalModal();
                        self.paymentInProgress = false;
                    } else {
                        // Start status monitoring
                        self.startPaymentMonitoring(response.data.payment_id, orderData);
                    }
                })
                .fail(function(xhr) {
                    self.showError('Failed to process terminal payment');
                    self.closeTerminalModal();
                    self.paymentInProgress = false;
                });
        },

        /**
         * Create order using existing POS checkout process
         */
        createOrder: function(callback) {
            const self = this;
            const $form = $('#checkout-form');
            const $container = $('#pos-container');
            const checkoutUrl = $container.data('checkout-url');
            
            // Collect form data
            const formData = {
                customer_id: $('#checkout-customer-id').val() || null,
                delivery_option: $('input[name="delivery_option"]:checked').val(),
                payment_method: 'cash', // Temporarily set to cash for order creation
                notes: $('textarea[name="notes"]').val() || null,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Add address data if shipping is selected
            if (formData.delivery_option === 'shipping') {
                const addressData = this.collectAddressData();
                Object.assign(formData, addressData);
            }

            // Show loading
            const $button = $('#complete-order-btn');
            $button.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-2"></div>Creating order...');

            // Create order
            $.post(checkoutUrl, formData)
                .done(function(response) {
                    if (response.error) {
                        self.showError(response.message || 'Order creation failed');
                        $button.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Complete Order');
                    } else {
                        callback(response.data);
                    }
                })
                .fail(function(xhr) {
                    self.showError('Failed to create order');
                    $button.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Complete Order');
                });
        },

        /**
         * Process terminal payment
         */
        processTerminalPayment: function(orderData) {
            const self = this;
            
            // Close checkout modal and show terminal payment modal
            $('#checkout-modal').modal('hide');
            self.showTerminalPaymentModal(orderData);

            // Start terminal payment process
            const paymentData = {
                order_id: orderData.order.id,
                payment_type: 'card',
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            self.paymentInProgress = true;

            $.post('/admin/mollie/pos/process-payment', paymentData)
                .done(function(response) {
                    if (response.error) {
                        self.showError(response.message || 'Terminal payment failed');
                        self.closeTerminalModal();
                    } else {
                        // Start status monitoring
                        self.startPaymentMonitoring(response.data.payment_id, orderData);
                    }
                })
                .fail(function(xhr) {
                    self.showError('Failed to process terminal payment');
                    self.closeTerminalModal();
                });
        },

        /**
         * Show terminal payment modal
         */
        showTerminalPaymentModal: function(orderData) {
            const modalHtml = `
                <div class="modal fade" id="mollie-terminal-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="ti ti-device-mobile me-2"></i>
                                    Mollie Terminal Payment
                                </h5>
                            </div>
                            <div class="modal-body text-center p-4">
                                <div class="mb-4">
                                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="visually-hidden">Processing...</span>
                                    </div>
                                    <h4 id="terminal-status-text">Sending payment to terminal...</h4>
                                    <p class="text-muted" id="terminal-order-info">
                                        Order: ${orderData.order.code}<br>
                                        Customer: ${orderData.customer ? orderData.customer.name : 'Guest'}<br>
                                        Amount: €${parseFloat(orderData.order.amount || 0).toFixed(2)}
                                    </p>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                         role="progressbar" style="width: 25%" id="terminal-progress-bar"></div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    Please complete the payment on the terminal device
                                </div>
                                <div id="terminal-timer" class="text-muted"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="cancel-terminal-payment" class="btn btn-danger">
                                    <i class="ti ti-x me-1"></i>Cancel Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal and add new one
            $('#mollie-terminal-modal').remove();
            $('body').append(modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('mollie-terminal-modal'));
            modal.show();

            // Bind cancel button
            $('#cancel-terminal-payment').on('click', function() {
                self.cancelTerminalPayment();
            });
        },

        /**
         * Start payment status monitoring
         */
        startPaymentMonitoring: function(paymentId, orderData) {
            const self = this;
            
            console.log('🕐 Using centralized PaymentTimer for monitoring');
            
            // Store payment ID for cancellation
            this.currentPaymentId = paymentId;
            
            // Clear any existing intervals to prevent conflicts
            if (this.statusInterval) {
                clearInterval(this.statusInterval);
                this.statusInterval = null;
            }
            
            // Update progress
            $('#terminal-status-text').text('Waiting for payment on terminal...');
            $('#terminal-progress-bar').css('width', '50%');

            // Start centralized timer
            if (window.PaymentTimer) {
                window.PaymentTimer.start({
                    onTick: function(timeLeft, formattedTime) {
                        $('#terminal-timer').text(`Time remaining: ${formattedTime}`);
                    },
                    onTimeout: function() {
                        if (self.statusInterval) {
                            clearInterval(self.statusInterval);
                            self.statusInterval = null;
                        }
                        self.handlePaymentTimeout();
                    }
                });
            } else {
                console.warn('⚠️ PaymentTimer not available, falling back to local timer');
                // Fallback timer if centralized timer not loaded
                let timeRemaining = 120;
                const timerInterval = setInterval(function() {
                    timeRemaining--;
                    const minutes = Math.floor(timeRemaining / 60);
                    const seconds = timeRemaining % 60;
                    $('#terminal-timer').text(`Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`);

                    if (timeRemaining <= 0) {
                        clearInterval(timerInterval);
                        clearInterval(self.statusInterval);
                        self.handlePaymentTimeout();
                    }
                }, 1000);
            }

            // Check payment status every 3 seconds
            const statusInterval = setInterval(function() {
                console.log('🔍 Checking payment status...', paymentId);
                
                $.get('/admin/mollie/pos/payment-status', { payment_id: paymentId })
                    .done(function(response) {
                        console.log('💳 Payment status response:', response);
                        
                        if (response.error) {
                            clearInterval(statusInterval);
                            clearInterval(timerInterval);
                            self.showError('Status check failed: ' + response.message);
                            return;
                        }

                        const status = response.data;
                        const message = response.message || '';
                        
                        // Check for completion
                        if (status.is_completed) {
                            clearInterval(statusInterval);
                            clearInterval(timerInterval);
                            console.log('✅ Payment completed, closing modal');
                            self.handlePaymentSuccess(orderData);
                            return;
                        } 
                        
                        // Check for failure/cancellation with multiple fallbacks
                        const isFailed = status.is_failed || 
                                       status.mollie_status === 'canceled' || 
                                       status.mollie_status === 'failed' || 
                                       status.mollie_status === 'expired' ||
                                       status.status === 'failed' ||
                                       message.includes('failed') ||
                                       message.includes('canceled');
                                       
                        if (isFailed) {
                            clearInterval(statusInterval);
                            clearInterval(timerInterval);
                            console.log('❌ Payment failed/canceled, closing modal');
                            self.handlePaymentFailure();
                            return;
                        }
                        
                        console.log('⏳ Payment still pending:', {
                            mollie_status: status.mollie_status,
                            mapped_status: status.status,
                            is_failed: status.is_failed,
                            is_completed: status.is_completed
                        });
                    })
                    .fail(function(xhr, textStatus, errorThrown) {
                        console.warn('❗ Status check failed, continuing monitoring:', textStatus);
                        // Continue monitoring on status check failure
                    });
            }, 3000);

            // Store intervals for cleanup
            this.statusInterval = statusInterval;
            this.timerInterval = timerInterval;
        },

        /**
         * Handle successful payment
         */
        handlePaymentSuccess: function(orderData) {
            const self = this;
            
            // Update modal
            $('#terminal-status-text').text('Payment completed successfully!');
            $('#terminal-progress-bar').css('width', '100%').removeClass('progress-bar-animated').addClass('bg-success');
            $('#cancel-terminal-payment').text('Close').removeClass('btn-danger').addClass('btn-success');

            // Show success message
            this.showSuccess('Payment completed successfully!');

            // Auto-close modal and redirect after 2 seconds
            setTimeout(function() {
                self.closeTerminalModal();
                self.showOrderReceipt(orderData.order.id);
            }, 2000);

            self.paymentInProgress = false;
        },

        /**
         * Handle payment failure
         */
        handlePaymentFailure: function() {
            const self = this;
            
            console.log('🔴 Handling payment failure - preparing to close modal');
            
            $('#terminal-status-text').text('Payment failed or was cancelled');
            $('#terminal-progress-bar').css('width', '100%').removeClass('progress-bar-animated').addClass('bg-danger');
            $('#cancel-terminal-payment').text('Close').removeClass('btn-danger').addClass('btn-secondary');
            
            this.showWarning('Payment was canceled or failed');
            this.paymentInProgress = false;
            
            // Auto-close modal after 3 seconds to give user time to read message
            setTimeout(function() {
                console.log('🏪 Auto-closing modal after payment failure');
                self.closeTerminalModal();
            }, 3000);
        },

        /**
         * Handle payment timeout
         */
        handlePaymentTimeout: function() {
            const self = this;
            
            // Cancel the payment on Mollie side
            if (self.currentPaymentId) {
                $.post('/admin/mollie/pos/cancel-payment', {
                    payment_id: self.currentPaymentId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }).done(function(response) {
                    console.log('Payment timeout cancellation:', response);
                }).fail(function(xhr) {
                    console.error('Failed to cancel payment on timeout:', xhr);
                });
            }

            $('#terminal-status-text').text('Payment timed out');
            $('#terminal-progress-bar').css('width', '100%').removeClass('progress-bar-animated').addClass('bg-warning');
            
            this.showError('Payment timed out after 5 minutes');
            this.paymentInProgress = false;
        },

        /**
         * Cancel terminal payment
         */
        cancelTerminalPayment: function() {
            const self = this;
            
            // Show canceling status
            $('#terminal-status-text').text('Canceling payment...');
            $('#cancel-terminal-payment').prop('disabled', true).text('Canceling...');
            
            if (self.currentPaymentId) {
                $.post('/admin/mollie/pos/cancel-payment', {
                    payment_id: self.currentPaymentId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }).done(function(response) {
                    console.log('Payment cancellation response:', response);
                    const data = response.data || {};
                    
                    if (data.terminal_cleared) {
                        self.showSuccess('Payment canceled successfully! Terminal cleared automatically.');
                    } else if (data.was_cancelable === false) {
                        self.showWarning('Payment canceled locally but terminal may need manual clearing.');
                    } else {
                        self.showSuccess('Payment canceled successfully! Please check terminal display.');
                    }
                    
                    setTimeout(() => self.closeTerminalModal(), 2500);
                }).fail(function(xhr) {
                    console.error('Payment cancellation failed:', xhr);
                    let errorMessage = 'Failed to cancel payment';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch(e) {}
                    
                    self.showError(errorMessage);
                    setTimeout(() => self.closeTerminalModal(), 2000);
                }).always(function() {
                    $('#cancel-terminal-payment').prop('disabled', false);
                });
            } else {
                self.closeTerminalModal();
            }

            // Clear intervals
            if (this.statusInterval) clearInterval(this.statusInterval);
            if (this.timerInterval) clearInterval(this.timerInterval);
            
            this.paymentInProgress = false;
        },

        /**
         * Close terminal modal
         */
        closeTerminalModal: function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('mollie-terminal-modal'));
            if (modal) {
                modal.hide();
            }
            
            // Clear intervals
            if (this.statusInterval) clearInterval(this.statusInterval);
            if (this.timerInterval) clearInterval(this.timerInterval);
            
            // Clear payment tracking
            this.paymentInProgress = false;
            this.currentOrderId = null;
            this.currentPaymentId = null;
            
            // Remove modal from DOM
            setTimeout(function() {
                $('#mollie-terminal-modal').remove();
            }, 300);
        },

        /**
         * Show order receipt
         */
        showOrderReceipt: function(orderId) {
            const $container = $('#pos-container');
            const receiptUrl = $container.data('receipt-url').replace(':id', orderId);
            
            // Clear cart and redirect to receipt
            window.location.href = receiptUrl;
        },

        /**
         * Collect address data from form
         */
        collectAddressData: function() {
            return {
                address: $('input[name="address"]').val() || null,
                city: $('input[name="city"]').val() || null,
                state: $('input[name="state"]').val() || null,
                country: $('input[name="country"]').val() || null,
                zip_code: $('input[name="zip_code"]').val() || null
            };
        },

        /**
         * Extend existing checkout process
         */
        extendCheckoutProcess: function() {
            // Override payment method display in checkout modal
            $(document).on('DOMSubtreeModified', '#checkout-modal', function() {
                const paymentMethod = $('input[name="payment_method"]:checked').val();
                if (paymentMethod === 'mollie_terminal') {
                    $('#checkout-payment-method-info').html('<i class="ti ti-device-mobile me-1 text-success"></i>Mollie Terminal');
                    $('#checkout-payment-method').val('mollie_terminal');
                }
            });
        },

        /**
         * Show success message
         */
        showSuccess: function(message) {
            if (window.Botble && window.Botble.showSuccess) {
                window.Botble.showSuccess(message);
            } else {
                console.log('Success: ' + message);
            }
        },

        /**
         * Show warning message
         */
        showWarning: function(message) {
            if (window.Botble && window.Botble.showWarning) {
                window.Botble.showWarning(message);
            } else if (window.Botble && window.Botble.showInfo) {
                window.Botble.showInfo(message);
            } else {
                console.warn('Warning: ' + message);
            }
        },

        /**
         * Show error message
         */
        showError: function(message) {
            if (window.Botble && window.Botble.showError) {
                window.Botble.showError(message);
            } else {
                console.error('Error: ' + message);
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Only initialize if we're on the POS page
        if ($('#pos-container').length) {
            window.MolliePosIntegration.init();
        }
    });

})(jQuery);