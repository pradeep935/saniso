<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupReservation extends BaseModel
{
    protected $table = 'mbi_pickup_reservations';

    protected $fillable = [
        'branch_id',
        'product_id',
        'quantity',
        'customer_name',
        'customer_phone',
        'customer_email',
        'pickup_date',
        'notes',
        'status',
        'reservation_number',
        'expires_at',
        'picked_up_at',
        'picked_up_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'expires_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'cancelled_at' => 'datetime',
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
     * Get the user who processed the pickup
     */
    public function pickedUpByUser(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'picked_up_by');
    }

    /**
     * Check if reservation is active
     */
    public function isActive(): bool
    {
        return $this->status === 'reserved' && $this->expires_at > now();
    }

    /**
     * Check if reservation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now() && $this->status === 'reserved';
    }

    /**
     * Mark as picked up
     */
    public function markAsPickedUp($userId = null): bool
    {
        if ($this->status !== 'reserved') {
            return false;
        }

        $this->status = 'picked_up';
        $this->picked_up_at = now();
        $this->picked_up_by = $userId;

        return $this->save();
    }

    /**
     * Cancel reservation
     */
    public function cancel($reason = null): bool
    {
        if (!in_array($this->status, ['reserved'])) {
            return false;
        }

        // Release reserved stock
        $branchInventory = BranchInventory::where([
            'branch_id' => $this->branch_id,
            'product_id' => $this->product_id,
        ])->first();

        if ($branchInventory) {
            $branchInventory->releaseReservedStock($this->quantity);
        }

        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;

        return $this->save();
    }

    /**
     * Auto-cancel expired reservations
     */
    public static function cancelExpiredReservations(): int
    {
        $expiredReservations = static::where('status', 'reserved')
            ->where('expires_at', '<=', now())
            ->get();

        $cancelledCount = 0;

        foreach ($expiredReservations as $reservation) {
            if ($reservation->cancel('Expired')) {
                $cancelledCount++;
            }
        }

        return $cancelledCount;
    }
}