<?php

namespace Botble\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteNotification extends Model
{
    protected $fillable = [
        'quote_request_id',
        'type',
        'recipient_type',
        'recipient_id',
        'title',
        'message',
        'data',
        'notification_hash',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
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

    public static function createNotification(
        QuoteRequest $quoteRequest,
        string $type,
        string $recipientType,
        ?int $recipientId,
        string $title,
        string $message,
        array $data = []
    ): self {
        // Create a unique hash to prevent duplicates
        $hash = md5(sprintf(
            '%d-%s-%s-%s-%s',
            $quoteRequest->id,
            $type,
            $recipientType,
            $recipientId ?? 'null',
            $title
        ));

        try {
            return self::create([
                'quote_request_id' => $quoteRequest->id,
                'type' => $type,
                'recipient_type' => $recipientType,
                'recipient_id' => $recipientId,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'notification_hash' => $hash
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // If duplicate hash constraint is violated, find and return existing notification
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'notification_hash')) {
                $existing = self::where('notification_hash', $hash)->first();

                if ($existing) {
                    // Update existing notification's timestamp and message
                    $existing->update([
                        'message' => $message,
                        'data' => $data,
                        'updated_at' => now()
                    ]);
                    return $existing;
                }
            }
            
            // Re-throw exception if it's not a duplicate constraint violation
            throw $e;
        }
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForRecipient($query, string $type, ?int $id = null)
    {
        $query->where('recipient_type', $type);
        if ($id) {
            $query->where('recipient_id', $id);
        }
        return $query;
    }
}