<?php

namespace Botble\AffiliatePro\Http\Requests;

use Botble\Support\Http\Requests\Request;

class AffiliateCouponRequest extends Request
{
    public function rules(): array
    {
        return [
            'affiliate_id' => 'required|exists:affiliates,id',
            'discount_amount' => 'required|numeric|min:1|max:' . ($this->input('discount_type') === 'percentage' ? '100' : '1000000'),
            'discount_type' => 'required|in:percentage,fixed',
            'description' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:today',
        ];
    }
}
