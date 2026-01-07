<?php

namespace Botble\AffiliatePro\Models;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends BaseModel
{
    protected $table = 'affiliates';

    protected $fillable = [
        'customer_id',
        'affiliate_code',
        'commission_rate',
        'balance',
        'total_commission',
        'total_withdrawn',
        'status',
    ];

    protected $casts = [
        'status' => AffiliateStatusEnum::class,
        'commission_rate' => 'float',
        'balance' => 'float',
        'total_commission' => 'float',
        'total_withdrawn' => 'float',
        'affiliate_code' => SafeContent::class,
    ];

    protected $hidden = [
        'commission_rate',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function ban(): void
    {
        $this->update([
            'status' => AffiliateStatusEnum::BANNED,
        ]);
    }

    public function unban(): void
    {
        $this->update([
            'status' => AffiliateStatusEnum::APPROVED,
        ]);
    }

    public function isBanned(): bool
    {
        return $this->status == AffiliateStatusEnum::BANNED;
    }

    public function canAccessDashboard(): bool
    {
        return $this->status == AffiliateStatusEnum::APPROVED;
    }

    public function getEffectiveCommissionRate(): float
    {
        if ($this->commission_rate !== null && $this->commission_rate > 0) {
            return $this->commission_rate;
        }

        return (float) setting('affiliate_commission_percentage', 10);
    }
}
