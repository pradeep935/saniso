<?php

namespace Botble\AffiliatePro\Forms;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Http\Requests\AffiliateRequest as AffiliateHttpRequest;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Models\Customer;

class AffiliateRequest extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->model(Affiliate::class)
            ->setValidatorClass(AffiliateHttpRequest::class)
            ->withCustomFields()
            ->add('customer_id', 'customSelect', [
                'label' => trans('plugins/affiliate-pro::affiliate.customer'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-search-full',
                ],
                'choices' => Customer::query()->pluck('name', 'id')->toArray(),
            ])
            ->add('affiliate_code', 'text', [
                'label' => trans('plugins/affiliate-pro::affiliate.affiliate_code'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/affiliate-pro::affiliate.affiliate_code'),
                    'data-counter' => 100,
                ],
            ])
            ->add('balance', 'number', [
                'label' => trans('plugins/affiliate-pro::affiliate.balance'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/affiliate-pro::affiliate.balance'),
                    'step' => 0.01,
                ],
                'default_value' => 0,
            ])
            ->add('total_commission', 'number', [
                'label' => trans('plugins/affiliate-pro::affiliate.total_commission'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/affiliate-pro::affiliate.total_commission'),
                    'step' => 0.01,
                ],
                'default_value' => 0,
            ])
            ->add('total_withdrawn', 'number', [
                'label' => trans('plugins/affiliate-pro::affiliate.total_withdrawn'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/affiliate-pro::affiliate.total_withdrawn'),
                    'step' => 0.01,
                ],
                'default_value' => 0,
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => AffiliateStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
