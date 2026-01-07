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
                                    <x-core::icon name="ti ti-cash" class="me-1 text-success" />
                                    {{ trans('plugins/pos-pro::pos.cash') }}
                                </div>
                                <!-- Hidden input to store payment method -->
                                <input type="hidden" id="checkout-payment-method" name="payment_method" value="cash">
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
                <!-- Single button that changes based on payment method -->
                <x-core::button color="primary" id="checkout-action-btn" class="checkout-action-btn">
                    <x-core::icon name="ti ti-check" class="me-1" id="checkout-action-icon" />
                    <span id="checkout-action-text">{{ trans('plugins/pos-pro::pos.complete_order') }}</span>
                </x-core::button>
            </div>
        </div>
        
        <script>
        $(document).ready(function() {
            console.log('🔧 Mollie Checkout Modal Extended: Initializing...');
            
            // FORCE TEST: Always change to Pay Now for debugging
            setTimeout(function() {
                console.log('🔧 FORCE TEST: Changing button to Pay Now');
                const $button = $('#checkout-action-btn');
                const $icon = $('#checkout-action-icon');
                const $text = $('#checkout-action-text');
                
                $button.removeClass('btn-primary').addClass('btn-success');
                $icon.attr('class', 'icon me-1 ti ti-device-mobile');
                $text.text('Pay Now - FORCE TEST');
                $button.attr('data-payment-method', 'mollie_terminal');
                console.log('🔧 Force test completed');
            }, 2000);
            
            // Function to update button based on payment method
            function updateCheckoutButton(paymentMethod) {
                console.log('🔧 Updating button for payment method:', paymentMethod);
                
                const $button = $('#checkout-action-btn');
                const $icon = $('#checkout-action-icon');
                const $text = $('#checkout-action-text');
                
                console.log('🔧 Button element found:', $button.length);
                console.log('🔧 Icon element found:', $icon.length);
                console.log('🔧 Text element found:', $text.length);
                
                if (paymentMethod === 'mollie_terminal') {
                    // Change to Pay Now button
                    $button.removeClass('btn-primary').addClass('btn-success');
                    $icon.attr('class', 'icon me-1 ti ti-device-mobile');
                    $text.text('Pay Now');
                    $button.attr('data-payment-method', 'mollie_terminal');
                    console.log('🔧 Button changed to Pay Now (Mollie Terminal)');
                } else {
                    // Change to Complete Order button
                    $button.removeClass('btn-success').addClass('btn-primary');
                    $icon.attr('class', 'icon me-1 ti ti-check');
                    $text.text('{{ trans("plugins/pos-pro::pos.complete_order") }}');
                    $button.attr('data-payment-method', paymentMethod || 'cash');
                    console.log('🔧 Button changed to Complete Order (' + paymentMethod + ')');
                }
                
                // Verify changes
                setTimeout(function() {
                    console.log('🔧 After update - Button classes:', $button.attr('class'));
                    console.log('🔧 After update - Icon classes:', $icon.attr('class'));
                    console.log('🔧 After update - Button text:', $text.text());
                }, 100);
            }
            
            // Check payment method when modal opens
            $('#checkout-modal').on('shown.bs.modal', function() {
                console.log('🔧 Checkout modal opened');
                const paymentMethod = $('input[name="payment_method"]:checked').val();
                console.log('🔧 Current payment method:', paymentMethod);
                updateCheckoutButton(paymentMethod);
                
                // Update payment method display in modal
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
            });
            
            // Handle button click
            $(document).on('click', '#checkout-action-btn', function(e) {
                e.preventDefault();
                
                const paymentMethod = $(this).attr('data-payment-method');
                console.log('🔧 Checkout button clicked for payment method:', paymentMethod);
                
                if (paymentMethod === 'mollie_terminal') {
                    // Handle Pay Now for Mollie Terminal
                    console.log('🔧 Processing Mollie Terminal payment...');
                    alert('Pay Now clicked! Processing Mollie Terminal payment...');
                    
                    // TODO: Integrate with actual Mollie Terminal payment processing
                    $('#checkout-modal').modal('hide');
                } else {
                    // Handle regular Complete Order
                    console.log('🔧 Processing regular order completion...');
                    
                    // Trigger the original POS Pro checkout process
                    const $form = $('#checkout-form');
                    const $button = $(this);
                    
                    // Use existing POS Pro checkout logic
                    if (typeof window.Botble !== 'undefined' && window.Botble.showButtonLoading) {
                        window.Botble.showButtonLoading($button[0]);
                    }
                    
                    // Find the checkout URL from the POS container
                    const checkoutUrl = $('#pos-container').data('checkout-url') || '/admin/pos/checkout';
                    
                    $.ajax({
                        url: checkoutUrl,
                        method: 'POST',
                        data: $form.serializeArray(),
                        success: function(response) {
                            if (response.error) {
                                if (typeof window.Botble !== 'undefined' && window.Botble.showError) {
                                    window.Botble.showError(response.message);
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            } else {
                                $('#checkout-modal').modal('hide');
                                if (typeof window.Botble !== 'undefined' && window.Botble.showSuccess) {
                                    window.Botble.showSuccess(response.message);
                                }
                                
                                // Handle order completion success
                                if (response.data.order_id || response.data.order) {
                                    const orderId = response.data.order_id || response.data.order.id;
                                    const orderCode = response.data.order_code || response.data.order.code;
                                    
                                    $('#order-number').text(orderCode);
                                    $('#print-receipt-btn').data('order-ids', orderId);
                                    $('#success-modal').modal('show');
                                }
                            }
                        },
                        error: function(xhr) {
                            if (typeof window.Botble !== 'undefined' && window.Botble.handleError) {
                                window.Botble.handleError(xhr);
                            } else {
                                alert('Error occurred while processing the order');
                            }
                        },
                        complete: function() {
                            if (typeof window.Botble !== 'undefined' && window.Botble.hideButtonLoading) {
                                window.Botble.hideButtonLoading($button[0]);
                            }
                        }
                    });
                }
            });
            
            // Listen for payment method changes in the cart
            $(document).on('change', 'input[name="payment_method"]', function() {
                const paymentMethod = $(this).val();
                console.log('🔧 Payment method changed to:', paymentMethod);
                
                if ($('#checkout-modal').hasClass('show')) {
                    updateCheckoutButton(paymentMethod);
                }
            });
        });
        </script>
    </x-slot:footer>
</x-core::modal>