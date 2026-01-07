<?php

namespace Botble\AffiliatePro\Http\Requests;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AffiliateRequest extends Request
{
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:ec_customers,id',
            'affiliate_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('affiliates')->ignore($this->route('affiliate')),
            ],
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'balance' => 'nullable|numeric|min:0',
            'total_commission' => 'nullable|numeric|min:0',
            'total_withdrawn' => 'nullable|numeric|min:0',
            'status' => 'required|' . Rule::in(AffiliateStatusEnum::values()),
        ];
    }
}
