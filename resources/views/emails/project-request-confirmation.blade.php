<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Request Confirmation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
        }
        h1 {
            color: #28a745;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 15px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .info-row {
            display: table-row;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label, .info-value {
            display: table-cell;
            padding: 12px 8px;
            vertical-align: top;
        }
        .info-label {
            font-weight: 600;
            background-color: #f8f9fa;
            width: 30%;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
            width: 70%;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #28a745;
        }
        .section h3 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 18px;
        }
        .next-steps {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .next-steps h3 {
            color: #004085;
            margin-top: 0;
        }
        .next-steps ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
            color: #004085;
        }
        .contact-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .contact-info h3 {
            color: #856404;
            margin-top: 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .reference-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                padding: 20px 15px;
                margin: 10px;
            }
            .info-label, .info-value {
                display: block;
                width: 100%;
                padding: 8px 0;
            }
            .info-label {
                background-color: transparent;
                font-weight: 600;
                border-bottom: none;
            }
            .info-value {
                margin-bottom: 15px;
                border-bottom: 1px solid #e9ecef;
                padding-bottom: 15px;
            }
            .reference-number {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(theme_option('logo'))
                <img src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" class="logo">
            @endif
            <h1>Request Received!</h1>
        </div>

        <div class="success-message">
            <div class="success-icon">✅</div>
            <h2 style="color: #28a745; margin: 0;">Thank You {{ $projectRequest->customer_name }}!</h2>
            <p style="margin: 15px 0 0 0; color: #155724; font-size: 16px;">
                Your project request has been successfully submitted and is being reviewed by our team.
            </p>
        </div>

        <div class="reference-number">
            Reference #{{ $projectRequest->id }}
        </div>

        <div class="section">
            <h3>📋 Your Request Summary</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Submitted</div>
                    <div class="info-value">{{ $projectRequest->created_at->format('F j, Y \a\t g:i A') }}</div>
                </div>
                @if($projectRequest->customer_email)
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $projectRequest->customer_email }}</div>
                    </div>
                @endif
                @if($projectRequest->customer_phone)
                    <div class="info-row">
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $projectRequest->customer_phone }}</div>
                    </div>
                @endif
            </div>
        </div>

        @if($projectRequest->form_data)
            <div class="section">
                <h3>📝 Details Submitted</h3>
                <div class="info-grid">
                    @foreach(json_decode($projectRequest->form_data, true) ?? [] as $field => $value)
                        <div class="info-row">
                            <div class="info-label">{{ ucfirst(str_replace('_', ' ', $field)) }}</div>
                            <div class="info-value">
                                @if(is_array($value))
                                    {{ implode(', ', $value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="next-steps">
            <h3>🚀 What Happens Next?</h3>
            <ol>
                <li>Our team will review your project requirements within 24 hours</li>
                <li>We'll contact you via {{ $projectRequest->customer_email ?? 'email' }} or phone to discuss details</li>
                <li>We'll provide you with a detailed proposal and timeline</li>
                <li>Once approved, we'll schedule a project kickoff meeting</li>
            </ol>
        </div>

        <div class="contact-info">
            <h3>📞 Need to reach us?</h3>
            <p><strong>Email:</strong> {{ theme_option('admin_email') ?? config('mail.from.address') }}</p>
            @if(theme_option('hotline'))
                <p><strong>Phone:</strong> {{ theme_option('hotline') }}</p>
            @endif
            <p><strong>Reference Number:</strong> #{{ $projectRequest->id }}</p>
            <p style="margin-top: 15px; color: #856404;">
                Please include your reference number in all communications for faster assistance.
            </p>
        </div>

        <div class="footer">
            <p><strong>{{ theme_option('site_title') ?? config('app.name') }}</strong></p>
            <p>We're excited to work with you on your project!</p>
            <p style="margin-top: 15px; font-size: 12px;">
                This is an automated confirmation email. Please do not reply directly to this address.
            </p>
        </div>
    </div>
</body>
</html>