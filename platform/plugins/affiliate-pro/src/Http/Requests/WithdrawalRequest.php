<?php

namespace Botble\AffiliatePro\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WithdrawalRequest extends Request
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_details' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => trans('plugins/affiliate-pro::withdrawal.amount_required'),
            'amount.numeric' => trans('plugins/affiliate-pro::withdrawal.amount_numeric'),
            'amount.min' => trans('plugins/affiliate-pro::withdrawal.amount_min'),
            'payment_method.required' => trans('plugins/affiliate-pro::withdrawal.payment_method_required'),
            'payment_details.required' => trans('plugins/affiliate-pro::withdrawal.payment_details_required'),
        ];
    }
}
