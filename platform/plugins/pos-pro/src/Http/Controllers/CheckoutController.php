<?php

namespace Botble\PosPro\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Enums\OrderHistoryActionEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\PosPro\Services\CartService;
use Botble\PosPro\Services\MarketplaceOrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends BaseController
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function checkout(Request $request, BaseHttpResponse $response)
    {
        try {
            $cart = $this->cartService->getCart();

            // Enhanced debugging for cart empty issue
            \Log::info('=== POS CHECKOUT DEBUG ===', [
                'cart_raw' => $cart,
                'cart_items_count' => isset($cart['items']) ? count($cart['items']) : 0,
                'session_id' => session()->getId(),
                'session_data' => session()->all(),
                'request_data' => $request->all()
            ]);

            if (empty($cart['items']) || (is_countable($cart['items']) && count($cart['items']) === 0)) {
                \Log::error('Cart is empty during checkout', [
                    'cart' => $cart,
                    'session_id' => session()->getId(),
                    'all_sessions' => session()->all()
                ]);
                
                return $response
                    ->setError()
                    ->setMessage('Cart is empty. Please add items to cart before checkout.')
                    ->setData([
                        'debug' => [
                            'cart_items_count' => isset($cart['items']) ? count($cart['items']) : 0,
                            'cart_keys' => array_keys($cart),
                            'session_id' => session()->getId()
                        ]
                    ])
                    ->toApiResponse();
            }

            // Check if marketplace plugin is active and cart has products from multiple vendors
            if (is_plugin_active('marketplace')) {
                $marketplaceService = app(MarketplaceOrderService::class);

                try {
                    $result = $marketplaceService->processCheckout($request);

                    $message = trans('plugins/pos-pro::pos.order_completed_successfully');

                    // Add message about discount reset if there was a manual discount
                    if (isset($cart['manual_discount']) && $cart['manual_discount'] > 0) {
                        $message .= ' ' . trans('plugins/pos-pro::pos.discount_reset_after_checkout');
                    }

                    $orders = $result['orders'];

                    // For compatibility, use first order as main order
                    $mainOrder = $orders->first();

                    // Return response with all orders
                    return $response
                        ->setData([
                            'order' => $mainOrder, // For backward compatibility
                            'order_code' => $mainOrder->code,
                            'order_id' => $mainOrder->id,
                            'order_ids' => $orders->pluck('id')->toArray(), // All order IDs for receipt
                            'orders' => $orders->map(fn ($order) => [
                                'id' => $order->id,
                                'code' => $order->code,
                                'store_id' => $order->store_id,
                                'store_name' => $order->store?->name ?: 'Main Store',
                                'amount' => $order->amount,
                            ]),
                            'message' => $message,
                        ])
                        ->setMessage($message)
                        ->toApiResponse();

                } catch (Exception $e) {
                    return $response
                        ->setError()
                        ->setMessage($e->getMessage())
                        ->toApiResponse();
                }
            }

            DB::beginTransaction();

            // Get customer information from the cart session
            $cart = $this->cartService->getCart();
            $customerId = $cart['customer_id'];
            $customerName = 'Guest';
            $customerPhone = 'N/A';
            $customerEmail = 'guest@example.com';

            $customer = null;
            if ($customerId) {
                /**
                 * @var Customer $customer
                 */
                $customer = Customer::query()->find($customerId);
                if ($customer) {
                    $customerName = $customer->name;
                    $customerPhone = $customer->phone ?: 'N/A';
                    $customerEmail = $customer->email ?: 'guest@example.com';
                }
            }

            // Check if this is a terminal payment
            $paymentMethod = $request->input('payment_method', 'cash');
            $isTerminalPayment = $request->input('is_terminal_payment', false) || 
                               $paymentMethod === 'mollie_terminal' || 
                               $request->has('terminal_payment') ||
                               $request->input('payment_type') === 'terminal';
            
            \Log::info('Payment method detection:', [
                'payment_method' => $paymentMethod,
                'is_terminal_payment' => $isTerminalPayment,
                'request_data' => $request->all()
            ]);
            
            // Map payment method properly for terminal payments
            if ($isTerminalPayment || $paymentMethod === 'mollie_terminal') {
                $mappedPaymentMethod = 'mollie'; // Always use Mollie for terminal payments
                $orderStatus = OrderStatusEnum::PENDING; // Keep pending until terminal payment completes
                $isFinished = false;
                \Log::info('Terminal payment detected - using Mollie payment method');
            } else {
                $mappedPaymentMethod = $this->mapPaymentMethod($paymentMethod);
                $orderStatus = OrderStatusEnum::COMPLETED;
                $isFinished = true;
                \Log::info('Standard payment detected', ['mapped_method' => $mappedPaymentMethod]);
            }
            
            /**
             * @var Order $order
             */
            $order = Order::query()->create([
                'user_id' => $customerId ?: 0,
                'amount' => $cart['total'],
                'sub_total' => $cart['subtotal'],
                'tax_amount' => $cart['tax'],
                'shipping_amount' => $cart['shipping_amount'] ?? 0,
                'discount_amount' => $cart['manual_discount'] ?? 0,
                'currency_id' => get_application_currency_id(),
                'payment_id' => null,
                'payment_method' => $mappedPaymentMethod,
                'status' => $orderStatus,
                'description' => $isTerminalPayment ? 'Awaiting terminal payment' : $request->input('notes'),
                'is_finished' => $isFinished,
                'shipping_method' => ShippingMethodEnum::DEFAULT,
                'shipping_option' => null,
            ]);

            \Log::info('Order created with payment method:', [
                'order_id' => $order->id,
                'order_code' => $order->code,
                'payment_method' => $mappedPaymentMethod,
                'original_payment_method' => $paymentMethod,
                'is_terminal_payment' => $isTerminalPayment,
                'status' => $orderStatus,
                'is_finished' => $isFinished
            ]);

            // Check delivery option
            $deliveryOption = $request->input('delivery_option', 'pickup');
            $isPickup = $deliveryOption === 'pickup';

            // Create order address only if shipping is required
            if (! $isPickup) {
                $addressData = [
                    'order_id' => $order->id,
                    'name' => $customerName,
                    'phone' => $customerPhone,
                    'email' => $customerEmail,
                    'type' => OrderAddressTypeEnum::SHIPPING,
                ];

                // Get address data from request (ecommerce format)
                $addressInput = $request->input('address', []);
                $addressId = $addressInput['address_id'] ?? null;

                if ($customer && $addressId && $addressId !== 'new') {
                    // Use selected customer address
                    $customerAddress = $customer->addresses()->find($addressId);
                    if ($customerAddress) {
                        $addressData['name'] = $customerAddress->name;
                        $addressData['phone'] = $customerAddress->phone;
                        $addressData['email'] = $customerAddress->email;
                        $addressData['country'] = $customerAddress->country;
                        $addressData['state'] = $customerAddress->state;
                        $addressData['city'] = $customerAddress->city;
                        $addressData['address'] = $customerAddress->address;
                        $addressData['zip_code'] = $customerAddress->zip_code;
                    }
                } else {
                    // Use custom address from request or default values
                    $addressData['name'] = $addressInput['name'] ?? $customerName;
                    $addressData['phone'] = $addressInput['phone'] ?? $customerPhone;
                    $addressData['email'] = $addressInput['email'] ?? $customerEmail;
                    $addressData['country'] = $addressInput['country'] ?? '';
                    $addressData['state'] = $addressInput['state'] ?? '';
                    $addressData['city'] = $addressInput['city'] ?? '';
                    $addressData['address'] = $addressInput['address'] ?? '';
                    $addressData['zip_code'] = $addressInput['zip_code'] ?? '';
                }

                // Create shipping address
                OrderAddress::query()->create($addressData);

                // Create billing address (same as shipping)
                $addressData['type'] = OrderAddressTypeEnum::BILLING;
                OrderAddress::query()->create($addressData);
            } else {
                // For pickup orders, create minimal address with store information
                $storeAddress = [
                    'order_id' => $order->id,
                    'name' => $customerName,
                    'phone' => $customerPhone,
                    'email' => $customerEmail,
                    'type' => OrderAddressTypeEnum::SHIPPING,
                    'address' => trans('plugins/pos-pro::pos.pickup_at_store'),
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'zip_code' => '',
                ];

                // Create shipping address (pickup location)
                OrderAddress::query()->create($storeAddress);

                // Create billing address (same as shipping)
                $storeAddress['type'] = OrderAddressTypeEnum::BILLING;
                OrderAddress::query()->create($storeAddress);
            }

            // Create order products
            foreach ($cart['items'] as $item) {
                $product = Product::query()->find($item['id']);

                // Get the actual tax rate for this product
                $taxRate = $product->total_taxes_percentage;

                // Prepare options array with product attributes and other data in the exact format required
                $options = [
                    'image' => $product->image ?? '',
                    'attributes' => '',
                    'taxRate' => $taxRate,
                    'taxClasses' => $taxRate > 0 ? ['VAT' => $taxRate] : [],
                    'options' => [],
                    'extras' => [],
                    'sku' => $product->sku ?? '',
                    'weight' => $product->weight ?? 0,
                ];

                // Add attributes from cart item if available
                if (! empty($item['attributes'])) {
                    $attributeLabels = [];

                    foreach ($item['attributes'] as $attributeItem) {
                        if (isset($attributeItem['set']) && isset($attributeItem['value'])) {
                            $attributeLabels[] = $attributeItem['set'] . ': ' . $attributeItem['value'];
                        }
                    }

                    if (! empty($attributeLabels)) {
                        // Format attributes exactly as required: "(Color: Black, Size: M)"
                        $options['attributes'] = '(' . implode(', ', $attributeLabels) . ')';
                    }
                }

                // Calculate tax amount using the actual product tax rate
                $taxAmount = $item['price'] * $item['quantity'] * ($taxRate / 100);

                OrderProduct::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'qty' => $item['quantity'],
                    'price' => $item['price'],
                    'tax_amount' => $taxAmount,
                    'options' => $options,
                ]);

                // Update product stock
                if ($product->with_storehouse_management) {
                    $product->quantity -= $item['quantity'];
                    $product->save();
                }
            }

            // Create order history
            OrderHistory::query()->create([
                'action' => 'create_order',
                'description' => trans('plugins/pos-pro::pos.order_created_by_pos'),
                'order_id' => $order->id,
                'user_id' => auth()->id(),
            ]);

            // Create payment
            if (is_plugin_active('payment')) {
                $paymentMethod = $this->mapPaymentMethod($request->input('payment_method', 'cash'));
                $paymentStatus = PaymentStatusEnum::PENDING;

                // Get payment fee directly from payment settings
                $paymentFee = (float) get_payment_setting('fee', $paymentMethod, 0);

                $paymentData = [
                    'amount' => $order->amount,
                    'payment_fee' => $paymentFee,
                    'currency' => get_application_currency()->title,
                    'payment_channel' => $paymentMethod,
                    'status' => $paymentStatus,
                    'payment_type' => 'confirm',
                    'order_id' => $order->id,
                    'charge_id' => Str::upper(Str::random(10)),
                    'user_id' => auth()->id() ?: 0,
                ];

                // Add customer information if available
                $customerId = $request->input('customer_id');
                if ($customerId) {
                    $paymentData['customer_id'] = $customerId;
                    $paymentData['customer_type'] = Customer::class;
                }

                $payment = Payment::query()->create($paymentData);

                $order->payment_id = $payment->id;
                $order->save();
            }

            OrderHelper::confirmOrder($order);

            // Confirm payment if payment is created
            if (is_plugin_active('payment') && isset($payment)) {
                OrderHelper::confirmPayment($order);
            }

            // Handle shipment based on delivery option
            if (! is_plugin_active('marketplace')) {
                if (! $isPickup) {
                    // Create shipment only for shipping orders
                    // Calculate the total weight of products in the order
                    $weight = 0;
                    foreach ($order->products as $orderProduct) {
                        $product = Product::query()->find($orderProduct->product_id);
                        if ($product) {
                            $weight += $product->weight * $orderProduct->qty;
                        }
                    }

                    // Get the default store if needed
                    $storeId = null;
                    if (function_exists('get_primary_store_locator')) {
                        $store = get_primary_store_locator();
                        $storeId = $store->id;
                    }

                    try {
                        $shipment = Shipment::query()->create([
                            'order_id' => $order->id,
                            'user_id' => auth()->id() ?: 0,
                            'weight' => $weight,
                            'cod_amount' => (is_plugin_active('payment') && $order->payment && $order->payment->status != PaymentStatusEnum::COMPLETED) ? $order->amount : 0,
                            'cod_status' => ShippingCodStatusEnum::PENDING,
                            'type' => ShippingMethodEnum::DEFAULT,
                            'status' => ShippingStatusEnum::PENDING,
                            'price' => $order->shipping_amount,
                            'store_id' => $storeId,
                        ]);

                        if ($shipment) {
                            // Create shipment history
                            ShipmentHistory::query()->create([
                                'action' => 'create_from_pos',
                                'description' => trans('plugins/ecommerce::order.shipping_was_created_from_pos', ['order_id' => $order->code]),
                                'shipment_id' => $shipment->id,
                                'order_id' => $order->id,
                                'user_id' => auth()->id() ?: 0,
                            ]);

                            // Add order history for shipment creation
                            OrderHistory::query()->create([
                                'action' => 'create_shipment',
                                'description' => trans('plugins/ecommerce::order.shipping_was_created_from', [
                                    'order_id' => $order->code,
                                ]),
                                'order_id' => $order->id,
                                'user_id' => auth()->id() ?: 0,
                            ]);
                        }
                    } catch (Exception $exception) {
                        BaseHelper::logError($exception);
                    }
                } else {
                    // For pickup orders, mark as completed immediately
                    $order->status = OrderStatusEnum::COMPLETED;
                    $order->save();

                    // Add order history for pickup completion
                    OrderHistory::query()->create([
                        'action' => OrderHistoryActionEnum::MARK_ORDER_AS_COMPLETED,
                        'description' => trans('plugins/pos-pro::pos.order_completed_pickup'),
                        'order_id' => $order->id,
                        'user_id' => auth()->id() ?: 0,
                    ]);
                }
            }

            event(new OrderCreated($order));

            if ($isPickup) {
                OrderHelper::setOrderCompleted($order->id, $request, auth()->id() ?: 0);
            }

            DB::commit();

            // Check if there was a manual discount before clearing the cart
            $hadManualDiscount = isset($cart['manual_discount']) && $cart['manual_discount'] > 0;

            // Clear cart
            $this->cartService->clearCart();

            // Reset customer and payment method
            $this->cartService->resetCustomerAndPayment();

            $message = trans('plugins/pos-pro::pos.order_completed_successfully');

            // Add message about discount reset if there was a manual discount
            if ($hadManualDiscount) {
                $message .= ' ' . trans('plugins/pos-pro::pos.discount_reset_after_checkout');
            }

            return $response
                ->setData([
                    'order' => $order,
                    'order_code' => $order->code,
                    'order_id' => $order->id,
                    'amount' => $order->amount,
                    'formatted_amount' => '€' . number_format((float)$order->amount, 2),
                    'message' => $message,
                ])
                ->setMessage($message)
                ->toApiResponse();

        } catch (Exception $e) {
            DB::rollBack();

            return $response
                ->setError()
                ->setMessage($e->getMessage())
                ->toApiResponse();
        }
    }

    public function receipt($orderIds)
    {
        $this->pageTitle(trans('plugins/pos-pro::pos.receipt'));

        // Handle both single order and multiple orders
        if (! is_array($orderIds)) {
            // Check if it's a comma-separated string of IDs
            if (str_contains($orderIds, ',')) {
                $orderIds = explode(',', $orderIds);
            } else {
                // Single order ID
                $orderIds = [$orderIds];
            }
        }

        $query = Order::query()
            ->whereIn('id', $orderIds)
            ->with(['products', 'user', 'address', 'payment']);

        // Only load store relationship if marketplace plugin is active
        if (is_plugin_active('marketplace')) {
            $query->with('store');
        }

        $orders = $query->get();

        // Check if no orders were found
        if ($orders->isEmpty()) {
            abort(404);
        }

        // If only one order, maintain backward compatibility
        if ($orders->count() === 1) {
            $order = $orders->first();

            return view('plugins/pos-pro::receipt', compact('order', 'orders'));
        }

        // Multiple orders from marketplace
        return view('plugins/pos-pro::receipt-multiple', compact('orders'));
    }

    /**
     * Cancel an order (for failed terminal payments)
     */
    public function cancelOrder($orderId, BaseHttpResponse $response)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            \Log::info('Cancelling order for failed terminal payment:', [
                'order_id' => $order->id,
                'order_code' => $order->code,
                'current_status' => $order->status,
                'payment_method' => $order->payment_method
            ]);
            
            // Update order status to cancelled
            $order->update([
                'status' => OrderStatusEnum::CANCELED,
                'description' => ($order->description ?? '') . ' - Cancelled due to failed terminal payment'
            ]);
            
            // Cancel payment if exists
            if ($order->payment) {
                $order->payment->update([
                    'status' => PaymentStatusEnum::FAILED
                ]);
            }
            
            // Create order history
            OrderHistory::query()->create([
                'action' => 'cancel_order',
                'description' => 'Order cancelled due to failed terminal payment',
                'order_id' => $order->id,
                'user_id' => auth()->id() ?: 0,
            ]);
            
            return $response
                ->setMessage('Order cancelled successfully')
                ->toApiResponse();
                
        } catch (Exception $e) {
            \Log::error('Failed to cancel order:', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            return $response
                ->setError()
                ->setMessage('Failed to cancel order: ' . $e->getMessage())
                ->toApiResponse();
        }
    }

    /**
     * Get order status for monitoring
     */
    public function getOrderStatus($orderId, BaseHttpResponse $response)
    {
        try {
            $order = Order::find($orderId);
            
            if (!$order) {
                return $response
                    ->setError()
                    ->setMessage('Order not found');
            }

            return $response
                ->setData([
                    'id' => $order->id,
                    'code' => $order->code,
                    'status' => $order->status,
                    'payment_method' => $order->payment->payment_channel ?? null,
                    'amount' => $order->amount,
                ])
                ->setMessage('Order status retrieved');

        } catch (\Exception $e) {
            \Log::error('Get order status error:', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return $response
                ->setError()
                ->setMessage('Failed to get order status: ' . $e->getMessage());
        }
    }

    /**
     * Map POS payment method to PaymentMethodEnum constant
     */
    protected function mapPaymentMethod(string $posMethod): string
    {
        return match ($posMethod) {
            'card' => POS_PRO_CARD_PAYMENT_METHOD_NAME,
            'other' => POS_PRO_OTHER_PAYMENT_METHOD_NAME,
            'mollie', 'mollie_terminal' => 'mollie', // Handle both regular and terminal Mollie payments
            default => POS_PRO_CASH_PAYMENT_METHOD_NAME,
        };
    }
}
