<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Ecommerce\Models\QuoteRequest;
use Botble\Ecommerce\Models\QuoteSettings;
use Botble\Ecommerce\Models\QuoteNotification;
use Botble\Ecommerce\Models\Product;
use App\Services\QuoteFormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Event;

class PublicQuoteController
{
    public function store(Request $request)
    {
        // Add debug logging
        \Log::info('Quote request received', [
            'method' => $request->method(),
            'url' => $request->url(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Use the QuoteFormService for validation and processing
        $formService = app(\App\Services\QuoteFormService::class);

        // Check if form builder is enabled
        if (!$formService->isFormBuilderEnabled()) {
            // Fallback to original validation for backward compatibility
            return $this->storeWithOriginalValidation($request);
        }

        // Validate using dynamic form rules
        $validator = Validator::make(
            $request->all(),
            $formService->getValidationRules(),
            $formService->getValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => __('Please check your form data.'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Process form data using the service
            $processedData = $formService->processFormData($request->all());

            // Get the product
            $product = Product::findOrFail($request->product_id);

            // Create quote request with mapped data
            $quoteRequest = QuoteRequest::create([
                'product_id' => $request->product_id,
                'customer_name' => $processedData['customer_name'] ?? '',
                'customer_email' => $processedData['customer_email'] ?? '',
                'customer_phone' => $processedData['customer_phone'] ?? null,
                'customer_company' => $processedData['company_name'] ?? null,
                'quantity' => $processedData['quantity'] ?? 1,
                'message' => $processedData['requirements'] ?? $processedData['message'] ?? '',
                'budget_range' => $processedData['budget_range'] ?? null,
                'urgency' => $processedData['urgency'] ?? null,
                'status' => 'pending',
                'custom_fields' => json_encode($processedData)
            ]);

            // Send notification emails and create notifications
            $this->sendNotifications($quoteRequest, QuoteSettings::getInstance());

            // Create database notifications for admin and vendor
            $this->createNewQuoteNotifications($quoteRequest);

            return response()->json([
                'error' => false,
                'message' => __('Thank you! Your quote request has been sent successfully. We will contact you within 24 hours.'),
                'quote_id' => $quoteRequest->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Quote request error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => true,
                'message' => __('Sorry, there was an error processing your request. Please try again.')
            ], 500);
        }
    }

    /**
     * Fallback method for original validation (backward compatibility)
     */
    private function storeWithOriginalValidation(Request $request)
    {
        // Check if quote system is enabled
        if (!QuoteSettings::isEnabled()) {
            return response()->json([
                'error' => true,
                'message' => __('Quote system is currently disabled.')
            ], 400);
        }

        $settings = QuoteSettings::getInstance();
        $formFields = $settings->getEnabledFormFields();

        // Build validation rules based on enabled form fields
        $rules = [
            'product_id' => 'required|exists:ec_products,id',
        ];

        foreach ($formFields as $field => $config) {
            if ($config['required'] ?? false) {
                $rules[$field] = 'required';
            }
        }

        // Add specific field validations
        if (isset($formFields['customer_email'])) {
            $rules['customer_email'] = ($formFields['customer_email']['required'] ?? false) ? 'required|email' : 'nullable|email';
        }
        if (isset($formFields['quantity'])) {
            $rules['quantity'] = ($formFields['quantity']['required'] ?? false) ? 'required|integer|min:1' : 'nullable|integer|min:1';
        }
        if (isset($formFields['customer_phone'])) {
            $rules['customer_phone'] = 'nullable|string|max:20';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => __('Please check your form data.'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the product
            $product = Product::findOrFail($request->product_id);

            // Check if this product should show quote form
            if (!QuoteSettings::shouldShowQuoteForm($product)) {
                return response()->json([
                    'error' => true,
                    'message' => __('Quote requests are not available for this product.')
                ], 400);
            }

            // Check if user needs to be logged in
            if ($settings->require_login && !auth('customer')->check()) {
                return response()->json([
                    'error' => true,
                    'message' => __('Please log in to submit a quote request.')
                ], 401);
            }

            // Prepare data for storage
            $data = [];
            foreach ($formFields as $field => $config) {
                if ($request->has($field)) {
                    $data[$field] = $request->input($field);
                }
            }

            // Add required fields
            $data['product_id'] = $request->product_id;

            // Handle special requirements array
            if ($request->has('special_requirements')) {
                $data['special_requirements'] = $request->special_requirements;
            }

            // Handle newsletter subscription
            $data['newsletter_subscribe'] = $request->boolean('newsletter_subscribe');

            // Create quote request
            $quoteRequest = QuoteRequest::create($data);

            // Send notifications
            $this->sendNotifications($quoteRequest, $settings);

            // Create database notifications for admin and vendor
            $this->createNewQuoteNotifications($quoteRequest);

            return response()->json([
                'error' => false,
                'message' => __('Thank you! Your quote request has been submitted successfully. We will contact you within :time.', [
                    'time' => $settings->response_time ?? '24 hours'
                ])
            ]);

        } catch (\Exception $e) {
            \Log::error('Quote request submission failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => true,
                'message' => __('Sorry, there was an error processing your request. Please try again or contact us directly.')
            ], 500);
        }
    }

    private function sendNotifications(QuoteRequest $quoteRequest, QuoteSettings $settings)
    {
        try {
            // Send customer confirmation email
            if ($settings->send_customer_confirmation) {
                Mail::send('emails.quote-request-confirmation', ['quoteRequest' => $quoteRequest], function ($message) use ($quoteRequest) {
                    $message->to($quoteRequest->customer_email, $quoteRequest->customer_name)
                            ->subject('Quote Request Confirmation - ' . ($quoteRequest->product->name ?? 'Product'));
                });
            }

            // Send admin notification email
            if ($settings->send_admin_notification && $settings->admin_email) {
                Mail::send('emails.quote-request-admin', ['quoteRequest' => $quoteRequest], function ($message) use ($settings, $quoteRequest) {
                    $message->to($settings->admin_email)
                            ->subject('New Quote Request - ' . ($quoteRequest->product->name ?? 'Product'));
                });
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send quote request notifications: ' . $e->getMessage());
        }
    }

    /**
     * Create database notifications for new quote requests
     */
    private function createNewQuoteNotifications(QuoteRequest $quoteRequest)
    {
        try {
            $product = $quoteRequest->product;
            $title = "New Quote Request #{$quoteRequest->id}";
            $message = "New quote request received for '{$product->name}' from {$quoteRequest->customer_name}";

            // Create notification for admin
            QuoteNotification::createNotification(
                $quoteRequest,
                'new_quote',
                'admin',
                null,
                $title,
                $message,
                [
                    'product_id' => $quoteRequest->product_id,
                    'customer_name' => $quoteRequest->customer_name,
                    'customer_email' => $quoteRequest->customer_email,
                    'quantity' => $quoteRequest->quantity,
                    'created_at' => $quoteRequest->created_at->toISOString()
                ]
            );

            // If product has a vendor, notify the vendor too
            if ($product->store_id) {
                QuoteNotification::createNotification(
                    $quoteRequest,
                    'new_quote',
                    'vendor',
                    $product->store_id,
                    $title,
                    $message,
                    [
                        'product_id' => $quoteRequest->product_id,
                        'customer_name' => $quoteRequest->customer_name,
                        'customer_email' => $quoteRequest->customer_email,
                        'quantity' => $quoteRequest->quantity,
                        'created_at' => $quoteRequest->created_at->toISOString()
                    ]
                );
            }

            // Broadcast real-time notification event
            Event::dispatch('new-quote-request', [
                'quote_id' => $quoteRequest->id,
                'title' => $title,
                'message' => $message,
                'product_name' => $product->name,
                'customer_name' => $quoteRequest->customer_name,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create quote request notifications: ' . $e->getMessage());
        }
    }
}