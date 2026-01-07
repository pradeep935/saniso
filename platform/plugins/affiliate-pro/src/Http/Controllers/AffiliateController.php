<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\AffiliatePro\Forms\AffiliateForm;
use Botble\AffiliatePro\Http\Requests\AffiliateRequest;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Tables\AffiliateTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Exception;
use Illuminate\Http\Request;

class AffiliateController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.all'), route('affiliate-pro.index'));
    }
    public function index(AffiliateTable $table)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::affiliate.name'));

        return $table->renderTable();
    }

    public function show(Affiliate $affiliate)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::affiliate.view_affiliate', ['name' => $affiliate->customer?->name ?: $affiliate->affiliate_code]));

        $affiliate->loadMissing(['customer', 'commissions', 'withdrawals', 'tracking']);

        return view('plugins/affiliate-pro::affiliates.show', compact('affiliate'));
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::affiliate.create'));

        return $formBuilder->create(AffiliateForm::class)->renderForm();
    }

    public function store(AffiliateRequest $request, BaseHttpResponse $response)
    {
        $data = $request->input();

        // Only allow commission_rate from admin panel
        if (! $request->user() || ! $request->user()->hasPermission('affiliate-pro.create')) {
            unset($data['commission_rate']);
        }

        $affiliate = Affiliate::query()->create($data);

        event(new CreatedContentEvent(AFFILIATE_PRO_MODULE_SCREEN_NAME, $request, $affiliate));

        return $response
            ->setPreviousUrl(route('affiliate-pro.index'))
            ->setNextUrl(route('affiliate-pro.edit', $affiliate))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Affiliate $affiliate, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::affiliate.edit', ['name' => $affiliate->customer?->name ?: $affiliate->affiliate_code]));

        return $formBuilder->create(AffiliateForm::class, ['model' => $affiliate])->renderForm();
    }

    public function update(Affiliate $affiliate, AffiliateRequest $request, BaseHttpResponse $response)
    {
        $data = $request->input();

        // Only allow commission_rate update from admin panel
        if (! $request->user() || ! $request->user()->hasPermission('affiliate-pro.edit')) {
            unset($data['commission_rate']);
        }

        $affiliate->fill($data);
        $affiliate->save();

        event(new UpdatedContentEvent(AFFILIATE_PRO_MODULE_SCREEN_NAME, $request, $affiliate));

        return $response
            ->setPreviousUrl(route('affiliate-pro.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Affiliate $affiliate, Request $request, BaseHttpResponse $response)
    {
        try {
            $affiliate->delete();

            event(new DeletedContentEvent(AFFILIATE_PRO_MODULE_SCREEN_NAME, $request, $affiliate));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function ban(Affiliate $affiliate, BaseHttpResponse $response)
    {
        $affiliate->ban();

        // Send email notification to the affiliate
        if ($affiliate->customer && $affiliate->customer->email) {
            EmailHandler::setModule(AFFILIATE_PRO_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $affiliate->customer->name,
                    'customer_email' => $affiliate->customer->email,
                    'site_title' => theme_option('site_title'),
                ])
                ->sendUsingTemplate('affiliate-banned', $affiliate->customer->email);
        }

        return $response
            ->setPreviousUrl(route('affiliate-pro.index'))
            ->setMessage(trans('plugins/affiliate-pro::affiliate.ban_success'));
    }

    public function unban(Affiliate $affiliate, BaseHttpResponse $response)
    {
        $affiliate->unban();

        // Send email notification to the affiliate
        if ($affiliate->customer && $affiliate->customer->email) {
            EmailHandler::setModule(AFFILIATE_PRO_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $affiliate->customer->name,
                    'customer_email' => $affiliate->customer->email,
                    'affiliate_dashboard_url' => route('affiliate-pro.dashboard'),
                    'site_title' => theme_option('site_title'),
                ])
                ->sendUsingTemplate('affiliate-unbanned', $affiliate->customer->email);
        }

        return $response
            ->setPreviousUrl(route('affiliate-pro.index'))
            ->setMessage(trans('plugins/affiliate-pro::affiliate.unban_success'));
    }
}
