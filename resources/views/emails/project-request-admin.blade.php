<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Project Request Received</title>
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
            color: #495057;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
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
            border-left: 4px solid #007bff;
        }
        .section h3 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 18px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .file-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .file-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .file-list li:last-child {
            border-bottom: none;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(theme_option('logo'))
                <img src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" class="logo">
            @endif
            <h1>New Project Request</h1>
        </div>

        <div class="alert">
            <strong>📋 New Project Request Received</strong><br>
            A new project request has been submitted through your website. Please review the details below and respond promptly.
        </div>

        <div class="section">
            <h3>📋 Request Information</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Request ID</div>
                    <div class="info-value">#{{ $projectRequest->id }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Submitted</div>
                    <div class="info-value">{{ $projectRequest->created_at->format('F j, Y \a\t g:i A') }}</div>
                </div>
                @if($projectRequest->customer_name)
                    <div class="info-row">
                        <div class="info-label">Customer Name</div>
                        <div class="info-value">{{ $projectRequest->customer_name }}</div>
                    </div>
                @endif
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
                <h3>📝 Form Details</h3>
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

        @if($projectRequest->attachments && count($projectRequest->attachments) > 0)
            <div class="section">
                <h3>📎 Attachments</h3>
                <ul class="file-list">
                    @foreach($projectRequest->attachments as $attachment)
                        <li>
                            <strong>{{ $attachment['name'] ?? 'Attachment' }}</strong>
                            @if(isset($attachment['size']))
                                <span style="color: #6c757d;">({{ number_format($attachment['size'] / 1024, 1) }} KB)</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            @if(route('project-requests.show', $projectRequest->id))
                <a href="{{ route('project-requests.show', $projectRequest->id) }}" class="btn">
                    View Request Details
                </a>
            @endif
        </div>

        <div class="footer">
            <p>This email was automatically generated by {{ theme_option('site_title') ??=config('app.name') }}.</p>
            <p>Please do not reply to this email address.</p>
        </div>
    </div>
</body>
</html>