<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends BaseModel
{
    protected $table = 'mbi_stock_transfer_items';

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity_requested',
        'quantity_shipped',
        'quantity_received',
        'notes',
    ];

    /**
     * Get the stock transfer
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if quantity is complete
     */
    public function isComplete(): bool
    {
        return $this->quantity_received >= $this->quantity_requested;
    }

    /**
     * Get variance between requested and received
     */
    public function getVarianceAttribute(): int
    {
        return ($this->quantity_received ?: $this->quantity_shipped) - $this->quantity_requested;
    }
}