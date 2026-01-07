<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Quote Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 20px; }
        .details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .customer { background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        .urgent { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Quote Request</h1>
        </div>
        
        <div class="content">
            <p><strong>A new quote request has been submitted and requires your attention.</strong></p>
            
            <div class="customer">
                <h3>Customer Information:</h3>
                <ul>
                    <li><strong>Name:</strong> {{ $quoteRequest->customer_name }}</li>
                    <li><strong>Email:</strong> {{ $quoteRequest->customer_email }}</li>
                    @if($quoteRequest->customer_phone)
                        <li><strong>Phone:</strong> {{ $quoteRequest->customer_phone }}</li>
                    @endif
                    @if($quoteRequest->customer_company)
                        <li><strong>Company:</strong> {{ $quoteRequest->customer_company }}</li>
                    @endif
                </ul>
            </div>
            
            <div class="details">
                <h3>Request Details:</h3>
                <ul>
                    <li><strong>Product:</strong> {{ $quoteRequest->product->name ?? 'N/A' }}</li>
                    <li><strong>Product SKU:</strong> {{ $quoteRequest->product->sku ?? 'N/A' }}</li>
                    <li><strong>Quantity:</strong> {{ $quoteRequest->quantity }}</li>
                    @if($quoteRequest->area_size)
                        <li><strong>Area Size:</strong> {{ $quoteRequest->area_size }}</li>
                    @endif
                    @if($quoteRequest->room_type_label)
                        <li><strong>Room Type:</strong> {{ $quoteRequest->room_type_label }}</li>
                    @endif
                    @if($quoteRequest->installation_needed)
                        <li><strong>Installation:</strong> {{ ucfirst($quoteRequest->installation_needed) }}</li>
                    @endif
                    @if($quoteRequest->budget_range_label)
                        <li><strong>Budget Range:</strong> {{ $quoteRequest->budget_range_label }}</li>
                    @endif
                    @if($quoteRequest->timeline_label)
                        <li><strong>Timeline:</strong> {{ $quoteRequest->timeline_label }}</li>
                    @endif
                    <li><strong>Submitted:</strong> {{ $quoteRequest->created_at->format('M d, Y \a\t H:i') }}</li>
                </ul>
                
                @if($quoteRequest->project_description)
                    <p><strong>Project Description:</strong><br>{{ $quoteRequest->project_description }}</p>
                @endif
                
                @if($quoteRequest->special_requirements)
                    <p><strong>Special Requirements:</strong><br>
                    @foreach($quoteRequest->special_requirements as $requirement)
                        • {{ ucfirst(str_replace('_', ' ', $requirement)) }}<br>
                    @endforeach
                    </p>
                @endif
            </div>
            
            @if($quoteRequest->timeline === 'urgent')
                <p class="urgent">⚠️ This is marked as URGENT - Customer needs quote ASAP (1-2 weeks)</p>
            @endif
            
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Review the customer requirements</li>
                <li>Prepare a detailed quote</li>
                <li>Respond within your promised timeframe</li>
            </ol>
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/admin/quote-requests/' . $quoteRequest->id) }}" 
                   style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    View & Respond to Quote Request
                </a>
            </p>
        </div>
        
        <div class="footer">
            <p>This notification was sent to {{ $settings->admin_email ?? config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>