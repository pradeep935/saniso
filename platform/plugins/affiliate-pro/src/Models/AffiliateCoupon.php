<?php

namespace Botble\AffiliatePro\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Discount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCoupon extends BaseModel
{
    protected $table = 'affiliate_coupons';

    protected $fillable = [
        'affiliate_id',
        'discount_id',
        'code',
        'description',
        'discount_amount',
        'discount_type',
        'expires_at',
    ];

    protected $casts = [
        'affiliate_id' => 'int',
        'discount_id' => 'int',
        'code' => SafeContent::class,
        'description' => SafeContent::class,
        'discount_amount' => 'float',
        'discount_type' => SafeContent::class,
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
