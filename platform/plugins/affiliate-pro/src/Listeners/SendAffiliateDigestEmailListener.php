<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateClick;
use Botble\AffiliatePro\Models\Commission;
use Botble\AffiliatePro\Notifications\AffiliateDigestNotification;
use Botble\AffiliatePro\Services\AffiliateTrackingService;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Customer;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendAffiliateDigestEmailListener implements ShouldQueue
{
    public function __construct(protected AffiliateTrackingService $trackingService)
    {
    }

    public function handle(): void
    {
        try {
            // Get all active affiliates
            $affiliates = Affiliate::query()
                ->where('status', AffiliateStatusEnum::APPROVED)
                ->get();

            if ($affiliates->isEmpty()) {
                return;
            }

            // Set period for the digest (last 7 days)
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays(7);

            foreach ($affiliates as $affiliate) {
                $this->sendDigestEmail($affiliate, $startDate, $endDate);
            }
        } catch (Exception $exception) {
            Log::error('Error sending affiliate digest emails: ' . $exception->getMessage());
        }
    }

    protected function sendDigestEmail(Affiliate $affiliate, Carbon $startDate, Carbon $endDate): void
    {
        /**
         * @var Customer $customer
         */
        $customer = Customer::query()->find($affiliate->customer_id);

        if (! $customer || ! $customer->email) {
            return;
        }

        // Get statistics for the period
        $totalClicks = $this->trackingService->getClicksCount($affiliate, $startDate, $endDate);
        $conversionRate = $this->trackingService->getConversionRate($affiliate, $startDate, $endDate);

        // Get new commissions for the period
        $newCommissions = Commission::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $newCommissionsCount = $newCommissions->count();
        $earningsThisWeek = $newCommissions->where('status', 'approved')->sum('amount');

        // Get top performing products
        $topProducts = $this->getTopPerformingProducts($affiliate, $startDate, $endDate);

        // Get traffic sources
        $trafficSources = $this->getTrafficSources($affiliate, $startDate, $endDate);

        // Generate optimization tips
        $tips = $this->generateOptimizationTips($affiliate, $totalClicks, $conversionRate, $newCommissionsCount);

        // Prepare digest data
        $digestData = [
            'period_start' => $startDate->format('M d, Y'),
            'period_end' => $endDate->format('M d, Y'),
            'total_clicks' => $totalClicks,
            'conversion_rate' => $conversionRate,
            'new_commissions_count' => $newCommissionsCount,
            'earnings_this_week' => $earningsThisWeek,
            'top_products' => $this->formatTopProducts($topProducts),
            'traffic_sources' => $this->formatTrafficSources($trafficSources),
            'tips' => $this->formatTips($tips),
        ];

        try {
            $customer->notify(new AffiliateDigestNotification($affiliate, $digestData));
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }

    protected function getTopPerformingProducts(Affiliate $affiliate, Carbon $startDate, Carbon $endDate): array
    {
        $topProducts = [];

        // Get commissions grouped by product
        $commissionsByProduct = Commission::query()
            ->join('ec_orders', 'affiliate_commissions.order_id', '=', 'ec_orders.id')
            ->join('ec_order_product', 'ec_orders.id', '=', 'ec_order_product.order_id')
            ->join('ec_products', 'ec_order_product.product_id', '=', 'ec_products.id')
            ->where('affiliate_commissions.affiliate_id', $affiliate->id)
            ->whereBetween('affiliate_commissions.created_at', [$startDate, $endDate])
            ->select('ec_products.id', 'ec_products.name', DB::raw('count(*) as commission_count'))
            ->groupBy('ec_products.id', 'ec_products.name')
            ->orderBy('commission_count', 'desc')
            ->limit(3)
            ->get();

        foreach ($commissionsByProduct as $item) {
            $topProducts[] = [
                'name' => $item->name,
                'commissions' => $item->commission_count,
            ];
        }

        return $topProducts;
    }

    protected function getTrafficSources(Affiliate $affiliate, Carbon $startDate, Carbon $endDate): array
    {
        $trafficSources = [];

        // Get clicks grouped by referrer
        $clicksByReferrer = AffiliateClick::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('referrer_url', DB::raw('count(*) as click_count'))
            ->groupBy('referrer_url')
            ->orderBy('click_count', 'desc')
            ->limit(3)
            ->get();

        $totalClicks = $this->trackingService->getClicksCount($affiliate, $startDate, $endDate);

        if ($totalClicks > 0) {
            foreach ($clicksByReferrer as $item) {
                $sourceName = $item->referrer_url ? parse_url($item->referrer_url, PHP_URL_HOST) : 'Direct';
                $percentage = ($item->click_count / $totalClicks) * 100;

                $trafficSources[] = [
                    'name' => $sourceName,
                    'percentage' => number_format($percentage, 1),
                ];
            }
        }

        return $trafficSources;
    }

    protected function generateOptimizationTips(Affiliate $affiliate, int $totalClicks, float $conversionRate, int $newCommissionsCount): array
    {
        $tips = [];

        // Add tips based on performance metrics
        if ($totalClicks < 10) {
            $tips[] = 'Try sharing your affiliate link on more platforms to increase visibility.';
        }

        if ($conversionRate < 1.0) {
            $tips[] = 'Your conversion rate is below average. Consider targeting more relevant audiences.';
        }

        if ($newCommissionsCount === 0) {
            $tips[] = 'No new commissions this week. Try promoting products with higher commission rates.';
        }

        // Add general tips if there are no specific ones
        if (empty($tips)) {
            $tips[] = 'Use our promotional materials to enhance your marketing efforts.';
            $tips[] = 'Share your QR code in physical locations to reach more potential customers.';
        }

        return $tips;
    }

    protected function formatTopProducts(array $topProducts): string
    {
        if (empty($topProducts)) {
            return 'No product data available for this period.';
        }

        $formatted = '';
        foreach ($topProducts as $product) {
            $formatted .= "• {$product['name']} ({$product['commissions']} commissions)\n";
        }

        return trim($formatted);
    }

    protected function formatTrafficSources(array $trafficSources): string
    {
        if (empty($trafficSources)) {
            return 'No traffic data available for this period.';
        }

        $formatted = '';
        foreach ($trafficSources as $source) {
            $formatted .= "• {$source['name']}: {$source['percentage']}%\n";
        }

        return trim($formatted);
    }

    protected function formatTips(array $tips): string
    {
        if (empty($tips)) {
            return 'Keep up the great work!';
        }

        $formatted = '';
        foreach ($tips as $tip) {
            $formatted .= "• {$tip}\n";
        }

        return trim($formatted);
    }
}
