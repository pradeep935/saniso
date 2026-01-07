<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateClick;
use Botble\AffiliatePro\Models\AffiliateShortLink;
use Botble\AffiliatePro\Models\Commission;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\AffiliatePro\Tables\Reports\RecentCommissionsTable;
use Botble\AffiliatePro\Tables\Reports\RecentWithdrawalsTable;
use Botble\AffiliatePro\Tables\Reports\TopAffiliatesTable;
use Botble\Base\Facades\Assets;
use Botble\Base\Supports\Breadcrumb;
use Botble\Base\Widgets\Contracts\AdminWidget;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::reports.name'), route('affiliate-pro.reports.index'));
    }
    public function index(Request $request, AdminWidget $widget)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::reports.name'));

        Assets::addScriptsDirectly([
            'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.js',
            'vendor/core/plugins/ecommerce/js/report.js',
        ])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.css',
                'vendor/core/plugins/ecommerce/css/report.css',
                'vendor/core/plugins/affiliate-pro/css/app.css',
            ]);

        Assets::usingVueJS();

        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport($request);

        if ($request->ajax()) {
            return $this
                ->httpResponse()
                ->setData(
                    view('plugins/affiliate-pro::reports.ajax', compact('startDate', 'endDate', 'widget'))->render()
                );
        }

        return view(
            'plugins/affiliate-pro::reports.index',
            compact('startDate', 'endDate', 'widget')
        );
    }

    public function getTopAffiliates(TopAffiliatesTable $topAffiliatesTable)
    {
        return $topAffiliatesTable->renderTable();
    }

    public function getRecentCommissions(RecentCommissionsTable $recentCommissionsTable)
    {
        return $recentCommissionsTable->renderTable();
    }

    public function getRecentWithdrawals(RecentWithdrawalsTable $recentWithdrawalsTable)
    {
        return $recentWithdrawalsTable->renderTable();
    }

    public function getDashboardWidgetGeneral()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now();

        $pendingCommissions = Commission::query()
            ->where('status', 'pending')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $approvedCommissions = Commission::query()
            ->where('status', 'approved')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $totalCommissionAmount = Commission::query()
            ->where('status', 'approved')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->sum('amount');

        $pendingWithdrawals = Withdrawal::query()
            ->where('status', 'pending')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $activeAffiliates = Affiliate::query()
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->count();

        return $this
            ->httpResponse()
            ->setData(
                view(
                    'plugins/affiliate-pro::reports.widgets.general',
                    compact(
                        'pendingCommissions',
                        'approvedCommissions',
                        'totalCommissionAmount',
                        'pendingWithdrawals',
                        'activeAffiliates'
                    )
                )->render()
            );
    }

    /**
     * Get geographic data for clicks
     */
    public function getGeographicData(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        if ($request->input('start_date')) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $endDate = Carbon::now();
        if ($request->input('end_date')) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->input('end_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $affiliateId = $request->input('affiliate_id');

        $query = AffiliateClick::query()
            ->select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate->endOfDay())
            ->groupBy('country')
            ->orderBy('total', 'desc');

        if ($affiliateId) {
            $query->where('affiliate_id', $affiliateId);
        }

        $countryData = $query->get();

        // Get city data for the top countries
        $topCountries = $countryData->take(5)->pluck('country')->toArray();

        $cityData = [];
        foreach ($topCountries as $country) {
            $cities = AffiliateClick::query()
                ->select('city', DB::raw('count(*) as total'))
                ->where('country', $country)
                ->whereNotNull('city')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate->endOfDay())
                ->groupBy('city')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            $cityData[$country] = $cities;
        }

        return $this->httpResponse()->setData([
            'country_data' => $countryData,
            'city_data' => $cityData,
        ]);
    }

    /**
     * Get short link performance data
     */
    public function getShortLinkPerformance(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        if ($request->input('start_date')) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $endDate = Carbon::now();
        if ($request->input('end_date')) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->input('end_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $affiliateId = $request->input('affiliate_id');

        $query = AffiliateShortLink::query()
            ->select('id', 'title', 'short_code', 'clicks', 'conversions', 'product_id')
            ->with('product:id,name')
            ->orderBy('clicks', 'desc');

        if ($affiliateId) {
            $query->where('affiliate_id', $affiliateId);
        }

        $shortLinks = $query->limit(10)->get();

        // Get daily click data for the top 5 short links
        $topShortLinkIds = $shortLinks->take(5)->pluck('id')->toArray();

        $dailyClicks = [];
        if (! empty($topShortLinkIds)) {
            $clickData = AffiliateClick::query()
                ->select(
                    'short_link_id',
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as total')
                )
                ->whereIn('short_link_id', $topShortLinkIds)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate->endOfDay())
                ->groupBy('short_link_id', DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            // Format the data for the chart
            foreach ($topShortLinkIds as $shortLinkId) {
                $shortLink = $shortLinks->firstWhere('id', $shortLinkId);
                $linkTitle = $shortLink ? ($shortLink->title ?: 'Link #' . $shortLink->id) : 'Unknown';

                $dailyClicks[$shortLinkId] = [
                    'name' => $linkTitle,
                    'data' => [],
                ];

                $currentDate = clone $startDate;
                while ($currentDate <= $endDate) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $clickCount = $clickData
                        ->where('short_link_id', $shortLinkId)
                        ->where('date', $dateStr)
                        ->first();

                    $dailyClicks[$shortLinkId]['data'][] = [
                        'x' => $dateStr,
                        'y' => $clickCount ? $clickCount->total : 0,
                    ];

                    $currentDate->addDay();
                }
            }
        }

        return $this->httpResponse()->setData([
            'short_links' => $shortLinks,
            'daily_clicks' => array_values($dailyClicks),
        ]);
    }

    /**
     * Get commission data by date
     */
    public function getCommissionData(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        if ($request->input('start_date')) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $endDate = Carbon::now();
        if ($request->input('end_date')) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->input('end_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $affiliateId = $request->input('affiliate_id');

        $query = Commission::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate->endOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date');

        if ($affiliateId) {
            $query->where('affiliate_id', $affiliateId);
        }

        $commissionData = $query->get();

        // Format the data for the chart
        $chartData = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $commission = $commissionData->firstWhere('date', $dateStr);

            $chartData[] = [
                'x' => $dateStr,
                'y' => $commission ? (float) $commission->total_amount : 0,
                'count' => $commission ? (int) $commission->total_count : 0,
            ];

            $currentDate->addDay();
        }

        return $this->httpResponse()->setData([
            'commission_data' => $chartData,
        ]);
    }

    /**
     * Get conversion rate data for the conversion rate widget
     */
    public function getConversionRateWidget(Request $request)
    {
        $startDate = Carbon::now()->subDays(30);
        if ($request->input('start_date')) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $endDate = Carbon::now();
        if ($request->input('end_date')) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->input('end_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $affiliateId = $request->input('affiliate_id');

        // Calculate overall conversion rate
        $totalClicks = AffiliateClick::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->count();

        $totalConversions = AffiliateClick::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->where('converted', true)
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->count();

        $conversionRate = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;

        // Get conversion data by source (referrer)
        $conversionSources = [];
        $clicksByReferrer = AffiliateClick::query()
            ->select('referrer_url', DB::raw('count(*) as clicks'))
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->groupBy('referrer_url')
            ->orderBy('clicks', 'desc')
            ->limit(5)
            ->get();

        foreach ($clicksByReferrer as $source) {
            $sourceName = $source->referrer_url ? parse_url($source->referrer_url, PHP_URL_HOST) : 'Direct';

            $conversions = AffiliateClick::query()
                ->where('referrer_url', $source->referrer_url)
                ->where('converted', true)
                ->when($affiliateId, function ($query) use ($affiliateId) {
                    return $query->where('affiliate_id', $affiliateId);
                })
                ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
                ->count();

            $sourceRate = $source->clicks > 0 ? ($conversions / $source->clicks) * 100 : 0;

            $conversionSources[] = [
                'name' => $sourceName,
                'clicks' => $source->clicks,
                'conversions' => $conversions,
                'rate' => $sourceRate,
            ];
        }

        // Get conversion rate over time for chart
        $interval = $startDate->diffInDays($endDate) > 30 ? 'week' : 'day';
        $chartLabels = [];
        $chartData = [];

        if ($interval === 'day') {
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                $dayStart = clone $currentDate;
                $dayEnd = clone $currentDate->endOfDay();

                $dayClicks = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();

                $dayConversions = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->where('converted', true)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();

                $dayRate = $dayClicks > 0 ? ($dayConversions / $dayClicks) * 100 : 0;

                $chartLabels[] = $currentDate->format('M d');
                $chartData[] = $dayRate;

                $currentDate->addDay();
            }
        } else {
            $currentWeekStart = clone $startDate->startOfWeek();
            while ($currentWeekStart <= $endDate) {
                $weekEnd = clone $currentWeekStart->endOfWeek();
                if ($weekEnd > $endDate) {
                    $weekEnd = clone $endDate;
                }

                $weekClicks = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->whereBetween('created_at', [$currentWeekStart, $weekEnd])
                    ->count();

                $weekConversions = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->where('converted', true)
                    ->whereBetween('created_at', [$currentWeekStart, $weekEnd])
                    ->count();

                $weekRate = $weekClicks > 0 ? ($weekConversions / $weekClicks) * 100 : 0;

                $chartLabels[] = $currentWeekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                $chartData[] = $weekRate;

                $currentWeekStart->addWeek();
            }
        }

        return view('plugins/affiliate-pro::reports.widgets.conversion-rate', [
            'conversionRate' => $conversionRate,
            'conversionSources' => $conversionSources,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Get performance metrics data for the performance widget
     */
    public function getPerformanceMetricsWidget(Request $request)
    {
        $startDate = Carbon::now()->subDays(30);
        if ($request->input('start_date')) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $endDate = Carbon::now();
        if ($request->input('end_date')) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->input('end_date'));
            } catch (Exception $e) {
                // Keep default value if date parsing fails
            }
        }

        $affiliateId = $request->input('affiliate_id');

        // Get current period metrics
        $totalClicks = AffiliateClick::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->count();

        $totalConversions = AffiliateClick::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->where('converted', true)
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->count();

        $totalEarnings = Commission::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->sum('amount');

        $commissionCount = Commission::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->count();

        $averageCommission = $commissionCount > 0 ? $totalEarnings / $commissionCount : 0;

        // Get previous period metrics for comparison
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = (clone $startDate)->subDays($periodDays);
        $prevEndDate = (clone $startDate)->subDay();

        $prevClicks = AffiliateClick::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $prevConversions = AffiliateClick::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->where('converted', true)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $prevEarnings = Commission::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->where('status', 'approved')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        $prevCommissionCount = Commission::query()
            ->when($affiliateId, function ($query) use ($affiliateId) {
                return $query->where('affiliate_id', $affiliateId);
            })
            ->where('status', 'approved')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $prevAverageCommission = $prevCommissionCount > 0 ? $prevEarnings / $prevCommissionCount : 0;

        // Calculate percentage changes
        $clicksChange = $prevClicks > 0 ? (($totalClicks - $prevClicks) / $prevClicks) * 100 : 0;
        $conversionsChange = $prevConversions > 0 ? (($totalConversions - $prevConversions) / $prevConversions) * 100 : 0;
        $earningsChange = $prevEarnings > 0 ? (($totalEarnings - $prevEarnings) / $prevEarnings) * 100 : 0;
        $avgCommissionChange = $prevAverageCommission > 0 ? (($averageCommission - $prevAverageCommission) / $prevAverageCommission) * 100 : 0;

        // Get chart data
        $interval = $startDate->diffInDays($endDate) > 30 ? 'week' : 'day';
        $chartLabels = [];
        $chartClicks = [];
        $chartConversions = [];
        $chartEarnings = [];

        if ($interval === 'day') {
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                $dayStart = clone $currentDate;
                $dayEnd = clone $currentDate->endOfDay();

                $dayClicks = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();

                $dayConversions = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->where('converted', true)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();

                $dayEarnings = Commission::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->sum('amount');

                $chartLabels[] = $currentDate->format('M d');
                $chartClicks[] = $dayClicks;
                $chartConversions[] = $dayConversions;
                $chartEarnings[] = $dayEarnings;

                $currentDate->addDay();
            }
        } else {
            $currentWeekStart = clone $startDate->startOfWeek();
            while ($currentWeekStart <= $endDate) {
                $weekEnd = clone $currentWeekStart->endOfWeek();
                if ($weekEnd > $endDate) {
                    $weekEnd = clone $endDate;
                }

                $weekClicks = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->whereBetween('created_at', [$currentWeekStart, $weekEnd])
                    ->count();

                $weekConversions = AffiliateClick::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->where('converted', true)
                    ->whereBetween('created_at', [$currentWeekStart, $weekEnd])
                    ->count();

                $weekEarnings = Commission::query()
                    ->when($affiliateId, function ($query) use ($affiliateId) {
                        return $query->where('affiliate_id', $affiliateId);
                    })
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$currentWeekStart, $weekEnd])
                    ->sum('amount');

                $chartLabels[] = $currentWeekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                $chartClicks[] = $weekClicks;
                $chartConversions[] = $weekConversions;
                $chartEarnings[] = $weekEarnings;

                $currentWeekStart->addWeek();
            }
        }

        return view('plugins/affiliate-pro::reports.widgets.performance-metrics', [
            'totalClicks' => $totalClicks,
            'totalConversions' => $totalConversions,
            'totalEarnings' => $totalEarnings,
            'averageCommission' => $averageCommission,
            'clicksChange' => $clicksChange,
            'conversionsChange' => $conversionsChange,
            'earningsChange' => $earningsChange,
            'avgCommissionChange' => $avgCommissionChange,
            'chartLabels' => $chartLabels,
            'chartClicks' => $chartClicks,
            'chartConversions' => $chartConversions,
            'chartEarnings' => $chartEarnings,
        ]);
    }
}
