<?php

namespace App\Models;

use Botble\ACL\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectQuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'project_description',
        'project_type',
        'budget_range',
        'timeline',
        'area_size',
        'installation_needed',
        'special_requirements',
        'form_data',
        'uploaded_files',
        'newsletter_subscribe',
        'status',
        'admin_notes',
        'quoted_price',
        'quote_details',
        'quoted_at',
        'quoted_by'
    ];

    protected $casts = [
        'special_requirements' => 'array',
        'form_data' => 'array',
        'uploaded_files' => 'array',
        'newsletter_subscribe' => 'boolean',
        'quoted_price' => 'decimal:2',
        'quoted_at' => 'timestamp',
    ];

    protected $dates = [
        'quoted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Status options
     */
    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pending Review',
            'in_progress' => 'In Progress',
            'quoted' => 'Quote Sent',
            'accepted' => 'Quote Accepted',
            'rejected' => 'Quote Rejected',
            'completed' => 'Project Completed'
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get budget range label
     */
    public function getBudgetRangeLabelAttribute()
    {
        $ranges = ProjectQuoteFormField::getBudgetRanges();
        return $ranges[$this->budget_range] ?? $this->budget_range;
    }

    /**
     * Get timeline label
     */
    public function getTimelineLabelAttribute()
    {
        $timelines = ProjectQuoteFormField::getTimelineOptions();
        return $timelines[$this->timeline] ?? $this->timeline;
    }

    /**
     * Relationship with user who quoted
     */
    public function quotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quoted_by');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent requests
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if quote has been sent
     */
    public function isQuoted()
    {
        return in_array($this->status, ['quoted', 'accepted', 'rejected', 'completed']);
    }

    /**
     * Check if quote is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if quote is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get formatted quote price
     */
    public function getFormattedQuotePriceAttribute()
    {
        if (!$this->quoted_price) {
            return null;
        }

        return '€' . number_format($this->quoted_price, 2);
    }

    /**
     * Get upload file URLs
     */
    public function getUploadedFileUrls()
    {
        if (!$this->uploaded_files || !is_array($this->uploaded_files)) {
            return [];
        }

        return array_map(function($file) {
            return [
                'name' => $file['name'] ?? 'File',
                'url' => $file['url'] ?? '',
                'size' => $file['size'] ?? 0,
                'type' => $file['type'] ?? ''
            ];
        }, $this->uploaded_files);
    }
}