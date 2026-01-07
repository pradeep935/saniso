<?php

namespace Botble\Mollie\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Order;
use Botble\Mollie\Services\MollieTerminalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MollieTerminalController extends BaseController
{
    protected $terminalService;

    public function __construct(MollieTerminalService $terminalService)
    {
        $this->terminalService = $terminalService;
    }

    /**
     * Send order payment to terminal (POS endpoint)
     */
    public function sendToTerminal(Request $request, BaseHttpResponse $response)
    {
        try {
            $orderId = $request->input('order_id');
            $terminalId = $request->input('terminal_id'); // Optional specific terminal
            $paymentType = $request->input('payment_type', 'card'); // 'card' or 'voucher'
            $voucherCategories = $request->input('voucher_categories', []); // For voucher payments
            
            $order = Order::findOrFail($orderId);
            
            // Validate order can be paid via terminal
            if ($order->payment && $order->payment->status === \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED) {
                return $response
                    ->setError()
                    ->setMessage('Order is already paid');
            }

            // Send payment to terminal based on type
            if ($paymentType === 'voucher' && !empty($voucherCategories)) {
                $result = $this->terminalService->sendToTerminalWithVoucher($order, $terminalId, $voucherCategories);
            } else {
                $result = $this->terminalService->sendToTerminal($order, $terminalId);
            }
            
            if ($result['success']) {
                return $response
                    ->setData($result)
                    ->setMessage($result['message'] ?? 'Payment sent to terminal successfully. Customer can now pay on the terminal device.');
            } else {
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to send payment to terminal')
                    ->setData(['error' => $result['error'] ?? '']);
            }

        } catch (\Exception $e) {
            Log::error('Terminal payment request failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $response
                ->setError()
                ->setMessage('Failed to process terminal payment: ' . $e->getMessage());
        }
    }

    /**
     * Check payment status on terminal
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

            $result = $this->terminalService->checkTerminalPayment($paymentId);
            
            return $response
                ->setData($result)
                ->setMessage($result['success'] ? 'Payment status retrieved' : 'Failed to check payment status');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to check payment status: ' . $e->getMessage());
        }
    }

    /**
     * Cancel terminal payment
     */
    public function cancelPayment(Request $request, BaseHttpResponse $response)
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
                    ->setData($result)
                    ->setMessage('Terminal payment canceled successfully');
            } else {
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to cancel payment')
                    ->setData(['error' => $result['error'] ?? '']);
            }

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to cancel payment: ' . $e->getMessage());
        }
    }

    /**
     * Refund terminal payment
     */
    public function refundPayment(Request $request, BaseHttpResponse $response)
    {
        try {
            $paymentId = $request->input('payment_id');
            $amount = $request->input('amount'); // Optional partial refund amount
            $description = $request->input('description', 'Terminal payment refund');
            
            if (!$paymentId) {
                return $response
                    ->setError()
                    ->setMessage('Payment ID is required');
            }

            $result = $this->terminalService->refundTerminalPayment($paymentId, $amount, $description);
            
            if ($result['success']) {
                return $response
                    ->setData($result)
                    ->setMessage($result['message']);
            } else {
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to refund payment')
                    ->setData(['error' => $result['error'] ?? '']);
            }

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to refund payment: ' . $e->getMessage());
        }
    }

    /**
     * Get available terminals
     */
    public function getTerminals(BaseHttpResponse $response)
    {
        try {
            $result = $this->terminalService->getAvailableTerminals();
            
            return $response
                ->setData($result)
                ->setMessage($result['success'] ? 'Terminals retrieved successfully' : 'Failed to get terminals');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to get terminals: ' . $e->getMessage());
        }
    }

    /**
     * Webhook handler for terminal payments (auto-complete orders)
     */
    public function terminalWebhook(Request $request, BaseHttpResponse $response)
    {
        try {
            $paymentId = $request->input('id');
            
            if (!$paymentId) {
                Log::warning('Terminal webhook called without payment ID', $request->all());
                return $response->setMessage('Payment ID missing');
            }

            Log::info('Terminal webhook received:', [
                'payment_id' => $paymentId,
                'request_data' => $request->all()
            ]);

            // Process the webhook
            $result = $this->terminalService->handleTerminalWebhook($paymentId);
            
            if ($result['success']) {
                Log::info('Terminal webhook processed successfully:', [
                    'payment_id' => $paymentId,
                    'status' => $result['status']
                ]);
                
                return $response->setMessage('Webhook processed successfully');
            } else {
                Log::error('Terminal webhook processing failed:', [
                    'payment_id' => $paymentId,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                
                return $response
                    ->setError()
                    ->setMessage('Webhook processing failed');
            }

        } catch (\Exception $e) {
            Log::error('Terminal webhook exception:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return $response
                ->setError()
                ->setMessage('Webhook processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Show POS Terminal Dashboard
     */
    public function dashboard(): View
    {
        return view('plugins/mollie::terminal-dashboard');
    }

    /**
     * POS Dashboard - Show orders ready for terminal payment
     */
    public function posDashboard(BaseHttpResponse $response)
    {
        try {
            // Get orders that can be paid via terminal
            $orders = Order::with(['payment', 'address', 'products'])
                ->where(function($query) {
                    $query->whereNull('payment_id')
                          ->orWhereHas('payment', function($q) {
                              $q->where('status', \Botble\Payment\Enums\PaymentStatusEnum::PENDING);
                          });
                })
                ->where('status', '!=', \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED)
                ->where('status', '!=', \Botble\Ecommerce\Enums\OrderStatusEnum::COMPLETED)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            // Get available terminals
            $terminalsResult = $this->terminalService->getAvailableTerminals();
            $terminals = $terminalsResult['success'] ? $terminalsResult['terminals'] : [];

            return $response->setData([
                'orders' => $orders->map(function($order) {
                    return [
                        'id' => $order->id,
                        'code' => $order->code,
                        'amount' => $order->amount,
                        'status' => $order->status,
                        'customer_name' => $order->address->name ?? 'Walk-in Customer',
                        'customer_phone' => $order->address->phone ?? '',
                        'created_at' => $order->created_at->format('H:i - d/m/Y'),
                        'payment_status' => $order->payment->status ?? 'not_paid',
                        'can_pay_terminal' => !$order->payment || $order->payment->status !== \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED,
                    ];
                }),
                'terminals' => $terminals,
                'stats' => [
                    'pending_orders' => $orders->count(),
                    'available_terminals' => count($terminals),
                ]
            ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to load POS dashboard: ' . $e->getMessage());
        }
    }
}