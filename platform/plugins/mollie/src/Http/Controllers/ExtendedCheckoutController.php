<?php

namespace Botble\Mollie\Http\Controllers;

use Botble\PosPro\Http\Controllers\CheckoutController as BaseCheckoutController;
use Botble\PosPro\Services\CartService;

class ExtendedCheckoutController extends BaseCheckoutController
{
    public function __construct(protected CartService $cartService)
    {
        parent::__construct($cartService);
    }

    /**
     * Map POS payment method to PaymentMethodEnum constant
     */
    protected function mapPaymentMethod(string $posMethod): string
    {
        return match ($posMethod) {
            'card' => POS_PRO_CARD_PAYMENT_METHOD_NAME,
            'other' => POS_PRO_OTHER_PAYMENT_METHOD_NAME,
            'mollie_terminal' => MOLLIE_TERMINAL_PAYMENT_METHOD_NAME,
            default => POS_PRO_CASH_PAYMENT_METHOD_NAME,
        };
    }
}