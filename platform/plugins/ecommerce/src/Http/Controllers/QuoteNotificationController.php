<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\QuoteNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuoteNotificationController extends BaseController
{
    public function getLatest(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Determine if user is admin or vendor
            $recipientType = 'admin'; // Default to admin
            $recipientId = null;
            
            // Check if this is a marketplace vendor context (vendor dashboard)
            if (is_plugin_active('marketplace') && request()->is('vendor/*')) {
                $recipientType = 'vendor';
                $recipientId = $user->id;
            }

            // Get latest notifications for this recipient
            $query = QuoteNotification::forRecipient($recipientType, $recipientId)
                ->with('quoteRequest.product')
                ->orderBy('created_at', 'desc')
                ->limit(10);

            // Only get unread notifications if requested
            if ($request->boolean('unread_only', true)) {
                $query->unread();
            }

            $notifications = $query->get();

            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->toISOString(),
                    'quote_id' => $notification->quote_request_id,
                    'url' => route('quote-requests.show', $notification->quote_request_id),
                    'data' => $notification->data
                ];
            });

            return response()->json([
                'success' => true,
                'notifications' => $formattedNotifications,
                'unread_count' => $notifications->where('is_read', false)->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get latest quote notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications'
            ], 500);
        }
    }

    public function markRead(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Determine recipient type
            $recipientType = 'admin';
            $recipientId = null;
            
            // Check if this is a marketplace vendor context
            if (is_plugin_active('marketplace') && request()->is('vendor/*')) {
                $recipientType = 'vendor';
                $recipientId = $user->id;
            }

            $notification = QuoteNotification::forRecipient($recipientType, $recipientId)
                ->findOrFail($id);

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to mark notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }
}