<?php

namespace Botble\PosPro\Listeners;

use Botble\Ecommerce\Events\OrderCreated;
use Botble\PosPro\Models\PosDeviceConfig;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendOrderToLocalDeviceListener implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Only process POS orders (identified by payment methods)
        if (! $this->isPosOrder($order)) {
            return;
        }

        // Get the user who created the order
        $user = auth()->user();
        if (! $user) {
            return;
        }

        // Get device configuration for the user
        $deviceConfig = PosDeviceConfig::getForUser($user->id);
        if (! $deviceConfig || ! $deviceConfig->device_ip) {
            return;
        }

        // Validate IP address format
        if (! $deviceConfig->isValidPrivateIp()) {
            Log::warning('Invalid POS device IP address for user', [
                'user_id' => $user->id,
                'device_ip' => $deviceConfig->device_ip,
                'device_name' => $deviceConfig->device_name,
                'order_id' => $order->id,
            ]);

            return;
        }

        // Prepare order data
        $orderData = $this->prepareOrderData($order);

        // Send API request to local device
        $this->sendToLocalDevice($deviceConfig->device_ip, $orderData, $order->id, $deviceConfig->device_name);
    }

    /**
     * Check if this is a POS order based on payment method
     */
    protected function isPosOrder($order): bool
    {
        $posPaymentMethods = [
            'pos_cash',
            'pos_card',
            'pos_other',
        ];

        return in_array($order->payment_method, $posPaymentMethods);
    }

    /**
     * Prepare order data for API request
     */
    protected function prepareOrderData($order): array
    {
        $order->load(['products.product', 'address']);

        return [
            'order_id' => $order->id,
            'order_code' => $order->code,
            'total_amount' => $order->amount,
            'sub_total' => $order->sub_total,
            'tax_amount' => $order->tax_amount,
            'discount_amount' => $order->discount_amount,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'notes' => $order->description,
            'created_at' => $order->created_at->toISOString(),
            'customer' => [
                'name' => $order->address->name ?? '',
                'phone' => $order->address->phone ?? '',
                'email' => $order->address->email ?? '',
            ],
            'items' => $order->products->map(function ($orderProduct) {
                return [
                    'product_id' => $orderProduct->product_id,
                    'product_name' => $orderProduct->product_name,
                    'sku' => $orderProduct->product->sku ?? '',
                    'quantity' => $orderProduct->qty,
                    'price' => $orderProduct->price,
                    'tax_amount' => $orderProduct->tax_amount,
                    'options' => $orderProduct->options,
                ];
            })->toArray(),
        ];
    }

    /**
     * Send order data to local device
     */
    protected function sendToLocalDevice(string $deviceIp, array $orderData, int $orderId, ?string $deviceName = null): void
    {
        try {
            $url = "http://{$deviceIp}/api";

            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->retry(1, 1000) // Retry once after 1 second
                ->post($url, $orderData);

            if ($response->successful()) {
                Log::info('Order data sent successfully to local device', [
                    'order_id' => $orderId,
                    'device_ip' => $deviceIp,
                    'device_name' => $deviceName,
                    'response_status' => $response->status(),
                ]);
            } else {
                Log::warning('Failed to send order data to local device', [
                    'order_id' => $orderId,
                    'device_ip' => $deviceIp,
                    'device_name' => $deviceName,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error sending order data to local device', [
                'order_id' => $orderId,
                'device_ip' => $deviceIp,
                'device_name' => $deviceName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
