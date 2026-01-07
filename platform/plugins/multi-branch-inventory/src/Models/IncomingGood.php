<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomingGood extends BaseModel
{
    protected $table = 'mbi_incoming_goods';

    protected $fillable = [
        'branch_id',
        'supplier_name',
        'supplier_id',
        'receiving_date',
        'reference_number',
        'status',
        'for_internal_use',
        'order_date',
        'order_reference',
        'notes',
        'received_by',
        'total_items',
        'photos',
        'cmr_image',
        'cmr_images',
        'packing_slip_image',
        'packing_slip_images',
        'product_image',
        'delivery_images',
        'proforma_images',
        'box_barcode',
    ];

    protected $casts = [
        'receiving_date' => 'datetime',
        'photos' => 'array',
        'cmr_images' => 'array',
        'packing_slip_images' => 'array',
        'delivery_images' => 'array',
        'proforma_images' => 'array',
        'order_date' => 'datetime',
    ];

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the items in this incoming goods
     */
    public function items(): HasMany
    {
        return $this->hasMany(IncomingGoodItem::class);
    }

    /**
     * Get the user who received the goods
     */
    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'received_by');
    }

    /**
     * Mark as received
     */
    public function markAsReceived(): bool
    {
        $this->status = 'received';
        return $this->save();
    }

    /**
     * Calculate total value
     */
    public function calculateTotalValue(): float
    {
        return $this->items()->sum(function ($item) {
            return $item->quantity_received * $item->unit_cost;
        });
    }
}