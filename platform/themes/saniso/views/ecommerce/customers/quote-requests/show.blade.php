@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Quote Request Details'))

@section('content')
    <div class="customer-list-order">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('customer.quote-requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>{{ __('Back to Quote Requests') }}
            </a>
        </div>

        <!-- Quote Request Details Card -->
        <div class="bb-customer-card order-card">
            <!-- Quote Header -->
            <div class="bb-customer-card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="bb-customer-card-title h5 mb-2">
                            {{ __('Quote Request #:id', ['id' => $quoteRequest->id]) }}
                        </h3>
                        <div class="bb-customer-card-status mb-2">
                            <span class="badge fs-6 bg-{{ $quoteRequest->status == 'pending' ? 'warning' : ($quoteRequest->status == 'quoted' ? 'info' : ($quoteRequest->status == 'accepted' ? 'success' : 'secondary')) }}">
                                {{ __(ucfirst($quoteRequest->status)) }}
                            </span>
                        </div>
                        <p class="text-muted small mb-0">
                            {{ __('Submitted on') }}: {{ $quoteRequest->created_at->translatedFormat('M d, Y \a\t g:i A') }}
                        </p>
                    </div>
                    <div class="text-end">
                        @if($quoteRequest->quoted_price)
                            <div class="h4 text-success mb-1">{{ format_price($quoteRequest->quoted_price) }}</div>
                            <small class="text-muted">{{ __('Quoted Price') }}</small>
                            @if($quoteRequest->quoted_at)
                                <div class="text-muted small mt-1">
                                    {{ __('Quoted on') }}: {{ $quoteRequest->quoted_at->format('M d, Y') }}
                                </div>
                            @endif
                        @else
                            <div class="text-muted">{{ __('Awaiting Quote') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quote Details -->
            <div class="bb-customer-card-body">
                <div class="row g-4">
                    <!-- Product Information -->
                    <div class="col-lg-8">
                        @if($quoteRequest->product)
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">{{ __('Product Information') }}</h6>
                                <div class="d-flex align-items-start">
                                    @if($quoteRequest->product->image)
                                        <img src="{{ RvMedia::getImageUrl($quoteRequest->product->image, 'thumb') }}" 
                                             alt="{{ $quoteRequest->product->name }}" 
                                             class="rounded me-3" 
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">{{ $quoteRequest->product->name }}</h6>
                                        @if($quoteRequest->product->sku)
                                            <div class="text-muted small mb-2">{{ __('SKU') }}: {{ $quoteRequest->product->sku }}</div>
                                        @endif
                                        @if($quoteRequest->product->price)
                                            <div class="text-success fw-semibold">${{ number_format($quoteRequest->product->price, 2) }}</div>
                                        @endif
                                        <a href="{{ $quoteRequest->product->url }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-external-link-alt me-2"></i>{{ __('View Product') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Request Details -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">{{ __('Request Details') }}</h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="text-muted small fw-semibold">{{ __('Quantity Requested') }}</label>
                                        <div class="h6">{{ number_format($quoteRequest->quantity) }} {{ __('units') }}</div>
                                    </div>
                                </div>
                                @if($quoteRequest->budget_range)
                                    <div class="col-sm-6">
                                        <div class="mb-3">
                                            <label class="text-muted small fw-semibold">{{ __('Budget Range') }}</label>
                                            <div>{{ $quoteRequest->budget_range_label }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($quoteRequest->timeline)
                                    <div class="col-sm-6">
                                        <div class="mb-3">
                                            <label class="text-muted small fw-semibold">{{ __('Timeline') }}</label>
                                            <div>{{ $quoteRequest->timeline_label }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($quoteRequest->project_description)
                                <div class="mt-3">
                                    <label class="text-muted small fw-semibold">{{ __('Project Description') }}</label>
                                    <div class="bg-light p-3 rounded mt-2">
                                        {{ $quoteRequest->project_description }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Quote Response (if available) -->
                        @if($quoteRequest->status == 'quoted' && ($quoteRequest->quoted_description || $quoteRequest->delivery_time || $quoteRequest->terms_and_conditions))
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-success">{{ __('Vendor Quote Response') }}</h6>
                                
                                @if($quoteRequest->quoted_description)
                                    <div class="mb-3">
                                        <label class="text-muted small fw-semibold">{{ __('Quote Description') }}</label>
                                        <div class="bg-light p-3 rounded mt-2">{{ $quoteRequest->quoted_description }}</div>
                                    </div>
                                @endif

                                @if($quoteRequest->delivery_time)
                                    <div class="mb-3">
                                        <label class="text-muted small fw-semibold">{{ __('Delivery Time') }}</label>
                                        <div class="mt-1">{{ $quoteRequest->delivery_time }}</div>
                                    </div>
                                @endif

                                @if($quoteRequest->terms_and_conditions)
                                    <div class="mb-3">
                                        <label class="text-muted small fw-semibold">{{ __('Terms & Conditions') }}</label>
                                        <div class="bg-light p-3 rounded mt-2">{{ $quoteRequest->terms_and_conditions }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Customer Information -->
                    <div class="col-lg-4">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold mb-3">{{ __('Contact Information') }}</h6>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold">{{ __('Name') }}</label>
                                <div>{{ $quoteRequest->customer_name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-semibold">{{ __('Email') }}</label>
                                <div>{{ $quoteRequest->customer_email }}</div>
                            </div>
                            @if($quoteRequest->customer_phone)
                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold">{{ __('Phone') }}</label>
                                    <div>{{ $quoteRequest->customer_phone }}</div>
                                </div>
                            @endif
                            @if($quoteRequest->customer_company)
                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold">{{ __('Company') }}</label>
                                    <div>{{ $quoteRequest->customer_company }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Footer -->
            @if($quoteRequest->status == 'quoted' || $quoteRequest->status == 'accepted')
                <div class="bb-customer-card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($quoteRequest->status == 'quoted')
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-success" 
                                            onclick="acceptQuote({{ $quoteRequest->id }})">
                                        <i class="fas fa-check me-2"></i>{{ __('Accept Quote') }}
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="rejectQuote({{ $quoteRequest->id }})">
                                        <i class="fas fa-times me-2"></i>{{ __('Reject Quote') }}
                                    </button>
                                </div>
                            @elseif($quoteRequest->status == 'accepted')
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>{{ __('Quote Accepted') }} - {{ __('We will contact you to arrange payment and delivery.') }}
                                </div>
                            @endif
                        </div>
                        @if($quoteRequest->quoted_price)
                            <div class="text-end">
                                <div class="h5 text-success mb-0">{{ format_price($quoteRequest->quoted_price) }}</div>
                                <small class="text-muted">{{ __('Total Quote') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quote Management JavaScript -->
    <script>
    // Make functions globally available  
    window.acceptQuote = function(quoteId) {
        console.log('Accept Quote clicked for ID:', quoteId);
        if (confirm('{{ __("Are you sure you want to accept this quote?") }}')) {
            updateQuoteStatus(quoteId, 'accepted');
        }
    };

    window.rejectQuote = function(quoteId) {
        console.log('Reject Quote clicked for ID:', quoteId);
        if (confirm('{{ __("Are you sure you want to reject this quote?") }}')) {
            updateQuoteStatus(quoteId, 'rejected');
        }
    };

    function updateQuoteStatus(quoteId, status) {
        console.log('Updating status for quote:', quoteId, 'to:', status);
        
        fetch(`/customer/quote-requests/${quoteId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => {
            console.log('Response received:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                alert(data.message || 'Status updated successfully');
                location.reload();
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    }

    // Debug: Check if functions are properly loaded
    console.log('Quote functions loaded:', {
        acceptQuote: typeof window.acceptQuote,
        rejectQuote: typeof window.rejectQuote
    });
    </script>
@endsection