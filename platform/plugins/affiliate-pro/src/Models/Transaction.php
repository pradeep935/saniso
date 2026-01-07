<?php

namespace Botble\AffiliatePro\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends BaseModel
{
    protected $table = 'affiliate_transactions';

    protected $fillable = [
        'affiliate_id',
        'amount',
        'description',
        'type',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'amount' => 'float',
        'description' => SafeContent::class,
        'type' => SafeContent::class,
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
}
