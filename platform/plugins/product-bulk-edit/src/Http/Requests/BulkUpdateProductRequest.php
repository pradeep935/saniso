<?php

namespace Botble\ProductBulkEdit\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class BulkUpdateProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'updates' => ['required', 'array', 'min:1'],
            'updates.*.id' => ['required', 'integer', 'exists:ec_products,id'],
            'updates.*.name' => ['sometimes', 'string', 'max:255'],
            'updates.*.sku' => ['sometimes', 'nullable', 'string', 'max:255'],
            'updates.*.price' => ['sometimes', 'numeric', 'min:0'],
            'updates.*.sale_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'updates.*.quantity' => ['sometimes', 'integer', 'min:0'],
            'updates.*.with_storehouse_management' => ['sometimes', 'boolean'],
            'updates.*.stock_status' => ['sometimes', Rule::in(StockStatusEnum::values())],
            'updates.*.status' => ['sometimes', Rule::in(BaseStatusEnum::values())],
            'updates.*.brand_id' => ['sometimes', 'nullable', 'integer', 'exists:ec_brands,id'],
            'updates.*.tax_id' => ['sometimes', 'nullable', 'integer', 'exists:ec_taxes,id'],
            'updates.*.category_ids' => ['sometimes', 'array'],
            'updates.*.category_ids.*' => ['integer', 'exists:ec_product_categories,id'],
            'updates.*.weight' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'updates.*.length' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'updates.*.wide' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'updates.*.height' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'updates.required' => trans('plugins/product-bulk-edit::product-bulk-edit.no_updates'),
            'updates.*.id.required' => 'Product ID is required',
            'updates.*.id.exists' => 'Product not found',
            'updates.*.price.min' => 'Price must be greater than or equal to 0',
            'updates.*.sale_price.min' => 'Sale price must be greater than or equal to 0',
            'updates.*.quantity.min' => 'Quantity must be greater than or equal to 0',
        ];
    }
}
