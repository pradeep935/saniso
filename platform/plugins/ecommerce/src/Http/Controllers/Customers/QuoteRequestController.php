<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\QuoteMessage;
use Botble\Ecommerce\Models\QuoteNotification;
use Botble\Ecommerce\Models\QuoteRequest;
use Botble\Ecommerce\Models\QuoteSettings;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Supports\OrderHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteRequestController extends BaseController
{
    public function __construct()
    {
        $version = EcommerceHelper::getAssetVersion();

        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: $version);
        Theme::asset()
            ->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css', version: $version);
    }
    public function index()
    {
        SeoHelper::setTitle(__('Quote Requests'));
        
        $customer = auth('customer')->user();
        $quoteRequests = QuoteRequest::where('customer_email', $customer->email)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        EcommerceHelper::registerThemeAssets();

        return Theme::scope(
            'ecommerce.customers.quote-requests.index',
            compact('quoteRequests'),
            'plugins/ecommerce::themes.customers.quote-requests.index'
        )->render();
    }

    public function show($id)
    {
        SeoHelper::setTitle(__('Quote Request #:id', ['id' => $id]));
        
        $customer = auth('customer')->user();
        
        if (!$customer) {
            return redirect()->route('customer.login');
        }

        $quoteRequest = QuoteRequest::with(['product', 'quotedBy'])
            ->where('customer_email', $customer->email)
            ->findOrFail($id);

        EcommerceHelper::registerThemeAssets();

        return Theme::scope(
            'ecommerce.customers.quote-requests.show',
            compact('quoteRequest'),
            'plugins/ecommerce::themes.customers.quote-requests.show'
        )->render();
    }

    public function updateStatus(Request $request, $id)
    {
        $customer = auth('customer')->user();
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $quoteRequest = QuoteRequest::where('customer_email', $customer->email)
            ->findOrFail($id);

        $status = $request->input('status');
        
        if (!in_array($status, ['accepted', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $oldStatus = $quoteRequest->status;
        $quoteRequest->update(['status' => $status]);

        // Create notification for vendor and admin
        $this->createStatusChangeNotifications($quoteRequest, $oldStatus, $status, $customer);

        $message = $status === 'accepted' 
            ? 'Quote accepted successfully! You can now proceed to payment.'
            : 'Quote has been rejected.';

        return response()->json(['success' => true, 'message' => $message]);
    }

    private function createStatusChangeNotifications(QuoteRequest $quoteRequest, string $oldStatus, string $newStatus, $customer)
    {
        $statusText = ucfirst($newStatus);
        $title = "Quote Request #{$quoteRequest->id} {$statusText}";
        $message = "Customer {$customer->name} has {$newStatus} quote request #{$quoteRequest->id}";

        // Notify vendor if exists
        if ($quoteRequest->vendor_id) {
            QuoteNotification::createNotification(
                $quoteRequest,
                'status_change',
                'vendor',
                $quoteRequest->vendor_id,
                $title,
                $message,
                ['old_status' => $oldStatus, 'new_status' => $newStatus]
            );
        }

        // Notify admin
        QuoteNotification::createNotification(
            $quoteRequest,
            'status_change',
            'admin',
            null,
            $title,
            $message,
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    public function sendMessage(Request $request, $id)
    {
        $customer = auth('customer')->user();
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $quoteRequest = QuoteRequest::where('customer_email', $customer->email)
            ->findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = QuoteMessage::create([
            'quote_request_id' => $quoteRequest->id,
            'sender_type' => 'customer',
            'sender_id' => $customer->id,
            'sender_name' => $customer->name,
            'sender_email' => $customer->email,
            'message' => $request->message
        ]);

        // Create notifications for new message
        $this->createMessageNotifications($quoteRequest, $message, $customer);

        return response()->json(['success' => true, 'message' => 'Message sent successfully']);
    }

    private function createMessageNotifications(QuoteRequest $quoteRequest, QuoteMessage $message, $customer)
    {
        $title = "New Message - Quote Request #{$quoteRequest->id}";
        $notificationMessage = "New message from {$customer->name}: " . \Str::limit($message->message, 50);

        // Notify vendor if exists
        if ($quoteRequest->vendor_id) {
            QuoteNotification::createNotification(
                $quoteRequest,
                'new_message',
                'vendor',
                $quoteRequest->vendor_id,
                $title,
                $notificationMessage,
                ['message_id' => $message->id]
            );
        }

        // Notify admin
        QuoteNotification::createNotification(
            $quoteRequest,
            'new_message',
            'admin',
            null,
            $title,
            $notificationMessage,
            ['message_id' => $message->id]
        );
    }

    public function getMessages($id)
    {
        $customer = auth('customer')->user();
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $quoteRequest = QuoteRequest::where('customer_email', $customer->email)
            ->findOrFail($id);

        $messages = $quoteRequest->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark customer messages as read
        $quoteRequest->messages()
            ->where('sender_type', '!=', 'customer')
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->sender_name,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('M d, Y g:i A'),
                    'timestamp' => $message->timestamp
                ];
            })
        ]);
    }

    public function getNotifications()
    {
        $customer = auth('customer')->user();
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notifications = QuoteNotification::forRecipient('customer', $customer->id)
            ->with('quoteRequest')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'quote_id' => $notification->quote_request_id
                ];
            }),
            'unread_count' => $notifications->where('is_read', false)->count()
        ]);
    }

    public function markNotificationAsRead($notificationId)
    {
        $customer = auth('customer')->user();
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = QuoteNotification::forRecipient('customer', $customer->id)
            ->findOrFail($notificationId);

        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    }
}