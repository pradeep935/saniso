<?php

namespace Botble\Mollie\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Mollie\Services\MollieTerminalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Laravel\Facades\Mollie;

class MollieController extends BaseController
{
    protected MollieTerminalService $terminalService;

    public function __construct(MollieTerminalService $terminalService)
    {
        $this->terminalService = $terminalService;
    }
    public function paymentCallback(string $token, Request $request, BaseHttpResponse $response)
    {
        try {
            $api = Mollie::api();

            $paymentId = $request->input('id');

            if (! $paymentId) {
                $message = __('Payment failed! Missing transaction ID.');

                return $response
                    ->setError()
                    ->setNextUrl(PaymentHelper::getCancelURL($token) . '&error_message=' . $message)
                    ->setMessage($message);
            }

            $payment = Payment::query()->where('charge_id', $paymentId)->first();

            if ($payment && $payment->status == PaymentStatusEnum::COMPLETED) {
                return $response
                    ->setNextUrl(PaymentHelper::getRedirectURL($token) . '?charge_id=' . $paymentId)
                    ->setMessage(__('Checkout successfully!'));
            }

            do_action('payment_before_making_api_request', MOLLIE_PAYMENT_METHOD_NAME, ['payment_id' => $paymentId]);

            $result = $api->payments->get($paymentId);

            do_action('payment_after_api_response', MOLLIE_PAYMENT_METHOD_NAME, ['payment_id' => $paymentId], (array) $result);

        } catch (ApiException $exception) {
            BaseHelper::logError($exception);

            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL($token) . '&error_message=' . $exception->getMessage())
                ->setMessage($exception->getMessage());
        }

