<?php

namespace Botble\AffiliatePro\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateShortLink extends BaseModel
{
    protected $table = 'affiliate_short_links';

    protected $fillable = [
        'affiliate_id',
        'short_code',
        'destination_url',
        'title',
        'product_id',
        'clicks',
        'conversions',
    ];

    protected $casts = [
        'affiliate_id' => 'int',
        'product_id' => 'int',
        'clicks' => 'int',
        'conversions' => 'int',
        'short_code' => SafeContent::class,
        'destination_url' => SafeContent::class,
        'title' => SafeContent::class,
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getShortUrl(): string
    {
        return route('affiliate-pro.short-link.redirect', ['shortCode' => $this->short_code]);
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }

    public function incrementConversions(): void
    {
        $this->increment('conversions');
    }
}
