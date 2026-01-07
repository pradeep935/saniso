@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Quote Requests'))

@section('content')
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
                                <div class="text-end">
                                    @if($quote->quoted_price)
                                        <div class="h6 text-success mb-1">{{ format_price($quote->quoted_price) }}</div>
                                        <small class="text-muted">{{ __('Quoted Price') }}</small>
                                        @if($quote->quantity > 1)
                                            <div class="small text-muted mt-1">
                                                {{ __('Per unit') }}: {{ format_price($quote->quoted_price / $quote->quantity) }}
                                            </div>
                                        @endif
                                    @else
                                        <small class="text-muted">{{ __('Pending Quote') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quote Details -->
                        <div class="bb-customer-card-body">
                            <div class="bb-customer-card-info">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        @if($quote->product)
                                            <div class="d-flex align-items-center mb-3">
                                                @if($quote->product->image)
                                                    <img src="{{ RvMedia::getImageUrl($quote->product->image, 'thumb') }}" 
                                                         alt="{{ $quote->product->name }}" 
                                                         class="rounded me-3" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <h6 class="mb-1">{{ $quote->product->name }}</h6>
                                                    <small class="text-muted">{{ __('Quantity') }}: {{ $quote->quantity }}</small>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($quote->project_description)
                                            <div class="mb-2">
                                                <small class="text-muted fw-semibold">{{ __('Project Description') }}:</small>
                                                <p class="small mb-0">{{ Str::limit($quote->project_description, 100) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-end">
                                            @if($quote->budget_range)
                                                <div class="mb-2">
                                                    <small class="text-muted">{{ __('Budget Range') }}</small>
                                                    <div class="fw-semibold">{{ $quote->budget_range_label }}</div>
                                                </div>
                                            @endif
                                            @if($quote->timeline)
                                                <div class="mb-2">
                                                    <small class="text-muted">{{ __('Timeline') }}</small>
                                                    <div class="fw-semibold">{{ $quote->timeline_label }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
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
                                                    class="btn btn-outline-primary btn-sm" 
                                                    onclick="showNegotiateModal({{ $quote->id }}, {{ $quote->quantity }}, {{ $quote->quoted_price }})">
                                                <x-core::icon name="ti ti-message-circle" class="me-1" />
                                                {{ __('Negotiate') }}
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
                    {{ $quoteRequests->links() }}
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

    <!-- Negotiate Quote Modal -->
    <div class="modal fade" id="negotiateModal" tabindex="-1" aria-labelledby="negotiateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="negotiateModalLabel">{{ __('Negotiate Quote') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="negotiateForm">
                    <input type="hidden" id="negotiate_quote_id" name="quote_id">
                    
                    <div class="mb-3">
                        <label for="new_quantity" class="form-label">{{ __('Quantity') }}</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">
                                <i class="ti ti-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control text-center" 
                                   id="new_quantity" 
                                   name="new_quantity" 
                                   min="1" 
                                   value="1">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">
                                <i class="ti ti-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted">{{ __('Adjust quantity if needed') }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expected_price" class="form-label">{{ __('Your Expected Price') }} ({{ __('Optional') }})</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ get_application_currency()->symbol }}</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="expected_price" 
                                   name="expected_price" 
                                   step="0.01" 
                                   placeholder="{{ __('Enter your expected price') }}">
                        </div>
                        <small class="text-muted">{{ __('Leave empty if you want to keep the quoted price') }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="negotiation_message" class="form-label">{{ __('Message') }}</label>
                        <textarea class="form-control" 
                                  id="negotiation_message" 
                                  name="message" 
                                  rows="3" 
                                  placeholder="{{ __('Any additional requirements or questions...') }}"></textarea>
                    </div>
                    
                    <div class="price-summary p-3 bg-light rounded">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Current Quote') }}:</span>
                            <span id="current_quote_price" class="fw-bold"></span>
                        </div>
                        <div class="d-flex justify-content-between" id="new_price_row" style="display: none;">
                            <span>{{ __('Your Expected Price') }}:</span>
                            <span id="new_total_price" class="fw-bold text-primary"></span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Quantity') }}:</span>
                            <span id="display_quantity" class="fw-bold"></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="submitNegotiation()">
                    <x-core::icon name="ti ti-send" class="me-1" />
                    {{ __('Send Negotiation') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

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