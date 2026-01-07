<?php

namespace Botble\AffiliatePro\Services;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateClick;
use Botble\AffiliatePro\Models\AffiliateShortLink;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkShorteningService
{
    /**
     * Generate a unique short code
     */
    public function generateUniqueShortCode(int $length = 6): string
    {
        $shortCode = Str::random($length);

        // Check if code already exists
        while (AffiliateShortLink::query()->where('short_code', $shortCode)->exists()) {
            $shortCode = Str::random($length);
        }

        return $shortCode;
    }

    /**
     * Create a short link for an affiliate
     */
    public function createShortLink(
        Affiliate $affiliate,
        string $destinationUrl,
        ?string $title = null,
        ?int $productId = null
    ): AffiliateShortLink {
        // Generate a unique short code
        $shortCode = $this->generateUniqueShortCode();

        // Create the short link
        return AffiliateShortLink::query()->create([
            'affiliate_id' => $affiliate->id,
            'short_code' => $shortCode,
            'destination_url' => $destinationUrl,
            'title' => $title,
            'product_id' => $productId,
            'clicks' => 0,
            'conversions' => 0,
        ]);
    }

    /**
     * Create a short link for a specific product
     */
    public function createProductShortLink(
        Affiliate $affiliate,
        Product $product,
        ?string $title = null
    ): AffiliateShortLink {
        // Generate the destination URL with affiliate code
        $destinationUrl = $product->url . '?aff=' . $affiliate->affiliate_code;

        // Use product name as title if not provided
        $title = $title ?: $product->name;

        return $this->createShortLink($affiliate, $destinationUrl, $title, $product->id);
    }

    /**
     * Create a short link for the homepage
     */
    public function createHomepageShortLink(
        Affiliate $affiliate,
        ?string $title = null
    ): AffiliateShortLink {
        // Generate the destination URL with affiliate code
        $destinationUrl = url('/?aff=' . $affiliate->affiliate_code);

        // Use default title if not provided
        $title = $title ?: 'Homepage';

        return $this->createShortLink($affiliate, $destinationUrl, $title);
    }

    /**
     * Get all short links for an affiliate
     */
    public function getAffiliateShortLinks(Affiliate $affiliate)
    {
        return AffiliateShortLink::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find a short link by its code
     */
    public function findByShortCode(string $shortCode): ?AffiliateShortLink
    {
        return AffiliateShortLink::query()
            ->where('short_code', $shortCode)
            ->first();
    }

    /**
     * Track a click on a short link
     */
    public function trackClick(AffiliateShortLink $shortLink, Request $request): void
    {
        // Increment the click count on the short link
        $shortLink->incrementClicks();

        // Get location data from IP
        $locationData = $this->getLocationFromIp($request->ip());

        // Record the click with the short link ID
        AffiliateClick::query()->create([
            'affiliate_id' => $shortLink->affiliate_id,
            'short_link_id' => $shortLink->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer_url' => $request->header('referer'),
            'landing_url' => $shortLink->destination_url,
            'country' => $locationData['country'] ?? null,
            'city' => $locationData['city'] ?? null,
        ]);
    }

    /**
     * Get location data from IP address
     * This is a simplified version for demonstration purposes
     */
    protected function getLocationFromIp(?string $ip): array
    {
        // In a real implementation, you would use a geolocation service
        // For now, we'll return random data for demonstration
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

    /**
     * Track a conversion from a short link
     */
    public function trackConversion(AffiliateShortLink $shortLink): void
    {
        $shortLink->incrementConversions();
    }
}
