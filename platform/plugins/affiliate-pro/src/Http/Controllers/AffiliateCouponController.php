<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\AffiliatePro\Forms\AffiliateCouponForm;
use Botble\AffiliatePro\Http\Requests\AffiliateCouponRequest;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateCoupon;
use Botble\AffiliatePro\Services\AffiliateCouponService;
use Botble\AffiliatePro\Tables\AffiliateCouponTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class AffiliateCouponController extends BaseController
{
    public function __construct(protected AffiliateCouponService $couponService)
    {
    }

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::coupon.name'), route('affiliate-pro.coupons.index'));
    }

    public function index(AffiliateCouponTable $table)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::coupon.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::coupon.create_new'));

        return $formBuilder->create(AffiliateCouponForm::class)->renderForm();
    }

    public function store(AffiliateCouponRequest $request, BaseHttpResponse $response)
    {
        $data = $request->validated();

        $affiliate = Affiliate::query()->findOrFail($data['affiliate_id']);
        $discountAmount = (float) $data['discount_amount'];
        $discountType = $data['discount_type'];
        $description = $data['description'] ?? null;
        $expiresAt = isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null;

        $coupon = $this->couponService->createCoupon(
            $affiliate,
            $discountAmount,
            $discountType,
            $description,
            $expiresAt
        );

        event(new CreatedContentEvent(AFFILIATE_PRO_MODULE_SCREEN_NAME, $request, $coupon));

        return $response
            ->setPreviousUrl(route('affiliate-pro.coupons.index'))
            ->setNextUrl(route('affiliate-pro.coupons.show', $coupon->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function show(int|string $id)
    {
        $coupon = AffiliateCoupon::query()->findOrFail($id);

        $this->pageTitle(trans('plugins/affiliate-pro::coupon.view', ['code' => $coupon->code]));

        return view('plugins/affiliate-pro::coupons.show', compact('coupon'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder)
    {
        $coupon = AffiliateCoupon::query()->findOrFail($id);

        $this->pageTitle(trans('plugins/affiliate-pro::coupon.edit', ['code' => $coupon->code]));

        return $formBuilder->create(AffiliateCouponForm::class, ['model' => $coupon])->setMethod('PUT')->renderForm();
    }

    public function update(int|string $id, AffiliateCouponRequest $request, BaseHttpResponse $response)
    {
        $coupon = AffiliateCoupon::query()->findOrFail($id);
        $data = $request->validated();

        $affiliate = Affiliate::query()->findOrFail($data['affiliate_id']);
        $discountAmount = (float) $data['discount_amount'];
        $discountType = $data['discount_type'];
        $description = $data['description'] ?? null;
        $expiresAt = isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null;

        // Code is not editable in the form, so we pass the existing code
        $this->couponService->updateCoupon(
            $coupon,
            $discountAmount,
            $discountType,
            $description,
            $expiresAt
        );

        event(new UpdatedContentEvent(AFFILIATE_PRO_MODULE_SCREEN_NAME, $request, $coupon));

        return $response
            ->setNextUrl(route('affiliate-pro.coupons.edit', $coupon->id))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $coupon = AffiliateCoupon::query()->findOrFail($id);

            $this->couponService->deleteCoupon($coupon);

            event(new DeletedContentEvent(AFFILIATE_PRO_MODULE_SCREEN_NAME, $request, $coupon));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function bulkActions(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response->setError()->setMessage(trans('core/base::notices.no_select'));
        }

        $action = $request->input('action');
        if ($action === 'delete') {
            $count = 0;
            foreach ($ids as $id) {
                $coupon = AffiliateCoupon::query()->findOrFail($id);
                $this->couponService->deleteCoupon($coupon);
                event(new DeletedContentEvent(AFFILIATE_PRO_MODULE_SCREEN_NAME, $request, $coupon));
                $count++;
            }

            return $response->setMessage(trans('core/base::notices.delete_success_message', ['count' => $count]));
        }

        return $response->setError()->setMessage(trans('core/base::notices.invalid_action'));
    }
}
