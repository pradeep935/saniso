<?php

namespace Botble\AffiliatePro\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Botble\AffiliatePro\Models\Affiliate;
use Illuminate\Support\Facades\URL;

class QrCodeService
{
    public function generateQrCode(string $affiliateCode, ?string $productSlug = null): string
    {
        $url = $this->generateAffiliateUrl($affiliateCode, $productSlug);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($url);

        return base64_encode($qrCode);
    }

    public function generateShortLinkQrCode(string $shortCode): string
    {
        $url = route('affiliate-pro.short-link.redirect', ['shortCode' => $shortCode]);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($url);

        return base64_encode($qrCode);
    }

    public function generateAffiliateUrl(string $affiliateCode, ?string $productSlug = null): string
    {
        if ($productSlug) {
            return URL::to("products/{$productSlug}") . "?aff={$affiliateCode}";
        }

        return URL::to("?aff={$affiliateCode}");
    }

    public function getAffiliateQrCode(Affiliate $affiliate, ?string $productSlug = null): string
    {
        return $this->generateQrCode($affiliate->affiliate_code, $productSlug);
    }
}
