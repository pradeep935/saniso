<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchInventory extends BaseModel
{
    protected $table = 'mbi_branch_inventory';

    protected $fillable = [
        'branch_id',
        'product_id',
        'sku',
        'ean',
        'quantity_on_hand',
        'quantity_reserved',
        'quantity_available',
        'minimum_stock',
        'maximum_stock',
        'storage_location',
        'cost_price',
        'selling_price',
        'local_price',
        'promo_price',
        'promo_start_date',
        'promo_end_date',
        'visible_online',
        'visible_in_pos',
        'only_visible_in_pos',
        'last_counted_at',
        'last_restocked_at',
        'needs_replenishment',
        'replenishment_quantity',
        'replenishment_requested_at',
        'notes',
    ];

    protected $casts = [
        'visible_online' => 'boolean',
        'visible_in_pos' => 'boolean',
        'only_visible_in_pos' => 'boolean',
        'promo_start_date' => 'datetime',
        'promo_end_date' => 'datetime',
        'last_counted_at' => 'datetime',
        'last_restocked_at' => 'datetime',
        'needs_replenishment' => 'boolean',
        'replenishment_requested_at' => 'datetime',
    ];

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
     * Get inventory movements
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Check if product is available for sale
     */
    public function isAvailable(): bool
    {
        return $this->quantity_available > 0;
    }

    /**
     * Check if product is visible online
     */
    public function isVisibleOnline(): bool
    {
        return $this->visible_online && !$this->only_visible_in_pos && $this->isAvailable();
    }

    /**
     * Check if product is visible in POS
     */
    public function isVisibleInPos(): bool
    {
        return $this->visible_in_pos && $this->isAvailable();
    }

    /**
     * Get effective selling price (considering promotions)
     */
    public function getEffectivePriceAttribute(): float
    {
        $now = now();
        
        if ($this->promo_price && 
            $this->promo_start_date && 
            $this->promo_end_date &&
            $now->between($this->promo_start_date, $this->promo_end_date)) {
            return $this->promo_price;
        }

        return $this->local_price ?: $this->selling_price;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->quantity_available <= $this->minimum_stock;
    }

    /**
     * Reserve stock
     */
    public function reserveStock(int $quantity): bool
    {
        if ($this->quantity_available < $quantity) {
            return false;
        }

        $this->quantity_reserved += $quantity;
        $this->quantity_available -= $quantity;
        
        return $this->save();
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock(int $quantity): bool
    {
        if ($this->quantity_reserved < $quantity) {
            return false;
        }

        $this->quantity_reserved -= $quantity;
        $this->quantity_available += $quantity;
        
        return $this->save();
    }

    /**
     * Update stock levels
     */
    public function updateStock(?int $quantity, string $type = 'add', ?string $reason = null): bool
    {
        // Handle null quantity - default to 0
        $quantity = $quantity ?? 0;
        
        $oldQuantity = $this->quantity_on_hand;
        
        if ($type === 'add') {
            $this->quantity_on_hand += $quantity;
            $this->quantity_available += $quantity;
        } elseif ($type === 'subtract') {
            if ($this->quantity_available < $quantity) {
                return false;
            }
            $this->quantity_on_hand -= $quantity;
            $this->quantity_available -= $quantity;
        } elseif ($type === 'set') {
            // Set absolute quantity - available = on_hand - reserved
            \Log::critical('🔍 BranchInventory::updateStock SET operation', [
                'inventory_id' => $this->id,
                'before_quantity_on_hand' => $this->quantity_on_hand,
                'before_quantity_available' => $this->quantity_available,
                'before_quantity_reserved' => $this->quantity_reserved,
                'input_quantity' => $quantity,
                'calculation' => "{$quantity} - {$this->quantity_reserved} = " . ($quantity - $this->quantity_reserved)
            ]);
            
            $this->quantity_on_hand = $quantity;
            $this->quantity_available = $quantity - $this->quantity_reserved;
            
            \Log::critical('🔍 AFTER SET operation', [
                'inventory_id' => $this->id,
                'new_quantity_on_hand' => $this->quantity_on_hand,
                'new_quantity_available' => $this->quantity_available,
                'quantity_reserved' => $this->quantity_reserved
            ]);
        }

        $saved = $this->save();

        if ($saved) {
            // Log the movement
            InventoryMovement::create([
                'branch_inventory_id' => $this->id,
                'branch_id' => $this->branch_id,
                'product_id' => $this->product_id,
                'type' => $type,
                'quantity_before' => $oldQuantity,
                'quantity_changed' => $type === 'set' ? ($quantity - $oldQuantity) : ($type === 'add' ? $quantity : -$quantity),
                'quantity_after' => $this->quantity_on_hand,
                'reason' => $reason,
                'created_at' => now(),
            ]);
        }

        return $saved;
    }

    /**
     * Track products being synced to prevent infinite loops
     */
    private static $syncingProducts = [];

    /**
     * Sync total quantity with ecommerce product
     */
    public function syncProductQuantity(): void
    {
        try {
            if (!class_exists(\Botble\Ecommerce\Models\Product::class)) {
                return;
            }

            // Prevent infinite loops
            if (isset(static::$syncingProducts[$this->product_id])) {
                return;
            }

            static::$syncingProducts[$this->product_id] = true;

            $product = \Botble\Ecommerce\Models\Product::find($this->product_id);
            if (!$product) {
                unset(static::$syncingProducts[$this->product_id]);
                return;
            }

            // Calculate total quantity across all branches for this product
            $totalQuantity = static::where('product_id', $this->product_id)
                ->sum('quantity_available');

            // Only update if the quantity has changed
            if ($product->quantity != $totalQuantity) {
                $product->updateQuietly(['quantity' => $totalQuantity]); // Use updateQuietly to prevent events
                \Log::info("Multi-Branch Inventory: Synced product {$this->product_id} quantity from {$product->quantity} to {$totalQuantity}");
            }

            unset(static::$syncingProducts[$this->product_id]);

        } catch (\Exception $e) {
            unset(static::$syncingProducts[$this->product_id]);
            \Log::error('Multi-Branch Inventory: Failed to sync product quantity: ' . $e->getMessage());
        }
    }

    /**
     * Trigger an automatic replenishment incoming goods (draft) for this inventory
     * Creates an IncomingGood + IncomingGoodItem with quantity = maximum_stock - quantity_on_hand
     */
    public function triggerReplenishment(): ?IncomingGood
    {
        // If maximum is not set, calculate using minimum -> no action
        if ($this->maximum_stock === null || $this->maximum_stock <= 0) {
            return null;
        }

        if ($this->quantity_on_hand >= $this->maximum_stock) {
            return null;
        }

        $needed = (int) max(0, $this->maximum_stock - $this->quantity_on_hand);
        if ($needed <= 0) {
            return null;
        }

        // Mark the inventory record as needing replenishment and store requested quantity/time
        $this->needs_replenishment = true;
        $this->replenishment_quantity = $needed;
        $this->replenishment_requested_at = now();
        $this->saveQuietly();

        // Log for visibility; front-end list should order by `needs_replenishment` and `replenishment_requested_at`
        \Log::info("Replenishment requested for branch_inventory {$this->id}: quantity {$needed}");

        return null;
    }

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-sync product quantity when branch inventory changes
        static::saved(function ($branchInventory) {
            $branchInventory->syncProductQuantity();
            // If stock falls to or below minimum, create a replenishment incoming goods (draft)
            try {
                if ($branchInventory->isLowStock()) {
                    $branchInventory->triggerReplenishment();
                }
            } catch (\Exception $e) {
                \Log::error('Failed to auto-trigger replenishment: ' . $e->getMessage());
            }
        });

        static::deleted(function ($branchInventory) {
            $branchInventory->syncProductQuantity();
        });
    }
}