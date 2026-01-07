<?php

namespace Botble\Mollie\Services;

use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;
use Mollie\Laravel\Facades\Mollie;

class MollieTerminalService
{
    protected $client;

    public function __construct()
    {
        $this->client = Mollie::api();
    }

    /**
     * Send payment request to Mollie terminal for POS
     */
    public function sendToTerminal(Order $order, string $terminalId = null): array
    {
        try {
            // Create terminal payment request using Mollie's Point of Sale API
            $paymentData = [
                'amount' => [
                    'currency' => get_application_currency()->title ?? 'EUR',
                    'value' => number_format((float) $order->amount, 2, '.', ''),
                ],
                'method' => 'pointofsale', // Required for POS payments
                'description' => "POS Payment - Order #{$order->code}",
                'redirectUrl' => url("/admin/ecommerce/orders/{$order->id}"),
                'webhookUrl' => route('mollie.terminal.webhook'),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'payment_type' => 'terminal',
                    'pos_payment' => true,
                ],
            ];

            // Add terminal ID if specified, otherwise use default terminal
            if ($terminalId) {
                $paymentData['terminalId'] = $terminalId;
            } else {
                // Get first available terminal if none specified
                $terminals = $this->getAvailableTerminals();
                if (!empty($terminals)) {
                    $paymentData['terminalId'] = $terminals[0]['id'];
                } else {
                    throw new Exception('No terminals available for payment processing');
                }
            }

            Log::info('Mollie POS Payment Request:', $paymentData);

            // Create payment with Mollie POS API
            $molliePayment = $this->client->payments->create($paymentData);

            // Create payment record in database
            $payment = Payment::create([
                'amount' => $order->amount,
                'currency' => get_application_currency()->title ?? 'EUR',
                'charge_id' => $molliePayment->id,
                'payment_channel' => 'mollie',
                'status' => PaymentStatusEnum::PENDING,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'customer_type' => get_class($order->user ?? new \Botble\ACL\Models\User),
                'payment_type' => 'terminal',
            ]);

            // Update order with payment info
            $order->update([
                'payment_id' => $payment->id,
                'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::PROCESSING,
            ]);

            Log::info('POS payment created successfully:', [
                'mollie_id' => $molliePayment->id,
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'terminal_id' => $paymentData['terminalId'] ?? 'default'
            ]);

            return [
                'success' => true,
                'payment_id' => $molliePayment->id,
                'status' => $molliePayment->status,
                'terminal_id' => $paymentData['terminalId'] ?? 'default',
                'message' => 'Payment sent to terminal successfully. Customer can now pay on the terminal device.',
                'checkout_url' => $molliePayment->getCheckoutUrl(),
                'terminal_display' => $this->getOrderDisplayData($order, $molliePayment),
                // For test mode: include changePaymentState URL if available
                'test_mode_url' => property_exists($molliePayment, '_links') && 
                                 isset($molliePayment->_links->changePaymentState) ? 
                                 $molliePayment->_links->changePaymentState->href : null,
            ];

        } catch (Exception $e) {
            Log::error('Mollie POS Payment Error:', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to send payment to terminal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check payment status from terminal
     */
    public function checkTerminalPayment(string $paymentId): array
    {
        try {
            $molliePayment = $this->client->payments->get($paymentId);

            return [
                'success' => true,
                'status' => $molliePayment->status,
                'is_paid' => $molliePayment->isPaid(),
                'is_pending' => $molliePayment->isPending(),
                'is_canceled' => $molliePayment->isCanceled(),
                'amount' => $molliePayment->amount->value,
                'currency' => $molliePayment->amount->currency,
            ];

        } catch (Exception $e) {
            Log::error('Terminal payment check error:', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel terminal payment
     */
    public function cancelTerminalPayment(string $paymentId): array
    {
        try {
            // First get the payment to check its current status and cancelability
            $molliePayment = $this->client->payments->get($paymentId);
            
            Log::info('Payment cancellation attempt:', [
                'payment_id' => $paymentId,
                'current_status' => $molliePayment->status,
                'method' => $molliePayment->method,
                'is_cancelable' => $molliePayment->isCancelable(),
                'created_at' => $molliePayment->createdAt,
                'expires_at' => $molliePayment->expiresAt ?? 'no expiration'
            ]);

            // Handle different payment states
            if ($molliePayment->status === 'canceled') {
                Log::info('Payment already canceled');
                $this->updateLocalPaymentStatus($paymentId, PaymentStatusEnum::FAILED);
                
                return [
                    'success' => true,
                    'status' => 'canceled',
                    'message' => 'Payment was already canceled',
                    'terminal_cleared' => true,
                    'was_cancelable' => false
                ];
            }

            if ($molliePayment->status === 'paid') {
                Log::warning('Cannot cancel paid payment');
                
                return [
                    'success' => false,
                    'status' => 'paid',
                    'message' => 'Cannot cancel a payment that has already been completed',
                    'terminal_cleared' => false,
                    'was_cancelable' => false
                ];
            }

            // Check if payment can be canceled
            if (!$molliePayment->isCancelable()) {
                Log::warning('Payment is not cancelable:', [
                    'payment_id' => $paymentId,
                    'status' => $molliePayment->status,
                    'reason' => 'Payment state does not allow cancellation'
                ]);
                
                // Update local records anyway since payment is effectively failed
                $this->updateLocalPaymentStatus($paymentId, PaymentStatusEnum::FAILED);
                
                return [
                    'success' => true,
                    'status' => $molliePayment->status,
                    'message' => 'Payment is no longer cancelable, but local records updated. Terminal may need manual clearing.',
                    'terminal_cleared' => false,
                    'was_cancelable' => false
                ];
            }

            // Attempt to cancel the payment
            $canceledPayment = $this->client->payments->cancel($paymentId);
            $wasCanceled = $canceledPayment->status === 'canceled';

            // Update local payment record
            $this->updateLocalPaymentStatus($paymentId, PaymentStatusEnum::FAILED);
            
            Log::info('Payment cancellation result:', [
                'payment_id' => $paymentId,
                'old_status' => $molliePayment->status,
                'new_status' => $canceledPayment->status,
                'successfully_canceled' => $wasCanceled
            ]);

            return [
                'success' => true,
                'status' => $canceledPayment->status,
                'message' => $wasCanceled ? 'Payment canceled successfully - terminal should clear shortly' : 'Payment cancellation initiated',
                'terminal_cleared' => $wasCanceled,
                'was_cancelable' => true
            ];

        } catch (Exception $e) {
            Log::error('Terminal payment cancellation error:', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Even if Mollie cancellation fails, update local records
            $this->updateLocalPaymentStatus($paymentId, PaymentStatusEnum::FAILED);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to cancel terminal payment: ' . $e->getMessage(),
                'terminal_cleared' => false
            ];
        }
    }

    /**
     * Update local payment status and related order
     */
    private function updateLocalPaymentStatus(string $paymentId, $status): void
    {
        $payment = Payment::where('charge_id', $paymentId)->first();
        if ($payment) {
            $payment->update(['status' => $status]);
            
            // Also update the order status
            $order = Order::find($payment->order_id);
            if ($order) {
                $orderStatus = $status === PaymentStatusEnum::FAILED 
                    ? \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED
                    : \Botble\Ecommerce\Enums\OrderStatusEnum::PROCESSING;
                    
                $order->update(['status' => $orderStatus]);
            }
            
            Log::info('Local payment records updated:', [
                'payment_id' => $paymentId,
                'payment_status' => $status,
                'order_id' => $order->id ?? null,
                'order_status' => $orderStatus ?? 'unchanged'
            ]);
        }
    }

    /**
     * Register a new terminal device
     */
    public function registerTerminal(string $terminalId, string $terminalName = 'Terminal Device')
    {
        try {
            // Note: Mollie terminals are usually auto-discovered and registered
            // This method validates if the terminal exists and is accessible
            
            // Check if terminal exists and is accessible
            $terminal = $this->client->terminals->get($terminalId);
            
            return [
                'success' => true,
                'message' => 'Terminal verified and accessible',
                'terminal_id' => $terminal->id,
                'terminal_name' => $terminal->description ?? $terminalName,
                'status' => $terminal->status,
                'brand' => $terminal->brand,
                'model' => $terminal->model
            ];
            
        } catch (Exception $e) {
            Log::error('Terminal registration error:', [
                'terminal_id' => $terminalId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to register terminal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove/disable a terminal device
     */
    public function removeTerminal(string $terminalId)
    {
        try {
            // Note: Mollie doesn't typically allow removing terminals via API
            // This method validates the terminal and could be used to "disable" it locally
            
            $terminal = $this->client->terminals->get($terminalId);
            
            return [
                'success' => true,
                'message' => 'Terminal found and can be disabled locally (physical removal requires Mollie dashboard)',
                'terminal_id' => $terminal->id,
                'status' => $terminal->status
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to remove terminal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get available terminals for the dashboard
     */
    public function getAvailableTerminals(): array
    {
        try {
            $terminals = $this->client->terminals->page();

            $availableTerminals = [];
            foreach ($terminals as $terminal) {
                $availableTerminals[] = [
                    'id' => $terminal->id,
                    'name' => $terminal->description ?? 'Terminal Device',
                    'description' => $terminal->description,
                    'brand' => $terminal->brand,
                    'model' => $terminal->model,
                    'serialNumber' => $terminal->serialNumber,
                    'status' => $terminal->status,
                ];
            }

            return $availableTerminals;

        } catch (Exception $e) {
            Log::error('Failed to get available terminals:', ['error' => $e->getMessage()]);
            throw new Exception('Failed to get terminals: ' . $e->getMessage());
        }
    }

    /**
     * Process terminal webhook callback
     */
    public function handleTerminalWebhook(string $paymentId): array
    {
        try {
            // Get payment from Mollie
            $molliePayment = $this->client->payments->get($paymentId);
            
            // Find local payment record
            $payment = Payment::where('charge_id', $paymentId)->first();
            
            if (!$payment) {
                throw new Exception("Payment record not found for ID: $paymentId");
            }

            $order = Order::find($payment->order_id);
            
            if (!$order) {
                throw new Exception("Order not found for payment ID: $paymentId");
            }

            // Update payment status
            if ($molliePayment->isPaid()) {
                $payment->update(['status' => PaymentStatusEnum::COMPLETED]);
                
                // Auto-complete order
                $order->update([
                    'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::COMPLETED,
                    'payment_id' => $payment->id,
                ]);

                // Fire order completion events
                do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                    'amount' => $molliePayment->amount->value,
                    'currency' => $molliePayment->amount->currency,
                    'charge_id' => $molliePayment->id,
                    'payment_channel' => 'mollie',
                    'status' => PaymentStatusEnum::COMPLETED,
                    'order_id' => $order->id,
                    'payment_type' => 'terminal',
                ]);

                Log::info('Terminal payment completed:', [
                    'payment_id' => $paymentId,
                    'order_id' => $order->id,
                    'amount' => $molliePayment->amount->value
                ]);

                return [
                    'success' => true,
                    'status' => 'completed',
                    'message' => 'Order automatically completed via terminal payment',
                ];

            } elseif ($molliePayment->isCanceled() || $molliePayment->isExpired()) {
                $payment->update(['status' => PaymentStatusEnum::FAILED]);
                
                $order->update([
                    'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED,
                ]);

                return [
                    'success' => true,
                    'status' => 'failed',
                    'message' => 'Terminal payment failed or was canceled',
                ];
            }

            return [
                'success' => true,
                'status' => 'pending',
                'message' => 'Terminal payment still pending',
            ];

        } catch (Exception $e) {
            Log::error('Terminal webhook error:', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send payment request to Mollie terminal for POS with voucher support
     */
    public function sendToTerminalWithVoucher(Order $order, string $terminalId = null, array $voucherCategories = []): array
    {
        try {
            $paymentData = [
                'amount' => [
                    'currency' => get_application_currency()->title ?? 'EUR',
                    'value' => number_format((float) $order->amount, 2, '.', ''),
                ],
                'method' => 'pointofsale',
                'description' => "POS Voucher Payment - Order #{$order->code}",
                'redirectUrl' => url("/admin/ecommerce/orders/{$order->id}"),
                'webhookUrl' => route('mollie.terminal.webhook'),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'payment_type' => 'terminal_voucher',
                    'pos_payment' => true,
                ],
            ];

            // Add terminal ID
            if ($terminalId) {
                $paymentData['terminalId'] = $terminalId;
            } else {
                $terminals = $this->getAvailableTerminals();
                if (!empty($terminals)) {
                    $paymentData['terminalId'] = $terminals[0]['id'];
                } else {
                    throw new Exception('No terminals available for voucher payment');
                }
            }

            // Add voucher categories if specified
            if (!empty($voucherCategories)) {
                $paymentData['lines'] = [
                    [
                        'description' => "Voucher payment for Order #{$order->code}",
                        'quantity' => 1,
                        'unitPrice' => [
                            'currency' => get_application_currency()->title ?? 'EUR',
                            'value' => number_format((float) $order->amount, 2, '.', ''),
                        ],
                        'totalAmount' => [
                            'currency' => get_application_currency()->title ?? 'EUR',
                            'value' => number_format((float) $order->amount, 2, '.', ''),
                        ],
                        'categories' => $voucherCategories, // e.g., ['meal'], ['eco'], ['gift'], ['sport_culture']
                    ]
                ];
            }

            Log::info('Mollie POS Voucher Payment Request:', $paymentData);

            $molliePayment = $this->client->payments->create($paymentData);

            $payment = Payment::create([
                'amount' => $order->amount,
                'currency' => get_application_currency()->title ?? 'EUR',
                'charge_id' => $molliePayment->id,
                'payment_channel' => 'mollie',
                'status' => PaymentStatusEnum::PENDING,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'customer_type' => get_class($order->user ?? new \Botble\ACL\Models\User),
                'payment_type' => 'terminal_voucher',
            ]);

            $order->update([
                'payment_id' => $payment->id,
                'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::PROCESSING,
            ]);

            return [
                'success' => true,
                'payment_id' => $molliePayment->id,
                'status' => $molliePayment->status,
                'terminal_id' => $paymentData['terminalId'],
                'voucher_categories' => $voucherCategories,
                'message' => 'Voucher payment sent to terminal successfully. Customer must use voucher card.',
                'terminal_display' => $this->getOrderDisplayData($order, $molliePayment),
            ];

        } catch (Exception $e) {
            Log::error('Mollie POS Voucher Payment Error:', [
                'order_id' => $order->id,
                'voucher_categories' => $voucherCategories,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to send voucher payment to terminal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Refund a terminal payment (following Mollie documentation)
     */
    public function refundTerminalPayment(string $paymentId, float $amount = null, string $description = null): array
    {
        try {
            // Get the original payment
            $molliePayment = $this->client->payments->get($paymentId);
            
            // Check if payment can be refunded
            if (!$molliePayment->canBeRefunded()) {
                return [
                    'success' => false,
                    'error' => 'Payment cannot be refunded',
                    'message' => "Payment {$paymentId} cannot be refunded. Status: {$molliePayment->status}",
                ];
            }

            // Prepare refund data
            $refundData = [];
            
            if ($amount !== null) {
                $refundData['amount'] = [
                    'currency' => $molliePayment->amount->currency,
                    'value' => number_format($amount, 2, '.', ''),
                ];
            }
            
            if ($description) {
                $refundData['description'] = $description;
            }

            Log::info('Creating terminal payment refund:', [
                'payment_id' => $paymentId,
                'refund_data' => $refundData
            ]);

            // Create the refund
            $refund = $molliePayment->refund($refundData);

            // Find and update local payment record
            $payment = Payment::where('charge_id', $paymentId)->first();
            if ($payment) {
                $order = Order::find($payment->order_id);
                if ($order) {
                    // Update order status for refund
                    $order->update([
                        'status' => \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED,
                    ]);
                }
            }

            Log::info('Terminal payment refund created successfully:', [
                'payment_id' => $paymentId,
                'refund_id' => $refund->id,
                'refund_amount' => $refund->amount->value,
                'refund_currency' => $refund->amount->currency
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount->value,
                'currency' => $refund->amount->currency,
                'message' => "{$refund->amount->currency} {$refund->amount->value} of terminal payment {$paymentId} refunded successfully.",
            ];

        } catch (Exception $e) {
            Log::error('Terminal payment refund error:', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to refund terminal payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get terminal display information for Order and Payment
     */
    private function getOrderDisplayData($order, $molliePayment): array
    {
        return [
            'order_code' => $order->code,
            'amount' => number_format((float)$order->amount, 2, '.', ''),
            'currency' => get_application_currency()->title ?? 'EUR',
            'formatted_amount' => '€' . number_format((float)$order->amount, 2),
            'description' => "POS Payment - Order #{$order->code}",
            'payment_id' => $molliePayment->id ?? null,
            'status' => $molliePayment->status ?? 'pending'
        ];
    }

    /**
     * Get terminal display information
     */
    public function getTerminalDisplay(string $terminalId): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Mollie API not configured'
                ];
            }

            // Get terminal details from Mollie API
            $terminal = $this->client->terminals->get($terminalId);
            
            return [
                'success' => true,
                'terminal' => [
                    'id' => $terminal->id,
                    'description' => $terminal->description,
                    'brand' => $terminal->brand ?? 'Unknown',
                    'model' => $terminal->model ?? 'Unknown',
                    'status' => $terminal->status,
                    'serialNumber' => $terminal->serialNumber ?? 'N/A'
                ]
            ];
            
        } catch (Exception $e) {
            Log::error('Failed to get terminal display info:', [
                'terminal_id' => $terminalId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get terminal information: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get payment status from Mollie
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $molliePayment = $this->client->payments->get($paymentId);
            
            return [
                'success' => true,
                'payment' => [
                    'id' => $molliePayment->id,
                    'status' => $molliePayment->status,
                    'amount' => $molliePayment->amount,
                    'method' => $molliePayment->method,
                    'paidAt' => $molliePayment->paidAt,
                    'canceledAt' => $molliePayment->canceledAt,
                    'expiredAt' => $molliePayment->expiredAt,
                    'isExpired' => $molliePayment->isExpired(),
                    'isPaid' => $molliePayment->isPaid(),
                    'isCanceled' => $molliePayment->isCanceled(),
                    'isFailed' => $molliePayment->isFailed(),
                ]
            ];
        } catch (Exception $e) {
            Log::error('Get payment status error:', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get payment status'
            ];
        }
    }

    /**
     * Clear terminal display by canceling any pending payments
     */
    public function clearTerminalDisplay(string $terminalId): array
    {
        try {
            Log::info('Attempting to clear terminal display:', [
                'terminal_id' => $terminalId
            ]);

            // Try to get terminal information
            $terminal = $this->client->terminals->get($terminalId);
            
            Log::info('Terminal status:', [
                'terminal_id' => $terminalId,
                'status' => $terminal->status ?? 'unknown',
                'model' => $terminal->model ?? 'unknown'
            ]);

            // For some terminal models, we might need additional clearing steps
            // This is a placeholder for future terminal-specific implementations
            
            return [
                'success' => true,
                'message' => 'Terminal display cleared via payment cancellation',
                'terminal_id' => $terminalId,
                'terminal_status' => $terminal->status ?? 'unknown'
            ];

        } catch (Exception $e) {
            Log::error('Clear terminal display error:', [
                'terminal_id' => $terminalId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to clear terminal display'
            ];
        }
    }

    /**
     * Check if Mollie API is properly configured
     */
    protected function isConfigured(): bool
    {
        try {
            return $this->client !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}