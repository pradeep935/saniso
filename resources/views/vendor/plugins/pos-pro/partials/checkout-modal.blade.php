<x-core::modal
    id="checkout-modal"
    :title="trans('plugins/pos-pro::pos.checkout')"
    size="xl"
    class="checkout-modal-responsive"
>
    <form id="checkout-form">
        <div class="row g-3">
            <!-- Order Information - Will stack on mobile -->
            <div class="col-lg-8 col-md-12">
                <x-core::card>
                    <x-core::card.header class="bg-light-subtle">
                        <x-core::card.title>
                            <div class="d-flex align-items-center">
                                <x-core::icon name="ti ti-clipboard-list" class="me-2 text-primary" />
                                {{ trans('plugins/pos-pro::pos.order_information') }}
                            </div>
                        </x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <x-core::datagrid>
                            <!-- Customer Information -->
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    <div class="d-flex align-items-center">
                                        <x-core::icon name="ti ti-user" class="me-1 text-primary" />
                                        {{ trans('plugins/pos-pro::pos.customer') }}
                                    </div>
                                </x-slot:title>
                                <div id="checkout-customer-info" class="d-flex align-items-center">
                                    <x-core::icon name="ti ti-user-circle" class="me-1 text-muted" />
                                    {{ trans('plugins/pos-pro::pos.guest') }}
                                </div>
                                <!-- Hidden input to store customer ID -->
                                <input type="hidden" id="checkout-customer-id" name="customer_id" value="">
                            </x-core::datagrid.item>

                            <!-- Customer Address Information -->
                            <x-core::datagrid.item id="customer-address-section">
                                <x-slot:title>
                                    <div class="d-flex align-items-center w-100">
                                        <x-core::icon name="ti ti-map-pin" class="me-1 text-primary" />
                                        <span class="text-nowrap">{{ trans('plugins/pos-pro::pos.address') }}</span>
                                    </div>
                                </x-slot:title>
                                <div id="address-form-container">
                                    <!-- Address form will be loaded dynamically -->
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">{{ trans('plugins/pos-pro::pos.loading') }}...</span>
                                        </div>
                                        <div class="mt-2 text-muted">{{ trans('plugins/pos-pro::pos.loading_address_form') }}</div>
                                    </div>
                                </div>
                            </x-core::datagrid.item>

                            <!-- Shipping Option -->
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    <div class="d-flex align-items-center">
                                        <x-core::icon name="ti ti-truck" class="me-1 text-primary" />
                                        {{ trans('plugins/pos-pro::pos.delivery_option') }}
                                    </div>
                                </x-slot:title>
                                <div class="w-100">
                                    <div class="row g-2">
                                        <div class="col-md-6 col-12">
                                            <label class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="delivery_option" value="pickup" checked>
                                                <span class="form-check-label">
                                                    <x-core::icon name="ti ti-building-store" class="me-1 text-success" />
                                                    {{ trans('plugins/pos-pro::pos.pickup_in_store') }}
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <label class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="delivery_option" value="shipping">
                                                <span class="form-check-label">
                                                    <x-core::icon name="ti ti-truck-delivery" class="me-1 text-info" />
                                                    {{ trans('plugins/pos-pro::pos.ship_to_address') }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted" id="delivery-option-help">
                                            {{ trans('plugins/pos-pro::pos.pickup_in_store_help') }}
                                        </small>
                                    </div>
                                </div>
                            </x-core::datagrid.item>

                            <!-- Payment Method Information -->
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    <div class="d-flex align-items-center">
                                        <x-core::icon name="ti ti-credit-card" class="me-1 text-primary" />
                                        {{ trans('plugins/pos-pro::pos.payment_method') }}
                                    </div>
                                </x-slot:title>
                                <div id="checkout-payment-method-info" class="d-flex align-items-center">
                                    <x-core::icon name="ti ti-device-mobile" class="me-1 text-success" />
                                    Mollie Terminal
                                </div>
                                <!-- Hidden input to store payment method -->
                                <input type="hidden" id="checkout-payment-method" name="payment_method" value="mollie_terminal">
                            </x-core::datagrid.item>

                            <!-- Notes -->
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    <div class="d-flex align-items-center">
                                        <x-core::icon name="ti ti-notes" class="me-1 text-primary" />
                                        {{ trans('plugins/pos-pro::pos.notes') }}
                                    </div>
                                </x-slot:title>
                                <div class="w-100">
                                    <textarea class="form-control" name="notes" rows="4" style="min-height: 100px;" placeholder="{{ trans('plugins/pos-pro::pos.notes_placeholder') }}"></textarea>
                                </div>
                            </x-core::datagrid.item>
                        </x-core::datagrid>
                    </x-core::card.body>
                </x-core::card>
            </div>

            <!-- Order Summary - Will stack on mobile -->
            <div class="col-lg-4 col-md-12">
                <x-core::card class="shadow-sm order-summary-card">
                    <x-core::card.header class="bg-primary-subtle">
                        <x-core::card.title>
                            <div class="d-flex align-items-center">
                                <x-core::icon name="ti ti-receipt" class="me-2 text-primary" />
                                {{ trans('plugins/pos-pro::pos.order_summary') }}
                            </div>
                        </x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <x-core::datagrid>
                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/pos-pro::pos.subtotal') }}</x-slot:title>
                                <div id="modal-subtotal">{{ format_price($cart['subtotal']) }}</div>
                            </x-core::datagrid.item>

                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/pos-pro::pos.tax') }}</x-slot:title>
                                <div id="modal-tax">{{ format_price($cart['tax']) }}</div>
                            </x-core::datagrid.item>

                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/pos-pro::pos.shipping') }}</x-slot:title>
                                <div id="modal-shipping">{{ format_price($cart['shipping_amount'] ?? 0) }}</div>
                            </x-core::datagrid.item>

                            @if(isset($cart['coupon_discount']) && $cart['coupon_discount'] > 0)
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    <div class="d-flex align-items-center">
                                        <x-core::icon name="ti ti-discount-check" class="me-1 text-primary" />
                                        {{ trans('plugins/pos-pro::pos.coupon_discount') }}
                                        @if(isset($cart['coupon_code']))
                                            <x-core::badge color="primary" lite class="ms-1">{{ $cart['coupon_code'] }}</x-core::badge>
                                        @endif
                                    </div>
                                </x-slot:title>
                                <div class="text-danger">-{{ format_price($cart['coupon_discount']) }}</div>
                            </x-core::datagrid.item>
                            @endif

                            {{-- Manual discount is dynamically added by JavaScript --}}

                            <x-core::datagrid.item>
                                <x-slot:title>
                                    <div class="fw-bold">{{ trans('plugins/pos-pro::pos.total') }}</div>
                                </x-slot:title>
                                <div class="fw-bold fs-4 text-primary" id="modal-total">{{ format_price($cart['total']) }}</div>
                            </x-core::datagrid.item>
                        </x-core::datagrid>
                    </x-core::card.body>
                </x-core::card>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <div class="d-flex flex-column flex-sm-row justify-content-between w-100 gap-2">
            <x-core::button data-bs-dismiss="modal" class="btn-link link-secondary order-2 order-sm-1">
                <x-core::icon name="ti ti-x" class="me-1" />
                {{ trans('plugins/pos-pro::pos.cancel') }}
            </x-core::button>
            
            <!-- Dynamic button based on payment method -->
            <div id="checkout-action-buttons" class="order-1 order-sm-2">
                <!-- Default Complete Order button (hidden initially) -->
                <x-core::button color="primary" id="complete-order-btn" class="complete-order-btn" style="display: none;">
                    <x-core::icon name="ti ti-check" class="me-1" />
                    {{ trans('plugins/pos-pro::pos.complete_order') }}
                </x-core::button>
                
                <!-- Pay Now button for Mollie Terminal (visible by default) -->
                <x-core::button color="success" id="pay-now-btn" class="pay-now-btn">
                    <x-core::icon name="ti ti-device-mobile" class="me-1" />
                    Pay Now with Terminal
                </x-core::button>
            </div>
        </div>
        
        <script>
        // Load centralized payment timer
        if (!window.PaymentTimer) {
            console.log('📥 Loading centralized PaymentTimer script...');
            $.getScript('/vendor/core/plugins/mollie/js/payment-timer.js')
                .done(function() {
                    console.log('✅ PaymentTimer loaded successfully');
                })
                .fail(function() {
                    console.warn('⚠️ Failed to load PaymentTimer, using fallback timers');
                });
        }
        
        $(document).ready(function() {
            console.log('✅ MOLLIE CHECKOUT: Starting...');
            
            // Show the correct button based on payment method
            function showCorrectButton(paymentMethod) {
                console.log('✅ Showing button for:', paymentMethod);
                
                if (paymentMethod === 'mollie_terminal') {
                    $('#complete-order-btn').hide();
                    $('#pay-now-btn').show();
                    console.log('✅ PAY NOW BUTTON SHOWN!');
                } else {
                    $('#pay-now-btn').hide();
                    $('#complete-order-btn').show();
                    console.log('✅ Complete order button shown');
                }
            }
            
            // Check payment method when modal opens
            $('#checkout-modal').on('shown.bs.modal', function() {
                console.log('✅ Checkout modal opened');
                const paymentMethod = $('input[name="payment_method"]:checked').val() || 'mollie_terminal';
                console.log('✅ Payment method is:', paymentMethod);
                
                showCorrectButton(paymentMethod);
                
                // Update payment method info
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
                    // Fallback to mollie_terminal if cash or unknown
                    $paymentInfo.html('<i class="ti ti-device-mobile me-1 text-success"></i>Mollie Terminal');
                    $paymentInput.val('mollie_terminal');
                    paymentMethod = 'mollie_terminal';
                    showCorrectButton('mollie_terminal');
                }
            });
            
            // Start terminal payment process
            function startTerminalPayment(orderData) {
                console.log('🔄 Starting terminal payment for order:', orderData.order_id);
                
                // Show terminal payment modal
                showTerminalPaymentModal(orderData);
                
                // Send payment to terminal
                $.ajax({
                    url: '/admin/mollie/pos/process-payment',
                    method: 'POST',
                    data: {
                        order_id: orderData.order_id,
                        payment_type: 'card',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.error) {
                            console.error('❌ Terminal payment failed:', response.message);
                            hideTerminalPaymentModal();
                            alert('Terminal payment failed: ' + response.message);
                        } else {
                            console.log('✅ Payment sent to terminal:', response.data);
                            // Start monitoring payment status
                            startPaymentMonitoring(response.data.payment_id, orderData);
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Terminal payment AJAX error:', xhr);
                        hideTerminalPaymentModal();
                        alert('Failed to send payment to terminal. Please try again.');
                    }
                });
            }
            
            // Show terminal payment modal with 5-minute timer
            function showTerminalPaymentModal(orderData) {
                console.log('📺 Showing payment modal with data:', orderData);
                
                // Extract order details with fallbacks
                const order = orderData.order || orderData;
                const rawAmount = order.amount || orderData.amount || '0.00';
                const amount = parseFloat(rawAmount).toFixed(2); // Format to 2 decimal places
                const orderCode = order.code || orderData.code || orderData.order_code || `#SF-${order.id || 'Unknown'}`;
                
                console.log('💰 Display values - Code:', orderCode, 'Amount:', amount);
                
                const modalHtml = `
                    <div class="modal fade" id="terminal-payment-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="ti ti-device-mobile me-2"></i>
                                        Mollie Terminal Payment
                                    </h5>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <div class="mb-4">
                                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                                        <h4 class="text-primary mb-2">Processing Payment</h4>
                                        <p class="text-muted mb-0">Order: <strong>${orderCode}</strong></p>
                                        <p class="text-muted mb-0">Amount: <strong>€${amount}</strong></p>
                                    </div>
                                    
                                    <div class="alert alert-info mb-4">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Please complete the payment on the terminal device
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="text-muted small mb-2">Time remaining:</div>
                                        <div class="fs-4 fw-bold text-primary" id="payment-timer">02:00</div>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar" id="payment-progress" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    
                                    <div id="payment-status" class="text-muted">
                                        <i class="ti ti-clock me-1"></i>
                                        Waiting for terminal response...
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger" onclick="showCancelConfirmation()">
                                        <i class="ti ti-x me-1"></i>Cancel Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                $('#terminal-payment-modal').remove();
                $('body').append(modalHtml);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('terminal-payment-modal'));
                modal.show();
                
                // Start 5-minute countdown timer
                startPaymentTimer();
                
                // Start monitoring payment status
                if (window.currentPaymentId) {
                    startPaymentMonitoring(window.currentPaymentId, orderData);
                }
            }
            
            // Hide terminal payment modal
            function hideTerminalPaymentModal() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('terminal-payment-modal'));
                if (modal) {
                    modal.hide();
                }
                $('#terminal-payment-modal').remove();
                stopPaymentTimer();
            }
            
            // Payment timer variables - USING CENTRALIZED TIMER NOW
            let paymentTimerInterval = null; // Keep for cleanup compatibility
            let paymentTimeLeft = 120; // Keep for display compatibility
            
            // Start payment countdown timer using centralized system
            function startPaymentTimer() {
                console.log('🕐 Checkout Modal: Starting centralized payment timer');
                
                // Use centralized timer if available
                if (window.PaymentTimer) {
                    console.log('✅ Using centralized PaymentTimer in checkout modal');
                    window.PaymentTimer.start({
                        onTick: function(timeLeft, formattedTime) {
                            // Update local variable for compatibility
                            paymentTimeLeft = timeLeft;
                            
                            // Update display
                            $('#payment-timer').text(formattedTime);
                            
                            // Update progress bar
                            const progress = window.PaymentTimer.getProgress();
                            $('#payment-progress').css('width', progress + '%');
                            
                            // Change color as time runs out
                            if (timeLeft < 60) {
                                $('#payment-timer').removeClass('text-primary').addClass('text-danger');
                                $('#payment-progress').removeClass('bg-primary').addClass('bg-danger');
                            } else if (timeLeft < 120) {
                                $('#payment-timer').removeClass('text-primary').addClass('text-warning');
                                $('#payment-progress').removeClass('bg-primary').addClass('bg-warning');
                            }
                        },
                        onTimeout: function() {
                            console.log('⏰ Centralized timer expired - auto-cancelling payment');
                            autoTimeoutPayment();
                        }
                    });
                } else {
                    console.log('⚠️ PaymentTimer not available, using fallback timer');
                    // Fallback to original timer logic
                    paymentTimeLeft = 120; // Reset to 2 minutes
                    
                    paymentTimerInterval = setInterval(function() {
                        paymentTimeLeft--;
                        
                        const minutes = Math.floor(paymentTimeLeft / 60);
                        const seconds = paymentTimeLeft % 60;
                        const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                        
                        $('#payment-timer').text(timeString);
                        
                        // Update progress bar
                        const progress = (paymentTimeLeft / 120) * 100;
                        $('#payment-progress').css('width', progress + '%');
                        
                        // Change color as time runs out
                        if (paymentTimeLeft < 60) {
                            $('#payment-timer').removeClass('text-primary').addClass('text-danger');
                            $('#payment-progress').removeClass('bg-primary').addClass('bg-danger');
                        } else if (paymentTimeLeft < 120) {
                            $('#payment-timer').removeClass('text-primary').addClass('text-warning');
                            $('#payment-progress').removeClass('bg-primary').addClass('bg-warning');
                        }
                        
                        // Auto-cancel when timer expires
                        if (paymentTimeLeft <= 0) {
                            console.log('⏰ Fallback timer expired - auto-cancelling');
                            autoTimeoutPayment();
                        }
                    }, 1000);
                }
            }
            
            // Stop payment timer
            function stopPaymentTimer() {
                // Stop centralized timer
                if (window.PaymentTimer && window.PaymentTimer.isActive()) {
                    window.PaymentTimer.stop();
                }
                
                // Stop fallback timer
                if (paymentTimerInterval) {
                    clearInterval(paymentTimerInterval);
                    paymentTimerInterval = null;
                }
            }
            
            // Start payment monitoring
            function startPaymentMonitoring(paymentId, orderData) {
                console.log('🔄 Starting payment monitoring for:', paymentId, orderData);
                
                // Store the interval globally so we can clear it from other functions
                window.paymentStatusInterval = setInterval(function() {
                    console.log('🔍 Checking payment status...', paymentId);
                    
                    // First check order status (faster via webhook)
                    if (orderData.order && orderData.order.id) {
                        $.ajax({
                            url: `/admin/pos/order-status/${orderData.order.id}`,
                            method: 'GET',
                            success: function(orderResponse) {
                                console.log('📊 Order status response:', orderResponse);
                                
                                if (orderResponse.success && orderResponse.data) {
                                    const orderStatus = orderResponse.data.status;
                                    console.log('📋 Current order status:', orderStatus);
                                    
                                    if (orderStatus === 'completed' || orderStatus === 'processing') {
                                        console.log('✅ Order completed! Redirecting to print page.');
                                        clearInterval(window.paymentStatusInterval);
                                        stopPaymentTimer();
                                        
                                        // Payment completed - go to print invoice
                                        handlePaymentCompleted(orderData.order.id);
                                        return;
                                    }
                                }
                                
                                // If order not completed, check Mollie payment status
                                $.ajax({
                                    url: '/admin/mollie/pos/payment-status',
                                    method: 'GET',
                                    data: { payment_id: paymentId },
                                    success: function(paymentResponse) {
                                        console.log('💳 Payment status response:', paymentResponse);
                                        
                                        if (paymentResponse.success && paymentResponse.data && paymentResponse.data.status) {
                                            const paymentStatus = paymentResponse.data.status;
                                            console.log('💰 Current payment status:', paymentStatus);
                                            
                                            if (paymentStatus === 'paid') {
                                                console.log('✅ Payment is paid! Redirecting to print page.');
                                                clearInterval(window.paymentStatusInterval);
                                                stopPaymentTimer();
                                                
                                                // Payment completed - go to print invoice
                                                handlePaymentCompleted(orderData.order.id);
                                            } else if (paymentStatus === 'failed' || paymentStatus === 'canceled' || paymentStatus === 'expired') {
                                                console.log('❌ Payment failed:', paymentStatus);
                                                clearInterval(window.paymentStatusInterval);
                                                stopPaymentTimer();
                                                
                                                // Payment failed - show failure and return to POS
                                                handlePaymentFailed(paymentStatus, orderData);
                                            }
                                        }
                                    },
                                    error: function(xhr) {
                                        console.error('❌ Payment status check error:', xhr);
                                    }
                                });
                            },
                            error: function(xhr) {
                                console.error('❌ Order status check error:', xhr);
                                
                                // Fallback to payment status only
                                $.ajax({
                                    url: '/admin/mollie/pos/payment-status',
                                    method: 'GET',
                                    data: { payment_id: paymentId },
                                    success: function(paymentResponse) {
                                        if (paymentResponse.success && paymentResponse.data && paymentResponse.data.status === 'paid') {
                                            clearInterval(window.paymentStatusInterval);
                                            stopPaymentTimer();
                                            handlePaymentCompleted(orderData.order.id);
                                        }
                                    }
                                });
                            }
                        });
                    } else {
                        // No order ID, just check payment status
                        $.ajax({
                            url: '/admin/mollie/pos/payment-status',
                            method: 'GET',
                            data: { payment_id: paymentId },
                            success: function(paymentResponse) {
                                if (paymentResponse.success && paymentResponse.data && paymentResponse.data.status === 'paid') {
                                    clearInterval(window.paymentStatusInterval);
                                    stopPaymentTimer();
                                    showPaymentSuccess(orderData);
                                }
                            }
                        });
                    }
                }, 2000); // Check every 2 seconds
                
                // Store interval for cleanup
                window.paymentStatusInterval = checkInterval;
            }
            
            // Show payment success
            function showPaymentSuccess(orderData) {
                // Use new payment completion handler
                const orderId = orderData.order ? orderData.order.id : null;
                handlePaymentCompleted(orderId, orderData);
            }
            
            // Show payment failure
            function showPaymentFailure(status, orderData) {
                // Use new payment failure handler
                handlePaymentFailed(status, orderData);
            }
            
            // Auto-timeout payment
            function autoTimeoutPayment() {
                console.log('⏰ Auto-timeout: Cancelling payment');
                
                if (window.paymentStatusInterval) {
                    clearInterval(window.paymentStatusInterval);
                }
                
                $('#payment-status').html('<i class="ti ti-clock-x me-1 text-danger"></i>Payment Timeout');
                $('#payment-timer').text('TIMEOUT').addClass('text-danger');
                $('#payment-progress').addClass('bg-danger');
                
                // Get order ID for cleanup
                const orderId = window.posOrderId || sessionStorage.getItem('pos_temp_order_id');
                
                // Cancel the Mollie payment on server
                if (window.currentPaymentId) {
                    $.ajax({
                        url: '/admin/mollie/pos/cancel-payment',
                        method: 'POST',
                        data: {
                            payment_id: window.currentPaymentId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            console.log('✅ Mollie payment cancelled due to timeout');
                        },
                        error: function(xhr) {
                            console.error('❌ Failed to cancel Mollie payment:', xhr);
                        }
                    });
                }
                
                // Cancel the order to prevent it remaining as completed
                if (orderId) {
                    cancelFailedOrder(orderId);
                }
                
                // Clear stored data
                window.posOrderId = null;
                window.currentPaymentId = null;
                sessionStorage.removeItem('pos_temp_order_id');
                
                setTimeout(function() {
                    hideTerminalPaymentModal();
                    alert('⏰ Payment Timeout\n\nThe payment has been automatically cancelled after 2 minutes.\nThe order has been cancelled.\n\nPlease try again.');
                    
                    // Reload cart to refresh state
                    if (typeof window.updateCartDisplay === 'function' && window.initialCart) {
                        window.updateCartDisplay(window.initialCart);
                    }
                }, 2000);
            }
            
            // Show cancellation confirmation inside modal
            window.showCancelConfirmation = function() {
                console.log('🤔 Showing cancel confirmation');
                
                // Update modal content to show confirmation
                const confirmationHtml = `
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <i class="ti ti-help-circle text-warning" style="font-size: 4rem;"></i>
                            <h4 class="text-warning mt-3 mb-2">Cancel Payment?</h4>
                            <p class="text-muted">Are you sure you want to cancel this payment?</p>
                            <p class="text-muted small">The payment will be cancelled and you'll return to the POS.</p>
                        </div>
                        
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="continuePaying()">
                                <i class="ti ti-arrow-left me-1"></i>Continue Paying
                            </button>
                            <button type="button" class="btn btn-danger" onclick="confirmCancelPayment()">
                                <i class="ti ti-x me-1"></i>Yes, Cancel Payment
                            </button>
                        </div>
                    </div>
                `;
                
                // Replace modal body content
                $('#terminal-payment-modal .modal-body').html(confirmationHtml);
                $('#terminal-payment-modal .modal-footer').hide();
            };
            
            // Continue with payment (go back to timer)
            window.continuePaying = function() {
                console.log('✅ User chose to continue paying');
                
                // Restore original modal content
                location.reload(); // Simple way to restore the payment modal
            };
            
            // Confirm payment cancellation
            window.confirmCancelPayment = function() {
                console.log('❌ User confirmed payment cancellation');
                
                // Update modal to show cancelling status
                const cancellingHtml = `
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <div class="spinner-border text-warning mb-3" style="width: 2rem; height: 2rem;"></div>
                            <h4 class="text-warning mb-2">Cancelling Payment...</h4>
                            <p class="text-muted">Please wait while we cancel the payment.</p>
                        </div>
                    </div>
                `;
                
                $('#terminal-payment-modal .modal-body').html(cancellingHtml);
                
                // Clear monitoring
                if (window.paymentStatusInterval) {
                    clearInterval(window.paymentStatusInterval);
                    window.paymentStatusInterval = null;
                }
                
                stopPaymentTimer();
                
                // Process the cancellation
                handlePaymentCancellation();
            };
            
            // Handle payment cancellation flow
            function handlePaymentCancellation() {
                console.log('🚫 Processing payment cancellation...');
                
                // Get order and payment IDs for cleanup
                const orderId = window.posOrderId || sessionStorage.getItem('pos_temp_order_id');
                
                // Cancel Mollie payment
                if (window.currentPaymentId) {
                    $.ajax({
                        url: '/admin/mollie/pos/cancel-payment',
                        method: 'POST',
                        data: {
                            payment_id: window.currentPaymentId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            console.log('✅ Mollie payment cancelled');
                        },
                        error: function(xhr) {
                            console.error('❌ Failed to cancel Mollie payment:', xhr);
                        }
                    });
                }
                
                // Cancel the order to prevent completed status
                if (orderId) {
                    cancelFailedOrder(orderId);
                }
                
                // Show success cancellation in modal
                const cancelledHtml = `
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <i class="ti ti-check-circle text-success" style="font-size: 4rem;"></i>
                            <h4 class="text-success mt-3 mb-2">Payment Cancelled</h4>
                            <p class="text-muted">The payment has been successfully cancelled.</p>
                            <p class="text-muted small">You can try again or use a different payment method.</p>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-primary" onclick="closeCancelledModal()">
                                <i class="ti ti-arrow-left me-1"></i>Return to POS
                            </button>
                        </div>
                    </div>
                `;
                
                $('#terminal-payment-modal .modal-body').html(cancelledHtml);
                
                // Auto-close after 3 seconds
                setTimeout(function() {
                    closeCancelledModal();
                }, 3000);
            }
            
            // Close cancelled modal and return to POS
            window.closeCancelledModal = function() {
                hideTerminalPaymentModal();
                
                // Clear stored data
                window.posOrderId = null;
                window.currentPaymentId = null;
                sessionStorage.removeItem('pos_temp_order_id');
                
                // Stay on POS for retry
                console.log('🏪 Returned to POS interface');
            };
            
            // Handle checkout - this is the main checkout button (Complete Order/Pay Now)
            $(document).on('click', '#complete-order-btn, #pay-now-btn', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const buttonId = $button.attr('id');
                const isTerminalPayment = buttonId === 'pay-now-btn';
                
                console.log(`✅ ${isTerminalPayment ? 'PAY NOW' : 'COMPLETE ORDER'} BUTTON CLICKED!`);
                
                // Prevent double-clicking
                if ($button.data('processing')) {
                    console.log('⚠️ Already processing, ignoring click');
                    return;
                }
                $button.data('processing', true);
                
                const $form = $('#checkout-form');
                
                if (typeof window.Botble !== 'undefined' && window.Botble.showButtonLoading) {
                    window.Botble.showButtonLoading($button[0]);
                }
                
                // Check if order already exists
                const existingOrderId = window.posOrderId || sessionStorage.getItem('pos_temp_order_id');
                
                if (existingOrderId && isTerminalPayment) {
                    console.log('🔄 Using existing order for terminal payment:', existingOrderId);
                    processTerminalPayment(existingOrderId, $button);
                    return;
                }
                
                // Create new order
                const checkoutUrl = $('#pos-container').data('checkout-url') || '/admin/pos/checkout';
                
                // Prepare form data
                const formData = $form.serializeArray();
                
                if (isTerminalPayment) {
                    formData.push({name: 'payment_method', value: 'mollie_terminal'});
                    formData.push({name: 'is_terminal_payment', value: '1'});
                    console.log('🆕 Creating order for terminal payment');
                } else {
                    console.log('🆕 Creating order for standard checkout');
                }
                
                $.ajax({
                    url: checkoutUrl,
                    method: 'POST',
                    data: formData,
                    success: function(orderResponse) {
                        if (orderResponse.error) {
                            alert(`Error creating order: ${orderResponse.message}`);
                            resetButton($button);
                            return;
                        }
                        
                        console.log('✅ Order created successfully:', orderResponse);
                        console.log('🔍 Order response data structure:', orderResponse.data);
                        console.log('🔍 Order object:', orderResponse.data?.order);
                        
                        const orderId = orderResponse.data?.order?.id || orderResponse.order?.id || orderResponse.id;
                        
                        if (!orderId) {
                            console.error('❌ Could not find order ID in response:', orderResponse);
                            alert('Error: Could not create order. Please try again.');
                            resetButton($button);
                            return;
                        }
                        
                        if (isTerminalPayment) {
                            // Store order ID and proceed with terminal payment
                            window.posOrderId = orderId;
                            sessionStorage.setItem('pos_temp_order_id', orderId);
                            
                            // Store order details for payment modal
                            window.currentOrderData = {
                                id: orderId,
                                code: orderResponse.data?.order?.code || orderResponse.data?.order_code || `#SF-${orderId}`,
                                amount: parseFloat(orderResponse.data?.order?.amount || orderResponse.data?.order?.total || orderResponse.data?.total || '0.00').toFixed(2)
                            };
                            
                            console.log('💾 Stored order data:', window.currentOrderData);
                            processTerminalPayment(orderId, $button);
                        } else {
                            // Standard checkout completed
                            $('#checkout-modal').modal('hide');
                            alert('Order completed successfully!');
                            resetButton($button);
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Order creation failed:', xhr);
                        let errorMessage = 'Error occurred while creating the order';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            // Keep default message
                        }
                        
                        alert(errorMessage);
                        resetButton($button);
                    }
                });
                
                function processTerminalPayment(orderId, $button) {
                    console.log('🔄 Starting terminal payment for order:', orderId);
                    
                    // First validate terminal status
                    $.ajax({
                        url: '/admin/mollie/pos/validate-config',
                        method: 'GET',
                        success: function(validationResponse) {
                            if (validationResponse.error || !validationResponse.data?.valid) {
                                alert(`Terminal Error: ${validationResponse.message || 'Terminal not available'}`);
                                cancelFailedOrder(orderId);
                                resetButton($button);
                                return;
                            }
                            
                            console.log('✅ Terminal validation passed, sending payment');
                            
                            // Send payment to terminal
                            $.ajax({
                                url: '/admin/mollie/pos/process-payment',
                                method: 'POST',
                                data: {
                                    order_id: orderId,
                                    payment_type: 'card',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(paymentResponse) {
                                    console.log('✅ Payment sent to terminal:', paymentResponse);
                                    
                                    if (paymentResponse.error) {
                                        alert(`Payment Error: ${paymentResponse.message}`);
                                        cancelFailedOrder(orderId);
                                        resetButton($button);
                                        return;
                                    }
                                    
                                    // Store payment ID for cancellation
                                    window.currentPaymentId = paymentResponse.data?.payment_id || paymentResponse.payment_id;
                                    console.log('💾 Stored payment ID for cancellation:', window.currentPaymentId);
                                    
                                    // Prepare order data with proper details for monitoring and display
                                    const orderData = {
                                        order: {
                                            id: orderId,
                                            code: window.currentOrderData?.code || `#SF-${orderId}`,
                                            amount: window.currentOrderData?.amount || '0.00'
                                        },
                                        payment_id: window.currentPaymentId,
                                        ...paymentResponse.data
                                    };
                                    
                                    console.log('📦 Final order data for modal:', orderData);
                                    
                                    // Clear stored order ID and show payment modal
                                    window.posOrderId = null;
                                    sessionStorage.removeItem('pos_temp_order_id');
                                    
                                    $('#checkout-modal').modal('hide');
                                    showTerminalPaymentModal(orderData);
                                    resetButton($button);
                                },
                                error: function(xhr, status, error) {
                                    console.error('❌ Terminal payment failed:', xhr);
                                    let errorMessage = 'Failed to send payment to terminal';
                                    
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        errorMessage = response.message || errorMessage;
                                    } catch (e) {
                                        errorMessage = `HTTP ${xhr.status}: ${error}`;
                                    }
                                    
                                    alert(`Payment Error: ${errorMessage}`);
                                    cancelFailedOrder(orderId);
                                    resetButton($button);
                                }
                            });
                        },
                        error: function(xhr) {
                            console.error('❌ Terminal validation failed:', xhr);
                            alert('Terminal validation failed. Please check terminal connection.');
                            cancelFailedOrder(orderId);
                            resetButton($button);
                        }
                    });
                }
                
                function cancelFailedOrder(orderId) {
                    console.log('🚫 Cancelling failed order:', orderId);
                    
                    // Cancel the Mollie payment if it exists
                    if (window.currentPaymentId) {
                        $.ajax({
                            url: '/admin/mollie/pos/cancel-payment',
                            method: 'POST',
                            data: {
                                payment_id: window.currentPaymentId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                console.log('✅ Mollie payment cancelled');
                            },
                            error: function(xhr) {
                                console.error('❌ Failed to cancel Mollie payment:', xhr);
                            }
                        });
                    }
                    
                    // Update order status to cancelled
                    $.ajax({
                        url: `/admin/pos/cancel-order/${orderId}`,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            notes: 'Terminal payment failed or cancelled'
                        },
                        success: function() {
                            console.log('✅ Order cancelled successfully');
                        },
                        error: function(xhr) {
                            console.error('❌ Failed to cancel order:', xhr);
                        }
                    });
                }
                
                function resetButton($button) {
                    $button.data('processing', false);
                    if (typeof window.Botble !== 'undefined' && window.Botble.hideButtonLoading) {
                        window.Botble.hideButtonLoading($button[0]);
                    }
                }
            });
            
            // Listen for payment method changes
            $(document).on('change', 'input[name="payment_method"]', function() {
                const paymentMethod = $(this).val();
                console.log('✅ Payment method changed to:', paymentMethod);
                
                if ($('#checkout-modal').hasClass('show')) {
                    showCorrectButton(paymentMethod);
                }
            });
            
            // Cleanup on page unload
            $(window).on('beforeunload', function() {
                if (window.paymentStatusInterval) {
                    clearInterval(window.paymentStatusInterval);
                }
                stopPaymentTimer();
            });
        });
        </script>
    </x-slot:footer>
</x-core::modal>