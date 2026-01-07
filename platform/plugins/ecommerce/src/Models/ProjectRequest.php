<?php

namespace Botble\Ecommerce\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequest extends BaseModel
{
    use HasFactory;

    protected $table = 'project_requests';

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'project_description',
        'uploaded_files',
        'budget_range',
        'deadline',
        'newsletter_subscribe',
        'form_data',
        'status',
        'admin_notes',
        'quoted_price',
        'quote_details',
        'quoted_at',
        'quoted_by'
    ];

    protected $casts = [
        'uploaded_files' => 'array',
        'form_data' => 'array',
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
        'under_5000' => 'Under $5,000',
        '5000_10000' => '$5,000 - $10,000',
        '10000_25000' => '$10,000 - $25,000',
        '25000_50000' => '$25,000 - $50,000',
        '50000_100000' => '$50,000 - $100,000',
        'over_100000' => 'Over $100,000'
    ];

    public const DEADLINES = [
        'asap' => 'ASAP (1-4 weeks)',
        'month' => 'Within 1 month',
        'quarter' => 'Within 3 months',
        'half_year' => 'Within 6 months',
        'flexible' => 'Flexible timing'
    ];

    /**
     * Get the admin user who quoted this request.
     */
    public function quotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quoted_by');
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
     * Get the deadline label.
     */
    public function getDeadlineLabelAttribute(): ?string
    {
        return $this->deadline ? (self::DEADLINES[$this->deadline] ?? $this->deadline) : null;
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
}