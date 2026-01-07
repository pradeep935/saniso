<?php

namespace Botble\AffiliatePro\Http\Controllers\Customers;

use Botble\AffiliatePro\Http\Requests\ShortLinkRequest;
use Botble\AffiliatePro\Models\AffiliateShortLink;
use Botble\AffiliatePro\Services\LinkShorteningService;
use Botble\AffiliatePro\Supports\AffiliateHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Auth;

class ShortLinkController extends BaseController
{
    protected LinkShorteningService $linkShorteningService;
    protected AffiliateHelper $affiliateHelper;

    public function __construct(
        LinkShorteningService $linkShorteningService,
        AffiliateHelper $affiliateHelper
    ) {
        $this->linkShorteningService = $linkShorteningService;
        $this->affiliateHelper = $affiliateHelper;

        $this->affiliateHelper->registerAssets();
    }

    /**
     * Display a listing of short links
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return redirect()->route('affiliate-pro.register')
                ->with('info', trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        SeoHelper::setTitle(trans('plugins/affiliate-pro::affiliate.short_links'));

        Theme::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.home'), route('public.index'))
            ->add(trans('plugins/affiliate-pro::affiliate.account'), route('customer.overview'))
            ->add(trans('plugins/affiliate-pro::affiliate.dashboard'), route('affiliate-pro.dashboard'))
            ->add(trans('plugins/affiliate-pro::affiliate.short_links'), route('affiliate-pro.short-links'));

        // Get all short links
        $shortLinks = $this->linkShorteningService->getAffiliateShortLinks($affiliate);

        return Theme::scope(
            'affiliate-pro::customers.short-links',
            compact('affiliate', 'shortLinks'),
            'plugins/affiliate-pro::themes.customers.short-links'
        )->render();
    }

    /**
     * Create a new short link
     */
    public function store(ShortLinkRequest $request, BaseHttpResponse $response)
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        $linkType = $request->input('link_type', 'custom');
        $title = $request->input('title');

        if ($linkType === 'product') {
            $productId = $request->input('product_id');
            $product = Product::query()->find($productId);

            if (! $product) {
                return $response
                    ->setError()
                    ->setMessage(trans('plugins/affiliate-pro::affiliate.product_not_found'));
            }

            $shortLink = $this->linkShorteningService->createProductShortLink($affiliate, $product, $title);
        } elseif ($linkType === 'homepage') {
            $shortLink = $this->linkShorteningService->createHomepageShortLink($affiliate, $title);
        } else {
            // Custom URL
            $destinationUrl = $request->input('destination_url');

            // Ensure the URL has the affiliate code
            if (strpos($destinationUrl, 'aff=') === false) {
                $separator = strpos($destinationUrl, '?') !== false ? '&' : '?';
                $destinationUrl .= $separator . 'aff=' . $affiliate->affiliate_code;
            }

            $shortLink = $this->linkShorteningService->createShortLink($affiliate, $destinationUrl, $title);
        }

        return $response
            ->setMessage(trans('plugins/affiliate-pro::affiliate.short_link_created_successfully'))
            ->setData([
                'short_link' => $shortLink,
                'short_url' => $shortLink->getShortUrl(),
            ]);
    }

    /**
     * Delete a short link
     */
    public function destroy(int $id, BaseHttpResponse $response)
    {
        $customer = Auth::guard('customer')->user();
        $affiliate = $this->affiliateHelper->getAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::affiliate.need_register_first'));
        }

        $shortLink = AffiliateShortLink::query()
            ->where('id', $id)
            ->where('affiliate_id', $affiliate->id)
            ->first();

        if (! $shortLink) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::affiliate.short_link_not_found'));
        }

        $shortLink->delete();

        return $response
            ->setMessage(trans('plugins/affiliate-pro::affiliate.short_link_deleted_successfully'));
    }
}