        if (in_array($result->status, [
            PaymentStatus::STATUS_CANCELED,
            PaymentStatus::STATUS_EXPIRED,
            PaymentStatus::STATUS_FAILED,
        ])) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL($token))
                ->setMessage(__('Payment failed! Status: :status', ['status' => $result->status]));
        }

        if (! $result->isPaid()) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL($token))
                ->setMessage(__('Error when processing payment via :paymentType!', ['paymentType' => 'Mollie']));
        }

        $status = PaymentStatusEnum::COMPLETED;

        if (in_array($result->status, [PaymentStatus::STATUS_OPEN, PaymentStatus::STATUS_AUTHORIZED])) {
            $status = PaymentStatusEnum::PENDING;
        }

        $orderIds = (array) $result->metadata->order_id;

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => $result->amount->value,
            'currency' => $result->amount->currency,
            'charge_id' => $result->id,
            'payment_channel' => MOLLIE_PAYMENT_METHOD_NAME,
            'status' => $status,
            'customer_id' => $result->metadata->customer_id,
            'customer_type' => $result->metadata->customer_type,
            'payment_type' => 'direct',
            'order_id' => $orderIds,
        ]);

        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL($token) . '?charge_id=' . $result->id)
            ->setMessage(__('Checkout successfully!'));
    }

    /**
     * Main Mollie Dashboard
     */
    public function dashboard(): View
    {
        page_title()->setTitle('Mollie Dashboard');

        // Get overview stats
        $stats = $this->getDashboardStats();
        
        // Get recent orders
        $recentOrders = $this->getRecentOrders(10);
        
        // Get direct Mollie payments (not from website orders)
        $directPayments = $this->getDirectMolliePayments(10);
        
        // Get terminal status
        $terminalStatus = $this->getTerminalStatus();

        return view('plugins/mollie::dashboard.index', compact('stats', 'recentOrders', 'directPayments', 'terminalStatus'));
    }

    /**
     * Analytics API endpoint
     */
    public function analytics(Request $request, BaseHttpResponse $response)
    {
        $period = $request->input('period', 'today');
        $type = $request->input('type', 'all');

        $analytics = $this->getAnalytics($period, $type);

        return $response->setData($analytics);
    }

    /**
     * Terminal Status API endpoint
     */
    public function terminalStatus(BaseHttpResponse $response)
    {
        $status = $this->getTerminalStatus();
        
        return $response->setData($status);
    }

    /**
     * Manual Terminal Payment
     */
    public function manualPayment(Request $request, BaseHttpResponse $response)
    {
        $orderId = $request->input('order_id');
        $amount = $request->input('amount');
        $paymentType = $request->input('payment_type', 'card');

        try {
            $result = $this->terminalService->processTerminalPayment($orderId, $paymentType);

            if ($result['success']) {
                return $response
                    ->setMessage('Payment sent to terminal successfully')
                    ->setData($result);
            } else {
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to process payment');
            }

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to process manual payment: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'amounts' => [
                'today' => number_format($this->getPaymentAmount($today), 2),
                'yesterday' => number_format($this->getPaymentAmount($yesterday, $yesterday->copy()->endOfDay()), 2),
                'week' => number_format($this->getPaymentAmount($thisWeek), 2),
                'month' => number_format($this->getPaymentAmount($thisMonth), 2),
                'terminal_today' => number_format($this->getPaymentAmount($today, null, 'terminal'), 2),
                'webshop_today' => number_format($this->getPaymentAmount($today, null, 'webshop'), 2),
            ],
            'orders' => [
                'today' => $this->getOrderCount($today),
                'yesterday' => $this->getOrderCount($yesterday, $yesterday->copy()->endOfDay()),
                'week' => $this->getOrderCount($thisWeek),
                'month' => $this->getOrderCount($thisMonth),
                'terminal_today' => $this->getOrderCount($today, null, 'terminal'),
                'webshop_today' => $this->getOrderCount($today, null, 'webshop'),
            ],
            'payments' => [
                'successful' => $this->getPaymentsByStatus('completed', $today),
                'failed' => $this->getPaymentsByStatus('failed', $today),
                'refunded' => $this->getPaymentsByStatus('refunded', $today),
                'pending' => $this->getPaymentsByStatus('pending', $today),
            ]
        ];
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders(int $limit = 10): array
    {
        $orders = Order::with(['payment', 'address'])
            ->whereHas('payment', function($query) {
                $query->where('payment_channel', 'mollie');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $orders->map(function($order) {
            $paymentStatus = $order->payment->status ?? 'unknown';
            $canPay = !in_array($paymentStatus, ['completed', 'paid', 'success']);
            
            return [
                'id' => $order->id,
                'code' => $order->code,
                'amount' => number_format($order->amount, 2),
                'status' => $order->status,
                'payment_status' => $paymentStatus,
                'customer_name' => $order->address->name ?? 'Guest',
                'source' => $this->getOrderSource($order),
                'created_at' => $order->created_at->format('H:i - d/m/Y'),
                'can_pay' => $canPay,
                'payment_status_class' => $this->getPaymentStatusClass($paymentStatus),
                'status_class' => $this->getOrderStatusClass($order->status),
            ];
        })->toArray();
    }

    /**
     * Get direct Mollie payments that are not in our local database
     */
    private function getDirectMolliePayments(int $limit = 10): array
    {
        try {
            // Get payments directly from Mollie API
            $molliePayments = Mollie::api()->payments->page(null, $limit);
            
            $directPayments = [];
            
            foreach ($molliePayments as $payment) {
                // Check if this payment is NOT in our local database (direct terminal payment)
                $localPayment = Payment::where('charge_id', $payment->id)->first();
                
                // Only include if NO local record exists (meaning it's NOT from website)
                // OR if local record exists but has no order_id (direct payment recorded locally)
                if (!$localPayment) {
                    // This is a direct payment - not recorded in our website at all
                    $isDirectPayment = true;
                } else if ($localPayment && !$localPayment->order_id) {
                    // This is recorded locally but not tied to a website order
                    $isDirectPayment = true;
                } else {
                    // This is a website payment - skip it
                    $isDirectPayment = false;
                }
                
                // Additional check: if payment method is pointofsale, it's definitely terminal
                // Even if it has a local record, it might be a terminal payment for a website order
                $isTerminalPayment = $payment->method === 'pointofsale';
                
                // Only add if it's truly a direct payment (not from website) OR it's a terminal payment without local order
                if ($isDirectPayment || ($isTerminalPayment && (!$localPayment || !$localPayment->order_id))) {
                    // Handle date formatting safely
                    $createdAt = 'Unknown';
                    $createdAtHuman = 'Unknown';
                    
                    if ($payment->createdAt) {
                        try {
                            if (is_string($payment->createdAt)) {
                                $date = new \DateTime($payment->createdAt);
                            } else {
                                $date = $payment->createdAt;
                            }
                            $createdAt = $date->format('Y-m-d H:i:s');
                            $createdAtHuman = $date->format('H:i - d/m/Y');
                        } catch (\Exception $e) {
                            \Log::warning('Failed to parse payment date: ' . $payment->createdAt);
                        }
                    }
                    
                    $directPayments[] = [
                        'id' => $payment->id,
                        'amount' => '€' . number_format((float)($payment->amount->value ?? 0), 2),
                        'currency' => $payment->amount->currency ?? 'EUR',
                        'status' => $payment->status,
                        'method' => $payment->method ?? 'unknown',
                        'description' => $payment->description ?? 'Direct Terminal Payment',
                        'created_at' => $createdAt,
                        'created_at_human' => $createdAtHuman,
                        'is_terminal' => $payment->method === 'pointofsale' || (isset($payment->metadata) && isset($payment->metadata->pos_payment)),
                        'customer_name' => (isset($payment->metadata) && isset($payment->metadata->customer_name)) ? $payment->metadata->customer_name : 'Walk-in Customer',
                        'source' => $payment->method === 'pointofsale' ? 'terminal' : 'mollie_app',
                        'status_class' => $this->getPaymentStatusClass($payment->status),
                        // Add order reference if it exists in description
                        'order_reference' => $this->extractOrderReference($payment->description ?? ''),
                        'payment_method_label' => $this->getPaymentMethodLabel($payment->method ?? 'unknown'),
                    ];
                }
            }
            
            return $directPayments;
            
        } catch (\Exception $e) {
            \Log::error('Failed to fetch direct Mollie payments: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get payment method display label
     */
    private function getPaymentMethodLabel(string $method): string
    {
        $labels = [
            'pointofsale' => 'Terminal',
            'creditcard' => 'Credit Card',
            'ideal' => 'iDEAL',
            'bancontact' => 'Bancontact',
            'sofort' => 'SOFORT',
            'eps' => 'EPS',
            'giropay' => 'Giropay',
            'belfius' => 'Belfius',
            'kbc' => 'KBC',
        ];
        
        return $labels[$method] ?? ucfirst($method);
    }
    
    /**
     * Extract order reference from payment description
     */
    private function extractOrderReference(string $description): ?string
    {
        // Look for order patterns like "#SF-10000062", "Order #123", etc.
        if (preg_match('/#?([A-Z]+-[0-9]+|[0-9]+)/', $description, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get terminal status
     */
    private function getTerminalStatus(): array
    {
        try {
            $terminals = $this->terminalService->getAvailableTerminals();
            
            return [
                'active' => !empty($terminals),
                'terminals' => $terminals,
                'count' => count($terminals),
                'api_status' => 'connected',
            ];
        } catch (\Exception $e) {
            return [
                'active' => false,
                'terminals' => [],
                'count' => 0,
                'api_status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get analytics data
     */
    private function getAnalytics(string $period, string $type): array
    {
        $dateRange = $this->getDateRange($period);
        
        $dailyData = [];
        $current = $dateRange['start']->copy();
        
        while ($current <= $dateRange['end']) {
            $dayAmount = $this->getPaymentAmount($current, $current->copy()->endOfDay(), $type === 'all' ? null : $type);
            $dayOrders = $this->getOrderCount($current, $current->copy()->endOfDay(), $type === 'all' ? null : $type);
            
            $dailyData[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('D'),
                'amount' => $dayAmount,
                'orders' => $dayOrders,
                'formatted_amount' => number_format($dayAmount, 2),
            ];
            
            $current->addDay();
        }

        $analytics = [
            'period' => $period,
            'type' => $type,
            'daily_data' => $dailyData,
            'totals' => [
                'amount' => number_format(array_sum(array_column($dailyData, 'amount')), 2),
                'orders' => array_sum(array_column($dailyData, 'orders')),
            ]
        ];

        // Add source-specific data for comparison
        if ($type === 'all') {
            $terminalAmount = $this->getPaymentAmount($dateRange['start'], $dateRange['end'], 'terminal');
            $terminalOrders = $this->getOrderCount($dateRange['start'], $dateRange['end'], 'terminal');
            $webshopAmount = $this->getPaymentAmount($dateRange['start'], $dateRange['end'], 'webshop');
            $webshopOrders = $this->getOrderCount($dateRange['start'], $dateRange['end'], 'webshop');

            $analytics['terminal'] = [
                'amount' => number_format($terminalAmount, 2),
                'orders' => $terminalOrders,
            ];
            
            $analytics['webshop'] = [
                'amount' => number_format($webshopAmount, 2),
                'orders' => $webshopOrders,
            ];
        }

        return $analytics;
    }

    private function getDateRange(string $period): array
    {
        switch ($period) {
            case 'yesterday':
                return ['start' => Carbon::yesterday(), 'end' => Carbon::yesterday()->endOfDay()];
            case 'week':
                return ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()->endOfWeek()];
            case 'month':
                return ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()];
            default:
                return ['start' => Carbon::today(), 'end' => Carbon::today()->endOfDay()];
        }
    }

    private function getTotalAmount($startDate, $endDate = null, $type = null): float
    {
        $query = Payment::where('payment_channel', 'mollie')
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate);

        if ($endDate) $query->where('created_at', '<=', $endDate);
        
        return $query->sum('amount') ?: 0;
    }

    private function getOrderCount($startDate, $endDate = null, $source = null): int
    {
        $query = Order::whereHas('payment', function($q) {
            $q->where('payment_channel', 'mollie')
              ->where('status', PaymentStatusEnum::COMPLETED); // Only count completed payments
        });
        
        if ($endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        // Filter by source if specified
        if ($source) {
            if ($source === 'terminal') {
                $query->where(function($q) {
                    $q->where('is_finished', true)
                      ->orWhereHas('payment', function($subq) {
                          $subq->where('payment_type', 'terminal')
                               ->orWhere('metadata->payment_type', 'terminal')
                               ->orWhere('metadata->pos_payment', true);
                      });
                });
            } elseif ($source === 'webshop') {
                $query->where(function($q) {
                    $q->where('is_finished', false)
                      ->orWhere('is_finished', null)
                      ->whereDoesntHave('payment', function($subq) {
                          $subq->where('payment_type', 'terminal')
                               ->orWhere('metadata->payment_type', 'terminal')
                               ->orWhere('metadata->pos_payment', true);
                      });
                });
            }
        }
        
        return $query->count();
    }

    private function getPaymentsByStatus(string $status, $startDate, $endDate = null): int
    {
        $query = Payment::where('payment_channel', 'mollie')
            ->where('status', $status)
            ->where('created_at', '>=', $startDate);

        if ($endDate) $query->where('created_at', '<=', $endDate);
        
        return $query->count();
    }

    /**
     * Get available terminals
     */
    public function getTerminals(BaseHttpResponse $response)
    {
        try {
            $result = $this->terminalService->getAvailableTerminals();
            
            return $response->setData($result);
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to get terminals: ' . $e->getMessage());
        }
    }

    /**
     * Add/Register new terminal device
     */
    public function addTerminal(Request $request, BaseHttpResponse $response)
    {
        try {
            $terminalId = $request->input('terminal_id');
            $terminalName = $request->input('terminal_name', 'Terminal Device');
            
            // Add terminal via Mollie API
            $result = $this->terminalService->registerTerminal($terminalId, $terminalName);
            
            if ($result['success']) {
                return $response
                    ->setMessage('Terminal device added successfully')
                    ->setData($result);
            } else {
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to add terminal');
            }
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to add terminal: ' . $e->getMessage());
        }
    }

    /**
     * Remove terminal device
     */
    public function removeTerminal(Request $request, BaseHttpResponse $response)
    {
        try {
            $terminalId = $request->input('terminal_id');
            
            $result = $this->terminalService->removeTerminal($terminalId);
            
            if ($result['success']) {
                return $response->setMessage('Terminal removed successfully');
            } else {
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to remove terminal');
            }
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to remove terminal: ' . $e->getMessage());
        }
    }

    /**
     * Get pending orders for terminal payment
     */
    public function getPendingOrders(BaseHttpResponse $response)
    {
        try {
            $orders = Order::with(['payment', 'address', 'products'])
                ->where(function($query) {
                    $query->whereNull('payment_id')
                          ->orWhereHas('payment', function($q) {
                              $q->where('status', PaymentStatusEnum::PENDING);
                          });
                })
                ->where('status', '!=', \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED)
                ->where('status', '!=', \Botble\Ecommerce\Enums\OrderStatusEnum::COMPLETED)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $pendingOrders = $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->code,
                    'amount' => number_format($order->amount, 2),
                    'status' => $order->status,
                    'customer_name' => $order->address->name ?? 'Walk-in Customer',
                    'customer_phone' => $order->address->phone ?? '',
                    'created_at' => $order->created_at->format('H:i - d/m/Y'),
                    'payment_status' => $order->payment->status ?? 'not_paid',
                    'can_pay_terminal' => !$order->payment || $order->payment->status !== PaymentStatusEnum::COMPLETED,
                ];
            });

            return $response->setData(['orders' => $pendingOrders]);
            
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to get pending orders: ' . $e->getMessage());
        }
    }

    /**
     * Get payment amount sum
     */
    private function getPaymentAmount($startDate, $endDate = null, $source = null): float
    {
        $query = Payment::where('payment_channel', 'mollie')
                       ->where('status', PaymentStatusEnum::COMPLETED);
                       
        if ($endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        // Filter by source if specified
        if ($source) {
            if ($source === 'terminal') {
                $query->where(function($q) {
                    $q->where('payment_type', 'terminal')
                      ->orWhere('metadata->payment_type', 'terminal')
                      ->orWhere('metadata->pos_payment', true)
                      ->orWhereHas('order', function($subq) {
                          $subq->where('is_finished', true);
                      });
                });
            } elseif ($source === 'webshop') {
                $query->whereDoesntHave('order', function($subq) {
                    $subq->where('is_finished', true);
                })
                ->where(function($q) {
                    $q->where('payment_type', '!=', 'terminal')
                      ->orWhereNull('payment_type')
                      ->where('metadata->payment_type', '!=', 'terminal')
                      ->orWhereNull('metadata->payment_type')
                      ->where('metadata->pos_payment', '!=', true)
                      ->orWhereNull('metadata->pos_payment');
                });
            }
        }
        
        return $query->sum('amount') ?? 0;
    }

    private function getOrderSource(Order $order): string
    {
        // Check if payment exists and has terminal-related metadata
        if ($order->payment) {
            // Check payment type from payment record
            if (isset($order->payment->payment_type) && $order->payment->payment_type === 'terminal') {
                return 'terminal';
            }
            
            // Check if order was processed via POS (is_finished flag)
            if ($order->is_finished) {
                return 'terminal';
            }
            
            // Check payment metadata for terminal indicators
            $metadata = $order->payment->metadata ?? '{}';
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true) ?? [];
            }
            
            if (isset($metadata['payment_type']) && $metadata['payment_type'] === 'terminal') {
                return 'terminal';
            }
            if (isset($metadata['pos_payment']) && $metadata['pos_payment']) {
                return 'terminal';
            }
        }
        
        return 'webshop';
    }

    private function getPaymentStatusClass(string $status): string
    {
        switch (strtolower($status)) {
            case 'completed':
            case 'paid':
            case 'success':
                return 'success';
            case 'pending':
            case 'processing':
                return 'warning';
            case 'failed':
            case 'canceled':
            case 'cancelled':
                return 'danger';
            case 'refunded':
                return 'info';
            default:
                return 'secondary';
        }
    }

    private function getOrderStatusClass(string $status): string
    {
        switch (strtolower($status)) {
            case 'completed':
            case 'delivered':
                return 'success';
            case 'processing':
            case 'confirmed':
                return 'primary';
            case 'pending':
                return 'warning';
            case 'canceled':
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }
}
