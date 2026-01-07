<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\QuoteMessage;
use Botble\Ecommerce\Models\QuoteNotification;
use Botble\Ecommerce\Models\QuoteRequest;
use Botble\Ecommerce\Models\QuoteSettings;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class QuoteRequestController extends BaseController
{
    public function index(Request $request)
    {
        $query = QuoteRequest::with(['product', 'quotedBy'])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_company', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $quoteRequests = $query->paginate(20);

        $stats = [
            'total' => QuoteRequest::count(),
            'pending' => QuoteRequest::where('status', 'pending')->count(),
            'in_progress' => QuoteRequest::where('status', 'in_progress')->count(),
            'quoted' => QuoteRequest::where('status', 'quoted')->count(),
            'this_month' => QuoteRequest::whereMonth('created_at', now()->month)->count(),
        ];

        return view('plugins/ecommerce::quote-requests.index', compact('quoteRequests', 'stats'));
    }

    public function show(QuoteRequest $quoteRequest)
    {
        $quoteRequest->load(['product.categories', 'quotedBy']);
        
        return view('plugins/ecommerce::quote-requests.show', compact('quoteRequest'));
    }

    public function update(Request $request, QuoteRequest $quoteRequest)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,quoted,accepted,rejected,completed',
            'admin_notes' => 'nullable|string',
            'quoted_price' => 'nullable|numeric|min:0',
            'quote_details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldStatus = $quoteRequest->status;
        $data = $request->only(['status', 'admin_notes', 'quoted_price', 'quote_details']);
        
        // If status is being changed to quoted, set quoted_at and quoted_by
        if ($request->status === 'quoted' && $quoteRequest->status !== 'quoted') {
            $data['quoted_at'] = now();
            $data['quoted_by'] = auth()->id();
        }

        $quoteRequest->update($data);

        // Create notification for customer if status changed
        if ($oldStatus !== $request->status) {
            $this->createStatusChangeNotification($quoteRequest, $oldStatus, $request->status);
        }

        // Send email notification if quote is sent
        if ($request->status === 'quoted' && $quoteRequest->wasChanged('status')) {
            $this->sendQuoteEmail($quoteRequest);
        }

        return back()->with('success', 'Quote request updated successfully.');
    }

    public function destroy(QuoteRequest $quoteRequest)
    {
        try {
            // Delete related notifications first
            \Botble\Ecommerce\Models\QuoteNotification::where('quote_request_id', $quoteRequest->id)->delete();
            
            // Delete the quote request
            $quoteRequest->delete();
            
            return back()->with('success', 'Quote request deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete quote request: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete quote request. Please try again.');
        }
    }

    public function settings()
    {
        $settings = QuoteSettings::getInstance();
        $categories = ProductCategory::all();
        $products = Product::select('id', 'name')->get();
        
        return view('plugins/ecommerce::quote-requests.settings', compact('settings', 'categories', 'products'));
    }

    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enable_quote_system' => 'boolean',
            'quote_categories' => 'nullable|array',
            'quote_categories.*' => 'exists:ec_product_categories,id',
            'quote_products' => 'nullable|array',
            'quote_products.*' => 'exists:ec_products,id',
            'quote_for_no_price_products' => 'boolean',
            'admin_email' => 'nullable|email',
            'send_customer_confirmation' => 'boolean',
            'send_admin_notification' => 'boolean',
            'response_time' => 'nullable|string|max:100',
            'require_login' => 'boolean',
            'max_file_uploads' => 'integer|min:0|max:20',
            'allowed_file_types' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = QuoteSettings::getInstance();

        // Use input() with sensible defaults so unchecked multi-selects are saved as empty arrays
        $data = $request->only([
            'enable_quote_system',
            'quote_for_no_price_products',
            'admin_email',
            'send_customer_confirmation',
            'send_admin_notification',
            'response_time',
            'quote_page_content',
            'require_login',
            'max_file_uploads',
            'allowed_file_types'
        ]);

        // Ensure categories and products are explicitly saved as arrays (empty when none selected)
        $data['quote_categories'] = $request->input('quote_categories', []);
        $data['quote_products'] = $request->input('quote_products', []);

        // Handle form fields configuration
        if ($request->has('form_fields')) {
            $data['form_fields'] = $request->form_fields;
        }

        // Handle budget ranges
        if ($request->has('budget_ranges')) {
            $data['budget_ranges'] = $request->budget_ranges;
        }

        // Handle timeline options
        if ($request->has('timeline_options')) {
            $data['timeline_options'] = $request->timeline_options;
        }

        // Handle room types
        if ($request->has('room_types')) {
            $data['room_types'] = $request->room_types;
        }

        $settings->update($data);

        return back()->with('success', 'Quote settings updated successfully.');
    }

    public function export(Request $request)
    {
        $query = QuoteRequest::with(['product', 'quotedBy']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $quoteRequests = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quote-requests.csv"',
        ];

        $callback = function () use ($quoteRequests) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Product',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Company',
                'Quantity',
                'Budget Range',
                'Timeline',
                'Status',
                'Quoted Price',
                'Created At',
                'Quoted At'
            ]);

            // CSV data
            foreach ($quoteRequests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->product->name ?? 'N/A',
                    $request->customer_name,
                    $request->customer_email,
                    $request->customer_phone ?? 'N/A',
                    $request->customer_company ?? 'N/A',
                    $request->quantity,
                    $request->budget_range_label ?? 'N/A',
                    $request->timeline_label ?? 'N/A',
                    $request->status_label,
                    $request->quoted_price ?? 'N/A',
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->quoted_at ? $request->quoted_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function createStatusChangeNotification(QuoteRequest $quoteRequest, string $oldStatus, string $newStatus)
    {
        $statusText = ucfirst($newStatus);
        $title = "Quote Request #{$quoteRequest->id} {$statusText}";
        
        $message = match($newStatus) {
            'quoted' => "Your quote request has been reviewed and a price quote is ready for your review.",
            'in_progress' => "Your quote request is being processed by our team.",
            'completed' => "Your quote request has been completed successfully.",
            default => "Your quote request status has been updated to {$statusText}."
        };

        QuoteNotification::createNotification(
            $quoteRequest,
            'status_change',
            'customer',
            null,
            $title,
            $message,
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    private function sendQuoteEmail(QuoteRequest $quoteRequest)
    {
        try {
            // Send email to customer with quote details
            Mail::send('emails.quote-sent', ['quoteRequest' => $quoteRequest], function ($message) use ($quoteRequest) {
                $message->to($quoteRequest->customer_email, $quoteRequest->customer_name)
                        ->subject('Your Quote Request - ' . $quoteRequest->product->name);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send quote email: ' . $e->getMessage());
        }
    }

    public function sendMessage(Request $request, QuoteRequest $quoteRequest)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = QuoteMessage::create([
            'quote_request_id' => $quoteRequest->id,
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'sender_name' => Auth::user()->name,
            'sender_email' => Auth::user()->email,
            'message' => $request->message
        ]);

        // Create notification for customer
        QuoteNotification::createNotification(
            $quoteRequest,
            'new_message',
            'customer',
            null,
            "New Message - Quote Request #{$quoteRequest->id}",
            "New message from admin: " . \Str::limit($request->message, 50),
            ['message_id' => $message->id]
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Message sent successfully']);
        }

        return redirect()->back()->with('success', 'Message sent successfully');
    }

    public function getMessages(QuoteRequest $quoteRequest)
    {
        $messages = $quoteRequest->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->sender_name,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('M d, Y g:i A'),
                    'timestamp' => $message->created_at->diffForHumans()
                ];
            })
        ]);
    }
}