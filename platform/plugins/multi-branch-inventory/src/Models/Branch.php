<?php

namespace Botble\MultiBranchInventory\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends BaseModel
{
    protected $table = 'mbi_branches';

    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'city',
        'country',
        'postal_code',
        'phone',
        'email',
        'manager_name',
        'status',
        'is_main_branch',
        'timezone',
        'currency_id',
        'settings',
        'features',
    ];

    protected $casts = [
        'is_main_branch' => 'boolean',
        'settings' => 'array',
        'features' => 'array',
    ];

    /**
     * Get branch inventory items
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    /**
     * Get incoming goods for this branch
     */
    public function incomingGoods(): HasMany
    {
        return $this->hasMany(IncomingGood::class);
    }

    /**
     * Get stock transfers from this branch
     */
    public function transfersFrom(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    /**
     * Get stock transfers to this branch
     */
    public function transfersTo(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }

    /**
     * Get temporary products for this branch
     */
    public function temporaryProducts(): HasMany
    {
        return $this->hasMany(TemporaryProduct::class);
    }

    /**
     * Check if branch is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get formatted address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the main branch
     */
    public static function getMainBranch()
    {
        return static::where('is_main_branch', true)->where('status', 'active')->first();
    }

    /**
     * Set this branch as the main branch
     */
    public function setAsMainBranch(): void
    {
        // First, remove main branch status from all other branches
        static::where('id', '!=', $this->id)->update(['is_main_branch' => false]);
        
        // Then set this branch as main
        $this->update(['is_main_branch' => true]);
    }

    /**
     * Boot method to ensure only one main branch exists
     */
    protected static function boot()
    {
        parent::boot();

        // Before saving, ensure main branch constraint
        static::saving(function ($branch) {
            if ($branch->is_main_branch) {
                // If this branch is being set as main, unset all others
                static::where('id', '!=', $branch->id)->update(['is_main_branch' => false]);
            }
        });
    }

    /**
     * Scope for main branch only
     */
    public function scopeMainBranch($query)
    {
        return $query->where('is_main_branch', true);
    }

    /**
     * Scope for active branches only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get formatted branch type name
     */
    public function getTypeNameAttribute(): string
    {
        $types = [
            'store' => 'Retail Store',
            'warehouse' => 'Warehouse',
            'distribution_center' => 'Distribution Center',
            'outlet' => 'Outlet Store',
            'flagship' => 'Flagship Store',
            'pop_up' => 'Pop-up Store',
            'showroom' => 'Showroom',
            'kiosk' => 'Kiosk',
            'franchise' => 'Franchise',
            'online_fulfillment' => 'Online Fulfillment Center',
        ];

        return $types[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Get branch type icon
     */
    public function getTypeIconAttribute(): string
    {
        $icons = [
            'store' => '🏪',
            'warehouse' => '🏭',
            'distribution_center' => '📦',
            'outlet' => '🛍️',
            'flagship' => '⭐',
            'pop_up' => '🎪',
            'showroom' => '🖼️',
            'kiosk' => '🏪',
            'franchise' => '🤝',
            'online_fulfillment' => '💻',
        ];

        return $icons[$this->type] ?? '🏢';
    }

    /**
     * Check if branch has specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get available branch types
     */
    public static function getAvailableTypes(): array
    {
        return [
            'store' => 'Retail Store',
            'warehouse' => 'Warehouse',
            'distribution_center' => 'Distribution Center',
            'outlet' => 'Outlet Store',
            'flagship' => 'Flagship Store',
            'pop_up' => 'Pop-up Store',
            'showroom' => 'Showroom',
            'kiosk' => 'Kiosk',
            'franchise' => 'Franchise',
            'online_fulfillment' => 'Online Fulfillment Center',
        ];
    }
}