<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quote Request Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 20px; }
        .details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quote Request Confirmation</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $quoteRequest->customer_name }},</p>
            
            <p>Thank you for your quote request! We have received your inquiry and will get back to you within 24 hours.</p>
            
            <div class="details">
                <h3>Request Details:</h3>
                <ul>
                    <li><strong>Product:</strong> {{ $quoteRequest->product->name ?? 'N/A' }}</li>
                    <li><strong>Quantity:</strong> {{ $quoteRequest->quantity }}</li>
                    @if($quoteRequest->area_size)
                        <li><strong>Area Size:</strong> {{ $quoteRequest->area_size }}</li>
                    @endif
                    @if($quoteRequest->budget_range_label)
                        <li><strong>Budget Range:</strong> {{ $quoteRequest->budget_range_label }}</li>
                    @endif
                    @if($quoteRequest->timeline_label)
                        <li><strong>Timeline:</strong> {{ $quoteRequest->timeline_label }}</li>
                    @endif
                </ul>
                
                @if($quoteRequest->project_description)
                    <p><strong>Project Description:</strong><br>{{ $quoteRequest->project_description }}</p>
                @endif
            </div>
            
            <p>Our team will review your requirements and prepare a detailed quote for you. You can expect to hear from us soon!</p>
            
            <p>If you have any questions in the meantime, please don't hesitate to contact us.</p>
            
            <p>Best regards,<br>{{ config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>