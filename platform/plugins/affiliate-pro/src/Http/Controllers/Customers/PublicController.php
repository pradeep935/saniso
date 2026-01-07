<?php

namespace Botble\AffiliatePro\Http\Controllers\Customers;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Events\WithdrawalRequestedEvent;
use Botble\AffiliatePro\Facades\AffiliateHelper;
use Botble\AffiliatePro\Http\Requests\AffiliateRegisterRequest;
use Botble\AffiliatePro\Http\Requests\WithdrawalRequest;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateClick;
use Botble\AffiliatePro\Models\Commission;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\AffiliatePro\Services\AffiliateCouponService;
use Botble\AffiliatePro\Services\AffiliateTrackingService;
use Botble\AffiliatePro\Services\QrCodeService;
use Botble\AffiliatePro\Supports\AffiliateHelper as AffiliateHelperSupport;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PublicController extends BaseController
{
    protected AffiliateHelperSupport $affiliateHelper;
    protected AffiliateTrackingService $trackingService;
    protected AffiliateCouponService $couponService;
    protected QrCodeService $qrCodeService;

    public function __construct(
        AffiliateTrackingService $trackingService,
        AffiliateCouponService $couponService,
        QrCodeService $qrCodeService
    ) {
        $this->affiliateHelper = app('affiliate-helper');
        $this->trackingService = $trackingService;
        $this->couponService = $couponService;
        $this->qrCodeService = $qrCodeService;

        // Register front-end assets for all affiliate customer pages
        $this->affiliateHelper->registerAssets();
    }

    /**
     * Show the affiliate registration form
     */
    public function showRegisterForm()
    {
        // Check if registration is enabled in settings
        if (! AffiliateHelper::isRegistrationEnabled()) {
            return redirect()->route('customer.overview')
                ->with('error', trans('plugins/affiliate-pro::affiliate.registration_disabled'));
        }

        // Check if the customer is already an affiliate
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if ($affiliate) {
            return redirect()->route('affiliate-pro.dashboard')
                ->with('info', trans('plugins/affiliate-pro::affiliate.already_affiliate'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::affiliate.become_affiliate'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.become_affiliate'), route('affiliate-pro.register'));

        return Theme::scope(
            'affiliate-pro::customers.register',
            [],
            'plugins/affiliate-pro::themes.customers.register'
        )->render();
    }

    /**
     * Process the affiliate registration
     */
    public function register(AffiliateRegisterRequest $request, BaseHttpResponse $response)
    {
        // Check if registration is enabled in settings
        if (! AffiliateHelper::isRegistrationEnabled()) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::affiliate.registration_disabled'));
        }

        $customer = Auth::guard('customer')->user();

        // Check if the customer is already an affiliate
        $existingAffiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if ($existingAffiliate) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::affiliate.already_affiliate'));
        }

        // Create a new affiliate
        $affiliate = new Affiliate();
        $affiliate->customer_id = $customer->id;
        $affiliate->affiliate_code = Str::upper(Str::random(10));
        $affiliate->balance = 0;
        $affiliate->total_commission = 0;
        $affiliate->total_withdrawn = 0;

        // Set status based on auto-approval setting
        $affiliate->status = AffiliateHelper::isAutoApproveAffiliatesEnabled()
            ? AffiliateStatusEnum::APPROVED
            : AffiliateStatusEnum::PENDING;

        $affiliate->save();

        return $response
            ->setNextUrl(route('affiliate-pro.dashboard'))
            ->setMessage(
                AffiliateHelper::isAutoApproveAffiliatesEnabled()
                    ? trans('plugins/affiliate-pro::affiliate.application_approved')
                    : trans('plugins/affiliate-pro::affiliate.application_submitted')
            );
    }

    /**
     * Show the affiliate dashboard
     */
    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return redirect()->route('affiliate-pro.register')
                ->with('info', trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::affiliate.dashboard'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.dashboard'), route('affiliate-pro.dashboard'));

        // Get recent commissions
        $recentCommissions = Commission::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent withdrawals
        $recentWithdrawals = Withdrawal::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get current month and year
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // Get statistics
        $statistics = [
            'total_clicks' => $this->trackingService->getClicksCount($affiliate),
            'conversion_rate' => $this->trackingService->getConversionRate($affiliate),
            'this_month_clicks' => $this->trackingService->getClicksCount($affiliate, $startOfMonth, $endOfMonth),
            'this_month_conversions' => $this->trackingService->getConversionsCount(
                $affiliate,
                $startOfMonth,
                $endOfMonth
            ),
            'this_month_commission' => Commission::query()
                ->where('affiliate_id', $affiliate->id)
                ->whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->sum('amount'),
        ];

        return Theme::scope(
            'affiliate-pro::customers.dashboard',
            compact('affiliate', 'recentCommissions', 'recentWithdrawals', 'statistics'),
            'plugins/affiliate-pro::themes.customers.dashboard'
        )->render();
    }

    /**
     * Show the affiliate commissions history
     */
    public function commissions()
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return redirect()->route('affiliate-pro.register')
                ->with('info', trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::commission.history'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.dashboard'), route('affiliate-pro.dashboard'))
            ->add(trans('plugins/affiliate-pro::commission.history'), route('affiliate-pro.commissions'));

        // Get all commissions
        $commissions = Commission::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Theme::scope(
            'affiliate-pro::customers.commissions',
            compact('affiliate', 'commissions'),
            'plugins/affiliate-pro::themes.customers.commissions'
        )->render();
    }

    /**
     * Show the affiliate withdrawals page
     */
    public function withdrawals()
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return redirect()->route('affiliate-pro.register')
                ->with('info', trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::withdrawal.request'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.dashboard'), route('affiliate-pro.dashboard'))
            ->add(trans('plugins/affiliate-pro::withdrawal.request'), route('affiliate-pro.withdrawals'));

        // Get all withdrawals
        $withdrawals = Withdrawal::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Theme::scope(
            'affiliate-pro::customers.withdrawals',
            compact('affiliate', 'withdrawals'),
            'plugins/affiliate-pro::themes.customers.withdrawals'
        )->render();
    }

    /**
     * Process withdrawal request
     */
    public function storeWithdrawal(WithdrawalRequest $request, BaseHttpResponse $response)
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        // Check if affiliate is published
        if ($affiliate->status != AffiliateStatusEnum::APPROVED) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::withdrawal.account_not_approved'));
        }

        // Check minimum withdrawal amount
        $minimumAmount = $this->affiliateHelper->getMinimumWithdrawalAmount();
        if ($request->input('amount') < $minimumAmount) {
            return $response
                ->setError()
                ->setMessage(
                    trans(
                        'plugins/affiliate-pro::withdrawal.minimum_amount',
                        ['amount' => format_price($minimumAmount)]
                    )
                );
        }

        // Check if affiliate has enough balance
        if ($affiliate->balance < $request->input('amount')) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::withdrawal.insufficient_balance'));
        }

        try {
            \DB::beginTransaction();

            // Create withdrawal request
            $withdrawal = new Withdrawal();
            $withdrawal->affiliate_id = $affiliate->id;
            $withdrawal->customer_id = $customer->id;
            $withdrawal->amount = $request->input('amount');
            $withdrawal->status = 'pending';
            $withdrawal->payment_method = $request->input('payment_method');
            $withdrawal->payment_details = $request->input('payment_details');
            $withdrawal->currency = get_application_currency()->title;

            // Set payment channel based on payment method
            $paymentMethod = $request->input('payment_method');
            if (strtolower($paymentMethod) === 'paypal') {
                $withdrawal->payment_channel = 'paypal';
                $withdrawal->bank_info = ['paypal_id' => $request->input('payment_details')];
            } elseif (strtolower($paymentMethod) === 'stripe') {
                $withdrawal->payment_channel = 'stripe';
            } elseif (strtolower($paymentMethod) === 'bank transfer') {
                $withdrawal->payment_channel = 'bank_transfer';
                $withdrawal->bank_info = ['bank_info' => $request->input('payment_details')];
            }

            $withdrawal->save();

            // Update affiliate balance
            $affiliate->balance -= $request->input('amount');
            $affiliate->save();

            // Fire withdrawal requested event
            event(new WithdrawalRequestedEvent($customer, $withdrawal));

            \DB::commit();

            return $response
                ->setNextUrl(route('affiliate-pro.withdrawals'))
                ->setMessage(trans('plugins/affiliate-pro::withdrawal.request_submitted'));
        } catch (\Throwable $th) {
            \DB::rollBack();

            return $response
                ->setError()
                ->setMessage($th->getMessage());
        }
    }

    /**
     * Show promotional materials
     */
    public function materials()
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return redirect()->route('affiliate-pro.register')
                ->with('info', trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::affiliate.promotional_materials'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.dashboard'), route('affiliate-pro.dashboard'))
            ->add(trans('plugins/affiliate-pro::affiliate.promotional_materials'), route('affiliate-pro.materials'));

        $banners = $this->affiliateHelper->getPromotionalBanners($affiliate);

        $qrCode = $this->qrCodeService->getAffiliateQrCode($affiliate);

        return Theme::scope(
            'affiliate-pro::customers.materials',
            compact('affiliate', 'banners', 'qrCode'),
            'plugins/affiliate-pro::themes.customers.materials'
        )->render();
    }

    /**
     * Show detailed reports
     */
    public function reports(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return redirect()->route('affiliate-pro.register')
                ->with('info', trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::affiliate.affiliate_reports'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.dashboard'), route('affiliate-pro.dashboard'))
            ->add(trans('plugins/affiliate-pro::affiliate.affiliate_reports'), route('affiliate-pro.reports'));

        // Get date range from request or use default (last 30 days)
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $endDate->copy(
        )->subDays(30);

        // Ensure end date is not before start date
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy()->addDays(30);
        }

        // Add time to dates for proper querying
        $startDate->startOfDay();
        $endDate->endOfDay();

        // Get clicks for the date range
        $clicks = AffiliateClick::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get commissions for the date range
        $commissions = Commission::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get statistics
        $statistics = [
            'total_clicks' => $this->trackingService->getClicksCount($affiliate, $startDate, $endDate),
            'total_conversions' => $this->trackingService->getConversionsCount($affiliate, $startDate, $endDate),
            'conversion_rate' => $this->trackingService->getConversionRate($affiliate, $startDate, $endDate),
            'total_earnings' => Commission::query()
                ->where('affiliate_id', $affiliate->id)
                ->where('status', 'approved')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount'),
        ];

        // Generate chart data
        $daysDifference = $startDate->diffInDays($endDate);
        $interval = 'day';
        $format = 'M d';

        // If the date range is more than 30 days, group by week
        if ($daysDifference > 30) {
            $interval = 'week';
            $format = 'M d';
        }

        // If the date range is more than 90 days, group by month
        if ($daysDifference > 90) {
            $interval = 'month';
            $format = 'M Y';
        }

        $periods = $this->generateDatePeriods($startDate, $endDate, $interval);

        $chartDates = [];
        $chartClicks = [];
        $chartConversions = [];

        foreach ($periods as $period) {
            $periodStart = $period['start'];
            $periodEnd = $period['end'];

            $chartDates[] = $periodStart->format($format);
            $chartClicks[] = $this->trackingService->getClicksCount($affiliate, $periodStart, $periodEnd);
            $chartConversions[] = $this->trackingService->getConversionsCount($affiliate, $periodStart, $periodEnd);
        }

        $statistics['chart_dates'] = $chartDates;
        $statistics['chart_clicks'] = $chartClicks;
        $statistics['chart_conversions'] = $chartConversions;

        // Get geographic data
        $geoData = $this->trackingService->getGeographicData($affiliate, $startDate, $endDate);
        $statistics['geo_countries'] = $geoData['countries'];
        $statistics['geo_cities'] = $geoData['cities'];

        return Theme::scope(
            'affiliate-pro::customers.reports',
            compact('affiliate', 'clicks', 'commissions', 'statistics', 'startDate', 'endDate'),
            'plugins/affiliate-pro::themes.customers.reports'
        )->render();
    }

    /**
     * Show the coupon management page
     */
    public function coupons()
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return redirect()->route('affiliate-pro.register')
                ->with('info', trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::affiliate.affiliate_coupons'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.dashboard'), route('affiliate-pro.dashboard'))
            ->add(trans('plugins/affiliate-pro::affiliate.affiliate_coupons'), route('affiliate-pro.coupons'));

        $coupons = $this->couponService->getAffiliateCoupons($affiliate);

        return Theme::scope(
            'affiliate-pro::customers.coupons',
            compact('affiliate', 'coupons'),
            'plugins/affiliate-pro::themes.customers.coupons'
        )->render();
    }

    /**
     * Generate date periods for chart data
     */
    protected function generateDatePeriods(Carbon $startDate, Carbon $endDate, string $interval = 'day'): array
    {
        $periods = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $periodStart = $current->copy();

            if ($interval === 'day') {
                $periodEnd = $current->copy()->endOfDay();
                $current->addDay();
            } elseif ($interval === 'week') {
                $periodEnd = $current->copy()->addDays(6)->endOfDay();
                $current->addDays(7);
            } elseif ($interval === 'month') {
                $periodEnd = $current->copy()->endOfMonth();
                $current->addMonth()->startOfMonth();
            }

            // Ensure period end doesn't exceed the overall end date
            if ($periodEnd->gt($endDate)) {
                $periodEnd = $endDate->copy();
            }

            $periods[] = [
                'start' => $periodStart,
                'end' => $periodEnd,
            ];

            // Break if we've reached or passed the end date
            if ($current->gt($endDate)) {
                break;
            }
        }

        return $periods;
    }

    /**
     * Ajax search for products
     */
    public function ajaxSearchProducts(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return response()->json([
                'error' => true,
                'message' => trans('plugins/affiliate-pro::affiliate.need_register_first'),
            ], 403);
        }

        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $products = Product::query()
            ->wherePublished()
            ->where('is_variation', 0)
            ->when($search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->select('id', 'name', 'price', 'sale_price')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'text' => $product->name,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'formatted_price' => format_price($product->sale_price ?: $product->price),
                'url' => $product->url,
            ];
        });

        return response()->json([
            'results' => $formattedProducts,
            'pagination' => [
                'more' => $products->hasMorePages(),
            ],
        ]);
    }

    /**
     * Show the banned page
     */
    public function banned()
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return redirect()->route('customer.login');
        }

        $affiliate = Affiliate::where('customer_id', $customer->id)->first();

        // If affiliate doesn't exist or is not banned, redirect to dashboard
        if (! $affiliate || ! $affiliate->isBanned()) {
            return redirect()->route('affiliate-pro.dashboard');
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::affiliate.account_banned'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.account_banned'));

        return Theme::scope(
            'affiliate-pro::customers.banned',
            compact('affiliate'),
            'plugins/affiliate-pro::themes.customers.banned'
        )->render();
    }
}
