<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\AffiliatePro\Forms\AffiliateShortLinkForm;
use Botble\AffiliatePro\Http\Requests\AffiliateShortLinkRequest;
use Botble\AffiliatePro\Models\AffiliateShortLink;
use Botble\AffiliatePro\Tables\AffiliateShortLinkTable;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Exception;
use Illuminate\Http\Request;

class AffiliateShortLinkController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::short-link.name'), route('affiliate-pro.short-links.index'));
    }
    public function index(AffiliateShortLinkTable $table)
    {
        PageTitle::setTitle(trans('plugins/affiliate-pro::short-link.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/affiliate-pro::short-link.create'));

        return $formBuilder->create(AffiliateShortLinkForm::class)->renderForm();
    }

    public function store(AffiliateShortLinkRequest $request, BaseHttpResponse $response)
    {
        $shortLink = AffiliateShortLink::query()->create($request->validated());

        event(new CreatedContentEvent(AFFILIATE_SHORT_LINK_MODULE_SCREEN_NAME, $request, $shortLink));

        return $response
            ->setNextUrl(route('affiliate-pro.short-links.edit', $shortLink->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function show(AffiliateShortLink $shortLink)
    {
        PageTitle::setTitle($shortLink->title ?: trans('plugins/affiliate-pro::short-link.short_link_details'));

        return view('plugins/affiliate-pro::short-links.show', compact('shortLink'));
    }

    public function edit(AffiliateShortLink $shortLink, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $shortLink));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $shortLink->title ?: $shortLink->short_code]));

        return $formBuilder->create(AffiliateShortLinkForm::class, ['model' => $shortLink])->setMethod('PUT')->renderForm();
    }

    public function update(AffiliateShortLink $shortLink, AffiliateShortLinkRequest $request, BaseHttpResponse $response)
    {
        $shortLink->fill($request->validated());
        $shortLink->save();

        event(new UpdatedContentEvent(AFFILIATE_SHORT_LINK_MODULE_SCREEN_NAME, $request, $shortLink));

        return $response
            ->setNextUrl(route('affiliate-pro.short-links.edit', $shortLink->getKey()))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(AffiliateShortLink $shortLink, Request $request, BaseHttpResponse $response)
    {
        try {
            $shortLink->delete();

            event(new DeletedContentEvent(AFFILIATE_SHORT_LINK_MODULE_SCREEN_NAME, $request, $shortLink));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
