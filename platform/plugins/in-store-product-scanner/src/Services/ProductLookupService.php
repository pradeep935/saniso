<?php

namespace Platform\InStoreProductScanner\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ProductLookupService
{
    /**
     * Find product by barcode, SKU or meta key depending on config
     * Returns structured array ready for JSON response
     */
    public function findByCode(string $code): ?array
    {
        $barcodeField = Config::get('scanner.barcode_field', 'barcode');

        // Attempt direct barcode field
        $product = $this->findByField($barcodeField, $code);

        // Fallback SKU
        if (! $product) {
            $product = $this->findByField('sku', $code);
        }

        // Fallback custom meta
        if (! $product) {
            $metaKey = Config::get('scanner.custom_meta_key');
            if ($metaKey) {
                $product = $this->findByMeta($metaKey, $code);
            }
        }

        if (! $product) {
            return null;
        }

        return $this->formatProduct($product);
    }

    protected function findByField(string $field, string $code)
    {
        // Use Botble Ecommerce Product model if available
        if (class_exists('\\Botble\\Ecommerce\\Models\\Product')) {
            $model = '\\Botble\\Ecommerce\\Models\\Product';
            $query = $model::with(['variations', 'variations.attributes', 'slugable', 'productCollections'])->where($field, $code);
            return $query->first();
        }

        // Generic fallback to products table
        return DB::table('products')->where($field, $code)->first();
    }

    protected function findByMeta(string $metaKey, string $code)
    {
        if (class_exists('\\Botble\\Ecommerce\\Models\\Product')) {
            $model = '\\Botble\\Ecommerce\\Models\\Product';
            return $model::whereHas('metadata', function ($q) use ($metaKey, $code) {
                $q->where('meta_key', $metaKey)->where('meta_value', $code);
            })->with(['variations'])->first();
        }

        return null;
    }

    protected function formatProduct($product): array
    {
        // If product is a stdObject from DB, map minimally
        if (is_object($product) && ! method_exists($product, 'toArray')) {
            return [
                'id' => $product->id ?? null,
                'name' => $product->name ?? null,
                'sku' => $product->sku ?? null,
                'barcode' => $product->{Config::get('scanner.barcode_field', 'barcode')} ?? null,
                'price' => $product->price ?? null,
                'sale_price' => $product->sale_price ?? null,
                'in_stock' => $product->quantity ?? null,
            ];
        }

        $data = $product->toArray();

        $variants = [];
        if (isset($data['variations']) && is_array($data['variations'])) {
            foreach ($data['variations'] as $v) {
                $variants[] = [
                    'id' => $v['id'] ?? null,
                    'sku' => $v['sku'] ?? null,
                    'price' => $v['price'] ?? null,
                    'sale_price' => $v['sale_price'] ?? null,
                    'in_stock' => $v['qty'] ?? ($v['stock'] ?? null),
                    'attributes' => $v['attributes'] ?? [],
                ];
            }
        }

        $image = null;
        if (isset($data['images']) && is_array($data['images']) && count($data['images'])) {
            $image = $data['images'][0]['url'] ?? $data['images'][0] ?? null;
        }

        $price = $data['price'] ?? null;
        $salePrice = $data['sale_price'] ?? null;
        $discount = null;
        if ($price && $salePrice && $price > 0) {
            $discount = round((1 - ($salePrice / $price)) * 100, 2);
        }

        return [
            'id' => $data['id'] ?? null,
            'name' => $data['name'] ?? $data['title'] ?? null,
            'sku' => $data['sku'] ?? null,
            'barcode' => $data[Config::get('scanner.barcode_field', 'barcode')] ?? null,
            'price' => $price,
            'sale_price' => $salePrice,
            'discount_percentage' => $discount,
            'in_stock' => $data['with_storehouse_management'] ?? ($data['quantity'] ?? $data['stock'] ?? null),
            'image' => $image,
            'variants' => $variants,
        ];
    }
}
