@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Quote Request Details'))

@section('content')
    @php
        EcommerceHelper::registerThemeAssets();
    @endphp

    <div class="customer-list-order">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('customer.quote-requests.index') }}" class="btn btn-outline-secondary">
                <x-core::icon name="ti ti-arrow-left" class="me-2" />
                {{ __('Back to Quote Requests') }}
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
                            <div class="h4 text-success mb-1">${{ number_format($quoteRequest->quoted_price, 2) }}</div>
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
                                            <x-core::icon name="ti ti-external-link" class="me-2" />
                                            {{ __('View Product') }}
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
                                    <div class="info-item">
                                        <span class="label text-muted small">{{ __('Quantity Requested') }}</span>
                                        <span class="value fw-semibold d-block">{{ number_format($quoteRequest->quantity) }} {{ __('units') }}</span>
                                    </div>
                                </div>
                                @if($quoteRequest->budget_range)
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <span class="label text-muted small">{{ __('Budget Range') }}</span>
                                            <span class="value fw-semibold d-block">{{ $quoteRequest->budget_range_label }}</span>
                                        </div>
                                    </div>
                                @endif
                                @if($quoteRequest->timeline)
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <span class="label text-muted small">{{ __('Timeline') }}</span>
                                            <span class="value fw-semibold d-block">{{ $quoteRequest->timeline_label }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($quoteRequest->project_description)
                                <div class="mt-3">
                                    <div class="info-item">
                                        <span class="label text-muted small">{{ __('Project Description') }}</span>
                                        <div class="bg-light p-3 rounded mt-2">
                                            {{ $quoteRequest->project_description }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Quote Response (if available) -->
                        @if($quoteRequest->status == 'quoted' && ($quoteRequest->quoted_description || $quoteRequest->delivery_time || $quoteRequest->terms_and_conditions))
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-success">{{ __('Vendor Quote Response') }}</h6>
                                
                                @if($quoteRequest->quoted_description)
                                    <div class="info-item mb-3">
                                        <span class="label text-muted small">{{ __('Quote Description') }}</span>
                                        <div class="bg-light p-3 rounded mt-2">{{ $quoteRequest->quoted_description }}</div>
                                    </div>
                                @endif

                                @if($quoteRequest->delivery_time)
                                    <div class="info-item mb-3">
                                        <span class="label text-muted small">{{ __('Delivery Time') }}</span>
                                        <span class="value fw-semibold d-block mt-1">{{ $quoteRequest->delivery_time }}</span>
                                    </div>
                                @endif

                                @if($quoteRequest->terms_and_conditions)
                                    <div class="info-item mb-3">
                                        <span class="label text-muted small">{{ __('Terms & Conditions') }}</span>
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
                            <div class="info-item mb-3">
                                <span class="label text-muted small">{{ __('Name') }}</span>
                                <span class="value fw-semibold d-block">{{ $quoteRequest->customer_name }}</span>
                            </div>
                            <div class="info-item mb-3">
                                <span class="label text-muted small">{{ __('Email') }}</span>
                                <span class="value fw-semibold d-block">{{ $quoteRequest->customer_email }}</span>
                            </div>
                            @if($quoteRequest->customer_phone)
                                <div class="info-item mb-3">
                                    <span class="label text-muted small">{{ __('Phone') }}</span>
                                    <span class="value fw-semibold d-block">{{ $quoteRequest->customer_phone }}</span>
                                </div>
                            @endif
                            @if($quoteRequest->customer_company)
                                <div class="info-item mb-3">
                                    <span class="label text-muted small">{{ __('Company') }}</span>
                                    <span class="value fw-semibold d-block">{{ $quoteRequest->customer_company }}</span>
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
                                        <x-core::icon name="ti ti-check" class="me-2" />
                                        {{ __('Accept Quote') }}
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="rejectQuote({{ $quoteRequest->id }})">
                                        <x-core::icon name="ti ti-x" class="me-2" />
                                        {{ __('Reject Quote') }}
                                    </button>
                                </div>
                            @elseif($quoteRequest->status == 'accepted')
                                <div class="alert alert-success">
                                    <x-core::icon name="ti ti-check" class="me-2" />
                                    {{ __('Quote Accepted - We will contact you to arrange payment and delivery') }}
                                </div>
                            @endif
                        </div>
                        @if($quoteRequest->quoted_price)
                            <div class="text-end">
                                <div class="h5 text-success mb-0">${{ number_format($quoteRequest->quoted_price, 2) }}</div>
                                <small class="text-muted">{{ __('Total Quote') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@push('scripts')
<script>
function acceptQuote(quoteId) {
    if (confirm('{{ __("Are you sure you want to accept this quote?") }}')) {
        updateQuoteStatus(quoteId, 'accepted');
    }
}

function rejectQuote(quoteId) {
    if (confirm('{{ __("Are you sure you want to reject this quote?") }}')) {
        updateQuoteStatus(quoteId, 'rejected');
    }
}

function updateQuoteStatus(quoteId, status) {
    fetch(`/customer/quote-requests/${quoteId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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
</script>
@endpush