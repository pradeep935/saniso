@extends('plugins/marketplace::themes.vendor-dashboard.layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">{{ __('Quote Requests') }}</h2>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-2"></i>{{ __('Filter') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('marketplace.vendor.quote-requests.index') }}">{{ __('All Requests') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('marketplace.vendor.quote-requests.index', ['status' => 'pending']) }}">{{ __('Pending') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('marketplace.vendor.quote-requests.index', ['status' => 'quoted']) }}">{{ __('Quoted') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('marketplace.vendor.quote-requests.index', ['status' => 'accepted']) }}">{{ __('Accepted') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-muted small">{{ __('Total Requests') }}</div>
                                        <div class="h4 mb-0">{{ $stats['total'] }}</div>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-clipboard-list fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-muted small">{{ __('Pending') }}</div>
                                        <div class="h4 mb-0 text-warning">{{ $stats['pending'] }}</div>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-muted small">{{ __('Quoted') }}</div>
                                        <div class="h4 mb-0 text-info">{{ $stats['quoted'] }}</div>
                                    </div>
                                    <div class="text-info">
                                        <i class="fas fa-quote-right fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-muted small">{{ __('Accepted') }}</div>
                                        <div class="h4 mb-0 text-success">{{ $stats['accepted'] }}</div>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quote Requests Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        @if($quoteRequests->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>{{ __('ID') }}</th>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Customer') }}</th>
                                            <th>{{ __('Quantity') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Quoted Price') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th class="text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quoteRequests as $quote)
                                            <tr>
                                                <td class="fw-bold">#{{ $quote->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($quote->product->image)
                                                            <img src="{{ RvMedia::getImageUrl($quote->product->image, 'thumb') }}" 
                                                                 alt="{{ $quote->product->name }}" 
                                                                 class="rounded me-2" 
                                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold">{{ $quote->product->name }}</div>
                                                            <small class="text-muted">{{ __('SKU') }}: {{ $quote->product->sku }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-semibold">{{ $quote->customer_name }}</div>
                                                        <small class="text-muted">{{ $quote->customer_email }}</small>
                                                        @if($quote->customer_company)
                                                            <br><small class="text-muted">{{ $quote->customer_company }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">{{ $quote->quantity }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $quote->status == 'pending' ? 'warning' : ($quote->status == 'quoted' ? 'info' : ($quote->status == 'accepted' ? 'success' : 'secondary')) }}">
                                                        {{ __(ucfirst($quote->status)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($quote->quoted_price)
                                                        <span class="fw-bold text-success">${{ number_format($quote->quoted_price, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">{{ __('Not quoted') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>{{ $quote->created_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $quote->created_at->format('g:i A') }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group">
                                                        <a href="{{ route('marketplace.vendor.quote-requests.show', $quote->id) }}" 
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="{{ __('View Details') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if($quote->status == 'pending')
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-primary"
                                                                    onclick="showQuoteModal({{ $quote->id }})"
                                                                    title="{{ __('Respond to Quote') }}">
                                                                <i class="fas fa-reply"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            @if($quoteRequests->hasPages())
                                <div class="p-3 border-top">
                                    {{ $quoteRequests->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('No Quote Requests Found') }}</h5>
                                <p class="text-muted">{{ __('You haven\'t received any quote requests yet.') }}</p>
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
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="delivery_time" class="form-label fw-semibold">{{ __('Delivery Time') }}</label>
                                <input type="text" class="form-control" id="delivery_time" name="delivery_time" 
                                       placeholder="{{ __('e.g., 7-10 business days') }}">
                            </div>
                            <div class="col-12">
                                <label for="quoted_description" class="form-label fw-semibold">{{ __('Quote Description') }}</label>
                                <textarea class="form-control" id="quoted_description" name="quoted_description" 
                                          rows="3" placeholder="{{ __('Provide details about your quote...') }}"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="terms_and_conditions" class="form-label fw-semibold">{{ __('Terms & Conditions') }}</label>
                                <textarea class="form-control" id="terms_and_conditions" name="terms_and_conditions" 
                                          rows="3" placeholder="{{ __('Any special terms or conditions...') }}"></textarea>
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
let currentQuoteId = null;

function showQuoteModal(quoteId) {
    currentQuoteId = quoteId;
    document.getElementById('quoteResponseForm').reset();
    new bootstrap.Modal(document.getElementById('quoteResponseModal')).show();
}

document.getElementById('quoteResponseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentQuoteId) return;
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Sending...") }}';
    
    fetch(`{{ route('marketplace.vendor.quote-requests.index') }}/${currentQuoteId}/respond`, {
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