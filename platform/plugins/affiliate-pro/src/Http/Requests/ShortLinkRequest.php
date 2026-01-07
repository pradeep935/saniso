<?php

namespace Botble\AffiliatePro\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ShortLinkRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'link_type' => 'required|in:custom,product,homepage',
            'title' => 'nullable|string|max:255',
        ];

        if ($this->input('link_type') === 'product') {
            $rules['product_id'] = 'required|exists:ec_products,id';
        } elseif ($this->input('link_type') === 'custom') {
            $rules['destination_url'] = 'required|url';
        }

        return $rules;
    }
}
