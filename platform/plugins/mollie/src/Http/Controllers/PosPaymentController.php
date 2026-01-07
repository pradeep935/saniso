<?php

namespace Botble\Mollie\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Order;
use Botble\Mollie\Services\MollieTerminalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PosPaymentController extends BaseController
{
    protected $terminalService;

    public function __construct(MollieTerminalService $terminalService)
    {
        $this->terminalService = $terminalService;
    }

    /**
     * Validate Mollie API and check if terminal is active
     */
    public function validateMollieConfig(BaseHttpResponse $response)
    {
        try {
            // Check if Mollie API key is configured
            $apiKey = get_payment_setting('api_key', MOLLIE_PAYMENT_METHOD_NAME);
            
            if (empty($apiKey)) {
                return $response
                    ->setError()
                    ->setMessage('Mollie API key is not configured. Please configure Mollie payment method first.');
            }

            // Test API connection and get terminals
            try {
                $terminals = $this->terminalService->getAvailableTerminals();
                
                if (empty($terminals)) {
                    return $response
                        ->setError()
                        ->setMessage('No active Mollie terminals found. Please ensure you have at least one terminal activated in your Mollie account.');
                }

                return $response
                    ->setData([
                        'valid' => true,
                        'terminals_count' => count($terminals),
                        'terminals' => $terminals
                    ])
                    ->setMessage('Mollie Terminal is ready! ' . count($terminals) . ' terminal(s) available.');

            } catch (\Exception $terminalError) {
                return $response
                    ->setError()
                    ->setMessage('Failed to connect to Mollie terminals: ' . $terminalError->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Mollie validation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $response
                ->setError()
                ->setMessage('Mollie configuration error: ' . $e->getMessage());
        }
    }

    /**
     * Process POS terminal payment for an order
     */
    public function processTerminalPayment(Request $request, BaseHttpResponse $response)
    {
        \Log::info('=== POS PAYMENT DEBUG START ===');
        \Log::info('POS Payment request received', [
            'order_id' => $request->input('order_id'),
            'payment_type' => $request->input('payment_type'),
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            \Log::info('Step 1: Starting validation');
            
            $orderId = $request->input('order_id');
            $paymentType = $request->input('payment_type', 'card'); // 'card' or 'voucher'
            $voucherCategories = $request->input('voucher_categories', []);
            $terminalId = $request->input('terminal_id'); // Optional specific terminal
            
            if (!$orderId) {
                \Log::error('Order ID missing');
                return $response
                    ->setError()
                    ->setMessage('Order ID is required');
            }

            \Log::info('Step 2: Finding order', ['order_id' => $orderId]);

            $order = Order::findOrFail($orderId);
            
            \Log::info('Step 3: Order found', [
                'order_id' => $order->id,
                'order_code' => $order->code,
                'order_amount' => $order->amount,
                'order_status' => $order->status
            ]);
            
            // Check if order is already paid
            if ($order->payment && $order->payment->status === \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED) {
                \Log::warning('Order already paid');
                return $response
                    ->setError()
                    ->setMessage('Order is already paid');
            }

            // Check if order is valid for payment
            if ($order->status === \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED) {
                \Log::warning('Order is canceled');
                return $response
                    ->setError()
                    ->setMessage('Cannot process payment for canceled order');
            }

            \Log::info('Step 4: Updating order status to processing');

            // Update order status to processing when payment is initiated
            $order->update([
                'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::PROCESSING
            ]);

            \Log::info('Step 5: Sending to terminal', [
                'payment_type' => $paymentType,
                'terminal_id' => $terminalId
            ]);

            // Send payment to terminal based on type
            if ($paymentType === 'voucher' && !empty($voucherCategories)) {
                \Log::info('Using voucher payment method');
                $result = $this->terminalService->sendToTerminalWithVoucher($order, $terminalId, $voucherCategories);
            } else {
                \Log::info('Using card payment method');
                $result = $this->terminalService->sendToTerminal($order, $terminalId);
            }
            
            \Log::info('Step 6: Terminal service result', ['result' => $result]);
            
            if ($result['success']) {
                \Log::info('Payment sent successfully');
                return $response
                    ->setData([
                        'payment_id' => $result['payment_id'],
                        'terminal_id' => $result['terminal_id'] ?? null,
                        'amount' => number_format((float)$order->amount, 2, '.', ''),
                        'formatted_amount' => '€' . number_format((float)$order->amount, 2),
                        'currency' => get_application_currency()->title ?? 'EUR',
                        'order_code' => $order->code,
                        'order_id' => $order->id,
                        'status' => 'sent_to_terminal',
                        'timeout' => 300, // 5 minutes
                        'terminal_display' => $result['terminal_display'] ?? []
                    ])
                    ->setMessage('Payment sent to terminal successfully. Customer can now pay.');
            } else {
                \Log::error('Failed to send to terminal', ['result' => $result]);
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to send payment to terminal')
                    ->setData(['error' => $result['error'] ?? '']);
            }

        } catch (\Exception $e) {
            \Log::error('=== POS PAYMENT ERROR ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            return $response
                ->setError()
                ->setMessage('Payment processing failed: ' . $e->getMessage());
        } finally {
            \Log::info('=== POS PAYMENT DEBUG END ===');
        }
    }

    /**
     * Check payment status (for polling during payment)
     */
    public function checkPaymentStatus(Request $request, BaseHttpResponse $response)
    {
        try {
            $paymentId = $request->input('payment_id');
            
            if (!$paymentId) {
                return $response
                    ->setError()
                    ->setMessage('Payment ID is required');
            }

            $result = $this->terminalService->getPaymentStatus($paymentId);
            
            if (!$result['success']) {
                return $response
                    ->setError()
                    ->setMessage('Failed to check payment status: ' . ($result['message'] ?? 'Unknown error'));
            }

            $paymentData = $result['payment'];
            $status = $paymentData['status'];
            
            // Map Mollie status to our status
            $mappedStatus = match($status) {
                'paid' => 'completed',
                'failed', 'expired', 'canceled' => 'failed',
                'pending', 'open' => 'pending',
                default => 'unknown'
            };
            
            $isFailed = in_array($mappedStatus, ['failed', 'expired', 'canceled']) || in_array($status, ['failed', 'expired', 'canceled']);
            $isCompleted = $mappedStatus === 'completed';

            // Update order status based on payment status
            $this->updateOrderStatusFromPayment($paymentId, $mappedStatus);
            
            Log::info('Payment status check result:', [
                'payment_id' => $paymentId,
                'mollie_status' => $status,
                'mapped_status' => $mappedStatus,
                'is_completed' => $isCompleted,
                'is_failed' => $isFailed
            ]);

            return $response
                ->setData([
                    'status' => $mappedStatus,
                    'mollie_status' => $status,
                    'payment_id' => $paymentId,
                    'amount' => $paymentData['amount'] ?? null,
                    'method' => $paymentData['method'] ?? null,
                    'paid_at' => $paymentData['paidAt'] ?? null,
                    'is_completed' => $isCompleted,
                    'is_failed' => $isFailed
                ])
                ->setMessage($isCompleted ? 'Payment completed successfully!' : 
                          ($isFailed ? 'Payment failed or was canceled' : 'Payment is still pending...'));

        } catch (\Exception $e) {
            Log::error('Payment status check error:', [
                'error' => $e->getMessage(),
                'payment_id' => $request->input('payment_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return $response
                ->setError()
                ->setMessage('Failed to check payment status: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a terminal payment
     */
    public function cancelTerminalPayment(Request $request, BaseHttpResponse $response)
    {
        try {
            $paymentId = $request->input('payment_id');
            
            if (!$paymentId) {
                return $response
                    ->setError()
                    ->setMessage('Payment ID is required');
            }

            $result = $this->terminalService->cancelTerminalPayment($paymentId);
            
            if ($result['success']) {
                return $response
                    ->setData([
                        'payment_id' => $paymentId,
                        'status' => $result['status'] ?? 'canceled',
                        'terminal_cleared' => $result['terminal_cleared'] ?? false,
                        'was_cancelable' => $result['was_cancelable'] ?? false,
                        'message' => $result['message']
                    ])
                    ->setMessage($result['message']);
            } else {
                return $response
                    ->setError()
                    ->setMessage('Failed to cancel payment: ' . ($result['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('Payment cancellation error:', [
                'error' => $e->getMessage(),
                'payment_id' => $request->input('payment_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return $response
                ->setError()
                ->setMessage('Failed to cancel payment: ' . $e->getMessage());
        }
    }

    /**
     * Update order status based on payment status
     */
    private function updateOrderStatusFromPayment(string $paymentId, string $paymentStatus): void
    {
        try {
            // Find order by payment reference in description
            $orders = Order::where('description', 'like', "%Mollie Payment ID: {$paymentId}%")->get();
            
            foreach ($orders as $order) {
                switch ($paymentStatus) {
                    case 'completed':
                        $order->update([
                            'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::COMPLETED,
                            'payment_status' => \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED
                        ]);
                        
                        // Create payment record if it doesn't exist
                        if (!$order->payment) {
                            \Botble\Payment\Models\Payment::create([
                                'amount' => $order->amount,
                                'currency' => $order->currency,
                                'charge_id' => $paymentId,
                                'payment_channel' => 'mollie_terminal',
                                'status' => \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED,
                                'order_id' => $order->id,
                                'customer_id' => $order->user_id,
                            ]);
                        } else {
                            $order->payment->update([
                                'status' => \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED,
                                'charge_id' => $paymentId
                            ]);
                        }
                        break;
                        
                    case 'failed':
                        $order->update([
                            'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED,
                            'payment_status' => \Botble\Payment\Enums\PaymentStatusEnum::FAILED
                        ]);
                        
                        if ($order->payment) {
                            $order->payment->update([
                                'status' => \Botble\Payment\Enums\PaymentStatusEnum::FAILED
                            ]);
                        }
                        break;
                        
                    case 'pending':
                        // Keep current status, just update payment to pending if exists
                        if ($order->payment) {
                            $order->payment->update([
                                'status' => \Botble\Payment\Enums\PaymentStatusEnum::PENDING
                            ]);
                        }
                        break;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to update order status from payment:', [
                'payment_id' => $paymentId,
                'status' => $paymentStatus,
                'error' => $e->getMessage()
            ]);
        }
    }
}