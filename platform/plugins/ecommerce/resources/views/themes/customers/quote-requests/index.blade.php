@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Quote Requests'))

@section('content')
    @php
        EcommerceHelper::registerThemeAssets();
    @endphp

    @if(isset($quoteRequests) && $quoteRequests->isNotEmpty())
        <div class="customer-list-order">
            <!-- Quote Requests Grid -->
            <div class="bb-customer-card-list order-cards">
                @foreach ($quoteRequests as $quote)
                    <div class="bb-customer-card order-card">
                        <!-- Quote Header -->
                        <div class="bb-customer-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="bb-customer-card-title h6 mb-1">
                                        {{ __('Quote Request #:id', ['id' => $quote->id]) }}
                                    </h3>
                                    <div class="bb-customer-card-status">
                                        <span class="badge bg-{{ $quote->status == 'pending' ? 'warning' : ($quote->status == 'quoted' ? 'info' : ($quote->status == 'accepted' ? 'success' : 'secondary')) }}">
                                            {{ __(ucfirst($quote->status)) }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        {{ $quote->created_at->translatedFormat('M d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Quote Details -->
                        <div class="bb-customer-card-body">
                            <div class="bb-customer-card-info">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <span class="label text-muted small">{{ __('Product') }}</span>
                                            <span class="value fw-semibold d-block">
                                                @if($quote->product)
                                                    {{ $quote->product->name }}
                                                @else
                                                    {{ __('Custom Quote') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-item">
                                            <span class="label text-muted small">{{ __('Quantity') }}</span>
                                            <span class="value fw-semibold d-block">{{ number_format($quote->quantity) }}</span>
                                        </div>
                                    </div>
                                    @if($quote->quoted_price)
                                        <div class="col-sm-6">
                                            <div class="info-item">
                                                <span class="label text-muted small">{{ __('Quoted Price') }}</span>
                                                <span class="value fw-semibold d-block text-success">${{ number_format($quote->quoted_price, 2) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if($quote->budget_range)
                                        <div class="col-sm-6">
                                            <div class="info-item">
                                                <span class="label text-muted small">{{ __('Budget Range') }}</span>
                                                <span class="value fw-semibold d-block">{{ $quote->budget_range_label }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quote Actions -->
                        <div class="bb-customer-card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($quote->status == 'quoted')
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-success btn-sm" 
                                                    onclick="acceptQuote({{ $quote->id }})">
                                                <x-core::icon name="ti ti-check" class="me-1" />
                                                {{ __('Accept') }}
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    onclick="rejectQuote({{ $quote->id }})">
                                                <x-core::icon name="ti ti-x" class="me-1" />
                                                {{ __('Reject') }}
                                            </button>
                                        </div>
                                    @elseif($quote->status == 'accepted')
                                        <div class="alert alert-success">
                                            <x-core::icon name="ti ti-check" class="me-1" />
                                            {{ __('Quote Accepted - We will contact you to arrange payment and delivery') }}
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('customer.quote-requests.show', $quote->id) }}" 
                                   class="btn btn-primary btn-sm">
                                    <x-core::icon name="ti ti-eye" class="me-1" />
                                    {{ __('View Details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($quoteRequests->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {!! $quoteRequests->links() !!}
                </div>
            @endif
        </div>
    @else
        @include(EcommerceHelper::viewPath('customers.partials.empty-state'), [
            'title' => __('No quote requests yet!'),
            'subtitle' => __('You have not submitted any quote requests yet.'),
            'actionUrl' => route('public.products'),
            'actionLabel' => __('Browse Products'),
        ])
    @endif
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