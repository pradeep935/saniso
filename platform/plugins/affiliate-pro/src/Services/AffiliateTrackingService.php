<?php

namespace Botble\AffiliatePro\Services;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateClick;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AffiliateTrackingService
{
    protected const COOKIE_NAME = 'affiliate_code';

    public function handleAffiliateTracking(Request $request, string $affiliateCode): void
    {
        $affiliate = Affiliate::query()
            ->where('affiliate_code', $affiliateCode)
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->first();

        if (! $affiliate) {
            return;
        }

        $cookieLifetime = app('affiliate-helper')->getCookieLifetime();
        Cookie::queue(self::COOKIE_NAME, $affiliateCode, $cookieLifetime * 1440);

        $this->recordClick($request, $affiliate);
    }

    public function recordClick(Request $request, Affiliate $affiliate): AffiliateClick
    {
        $locationData = $this->getLocationFromIp($request->ip());

        return AffiliateClick::query()->create([
            'affiliate_id' => $affiliate->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer_url' => $request->header('referer'),
            'landing_url' => $request->fullUrl(),
            'country' => $locationData['country'] ?? null,
            'city' => $locationData['city'] ?? null,
        ]);
    }

    protected function getLocationFromIp(string $ip): array
    {
        // For demo purposes, we'll return random countries
        // In a real application, you would use a service like MaxMind GeoIP or ipinfo.io
        $countries = [
            'United States' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'],
            'United Kingdom' => ['London', 'Manchester', 'Birmingham', 'Glasgow', 'Liverpool'],
            'Canada' => ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Ottawa'],
            'Australia' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide'],
            'Germany' => ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt'],
            'France' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice'],
            'Japan' => ['Tokyo', 'Osaka', 'Kyoto', 'Yokohama', 'Nagoya'],
        ];

        $country = array_rand($countries);
        $cities = $countries[$country];
        $city = $cities[array_rand($cities)];

        return [
            'country' => $country,
            'city' => $city,
        ];
    }

    public function getAffiliateFromCookie(): ?Affiliate
    {
        $affiliateCode = Cookie::get(self::COOKIE_NAME);

        if (! $affiliateCode) {
            return null;
        }

        return Affiliate::query()
            ->where('affiliate_code', $affiliateCode)
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->first();
    }

    public function markClicksAsConverted(Affiliate $affiliate): void
    {
        AffiliateClick::query()
            ->where('affiliate_id', $affiliate->id)
            ->where('converted', false)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->update([
                'converted' => true,
                'conversion_time' => Carbon::now(),
            ]);
    }

    public function getClicksCount(Affiliate $affiliate, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = AffiliateClick::query()->where('affiliate_id', $affiliate->id);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->count();
    }

    public function getConversionsCount(Affiliate $affiliate, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = AffiliateClick::query()
            ->where('affiliate_id', $affiliate->id)
            ->where('converted', true);

        if ($startDate) {
            $query->where('conversion_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('conversion_time', '<=', $endDate);
        }

        return $query->count();
    }

    public function getConversionRate(Affiliate $affiliate, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $clicks = $this->getClicksCount($affiliate, $startDate, $endDate);

        if ($clicks === 0) {
            return 0;
        }

        $conversions = $this->getConversionsCount($affiliate, $startDate, $endDate);

        return round(($conversions / $clicks) * 100, 2);
    }

    /**
     * Get geographic data for clicks
     */
    public function getGeographicData(Affiliate $affiliate, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = AffiliateClick::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereNotNull('country');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $clicks = $query->get();

        $countries = [];
        $cities = [];

        foreach ($clicks as $click) {
            $country = $click->country;
            $city = $click->city;

            if ($country) {
                if (! isset($countries[$country])) {
                    $countries[$country] = 0;
                }
                $countries[$country]++;
            }

            if ($city && $country) {
                $cityKey = $city . ', ' . $country;
                if (! isset($cities[$cityKey])) {
                    $cities[$cityKey] = 0;
                }
                $cities[$cityKey]++;
            }
        }

        // Sort by count in descending order
        arsort($countries);
        arsort($cities);

        // Limit to top 10
        $countries = array_slice($countries, 0, 10, true);
        $cities = array_slice($cities, 0, 10, true);

        return [
            'countries' => $countries,
            'cities' => $cities,
        ];
    }
}
