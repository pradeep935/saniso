<?php

namespace Botble\AffiliatePro\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends BaseModel
{
    protected $table = 'affiliate_clicks';

    protected $fillable = [
        'affiliate_id',
        'short_link_id',
        'ip_address',
        'user_agent',
        'referrer_url',
        'landing_url',
        'converted',
        'conversion_time',
        'country',
        'city',
    ];

    protected $casts = [
        'affiliate_id' => 'int',
        'short_link_id' => 'int',
        'ip_address' => SafeContent::class,
        'user_agent' => SafeContent::class,
        'referrer_url' => SafeContent::class,
        'landing_url' => SafeContent::class,
        'converted' => 'boolean',
        'conversion_time' => 'datetime',
        'country' => SafeContent::class,
        'city' => SafeContent::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateShortLink::class);
    }

    public function markAsConverted(): self
    {
        $this->converted = true;
        $this->conversion_time = Carbon::now();
        $this->save();

        return $this;
    }
}
