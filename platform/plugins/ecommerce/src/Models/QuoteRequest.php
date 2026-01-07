<?php

namespace Botble\Ecommerce\Models;

use Botble\ACL\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'vendor_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'quantity',
        'budget_range',
        'timeline',
        'room_type',
        'room_size',
        'installation_required',
        'project_description',
        'special_requirements',
        'status',
        'admin_notes',
        'quoted_price',
        'quote_details',
        'quoted_at',
        'quoted_by',
        'newsletter_subscribe'
    ];

    protected $casts = [
        'special_requirements' => 'array',
        'newsletter_subscribe' => 'boolean',
        'quoted_price' => 'decimal:2',
        'quoted_at' => 'datetime'
    ];

    public const STATUSES = [
        'pending' => 'Pending Review',
        'in_progress' => 'In Progress',
        'quoted' => 'Quote Sent',
        'accepted' => 'Quote Accepted',
        'rejected' => 'Quote Rejected',
        'completed' => 'Completed'
    ];

    public const BUDGET_RANGES = [
        'under_1000' => 'Under $1,000',
        '1000_5000' => '$1,000 - $5,000',
        '5000_10000' => '$5,000 - $10,000',
        '10000_25000' => '$10,000 - $25,000',
        'over_25000' => 'Over $25,000'
    ];

    public const TIMELINES = [
        'urgent' => 'ASAP (1-2 weeks)',
        'month' => 'Within a month',
        'quarter' => 'Within 3 months',
        'flexible' => 'Flexible timing'
    ];

    public const ROOM_TYPES = [
        'bathroom' => 'Bathroom',
        'kitchen' => 'Kitchen',
        'living_room' => 'Living Room',
        'bedroom' => 'Bedroom',
        'commercial' => 'Commercial Space',
        'outdoor' => 'Outdoor/Patio',
        'other' => 'Other'
    ];

    /**
     * Get the product associated with the quote request.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the admin user who quoted this request.
     */
    public function quotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quoted_by');
    }

    /**
     * Get the vendor associated with this quote request.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'vendor_id');
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the budget range label.
     */
    public function getBudgetRangeLabelAttribute(): ?string
    {
        return $this->budget_range ? (self::BUDGET_RANGES[$this->budget_range] ?? $this->budget_range) : null;
    }

    /**
     * Get the timeline label.
     */
    public function getTimelineLabelAttribute(): ?string
    {
        return $this->timeline ? (self::TIMELINES[$this->timeline] ?? $this->timeline) : null;
    }

    /**
     * Get the room type label.
     */
    public function getRoomTypeLabelAttribute(): ?string
    {
        return $this->room_type ? (self::ROOM_TYPES[$this->room_type] ?? $this->room_type) : null;
    }

    /**
     * Check if the quote request is for a tiles/flooring product.
     */
    public function getIsTilesProductAttribute(): bool
    {
        if (!$this->product) {
            return false;
        }

        $tilesCategories = ['tiles', 'tile', 'flooring'];
        
        foreach ($this->product->categories as $category) {
            if (in_array(strtolower($category->name), $tilesCategories) || 
                in_array(strtolower($category->slug), $tilesCategories)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent requests.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get all messages for this quote request.
     */
    public function messages()
    {
        return $this->hasMany(QuoteMessage::class)->orderBy('created_at');
    }

    /**
     * Get unread messages count.
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->messages()->unread()->count();
    }
}