<?php

namespace Botble\Mollie\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class PosProExtendedSettingRequest extends Request
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure pos_pro_active_payment_methods is an array
        if ($this->has('pos_pro_active_payment_methods') && !is_array($this->input('pos_pro_active_payment_methods'))) {
            $this->merge([
                'pos_pro_active_payment_methods' => [$this->input('pos_pro_active_payment_methods')]
            ]);
        }

        // If no payment methods are selected, set to empty array for proper validation
        if (!$this->has('pos_pro_active_payment_methods')) {
            $this->merge([
                'pos_pro_active_payment_methods' => []
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'pos_pro_enabled' => new OnOffRule(),
            'pos_pro_active_payment_methods' => ['required', 'array', 'min:1'],
            'pos_pro_active_payment_methods.*' => ['required', 'string', 'in:cash,card,other,mollie_terminal'],
            'pos_pro_default_payment_method' => [
                'required',
                'string',
                'in:cash,card,other,mollie_terminal',
                function ($attribute, $value, $fail) {
                    $activeMethods = $this->input('pos_pro_active_payment_methods', []);
                    if (! in_array($value, $activeMethods)) {
                        $fail(trans('validation.in_array', [
                            'attribute' => trans('plugins/pos-pro::pos.settings.default_payment_method'),
                            'other' => trans('plugins/pos-pro::pos.settings.active_payment_methods'),
                        ]));
                    }
                },
            ],
            'pos_pro_auto_apply_discount' => new OnOffRule(),
            'pos_pro_auto_add_shipping' => new OnOffRule(),
            'pos_pro_default_shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'pos_pro_remember_customer_selection' => new OnOffRule(),
            'pos_pro_print_receipt' => new OnOffRule(),
            'pos_pro_separate_vendor_orders' => new OnOffRule(),
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'pos_pro_active_payment_methods.*.in' => 'The selected payment method is invalid. Valid options are: cash, card, mollie_terminal, other.',
            'pos_pro_default_payment_method.in' => 'The selected default payment method is invalid. Valid options are: cash, card, mollie_terminal, other.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'pos_pro_active_payment_methods' => 'active payment methods',
            'pos_pro_default_payment_method' => 'default payment method',
        ];
    }
}