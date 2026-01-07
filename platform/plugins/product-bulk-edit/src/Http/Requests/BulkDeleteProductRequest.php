<?php

namespace Botble\ProductBulkEdit\Http\Requests;

use Botble\Support\Http\Requests\Request;

class BulkDeleteProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:ec_products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => trans('plugins/product-bulk-edit::product-bulk-edit.no_products_selected'),
            'ids.min' => trans('plugins/product-bulk-edit::product-bulk-edit.no_products_selected'),
        ];
    }
}
