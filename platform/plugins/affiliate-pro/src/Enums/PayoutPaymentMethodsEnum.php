<?php

namespace Botble\AffiliatePro\Enums;

use Botble\AffiliatePro\Facades\AffiliateHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

/**
 * @method static PayoutPaymentMethodsEnum BANK_TRANSFER()
 * @method static PayoutPaymentMethodsEnum PAYPAL()
 * @method static PayoutPaymentMethodsEnum STRIPE()
 * @method static PayoutPaymentMethodsEnum OTHER()
 */
class PayoutPaymentMethodsEnum extends Enum
{
    public const BANK_TRANSFER = 'bank_transfer';
    public const PAYPAL = 'paypal';
    public const STRIPE = 'stripe';
    public const OTHER = 'other';

    public static $langPath = 'plugins/affiliate-pro::withdrawal.payout_payment_methods';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::BANK_TRANSFER => BaseHelper::renderBadge(trans('plugins/affiliate-pro::withdrawal.bank_transfer')),
            self::PAYPAL => BaseHelper::renderBadge(trans('plugins/affiliate-pro::withdrawal.paypal'), 'info'),
            self::STRIPE => BaseHelper::renderBadge(trans('plugins/affiliate-pro::withdrawal.stripe'), 'primary'),
            self::OTHER => BaseHelper::renderBadge(trans('plugins/affiliate-pro::withdrawal.other'), 'secondary'),
            default => parent::toHtml(),
        };
    }

    public static function payoutMethodsEnabled(): array
    {
        $data = [
            self::BANK_TRANSFER => [
                'is_enabled' => (bool) Arr::get(AffiliateHelper::getSetting('payout_methods'), self::BANK_TRANSFER, true),
                'key' => self::BANK_TRANSFER,
                'label' => self::BANK_TRANSFER()->label(),
                'fields' => [
                    'bank_info' => [
                        'title' => trans('plugins/affiliate-pro::withdrawal.bank_information'),
                        'rules' => 'max:500',
                    ],
                ],
            ],
            self::PAYPAL => [
                'is_enabled' => (bool) Arr::get(AffiliateHelper::getSetting('payout_methods'), self::PAYPAL, true),
                'key' => self::PAYPAL,
                'label' => self::PAYPAL()->label(),
                'fields' => [
                    'paypal_id' => [
                        'title' => trans('plugins/affiliate-pro::withdrawal.paypal_id'),
                        'rules' => 'max:120',
                    ],
                ],
            ],
            self::OTHER => [
                'is_enabled' => (bool) Arr::get(AffiliateHelper::getSetting('payout_methods'), self::OTHER, true),
                'key' => self::OTHER,
                'label' => self::OTHER()->label(),
                'fields' => [
                    'payment_details' => [
                        'title' => trans('plugins/affiliate-pro::withdrawal.payment_details'),
                        'rules' => 'max:500',
                    ],
                ],
            ],
        ];

        return apply_filters('affiliate_pro_payout_methods', $data);
    }
}
