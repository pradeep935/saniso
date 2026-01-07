<?php

namespace Botble\ProductBulkEdit\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProductBulkEdit\Http\Requests\BulkUpdateProductRequest;
use Botble\ProductBulkEdit\Http\Requests\BulkDeleteProductRequest;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Exception;

class ProductBulkEditController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/ecommerce::products.name'), route('products.index'))
            ->add(trans('plugins/product-bulk-edit::product-bulk-edit.name'), route('product-bulk-edit.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('plugins/product-bulk-edit::product-bulk-edit.name'));

        Assets::addStyles(['datatables'])
            ->addScripts(['datatables'])
            ->addScriptsDirectly([
                'https://cdn.jsdelivr.net/npm/handsontable@12.3.1/dist/handsontable.full.min.js',
                'vendor/core/plugins/product-bulk-edit/js/product-bulk-edit.js',
            ])
            ->addStylesDirectly([
                'https://cdn.jsdelivr.net/npm/handsontable@12.3.1/dist/handsontable.full.min.css',
                'vendor/core/plugins/product-bulk-edit/css/product-bulk-edit.css',
            ]);

        $categories = ProductCategory::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->pluck('name', 'id')
            ->all();

        $brands = Brand::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->pluck('name', 'id')
            ->all();

        $taxes = Tax::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->pluck('title', 'id')
            ->all();

        return view('plugins/product-bulk-edit::index', compact('categories', 'brands', 'taxes'));
    }

    public function getData(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $categoryId = $request->input('category_id');
        $brandId = $request->input('brand_id');

        $query = Product::query()
            ->with(['categories', 'brand', 'taxes'])
            ->where('is_variation', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('ec_product_categories.id', $categoryId);
            });
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $total = $query->count();
        $products = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $data = $products->map(function ($product) {
            $product->loadMissing('metadata');
            $seoMeta = $product->getMetaData('seo_meta', true) ?? [];
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'cost_per_item' => $product->cost_per_item,
                'quantity' => $product->quantity,
                'allow_checkout_when_out_of_stock' => $product->allow_checkout_when_out_of_stock,
                'with_storehouse_management' => $product->with_storehouse_management,
                'stock_status' => StockStatusEnum::getLabel($product->stock_status),
                'status' => BaseStatusEnum::getLabel($product->status),
                'brand_id' => $product->brand_id,
                'brand_name' => $product->brand?->name,
                'category_ids' => $product->categories->pluck('id')->toArray(),
                'category_names' => $product->categories->pluck('name')->implode(', '),
                'tax_id' => $product->taxes->first()?->id,
                'weight' => $product->weight,
                'length' => $product->length,
                'wide' => $product->wide,
                'height' => $product->height,
                'description' => $product->description,
                'content' => $product->content,
                'barcode' => $product->barcode,
                'minimum_order_quantity' => $product->minimum_order_quantity,
                'maximum_order_quantity' => $product->maximum_order_quantity,
                'is_featured' => $product->is_featured,
                'is_variation' => $product->is_variation,
                'image' => $product->image ? RvMedia::url($product->image) : null,
                'images' => array_map(fn($img) => RvMedia::url($img), is_array($product->images) ? $product->images : json_decode($product->images ?? '[]', true)),
                'video_media' => $product->video_media,
                'tag_names' => $product->tags?->pluck('name')->implode(', ') ?? '',
                // SEO Fields
                'seo_title' => $seoMeta['seo_title'] ?? '',
                'seo_description' => $seoMeta['seo_description'] ?? '',
                'seo_image' => $seoMeta['seo_image'] ?? '',
            ];
        });

        return response()->json([
            'error' => false,
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ]);
    }

    public function update(Request $request, BaseHttpResponse $response)
    {
        try {
            // Handle both JSON and form data
            $updates = $request->input('updates', []);
            
            // Validate the request
            $validator = Validator::make($request->all(), [
                'updates' => ['required', 'array', 'min:1'],
                'updates.*.id' => ['required', 'integer', 'exists:ec_products,id'],
                'updates.*.price' => ['sometimes', 'numeric', 'min:0'],
                'updates.*.sale_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'updates.*.quantity' => ['sometimes', 'integer', 'min:0'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            if (empty($updates)) {
                return $response
                    ->setError()
                    ->setMessage(trans('plugins/product-bulk-edit::product-bulk-edit.no_updates'));
            }

            DB::beginTransaction();

            $updatedCount = 0;
            $errors = [];
            $skuMap = [];

            // Pre-validate SKUs for duplicates across the batch
            foreach ($updates as $index => $update) {
                if (isset($update['sku']) && $update['sku']) {
                    if (isset($skuMap[$update['sku']]) && $skuMap[$update['sku']] != $update['id']) {
                        $errors[] = "Duplicate SKU '{$update['sku']}' found in batch (products {$skuMap[$update['sku']]} and {$update['id']})";
                    }
                    $skuMap[$update['sku']] = $update['id'];
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage(implode('; ', $errors));
            }

            foreach ($updates as $update) {
                try {
                    $product = Product::query()->find($update['id']);

                    if (!$product) {
                        $errors[] = "Product ID {$update['id']} not found";
                        continue;
                    }

                    $updateData = [];

                    // Validate SKU uniqueness if changed
                    if (isset($update['sku']) && $update['sku'] && $update['sku'] != $product->sku) {
                        $exists = Product::query()
                            ->where('sku', $update['sku'])
                            ->where('id', '!=', $product->id)
                            ->exists();
                        
                        if ($exists) {
                            $errors[] = "SKU '{$update['sku']}' already exists for another product";
                            continue;
                        }
                    }

                    // Update basic fields
                    if (isset($update['name'])) {
                        $updateData['name'] = $update['name'];
                    }

                    if (isset($update['sku'])) {
                        $updateData['sku'] = $update['sku'];
                    }

                    if (isset($update['price'])) {
                        $updateData['price'] = floatval($update['price']);
                    }

                    if (isset($update['sale_price'])) {
                        $salePrice = $update['sale_price'] ? floatval($update['sale_price']) : null;
                        
                        // Validate sale price is less than regular price
                        if ($salePrice && isset($updateData['price']) && $salePrice >= $updateData['price']) {
                            $errors[] = "Product ID {$update['id']}: Sale price must be less than regular price";
                            continue;
                        } elseif ($salePrice && !isset($updateData['price']) && $salePrice >= $product->price) {
                            $errors[] = "Product ID {$update['id']}: Sale price must be less than regular price";
                            continue;
                        }
                        
                        $updateData['sale_price'] = $salePrice;
                    }

                    if (isset($update['quantity'])) {
                        $updateData['quantity'] = intval($update['quantity']);
                    }

                    if (isset($update['with_storehouse_management'])) {
                        $updateData['with_storehouse_management'] = (bool)$update['with_storehouse_management'];
                    }

                    if (isset($update['stock_status'])) {
                        $updateData['stock_status'] = $update['stock_status'];
                    }

                    if (isset($update['status'])) {
                        $updateData['status'] = $update['status'];
                    }

                    if (isset($update['brand_id'])) {
                        $updateData['brand_id'] = $update['brand_id'] ?: null;
                    }

                    if (isset($update['weight'])) {
                        $updateData['weight'] = $update['weight'] ? floatval($update['weight']) : null;
                    }

                    if (isset($update['length'])) {
                        $updateData['length'] = $update['length'] ? floatval($update['length']) : null;
                    }

                    if (isset($update['wide'])) {
                        $updateData['wide'] = $update['wide'] ? floatval($update['wide']) : null;
                    }

                    if (isset($update['height'])) {
                        $updateData['height'] = $update['height'] ? floatval($update['height']) : null;
                    }

                    if (isset($update['description'])) {
                        $updateData['description'] = $update['description'];
                    }

                    if (isset($update['content'])) {
                        $updateData['content'] = $update['content'];
                    }

                    if (isset($update['barcode'])) {
                        $updateData['barcode'] = $update['barcode'];
                    }

                    if (isset($update['is_featured'])) {
                        $updateData['is_featured'] = (bool)$update['is_featured'];
                    }

                    if (isset($update['minimum_order_quantity'])) {
                        $updateData['minimum_order_quantity'] = $update['minimum_order_quantity'] ? intval($update['minimum_order_quantity']) : null;
                    }

                    if (isset($update['maximum_order_quantity'])) {
                        $updateData['maximum_order_quantity'] = $update['maximum_order_quantity'] ? intval($update['maximum_order_quantity']) : null;
                    }

                    // Update the product
                    $product->fill($updateData);
                    $product->save();

                    // Update categories
                    if (isset($update['category_ids']) && is_array($update['category_ids'])) {
                        $product->categories()->sync($update['category_ids']);
                    }

                    // Update taxes
                    if (isset($update['tax_id'])) {
                        if ($update['tax_id']) {
                            $product->taxes()->sync([$update['tax_id']]);
                        } else {
                            $product->taxes()->sync([]);
                        }
                    }

                    $updatedCount++;
                } catch (Exception $e) {
                    $errors[] = "Error updating product ID {$update['id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = trans('plugins/product-bulk-edit::product-bulk-edit.updated_successfully', ['count' => $updatedCount]);

            if (!empty($errors)) {
                $message .= ' ' . trans('plugins/product-bulk-edit::product-bulk-edit.with_errors') . ': ' . implode('; ', $errors);
            }

            return $response
                ->setMessage($message)
                ->setData(['updated' => $updatedCount, 'errors' => $errors]);
        } catch (Exception $exception) {
            DB::rollBack();

            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deleteProducts(Request $request, BaseHttpResponse $response)
    {
        try {
            $ids = $request->input('ids', []);

            // Validate
            $validator = Validator::make($request->all(), [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['required', 'integer', 'exists:ec_products,id'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            if (empty($ids)) {
                return $response
                    ->setError()
                    ->setMessage(trans('plugins/product-bulk-edit::product-bulk-edit.no_products_selected'));
            }

            // Check for products that have orders
            $productsWithOrders = DB::table('ec_order_product')
                ->whereIn('product_id', $ids)
                ->distinct()
                ->pluck('product_id')
                ->toArray();

            if (!empty($productsWithOrders)) {
                $products = Product::query()->whereIn('id', $productsWithOrders)->pluck('name', 'id');
                $productNames = $products->implode(', ');
                
                return $response
                    ->setError()
                    ->setMessage(
                        trans('plugins/product-bulk-edit::product-bulk-edit.cannot_delete_products_with_orders', 
                            ['products' => $productNames])
                    );
            }

            DB::beginTransaction();
            
            $count = Product::query()->whereIn('id', $ids)->delete();
            
            DB::commit();

            return $response
                ->setMessage(trans('plugins/product-bulk-edit::product-bulk-edit.deleted_successfully', ['count' => $count]));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    /**
     * Update a single field value for instant AJAX save
     */
    public function updateField(Request $request, BaseHttpResponse $response)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:ec_products,id'],
                'field' => ['required', 'string'],
                'value' => ['nullable'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            $product = Product::findOrFail($request->id);
            $field = $request->field;
            $value = $request->value;

            // Field-specific validation
            switch ($field) {
                case 'price':
                case 'sale_price':
                case 'cost_per_item':
                    if ($value !== null && (!is_numeric($value) || $value < 0)) {
                        return $response->setError()->setMessage('Invalid price value');
                    }
                    if ($field === 'sale_price' && $value !== null && $value > $product->price) {
                        return $response->setError()->setMessage('Sale price cannot be greater than regular price');
                    }
                    break;

                case 'quantity':
                case 'minimum_order_quantity':
                case 'maximum_order_quantity':
                    if ($value !== null && (!is_numeric($value) || $value < 0)) {
                        return $response->setError()->setMessage('Invalid quantity value');
                    }
                    break;

                case 'sku':
                    if ($value && Product::where('sku', $value)->where('id', '!=', $product->id)->exists()) {
                        return $response->setError()->setMessage('SKU already exists');
                    }
                    break;

                case 'status':
                    // Convert display label to key
                    $statusMap = [
                        'Published' => 'published',
                        'Draft' => 'draft',
                        'Pending' => 'pending',
                    ];
                    $value = $statusMap[$value] ?? $value;
                    if (!in_array($value, ['published', 'draft', 'pending'])) {
                        return $response->setError()->setMessage('Invalid status value');
                    }
                    break;

                case 'stock_status':
                    // Convert display label to key
                    $stockStatusMap = [
                        'In Stock' => 'in_stock',
                        'Out Of Stock' => 'out_of_stock',
                        'On Backorder' => 'on_backorder',
                    ];
                    $value = $stockStatusMap[$value] ?? $value;
                    if (!in_array($value, ['in_stock', 'out_of_stock', 'on_backorder'])) {
                        return $response->setError()->setMessage('Invalid stock status');
                    }
                    break;

                case 'with_storehouse_management':
                case 'allow_checkout_when_out_of_stock':
                case 'featured':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                    
                case 'seo_title':
                case 'seo_description':
                case 'seo_image':
                    // SEO fields are stored in metadata
                    $product->loadMissing('metadata');
                    $seoMeta = $product->getMetaData('seo_meta', true) ?? [];
                    $seoMeta[$field] = $value;
                    MetaBox::saveMetaBoxData($product, 'seo_meta', $seoMeta);
                    
                    return $response
                        ->setMessage(trans('plugins/product-bulk-edit::product-bulk-edit.field_updated'))
                        ->setData([
                            'id' => $product->id,
                            'field' => $field,
                            'value' => $value,
                        ]);
            }

            // Update the field
            $product->$field = $value;
            $product->save();

            // Return display value for enums
            $displayValue = $product->$field;
            if ($field === 'stock_status') {
                $displayValue = StockStatusEnum::getLabel($displayValue);
            } elseif ($field === 'status') {
                $displayValue = BaseStatusEnum::getLabel($displayValue);
            }

            return $response
                ->setMessage(trans('plugins/product-bulk-edit::product-bulk-edit.field_updated'))
                ->setData([
                    'id' => $product->id,
                    'field' => $field,
                    'value' => $displayValue,
                ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Upload product image
     */
    public function uploadImage(Request $request, BaseHttpResponse $response)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:ec_products,id'],
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            $product = Product::findOrFail($request->input('id'));
            
            if ($request->hasFile('image')) {
                // Use RvMedia to handle upload - skip validation to allow all image types
                $result = RvMedia::handleUpload($request->file('image'), 0, 'products', true);
                
                if ($result['error']) {
                    return $response
                        ->setError()
                        ->setMessage($result['message']);
                }
                
                // Get the uploaded file path (works with both local and S3)
                $path = $result['data']->url;
                
                $product->image = $path;
                $product->save();
                
                // RvMedia::url() returns full URL (local or S3)
                $fullUrl = RvMedia::url($path);
                
                return $response
                    ->setMessage('Image uploaded successfully')
                    ->setData([
                        'id' => $product->id,
                        'image' => $fullUrl,
                        'image_url' => $fullUrl,
                    ]);
            }
            
            return $response
                ->setError()
                ->setMessage('No image file provided');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Upload gallery images
     */
    public function uploadGallery(Request $request, BaseHttpResponse $response)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:ec_products,id'],
                'images' => ['required', 'array'],
                'images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            $product = Product::findOrFail($request->input('id'));
            $existingImages = is_array($product->images) ? $product->images : json_decode($product->images ?? '[]', true);
            
            $uploadedPaths = [];
            $errors = [];
            
            foreach ($request->file('images') as $image) {
                // Use RvMedia to handle each upload - works with both local and S3 storage
                $result = RvMedia::handleUpload($image, 0, 'products', true);
                
                if ($result['error']) {
                    $errors[] = $result['message'];
                } else {
                    $uploadedPaths[] = $result['data']->url;
                }
            }
            
            if (!empty($errors)) {
                return $response
                    ->setError()
                    ->setMessage('Some images failed to upload: ' . implode(', ', $errors));
            }
            
            // Auto-set first uploaded image as featured image (replaces existing)
            if (!empty($uploadedPaths)) {
                $product->image = $uploadedPaths[0];
            }
            
            $product->images = array_merge($existingImages, $uploadedPaths);
            $product->save();
            
            // Convert paths to full URLs (automatically handles S3 or local storage)
            $fullUrls = array_map(fn($path) => RvMedia::url($path), $product->images);
            $featuredImageUrl = $product->image ? RvMedia::url($product->image) : null;
            
            return $response
                ->setMessage(count($uploadedPaths) . ' image(s) uploaded successfully')
                ->setData([
                    'id' => $product->id,
                    'images' => $fullUrls,
                    'featured_image' => $featuredImageUrl,
                ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Delete gallery image
     */
    public function deleteGalleryImage(Request $request, BaseHttpResponse $response)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:ec_products,id'],
                'image' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            $product = Product::findOrFail($request->input('id'));
            $imageToDelete = $request->input('image');
            $existingImages = is_array($product->images) ? $product->images : json_decode($product->images ?? '[]', true);
            
            $updatedImages = array_filter($existingImages, function($img) use ($imageToDelete) {
                return $img !== $imageToDelete;
            });
            
            $product->images = array_values($updatedImages);
            $product->save();
            
            // Delete physical file and thumbnails using RvMedia (supports S3)
            try {
                $file = \Botble\Media\Models\MediaFile::query()
                    ->where('url', $imageToDelete)
                    ->first();
                    
                if ($file) {
                    RvMedia::deleteFile($file);
                } else {
                    // Fallback if file not in media library - use configured default disk (supports S3)
                    if (Storage::exists($imageToDelete)) {
                        Storage::delete($imageToDelete);
                    }
                }
            } catch (\Exception $e) {
                // Continue even if deletion fails
            }
            
            return $response
                ->setMessage('Image deleted successfully')
                ->setData([
                    'id' => $product->id,
                    'images' => $product->images,
                ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Upload featured image from URL
     */
    public function uploadImageFromUrl(Request $request, BaseHttpResponse $response)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:ec_products,id'],
                'url' => ['required', 'url'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            $product = Product::findOrFail($request->input('id'));
            
            // Use RvMedia to download and upload from URL (works with S3)
            $result = RvMedia::uploadFromUrl($request->input('url'), 0, 'products');
            
            if ($result['error'] ?? false) {
                return $response
                    ->setError()
                    ->setMessage($result['message'] ?? 'Failed to upload image from URL');
            }
            
            // Update product featured image
            $product->image = $result['data']->url;
            $product->save();
            
            // Get full URL (automatically handles S3 or local storage)
            $fullUrl = RvMedia::url($product->image);
            
            return $response
                ->setMessage('Image uploaded successfully from URL')
                ->setData([
                    'id' => $product->id,
                    'image' => $fullUrl,
                ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Upload gallery images from URLs
     */
    public function uploadGalleryFromUrl(Request $request, BaseHttpResponse $response)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:ec_products,id'],
                'urls' => ['required', 'array'],
                'urls.*' => ['required', 'url'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            $product = Product::findOrFail($request->input('id'));
            $existingImages = is_array($product->images) ? $product->images : json_decode($product->images ?? '[]', true);
            
            $uploadedPaths = [];
            $errors = [];
            
            foreach ($request->input('urls') as $url) {
                // Use RvMedia to download and upload from URL (works with S3)
                $result = RvMedia::uploadFromUrl($url, 0, 'products');
                
                if ($result['error'] ?? false) {
                    $errors[] = $result['message'] ?? "Failed to upload from: $url";
                } else {
                    $uploadedPaths[] = $result['data']->url;
                }
            }
            
            if (!empty($errors)) {
                return $response
                    ->setError()
                    ->setMessage('Some images failed to upload: ' . implode(', ', $errors));
            }
            
            // Auto-set first imported image as featured image (replaces existing)
            if (!empty($uploadedPaths)) {
                $product->image = $uploadedPaths[0];
            }
            
            // Merge with existing gallery images
            $allImages = array_merge($existingImages, $uploadedPaths);
            $product->images = $allImages;
            $product->save();
            
            // Convert paths to full URLs (automatically handles S3 or local storage)
            $fullUrls = array_map(fn($path) => RvMedia::url($path), $product->images);
            $featuredImageUrl = $product->image ? RvMedia::url($product->image) : null;
            
            return $response
                ->setMessage('Gallery images uploaded successfully from URLs')
                ->setData([
                    'id' => $product->id,
                    'images' => $fullUrls,
                    'featured_image' => $featuredImageUrl,
                ]);

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Export products to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Product::query()
                ->with(['categories', 'brand', 'tax'])
                ->where('is_variation', false);

            // Apply filters if provided
            if ($request->has('search') && $request->input('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            // Stream the CSV to avoid loading all products into memory
            set_time_limit(0);
            $filename = 'products_export_' . date('Y-m-d_His') . '.csv';

            $headers = [
                'ID', 'Name', 'SKU', 'Barcode', 'Price', 'Sale Price', 'Cost Per Item',
                'Quantity', 'Stock Status', 'Status', 'Brand', 'Categories', 'Tax',
                'Description', 'Content', 'Width', 'Length', 'Height', 'Weight',
                'Min Order Qty', 'Max Order Qty', 'Is Featured', 'Image URL',
                'Gallery Images', 'SEO Title', 'SEO Description'
            ];

            $callback = function () use ($query, $headers) {
                $out = fopen('php://output', 'w');
                // write header
                fputcsv($out, $headers);

                // use cursor to stream results and reduce memory usage
                foreach ($query->cursor() as $product) {
                    try {
                        $metadata = $product->getMetaData('seo_meta', true) ?: [];
                        $categoryNames = $product->categories->pluck('name')->join('|');
                        $images = is_array($product->images) ? $product->images : (json_decode($product->images ?? '[]', true) ?: []);
                        $galleryImages = !empty($images) ? implode('|', $images) : '';

                        $row = [
                            $product->id,
                            $product->name,
                            $product->sku,
                            $product->barcode ?? '',
                            $product->price ?? 0,
                            $product->sale_price ?? '',
                            $product->cost_per_item ?? '',
                            $product->quantity ?? 0,
                            $product->stock_status == 'in_stock' ? 'In Stock' : ($product->stock_status == 'out_of_stock' ? 'Out Of Stock' : 'On Backorder'),
                            $product->status == 'published' ? 'Published' : 'Draft',
                            $product->brand ? $product->brand->name : '',
                            $categoryNames,
                            $product->tax ? $product->tax->title : '',
                            strip_tags($product->description ?? ''),
                            strip_tags($product->content ?? ''),
                            $product->wide ?? '',
                            $product->length ?? '',
                            $product->height ?? '',
                            $product->weight ?? '',
                            $product->minimum_order_quantity ?? '',
                            $product->maximum_order_quantity ?? '',
                            $product->is_featured ? 'Yes' : 'No',
                            $product->image ?? '',
                            $galleryImages,
                            $metadata['seo_title'] ?? '',
                            $metadata['seo_description'] ?? '',
                        ];

                        fputcsv($out, $row);
                        // flush output buffer
                        if (function_exists('flush')) {
                            flush();
                        }
                    } catch (\Exception $e) {
                        // ignore single product failures and continue
                        continue;
                    }
                }

                fclose($out);
            };

            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Import products from CSV
     */
    public function import(Request $request, BaseHttpResponse $response)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            ]);

            if ($validator->fails()) {
                return $response
                    ->setError()
                    ->setMessage($validator->errors()->first());
            }

            $file = $request->file('file');

            // Stream the CSV using SplFileObject to avoid loading entire file into memory
            $splFile = new \SplFileObject($file->getRealPath());
            $splFile->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

            // Seek to start in case
            $splFile->rewind();

            // Read headers
            $headers = [];
            if (!$splFile->eof()) {
                $headers = $splFile->fgetcsv();
                if (is_array($headers)) {
                    $headers = array_map('trim', $headers);
                } else {
                    $headers = [];
                }
            }

            if (empty($headers)) {
                return $response->setError()->setMessage('CSV file is empty or invalid');
            }

            // Map headers to database fields
            $headerMap = [
                'ID' => 'id',
                'Name' => 'name',
                'SKU' => 'sku',
                'Barcode' => 'barcode',
                'Price' => 'price',
                'Sale Price' => 'sale_price',
                'Cost Per Item' => 'cost_per_item',
                'Quantity' => 'quantity',
                'Stock Status' => 'stock_status',
                'Status' => 'status',
                'Brand' => 'brand',
                'Categories' => 'categories',
                'Tax' => 'tax',
                'Description' => 'description',
                'Content' => 'content',
                'Width' => 'width',
                'Length' => 'length',
                'Height' => 'height',
                'Weight' => 'weight',
                'Min Order Qty' => 'minimum_order_quantity',
                'Max Order Qty' => 'maximum_order_quantity',
                'Is Featured' => 'is_featured',
                'Image URL' => 'image',
                'Gallery Images' => 'images',
                'SEO Title' => 'seo_title',
                'SEO Description' => 'seo_description',
            ];

            $created = 0;
            $updated = 0;
            $errors = [];

            // Process rows in batches to avoid long transactions and high memory usage
            $batchSize = 200;
            $batch = [];
            $rowIndex = 0;

            $processBatch = function (&$batch) use (&$created, &$updated, &$errors, $headerMap) {
                if (empty($batch)) return;

                DB::beginTransaction();
                try {
                    foreach ($batch as $rowInfo) {
                        [$rowIndex, $row, $headers] = $rowInfo;
                        try {
                            $rowData = [];
                            foreach ($headers as $index => $header) {
                                $field = $headerMap[$header] ?? null;
                                if ($field) {
                                    $rowData[$field] = $row[$index] ?? '';
                                }
                            }

                            // Skip if no barcode and no name
                            if (empty($rowData['barcode']) && empty($rowData['name'])) {
                                continue;
                            }

                            // Find product by barcode or ID
                            $product = null;
                            if (!empty($rowData['barcode'])) {
                                $product = Product::where('barcode', $rowData['barcode'])->first();
                            }
                            if (!$product && !empty($rowData['id'])) {
                                $product = Product::find($rowData['id']);
                            }

                            $isNewProduct = !$product;
                            if ($isNewProduct) {
                                $product = new Product();
                                if (empty($rowData['sku'])) {
                                    $rowData['sku'] = 'PRD-' . strtoupper(Str::random(8));
                                }
                            }

                            // Map fields: only update when CSV provides a non-empty value
                            if (array_key_exists('name', $rowData) && trim((string)$rowData['name']) !== '') {
                                $product->name = $rowData['name'];
                            }

                            if (array_key_exists('sku', $rowData) && trim((string)$rowData['sku']) !== '') {
                                $product->sku = $rowData['sku'];
                            }

                            if (array_key_exists('barcode', $rowData) && trim((string)$rowData['barcode']) !== '') {
                                $product->barcode = $rowData['barcode'];
                            }

                            if (array_key_exists('price', $rowData) && trim((string)$rowData['price']) !== '') {
                                $product->price = floatval($rowData['price']);
                            }

                            if (array_key_exists('sale_price', $rowData) && trim((string)$rowData['sale_price']) !== '') {
                                $product->sale_price = $rowData['sale_price'] !== '' ? floatval($rowData['sale_price']) : null;
                            }

                            if (array_key_exists('cost_per_item', $rowData) && trim((string)$rowData['cost_per_item']) !== '') {
                                $product->cost_per_item = $rowData['cost_per_item'] !== '' ? floatval($rowData['cost_per_item']) : null;
                            }

                            if (array_key_exists('quantity', $rowData) && trim((string)$rowData['quantity']) !== '') {
                                $product->quantity = intval($rowData['quantity']);
                            }

                            if (array_key_exists('stock_status', $rowData) && trim((string)$rowData['stock_status']) !== '') {
                                $product->stock_status = strtolower(str_replace(' ', '_', $rowData['stock_status']));
                            }

                            if (array_key_exists('status', $rowData) && trim((string)$rowData['status']) !== '') {
                                $product->status = strtolower($rowData['status']);
                            }

                            if (array_key_exists('width', $rowData) && trim((string)$rowData['width']) !== '') {
                                $product->wide = $rowData['width'] !== '' ? floatval($rowData['width']) : null;
                            }
                            if (array_key_exists('length', $rowData) && trim((string)$rowData['length']) !== '') {
                                $product->length = $rowData['length'] !== '' ? floatval($rowData['length']) : null;
                            }
                            if (array_key_exists('height', $rowData) && trim((string)$rowData['height']) !== '') {
                                $product->height = $rowData['height'] !== '' ? floatval($rowData['height']) : null;
                            }
                            if (array_key_exists('weight', $rowData) && trim((string)$rowData['weight']) !== '') {
                                $product->weight = $rowData['weight'] !== '' ? floatval($rowData['weight']) : null;
                            }

                            if (array_key_exists('minimum_order_quantity', $rowData) && trim((string)$rowData['minimum_order_quantity']) !== '') {
                                $product->minimum_order_quantity = $rowData['minimum_order_quantity'] !== '' ? intval($rowData['minimum_order_quantity']) : null;
                            }
                            if (array_key_exists('maximum_order_quantity', $rowData) && trim((string)$rowData['maximum_order_quantity']) !== '') {
                                $product->maximum_order_quantity = $rowData['maximum_order_quantity'] !== '' ? intval($rowData['maximum_order_quantity']) : null;
                            }

                            if (array_key_exists('is_featured', $rowData) && trim((string)$rowData['is_featured']) !== '') {
                                $product->is_featured = in_array(strtolower($rowData['is_featured']), ['yes', '1', 'true']);
                            }

                            if (array_key_exists('description', $rowData) && trim((string)$rowData['description']) !== '') {
                                $product->description = $rowData['description'];
                            }
                            if (array_key_exists('content', $rowData) && trim((string)$rowData['content']) !== '') {
                                $product->content = $rowData['content'];
                            }

                            if (array_key_exists('image', $rowData) && trim((string)$rowData['image']) !== '') {
                                $product->image = $rowData['image'];
                            }
                            if (array_key_exists('images', $rowData) && trim((string)$rowData['images']) !== '') {
                                $product->images = array_filter(explode('|', $rowData['images']));
                            }

                            if (!empty($rowData['brand'])) {
                                $brand = Brand::where('name', $rowData['brand'])->first();
                                if ($brand) $product->brand_id = $brand->id;
                            }

                            if (!empty($rowData['tax'])) {
                                $tax = Tax::where('title', $rowData['tax'])->first();
                                if ($tax) $product->tax_id = $tax->id;
                            }

                            if ($isNewProduct) {
                                $product->product_type = ProductTypeEnum::PHYSICAL;
                                $product->is_variation = false;
                                if (empty($product->status)) $product->status = BaseStatusEnum::PUBLISHED;
                                if (empty($product->stock_status)) $product->stock_status = StockStatusEnum::IN_STOCK;
                            }

                            $product->save();

                            if (!empty($rowData['categories'])) {
                                $categoryNames = array_filter(explode('|', $rowData['categories']));
                                $categoryIds = [];
                                foreach ($categoryNames as $categoryName) {
                                    $cat = ProductCategory::where('name', trim($categoryName))->first();
                                    if ($cat) $categoryIds[] = $cat->id;
                                }
                                if (!empty($categoryIds)) $product->categories()->sync($categoryIds);
                            }

                            if (!empty($rowData['seo_title']) || !empty($rowData['seo_description'])) {
                                $seoData = [];
                                if (!empty($rowData['seo_title'])) $seoData['seo_title'] = $rowData['seo_title'];
                                if (!empty($rowData['seo_description'])) $seoData['seo_description'] = $rowData['seo_description'];
                                MetaBox::saveMetaBoxData($product, 'seo_meta', $seoData);
                            }

                            if ($isNewProduct) $created++; else $updated++;
                        } catch (\Exception $e) {
                            $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                        }
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = 'Batch processing error: ' . $e->getMessage();
                }
                // clear batch
                $batch = [];
            };

            // Iterate through CSV rows
            foreach ($splFile as $row) {
                $rowIndex++;
                if ($row === [null] || $row === false) continue;

                // Skip blank lines
                $isEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') { $isEmpty = false; break; }
                }
                if ($isEmpty) continue;

                // Ensure row has same number of columns as headers
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }

                $batch[] = [$rowIndex, $row, $headers];
                if (count($batch) >= $batchSize) {
                    $processBatch($batch);
                }
            }

            // process remaining
            if (!empty($batch)) {
                $processBatch($batch);
            }

            $message = "Import completed. Created: {$created}, Updated: {$updated}";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 10));
            }

            return $response
                ->setMessage($message)
                ->setData([
                    'created' => $created,
                    'updated' => $updated,
                    'errors' => $errors,
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
}
