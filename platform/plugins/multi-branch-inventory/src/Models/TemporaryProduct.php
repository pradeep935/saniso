<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporaryProduct extends BaseModel
{
    protected $table = 'mbi_temporary_products';

    protected $fillable = [
        'branch_id',
        'ean',
        'sku',
        'product_code',
        'name',
        'description',
        'quantity',
        'cost_price',
        'selling_price',
        'storage_location',
        'status',
        'linked_product_id',
        'created_by',
        'notes',
        'photos',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the linked product (if converted)
     */
    public function linkedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'linked_product_id');
    }

    /**
     * Get the user who created this temporary product
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'created_by');
    }

    /**
     * Check if this temporary product is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->quantity > 0;
    }

    /**
     * Convert to permanent product and create branch inventory
     */
    public function convertToPermanentProduct(array $productData): ?Product
    {
        // This would typically integrate with the main product creation system
        // For now, we'll mark it as linked
        $this->status = 'converted';
        $this->linked_product_id = $productData['product_id'] ?? null;
        $this->save();

        return $this->linkedProduct;
    }

    /**
     * Sell quantity from temporary stock
     */
    public function sellQuantity(int $quantity): bool
    {
        if ($this->quantity < $quantity || !$this->isActive()) {
            return false;
        }

        $this->quantity -= $quantity;
        
        if ($this->quantity <= 0) {
            $this->status = 'sold_out';
        }

        return $this->save();
    }
}