<?php

namespace Botble\Ecommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_request_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'sender_email',
        'message',
        'attachments',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByQuoteRequest($query, $quoteRequestId)
    {
        return $query->where('quote_request_id', $quoteRequestId);
    }

    public function getTimestampAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}