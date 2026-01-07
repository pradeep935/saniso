<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingGoodItem extends BaseModel
{
    protected $table = 'mbi_incoming_good_items';

    protected $fillable = [
        'incoming_good_id',
        'product_id',
        'ean',
        'sku',
        'product_name',
        'quantity_expected',
        'quantity_received',
        'unit_cost',
        'storage_location',
        'condition_notes',
        'photos',
        'is_new_product',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_new_product' => 'boolean',
    ];

    /**
     * Get the incoming good record
     */
    public function incomingGood(): BelongsTo
    {
        return $this->belongsTo(IncomingGood::class);
    }

    /**
     * Get the product (if exists)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch through incoming good
     */
    public function branch(): BelongsTo
    {
        return $this->incomingGood->branch();
    }

    /**
     * Check if quantity matches expected
     */
    public function isQuantityComplete(): bool
    {
        return $this->quantity_received >= $this->quantity_expected;
    }

    /**
     * Get quantity variance
     */
    public function getQuantityVarianceAttribute(): int
    {
        return $this->quantity_received - $this->quantity_expected;
    }

    /**
     * Process this item into inventory
     */
    public function processToInventory(): bool
    {
        if (!$this->product_id || $this->quantity_received <= 0) {
            return false;
        }

        $branch = $this->incomingGood->branch;
        
        // Find or create branch inventory record
        $branchInventory = BranchInventory::firstOrCreate([
            'branch_id' => $branch->id,
            'product_id' => $this->product_id,
        ], [
            'sku' => $this->sku,
            'ean' => $this->ean,
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
            'quantity_available' => 0,
            'storage_location' => $this->storage_location,
            'cost_price' => $this->unit_cost,
            'visible_in_pos' => true,
            'visible_online' => false, // Default to POS only until properly configured
        ]);

        // Update stock
        return $branchInventory->updateStock(
            $this->quantity_received, 
            'add', 
            "Incoming goods: {$this->incomingGood->reference_number}"
        );
    }
}