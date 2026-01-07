<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\QuoteRequest;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VendorQuoteRequestController extends BaseController
{
    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        
        if (!$customer || !$customer->is_vendor) {
            abort(404);
        }

        // Get store products IDs
        $store = $customer->store;
        $productIds = $store->products()->pluck('ec_products.id')->toArray();

        // Get quote requests for vendor's products
        $quoteRequests = QuoteRequest::with(['product', 'quotedBy'])
            ->whereIn('product_id', $productIds)
            ->when($request->get('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Statistics
        $stats = [
            'total' => QuoteRequest::whereIn('product_id', $productIds)->count(),
            'pending' => QuoteRequest::whereIn('product_id', $productIds)->where('status', 'pending')->count(),
            'quoted' => QuoteRequest::whereIn('product_id', $productIds)->where('status', 'quoted')->count(),
            'accepted' => QuoteRequest::whereIn('product_id', $productIds)->where('status', 'accepted')->count(),
        ];

        Theme::breadcrumb()
            ->add(__('Vendor Dashboard'), route('marketplace.vendor.dashboard'))
            ->add(__('Quote Requests'), route('marketplace.vendor.quote-requests.index'));

        return Theme::scope('marketplace.vendor.quote-requests.index', compact('quoteRequests', 'stats'), MarketplaceHelper::viewPath('vendor.quote-requests.index'))->render();
    }

    public function show($id)
    {
        $customer = auth('customer')->user();
        
        if (!$customer || !$customer->is_vendor) {
            abort(404);
        }

        // Get store products IDs
        $store = $customer->store;
        $productIds = $store->products()->pluck('ec_products.id')->toArray();

        $quoteRequest = QuoteRequest::with(['product', 'quotedBy'])
            ->whereIn('product_id', $productIds)
            ->findOrFail($id);

        Theme::breadcrumb()
            ->add(__('Vendor Dashboard'), route('marketplace.vendor.dashboard'))
            ->add(__('Quote Requests'), route('marketplace.vendor.quote-requests.index'))
            ->add(__('Quote #:id', ['id' => $quoteRequest->id]), route('marketplace.vendor.quote-requests.show', $quoteRequest->id));

        return Theme::scope('marketplace.vendor.quote-requests.show', compact('quoteRequest'), MarketplaceHelper::viewPath('vendor.quote-requests.show'))->render();
    }

    public function respond(Request $request, $id): JsonResponse
    {
        $customer = auth('customer')->user();
        
        if (!$customer || !$customer->is_vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Validate request
        $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'quoted_description' => 'nullable|string|max:1000',
            'delivery_time' => 'nullable|string|max:255',
            'terms_and_conditions' => 'nullable|string|max:2000',
        ]);

        // Get store products IDs
        $store = $customer->store;
        $productIds = $store->products()->pluck('ec_products.id')->toArray();

        $quoteRequest = QuoteRequest::whereIn('product_id', $productIds)
            ->findOrFail($id);

        // Update quote with vendor response
        $quoteRequest->update([
            'vendor_id' => $customer->id,
            'quoted_price' => $request->input('quoted_price'),
            'quoted_description' => $request->input('quoted_description'),
            'delivery_time' => $request->input('delivery_time'),
            'terms_and_conditions' => $request->input('terms_and_conditions'),
            'quoted_at' => now(),
            'quoted_by' => auth()->id(),
            'status' => 'quoted',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Quote response sent successfully. The customer will be notified.'),
        ]);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $customer = auth('customer')->user();
        
        if (!$customer || !$customer->is_vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $status = $request->input('status');
        
        if (!in_array($status, ['processing', 'completed', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        // Get store products IDs
        $store = $customer->store;
        $productIds = $store->products()->pluck('ec_products.id')->toArray();

        $quoteRequest = QuoteRequest::whereIn('product_id', $productIds)
            ->findOrFail($id);

        $quoteRequest->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => __('Status updated successfully'),
        ]);
    }
}