<?php

namespace Botble\AffiliatePro\Http\Requests;

use Botble\Support\Http\Requests\Request;

class AffiliateShortLinkRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'affiliate_id' => ['required', 'integer', 'exists:affiliates,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'short_code' => ['required', 'string', 'max:20', 'alpha_dash'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'clicks' => ['nullable', 'integer', 'min:0'],
            'conversions' => ['nullable', 'integer', 'min:0'],
        ];

        // Add unique rule for short_code, excluding current record if updating
        if ($this->route('short_link')) {
            $rules['short_code'][] = 'unique:affiliate_short_links,short_code,' . $this->route('short_link')->id;
        } else {
            $rules['short_code'][] = 'unique:affiliate_short_links,short_code';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'affiliate_id.required' => trans('plugins/affiliate-pro::short-link.affiliate_required'),
            'affiliate_id.exists' => trans('plugins/affiliate-pro::short-link.affiliate_not_exists'),
            'short_code.required' => trans('plugins/affiliate-pro::short-link.short_code_required'),
            'short_code.unique' => trans('plugins/affiliate-pro::short-link.short_code_unique'),
            'short_code.alpha_dash' => trans('plugins/affiliate-pro::short-link.short_code_alpha_dash'),
            'destination_url.required' => trans('plugins/affiliate-pro::short-link.destination_url_required'),
            'destination_url.url' => trans('plugins/affiliate-pro::short-link.destination_url_invalid'),
            'product_id.exists' => trans('plugins/affiliate-pro::short-link.product_not_exists'),
        ];
    }

    public function attributes(): array
    {
        return [
            'affiliate_id' => trans('plugins/affiliate-pro::short-link.affiliate'),
            'title' => trans('plugins/affiliate-pro::short-link.title'),
            'short_code' => trans('plugins/affiliate-pro::short-link.short_code'),
            'destination_url' => trans('plugins/affiliate-pro::short-link.destination_url'),
            'product_id' => trans('plugins/affiliate-pro::short-link.product'),
            'clicks' => trans('plugins/affiliate-pro::short-link.clicks'),
            'conversions' => trans('plugins/affiliate-pro::short-link.conversions'),
        ];
    }
}
