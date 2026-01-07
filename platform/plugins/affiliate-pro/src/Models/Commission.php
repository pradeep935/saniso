<?php

namespace Botble\AffiliatePro\Models;

use Botble\AffiliatePro\Enums\CommissionStatusEnum;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends BaseModel
{
    protected $table = 'affiliate_commissions';

    protected $fillable = [
        'affiliate_id',
        'order_id',
        'amount',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'float',
        'description' => SafeContent::class,
        'status' => CommissionStatusEnum::class,
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
