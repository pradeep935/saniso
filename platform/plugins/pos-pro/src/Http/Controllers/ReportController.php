<?php

namespace Botble\PosPro\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseController
{
    public function index(Request $request)
    {
        $this->pageTitle(trans('plugins/pos-pro::pos.reports.title'));

        // Add required assets
        Assets::addScriptsDirectly([
            'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.js',
            'vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.min.js',
            'vendor/core/plugins/pos-pro/js/report.js',
        ])
        ->addStylesDirectly([
            'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.css',
            'vendor/core/plugins/ecommerce/css/report.css',
        ]);

        Assets::addScripts(['moment', 'jquery']);

        // Get date range from request
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        try {
            // Get POS orders
            $posOrders = $this->getPosOrders($startDate, $endDate);

            // Get POS orders by payment method
            $ordersByPaymentMethod = $this->getOrdersByPaymentMethod($startDate, $endDate);

            // Get daily sales data for chart
            $salesData = $this->getDailySalesData($startDate, $endDate);

            // Get top selling products
            $topProducts = $this->getTopSellingProducts($startDate, $endDate);
        } catch (Exception $e) {
            BaseHelper::logError($e);

            $posOrders = [
                'total_sales' => 0,
                'total_orders' => 0,
                'completed_orders' => 0,
                'average_order_value' => 0,
            ];
            $ordersByPaymentMethod = [];
            $salesData = [];
            $topProducts = collect();
        }

        return view('plugins/pos-pro::reports.index', compact(
            'startDate',
            'endDate',
            'posOrders',
            'ordersByPaymentMethod',
            'salesData',
            'topProducts'
        ));
    }

    protected function getPosOrders($startDate, $endDate)
    {
        // Define POS payment methods
        $posPaymentMethods = [
            'pos_cash',
            'pos_card',
            'pos_other',
        ];

        // Get orders with POS payment methods
        $orders = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('payment', function ($query) use ($posPaymentMethods) {
                $query->whereIn('payment_channel', $posPaymentMethods);
            })
            ->with(['payment'])
            ->get();

        $totalSales = $orders->sum('amount');
        $totalOrders = $orders->count();
        $completedOrders = $orders->where('status', 'completed')->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'average_order_value' => $averageOrderValue,
        ];
    }

    protected function getOrdersByPaymentMethod($startDate, $endDate)
    {
        // Define POS payment methods
        $posPaymentMethods = [
            'pos_cash',
            'pos_card',
            'pos_other',
        ];

        try {
            // Get all orders with POS payment methods
            $orders = Order::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereHas('payment', function ($query) use ($posPaymentMethods) {
                    $query->whereIn('payment_channel', $posPaymentMethods);
                })
                ->with(['payment'])
                ->get();
        } catch (Exception) {
            // If there's an error, return empty data
            return [];
        }

        // Initialize payment method totals
        $ordersByPaymentMethod = [];
        foreach ($posPaymentMethods as $method) {
            $ordersByPaymentMethod[$method] = [
                'count' => 0,
                'total' => 0,
            ];
        }

        // Group by payment method
        foreach ($orders as $order) {
            if (! $order->payment) {
                continue;
            }

            $paymentMethod = $order->payment->payment_channel->getValue();

            // Skip if payment method is not a valid array key
            if (! is_string($paymentMethod) || empty($paymentMethod)) {
                continue;
            }

            // Convert to string to ensure it's a valid array key
            $paymentMethodKey = (string) $paymentMethod;

            // Skip if not a POS payment method
            if (! in_array($paymentMethodKey, $posPaymentMethods)) {
                continue;
            }

            $ordersByPaymentMethod[$paymentMethodKey]['count']++;
            $ordersByPaymentMethod[$paymentMethodKey]['total'] += $order->amount;
        }

        // Remove payment methods with zero orders
        foreach ($ordersByPaymentMethod as $key => $data) {
            if ($data['total'] == 0) {
                unset($ordersByPaymentMethod[$key]);
            }
        }

        // If all payment methods have zero orders, return an empty array
        if (empty($ordersByPaymentMethod)) {
            return [];
        }

        return $ordersByPaymentMethod;
    }

    protected function getDailySalesData($startDate, $endDate)
    {
        // Define POS payment methods
        $posPaymentMethods = [
            'pos_cash',
            'pos_card',
            'pos_other',
        ];

        // Create a period for each day in the date range
        $period = CarbonPeriod::create($startDate, $endDate);

        $salesData = [];

        // Initialize with zero values for each day
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $salesData[$formattedDate] = [
                'date' => $formattedDate,
                'sales' => 0,
                'orders' => 0,
            ];
        }

        // Get orders grouped by date
        $orders = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('payment', function ($query) use ($posPaymentMethods) {
                $query->whereIn('payment_channel', $posPaymentMethods);
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_sales'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('date')
            ->get();

        // Fill in the actual data
        foreach ($orders as $order) {
            $salesData[$order->date] = [
                'date' => $order->date,
                'sales' => $order->total_sales,
                'orders' => $order->total_orders,
            ];
        }

        // Convert to indexed array for easier use in JavaScript
        return array_values($salesData);
    }

    protected function getTopSellingProducts($startDate, $endDate)
    {
        // Define POS payment methods
        $posPaymentMethods = [
            'pos_cash',
            'pos_card',
            'pos_other',
        ];

        // Get top selling products from orders with POS payment methods
        return DB::table('ec_order_product')
            ->join('ec_products', 'ec_order_product.product_id', '=', 'ec_products.id')
            ->join('ec_orders', 'ec_order_product.order_id', '=', 'ec_orders.id')
            ->join('payments', 'ec_orders.id', '=', 'payments.order_id')
            ->whereIn('payments.payment_channel', $posPaymentMethods)
            ->whereBetween('ec_orders.created_at', [$startDate, $endDate])
            ->select(
                'ec_order_product.product_id',
                'ec_order_product.product_name',
                DB::raw('SUM(ec_order_product.qty) as quantity_sold'),
                DB::raw('SUM(ec_order_product.price * ec_order_product.qty) as revenue')
            )
            ->groupBy('ec_order_product.product_id', 'ec_order_product.product_name')
            ->orderBy('quantity_sold', 'desc')
            ->limit(10)
            ->get();
    }
}
