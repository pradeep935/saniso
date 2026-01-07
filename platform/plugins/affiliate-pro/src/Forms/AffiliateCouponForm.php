<?php

namespace Botble\AffiliatePro\Forms;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Http\Requests\AffiliateCouponRequest;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateCoupon;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Carbon\Carbon;

class AffiliateCouponForm extends FormAbstract
{
    public function setup(): void
    {
        Assets::addScriptsDirectly('vendor/core/plugins/affiliate-pro/js/affiliate-coupon.js');

        $affiliates = [];

        foreach (Affiliate::query()
                     ->where('status', AffiliateStatusEnum::APPROVED)
                     ->with('customer')
                     ->get() as $affiliate) {
            $affiliates[$affiliate->id] = $affiliate->customer->name . ' (' . $affiliate->affiliate_code . ')';
        }

        $this
            ->model(AffiliateCoupon::class)
            ->setValidatorClass(AffiliateCouponRequest::class);

        // Add a read-only field to display the coupon code when editing
        if ($this->getModel()->id) {
            $this->add(
                'code',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::coupon.code'))
                    ->disabled()
            );
        }

        $this
            ->add(
                'affiliate_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::coupon.affiliate'))
                    ->required()
                    ->choices($affiliates)
                    ->searchable()
            )
            ->add(
                'discount_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::coupon.discount_type'))
                    ->required()
                    ->choices([
                        'percentage' => trans('plugins/affiliate-pro::coupon.percentage'),
                        'fixed' => trans('plugins/affiliate-pro::coupon.fixed'),
                    ])
                    ->selected($this->getModel()->id ? $this->getModel()->discount_type : 'percentage')
                    ->attributes([
                        'id' => 'discount-type',
                    ])
            )
            ->add(
                'discount_amount',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::coupon.discount_amount'))
                    ->required()
                    ->placeholder(trans('plugins/affiliate-pro::coupon.discount_amount'))
                    ->min(1)
                    ->max($this->getModel()->discount_type === 'fixed' ? 1000000 : 100)
                    ->attributes([
                        'id' => 'discount-amount',
                    ])
            )
            ->add(
                'description',
                TextField::class,
                DescriptionFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::coupon.description'))
                    ->maxLength(255)
            )
            ->add(
                'expires_at',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::coupon.expires_at'))
                    ->defaultValue($this->getModel()->expires_at
                        ? $this->getModel()->expires_at
                        : Carbon::now()->addDays(30)->toDateString())
            )
            ->setBreakFieldPoint('expires_at');
    }
}
