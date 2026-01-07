<?php

namespace Botble\AffiliatePro\Forms;

use Botble\AffiliatePro\Http\Requests\AffiliateShortLinkRequest;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateShortLink;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Models\Product;

class AffiliateShortLinkForm extends FormAbstract
{
    public function buildForm(): void
    {
        $affiliates = Affiliate::query()
            ->with('customer')
            ->get()
            ->pluck('customer.name', 'id')
            ->prepend(trans('core/base::forms.select_placeholder'), '');

        $products = Product::query()
            ->wherePublished()
            ->pluck('name', 'id')
            ->prepend(trans('plugins/affiliate-pro::short-link.all_products'), '');

        $this
            ->setupModel(new AffiliateShortLink())
            ->setValidatorClass(AffiliateShortLinkRequest::class)
            ->withCustomFields()
            ->add('affiliate_id', SelectField::class, [
                'label' => trans('plugins/affiliate-pro::short-link.affiliate'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => $affiliates,
                'attr' => [
                    'class' => 'form-control select-search-full',
                ],
            ])
            ->add('title', TextField::class, [
                'label' => trans('plugins/affiliate-pro::short-link.title'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder' => trans('plugins/affiliate-pro::short-link.title_placeholder'),
                ],
            ])
            ->add('short_code', TextField::class, [
                'label' => trans('plugins/affiliate-pro::short-link.short_code'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('plugins/affiliate-pro::short-link.short_code_placeholder'),
                    'data-counter' => 20,
                ],
                'help_block' => [
                    'text' => trans('plugins/affiliate-pro::short-link.short_code_help'),
                ],
            ])
            ->add('destination_url', TextField::class, [
                'label' => trans('plugins/affiliate-pro::short-link.destination_url'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => 'https://example.com/product/123',
                ],
                'help_block' => [
                    'text' => trans('plugins/affiliate-pro::short-link.destination_url_help'),
                ],
            ])
            ->add('product_id', SelectField::class, [
                'label' => trans('plugins/affiliate-pro::short-link.product'),
                'label_attr' => ['class' => 'control-label'],
                'choices' => $products,
                'attr' => [
                    'class' => 'form-control select-search-full',
                ],
                'help_block' => [
                    'text' => trans('plugins/affiliate-pro::short-link.product_help'),
                ],
            ])
            ->add('clicks', NumberField::class, [
                'label' => trans('plugins/affiliate-pro::short-link.clicks'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'min' => 0,
                    'readonly' => true,
                ],
                'default_value' => 0,
            ])
            ->add('conversions', NumberField::class, [
                'label' => trans('plugins/affiliate-pro::short-link.conversions'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'min' => 0,
                    'readonly' => true,
                ],
                'default_value' => 0,
            ])
            ->setBreakFieldPoint('destination_url');
    }
}
