<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\AffiliatePro\Services\LinkShorteningService;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Illuminate\Http\Request;

class ShortLinkController extends BaseController
{
    public function __construct(protected LinkShorteningService $linkShorteningService)
    {
    }

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::short-link.name'));
    }

    public function redirect(string $shortCode, Request $request, BaseHttpResponse $response)
    {
        $shortLink = $this->linkShorteningService->findByShortCode($shortCode);

        if (! $shortLink) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/affiliate-pro::affiliate.short_link_not_found'))
                ->setNextUrl(url('/'));
        }

        // Track the click with the short link ID
        $this->linkShorteningService->trackClick($shortLink, $request);

        // Redirect to the destination URL
        return redirect()->away($shortLink->destination_url);
    }
}
