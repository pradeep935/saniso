<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends BaseModel
{
    protected $table = 'mbi_inventory_movements';

    protected $fillable = [
        'branch_inventory_id',
        'branch_id',
        'product_id',
        'type',
        'quantity_before',
        'quantity_changed',
        'quantity_after',
        'reason',
        'reference_id',
        'reference_type',
        'user_id',
        'notes',
    ];

    /**
     * Get the branch inventory record
     */
    public function branchInventory(): BelongsTo
    {
        return $this->belongsTo(BranchInventory::class);
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made this movement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class);
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Movement types
     */
    public static function getTypes(): array
    {
        return [
            'add' => 'Stock Added',
            'subtract' => 'Stock Removed', 
            'set' => 'Stock Adjusted',
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'sale' => 'Sale',
            'return' => 'Return',
            'damage' => 'Damage/Loss',
            'count' => 'Stock Count',
            'incoming' => 'Incoming Goods',
        ];
    }
}