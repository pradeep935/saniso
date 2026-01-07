@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::affiliate.account_banned'))

@section('content')
    <div class="affiliate-banned-page">
        <div class="banned-card text-center">
            <div class="banned-icon mb-4">
                <x-core::icon name="ti ti-lock" class="text-danger" style="font-size: 5rem;" />
            </div>
            
            <h2 class="banned-title mb-3">{{ trans('plugins/affiliate-pro::affiliate.account_banned') }}</h2>
            
            <div class="banned-message mb-4">
                <p class="lead">{{ trans('plugins/affiliate-pro::affiliate.account_banned_message') }}</p>
            </div>
            
            <div class="banned-actions">
                <a href="{{ route('public.index') }}" class="btn btn-primary d-flex align-items-center justify-content-center">
                    <x-core::icon name="ti ti-home" class="me-2" />
                    {{ trans('plugins/affiliate-pro::affiliate.return_to_home') }}
                </a>
                
                <a href="{{ route('customer.overview') }}" class="btn btn-outline-secondary ms-2 d-flex align-items-center justify-content-center">
                    <x-core::icon name="ti ti-user" class="me-2" />
                    {{ trans('plugins/affiliate-pro::affiliate.go_to_account') }}
                </a>
            </div>
            
            <div class="contact-support mt-5">
                <p class="text-muted">
                    {{ trans('plugins/affiliate-pro::affiliate.contact_support_message') }}
                </p>
            </div>
        </div>
    </div>

    <style>
        .affiliate-banned-page {
            min-height: 400px;
        }
        
        .banned-card {
            background: #fff;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
        }
        
        .banned-icon {
            display: inline-block;
            padding: 2rem;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 50%;
        }
        
        .banned-title {
            color: #dc3545;
            font-weight: 600;
        }
        
        .banned-message {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .banned-message p {
            color: #666;
            margin-bottom: 0;
        }
        
        .banned-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .contact-support {
            border-top: 1px solid #e9ecef;
            padding-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .banned-card {
                padding: 2rem 1.5rem;
            }
            
            .banned-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .banned-actions .btn {
                width: 100%;
                max-width: 250px;
            }
            
            .banned-actions .ms-2 {
                margin-left: 0 !important;
                margin-top: 0.5rem;
            }
        }
    </style>
@endsection