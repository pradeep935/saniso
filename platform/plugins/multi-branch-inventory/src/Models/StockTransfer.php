<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends BaseModel
{
    protected $table = 'mbi_stock_transfers';

    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'reference_number',
        'status',
        'requested_by',
        'approved_by',
        'picked_by',
        'received_by',
        'requested_at',
        'approved_at',
        'picked_at',
        'shipped_at',
        'received_at',
        'notes',
        'total_items',
        'tracking_number',
        'shipping_method',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'picked_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Get the source branch
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Get the destination branch
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Get transfer items
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Get the user who requested the transfer
     */
    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'requested_by');
    }

    /**
     * Get the user who approved the transfer
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'approved_by');
    }

    /**
     * Approve the transfer
     */
    public function approve($userId): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();

        return $this->save();
    }

    /**
     * Start picking process
     */
    public function startPicking($userId): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        // Reserve stock in source branch
        foreach ($this->items as $item) {
            $branchInventory = BranchInventory::where([
                'branch_id' => $this->from_branch_id,
                'product_id' => $item->product_id,
            ])->first();

            if (!$branchInventory || !$branchInventory->reserveStock($item->quantity_requested)) {
                return false;
            }
        }

        $this->status = 'picking';
        $this->picked_by = $userId;
        $this->picked_at = now();

        return $this->save();
    }

    /**
     * Ship the transfer
     */
    public function ship(): bool
    {
        if ($this->status !== 'picking') {
            return false;
        }

        // Reduce stock from source branch
        foreach ($this->items as $item) {
            $branchInventory = BranchInventory::where([
                'branch_id' => $this->from_branch_id,
                'product_id' => $item->product_id,
            ])->first();

            if ($branchInventory) {
                // Remove from reserved and on_hand
                $branchInventory->quantity_reserved -= $item->quantity_shipped;
                $branchInventory->quantity_on_hand -= $item->quantity_shipped;
                $branchInventory->save();

                // Log movement
                InventoryMovement::create([
                    'branch_inventory_id' => $branchInventory->id,
                    'branch_id' => $this->from_branch_id,
                    'product_id' => $item->product_id,
                    'type' => 'transfer_out',
                    'quantity_changed' => -$item->quantity_shipped,
                    'reason' => "Transfer to {$this->toBranch->name} - {$this->reference_number}",
                    'reference_id' => $this->id,
                    'reference_type' => self::class,
                ]);
            }
        }

        $this->status = 'in_transit';
        $this->shipped_at = now();

        return $this->save();
    }

    /**
     * Receive the transfer
     */
    public function receive($userId): bool
    {
        if ($this->status !== 'in_transit') {
            return false;
        }

        // Add stock to destination branch
        foreach ($this->items as $item) {
            $branchInventory = BranchInventory::firstOrCreate([
                'branch_id' => $this->to_branch_id,
                'product_id' => $item->product_id,
            ], [
                'sku' => $item->product->sku,
                'ean' => $item->product->barcode,
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
                'visible_in_pos' => true,
                'visible_online' => true,
            ]);

            $branchInventory->updateStock(
                $item->quantity_received ?: $item->quantity_shipped,
                'add',
                "Transfer from {$this->fromBranch->name} - {$this->reference_number}"
            );
        }

        $this->status = 'completed';
        $this->received_by = $userId;
        $this->received_at = now();

        return $this->save();
    }

    /**
     * Generate reference number
     */
    public static function generateReferenceNumber(): string
    {
        return 'TXF' . date('Ymd') . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
    }
}