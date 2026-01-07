@extends('plugins/marketplace::themes.vendor-dashboard.layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">{{ __('Quote Request #:id', ['id' => $quoteRequest->id]) }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('marketplace.vendor.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('marketplace.vendor.quote-requests.index') }}">{{ __('Quote Requests') }}</a>
                                </li>
                                <li class="breadcrumb-item active">{{ __('Quote #:id', ['id' => $quoteRequest->id]) }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('marketplace.vendor.quote-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('Back to List') }}
                        </a>
                        @if($quoteRequest->status == 'pending')
                            <button type="button" class="btn btn-primary" onclick="showQuoteModal()">
                                <i class="fas fa-reply me-2"></i>{{ __('Respond to Quote') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Quote Details -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>{{ __('Quote Details') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="fw-semibold text-muted mb-1">{{ __('Status') }}</label>
                                            <div>
                                                <span class="badge fs-6 bg-{{ $quoteRequest->status == 'pending' ? 'warning' : ($quoteRequest->status == 'quoted' ? 'info' : ($quoteRequest->status == 'accepted' ? 'success' : 'secondary')) }}">
                                                    {{ __(ucfirst($quoteRequest->status)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fw-semibold text-muted mb-1">{{ __('Quantity') }}</label>
                                            <div class="h5">{{ $quoteRequest->quantity }}</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fw-semibold text-muted mb-1">{{ __('Budget Range') }}</label>
                                            <div>{{ $quoteRequest->budget_range ?: __('Not specified') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="fw-semibold text-muted mb-1">{{ __('Request Date') }}</label>
                                            <div>{{ $quoteRequest->created_at->format('M d, Y g:i A') }}</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fw-semibold text-muted mb-1">{{ __('Timeline') }}</label>
                                            <div>{{ $quoteRequest->timeline ?: __('Not specified') }}</div>
                                        </div>
                                        @if($quoteRequest->quoted_price)
                                            <div class="mb-3">
                                                <label class="fw-semibold text-muted mb-1">{{ __('Quoted Price') }}</label>
                                                <div class="h5 text-success">${{ number_format($quoteRequest->quoted_price, 2) }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($quoteRequest->project_description)
                                    <div class="border-top pt-3 mt-3">
                                        <label class="fw-semibold text-muted mb-2">{{ __('Project Description') }}</label>
                                        <div class="bg-light p-3 rounded">
                                            {{ $quoteRequest->project_description }}
                                        </div>
                                    </div>
                                @endif

                                @if($quoteRequest->quoted_description || $quoteRequest->delivery_time || $quoteRequest->terms_and_conditions)
                                    <div class="border-top pt-3 mt-3">
                                        <h6 class="fw-bold mb-3">{{ __('Your Quote Response') }}</h6>
                                        
                                        @if($quoteRequest->quoted_description)
                                            <div class="mb-3">
                                                <label class="fw-semibold text-muted mb-1">{{ __('Quote Description') }}</label>
                                                <div class="bg-light p-3 rounded">{{ $quoteRequest->quoted_description }}</div>
                                            </div>
                                        @endif

                                        @if($quoteRequest->delivery_time)
                                            <div class="mb-3">
                                                <label class="fw-semibold text-muted mb-1">{{ __('Delivery Time') }}</label>
                                                <div>{{ $quoteRequest->delivery_time }}</div>
                                            </div>
                                        @endif

                                        @if($quoteRequest->terms_and_conditions)
                                            <div class="mb-3">
                                                <label class="fw-semibold text-muted mb-1">{{ __('Terms & Conditions') }}</label>
                                                <div class="bg-light p-3 rounded">{{ $quoteRequest->terms_and_conditions }}</div>
                                            </div>
                                        @endif

                                        @if($quoteRequest->quoted_at)
                                            <div class="text-muted small">
                                                {{ __('Quoted on') }}: {{ $quoteRequest->quoted_at->format('M d, Y g:i A') }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Customer & Product Info -->
                    <div class="col-lg-4">
                        <!-- Customer Information -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user me-2"></i>{{ __('Customer Information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="fw-semibold text-muted small">{{ __('Name') }}</label>
                                    <div>{{ $quoteRequest->customer_name }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-semibold text-muted small">{{ __('Email') }}</label>
                                    <div>
                                        <a href="mailto:{{ $quoteRequest->customer_email }}" class="text-decoration-none">
                                            {{ $quoteRequest->customer_email }}
                                        </a>
                                    </div>
                                </div>
                                @if($quoteRequest->customer_phone)
                                    <div class="mb-3">
                                        <label class="fw-semibold text-muted small">{{ __('Phone') }}</label>
                                        <div>
                                            <a href="tel:{{ $quoteRequest->customer_phone }}" class="text-decoration-none">
                                                {{ $quoteRequest->customer_phone }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if($quoteRequest->customer_company)
                                    <div class="mb-3">
                                        <label class="fw-semibold text-muted small">{{ __('Company') }}</label>
                                        <div>{{ $quoteRequest->customer_company }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-box me-2"></i>{{ __('Product Information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    @if($quoteRequest->product->image)
                                        <img src="{{ RvMedia::getImageUrl($quoteRequest->product->image, 'thumb') }}" 
                                             alt="{{ $quoteRequest->product->name }}" 
                                             class="rounded me-3" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <h6 class="mb-1">{{ $quoteRequest->product->name }}</h6>
                                        <div class="text-muted small">{{ __('SKU') }}: {{ $quoteRequest->product->sku }}</div>
                                        @if($quoteRequest->product->price)
                                            <div class="text-success fw-semibold">${{ number_format($quoteRequest->product->price, 2) }}</div>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('public.products.show', $quoteRequest->product->slug) }}" 
                                   target="_blank" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-external-link-alt me-2"></i>{{ __('View Product') }}
                                </a>
                            </div>
                        </div>

                        <!-- Status Actions -->
                        @if($quoteRequest->status == 'accepted')
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-check-circle me-2"></i>{{ __('Quote Accepted') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3 text-success">
                                        <i class="fas fa-info-circle me-2"></i>
                                        {{ __('The customer has accepted your quote. You can now update the status as needed.') }}
                                    </p>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-warning" onclick="updateStatus('processing')">
                                            <i class="fas fa-cog me-2"></i>{{ __('Mark as Processing') }}
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="updateStatus('completed')">
                                            <i class="fas fa-check me-2"></i>{{ __('Mark as Completed') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quote Response Modal -->
    <div class="modal fade" id="quoteResponseModal" tabindex="-1" aria-labelledby="quoteResponseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quoteResponseModalLabel">{{ __('Respond to Quote Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quoteResponseForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="quoted_price" class="form-label fw-semibold">
                                    {{ __('Quoted Price') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="quoted_price" name="quoted_price" 
                                           step="0.01" min="0" required value="{{ $quoteRequest->quoted_price }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="delivery_time" class="form-label fw-semibold">{{ __('Delivery Time') }}</label>
                                <input type="text" class="form-control" id="delivery_time" name="delivery_time" 
                                       placeholder="{{ __('e.g., 7-10 business days') }}" value="{{ $quoteRequest->delivery_time }}">
                            </div>
                            <div class="col-12">
                                <label for="quoted_description" class="form-label fw-semibold">{{ __('Quote Description') }}</label>
                                <textarea class="form-control" id="quoted_description" name="quoted_description" 
                                          rows="3" placeholder="{{ __('Provide details about your quote...') }}">{{ $quoteRequest->quoted_description }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="terms_and_conditions" class="form-label fw-semibold">{{ __('Terms & Conditions') }}</label>
                                <textarea class="form-control" id="terms_and_conditions" name="terms_and_conditions" 
                                          rows="3" placeholder="{{ __('Any special terms or conditions...') }}">{{ $quoteRequest->terms_and_conditions }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>{{ __('Send Quote') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function showQuoteModal() {
    new bootstrap.Modal(document.getElementById('quoteResponseModal')).show();
}

function updateStatus(status) {
    if (!confirm(`{{ __('Are you sure you want to update the status?') }}`)) {
        return;
    }
    
    fetch(`{{ route('marketplace.vendor.quote-requests.update-status', $quoteRequest->id) }}`, {
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
            alert(data.message);
            location.reload();
        } else {
            throw new Error(data.message || '{{ __("An error occurred") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || '{{ __("An error occurred") }}');
    });
}

document.getElementById('quoteResponseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Sending...") }}';
    
    fetch(`{{ route('marketplace.vendor.quote-requests.respond', $quoteRequest->id) }}`, {
        method: 'PATCH',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            throw new Error(data.message || '{{ __("An error occurred") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || '{{ __("An error occurred") }}');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});
</script>
@endpush