@extends(MarketplaceHelper::viewPath('dashboard.layouts.master'))

@section('title', __('Quote Request #:id', ['id' => $quoteRequest->id]))

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a href="{{ route('vendor.quote-requests.index') }}">{{ __('Quote Requests') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ __('Quote #:id', ['id' => $quoteRequest->id]) }}</li>
                        </ol>
                    </nav>
                    <h4 class="mb-0">{{ __('Quote Request Details') }}</h4>
                </div>
                <div>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'in_progress' => 'info', 
                            'quoted' => 'success',
                            'accepted' => 'primary',
                            'rejected' => 'danger',
                            'completed' => 'dark'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$quoteRequest->status] ?? 'secondary' }} fs-6">
                        {{ $quoteRequest->status_label }}
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Product Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ __('Product Information') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($quoteRequest->product)
                                <div class="d-flex align-items-start">
                                    @if($quoteRequest->product->image)
                                        <img src="{{ RvMedia::getImageUrl($quoteRequest->product->image, 'thumb') }}" 
                                             alt="{{ $quoteRequest->product->name }}" 
                                             class="me-3 rounded" 
                                             width="80" height="80">
                                    @endif
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">{{ $quoteRequest->product->name }}</h5>
                                        <p class="text-muted small mb-2">{{ __('Product ID') }}: {{ $quoteRequest->product->id }}</p>
                                        @if($quoteRequest->product->sku)
                                            <p class="text-muted small mb-2">{{ __('SKU') }}: {{ $quoteRequest->product->sku }}</p>
                                        @endif
                                        @if($quoteRequest->product->price)
                                            <div class="text-primary fw-bold">{{ __('Listed Price') }}: {{ format_price($quoteRequest->product->price) }}</div>
                                        @endif
                                        <a href="{{ $quoteRequest->product->url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-external-link-alt"></i> {{ __('View Product') }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-exclamation-triangle"></i> {{ __('Product has been deleted') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quote Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ __('Request Details') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>{{ __('Quantity Requested') }}</strong>
                                    <div class="text-muted">{{ number_format($quoteRequest->quantity) }} {{ __('units') }}</div>
                                </div>
                                
                                @if($quoteRequest->budget_range)
                                    <div class="col-md-6">
                                        <strong>{{ __('Budget Range') }}</strong>
                                        <div class="text-muted">{{ $quoteRequest->budget_range_label }}</div>
                                    </div>
                                @endif

                                @if($quoteRequest->timeline)
                                    <div class="col-md-6">
                                        <strong>{{ __('Timeline') }}</strong>
                                        <div class="text-muted">{{ $quoteRequest->timeline_label }}</div>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <strong>{{ __('Request Date') }}</strong>
                                    <div class="text-muted">{{ $quoteRequest->created_at->format('M j, Y \a\t g:i A') }}</div>
                                </div>
                            </div>

                            @if($quoteRequest->project_description)
                                <hr>
                                <div>
                                    <strong>{{ __('Project Description') }}</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        {{ $quoteRequest->project_description }}
                                    </div>
                                </div>
                            @endif

                            @if($quoteRequest->special_requirements)
                                <hr>
                                <div>
                                    <strong>{{ __('Special Requirements') }}</strong>
                                    <div class="mt-2">
                                        @foreach($quoteRequest->special_requirements as $requirement)
                                            <span class="badge bg-light text-dark me-1">{{ $requirement }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Your Response -->
                    @if($quoteRequest->status === 'quoted' || $quoteRequest->quoted_price)
                        <div class="card mb-4">
                            <div class="card-header bg-success bg-opacity-10">
                                <h6 class="card-title mb-0 text-success">
                                    <i class="fas fa-check-circle"></i> {{ __('Your Quote Response') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <strong>{{ __('Quoted Price') }}</strong>
                                        <div class="h5 text-success mb-0">{{ format_price($quoteRequest->quoted_price) }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __('Quote Date') }}</strong>
                                        <div class="text-muted">{{ $quoteRequest->quoted_at ? $quoteRequest->quoted_at->format('M j, Y \a\t g:i A') : '' }}</div>
                                    </div>
                                </div>

                                @if($quoteRequest->quote_details)
                                    <hr>
                                    <div>
                                        <strong>{{ __('Quote Details') }}</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            {{ $quoteRequest->quote_details }}
                                        </div>
                                    </div>
                                @endif

                                @if($quoteRequest->admin_notes)
                                    <hr>
                                    <div>
                                        <strong>{{ __('Internal Notes') }}</strong>
                                        <div class="mt-2 p-3 bg-warning bg-opacity-10 rounded">
                                            <small>{{ $quoteRequest->admin_notes }}</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Customer Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ __('Customer Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>{{ __('Name') }}</strong>
                                <div>{{ $quoteRequest->customer_name }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>{{ __('Email') }}</strong>
                                <div>
                                    <a href="mailto:{{ $quoteRequest->customer_email }}">{{ $quoteRequest->customer_email }}</a>
                                </div>
                            </div>
                            
                            @if($quoteRequest->customer_phone)
                                <div class="mb-3">
                                    <strong>{{ __('Phone') }}</strong>
                                    <div>
                                        <a href="tel:{{ $quoteRequest->customer_phone }}">{{ $quoteRequest->customer_phone }}</a>
                                    </div>
                                </div>
                            @endif
                            
                            @if($quoteRequest->customer_company)
                                <div class="mb-3">
                                    <strong>{{ __('Company') }}</strong>
                                    <div>{{ $quoteRequest->customer_company }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ __('Actions') }}</h6>
                        </div>
                        <div class="card-body">
                            @if(in_array($quoteRequest->status, ['pending', 'in_progress']))
                                <button class="btn btn-success w-100 mb-2" onclick="sendQuote()">
                                    <i class="fas fa-paper-plane"></i> {{ __('Send Quote') }}
                                </button>
                                
                                <button class="btn btn-info w-100 mb-2" onclick="markInProgress()">
                                    <i class="fas fa-clock"></i> {{ __('Mark In Progress') }}
                                </button>
                                
                                <button class="btn btn-outline-danger w-100 mb-2" onclick="rejectRequest()">
                                    <i class="fas fa-times"></i> {{ __('Reject Request') }}
                                </button>
                            @elseif($quoteRequest->status === 'quoted')
                                <button class="btn btn-primary w-100 mb-2" onclick="editQuote()">
                                    <i class="fas fa-edit"></i> {{ __('Edit Quote') }}
                                </button>
                                
                                @if($quoteRequest->status !== 'completed')
                                    <button class="btn btn-outline-success w-100 mb-2" onclick="markCompleted()">
                                        <i class="fas fa-check"></i> {{ __('Mark Completed') }}
                                    </button>
                                @endif
                            @endif
                            
                            <a href="{{ route('vendor.quote-requests.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quote Response Modal -->
    <div class="modal fade" id="quoteResponseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Send Quote Response') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="quoteResponseForm">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Quoted Price') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="quoted_price" 
                                           step="0.01" min="0" required 
                                           value="{{ $quoteRequest->quoted_price }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Total for Quantity') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="total_price" readonly>
                                </div>
                                <small class="text-muted">{{ __('For :qty units', ['qty' => number_format($quoteRequest->quantity)]) }}</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Quote Details') }}</label>
                            <textarea class="form-control" name="quote_details" rows="4" 
                                      placeholder="{{ __('Provide details about your quote: delivery time, terms, conditions, specifications...') }}">{{ $quoteRequest->quote_details }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Internal Notes') }}</label>
                            <textarea class="form-control" name="admin_notes" rows="2" 
                                      placeholder="{{ __('Private notes for your reference (not visible to customer)') }}">{{ $quoteRequest->admin_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('Send Quote') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Calculate total when price changes
document.querySelector('input[name="quoted_price"]').addEventListener('input', function() {
    const price = parseFloat(this.value) || 0;
    const quantity = {{ $quoteRequest->quantity }};
    document.getElementById('total_price').value = (price * quantity).toFixed(2);
});

function sendQuote() {
    // Trigger calculation on modal show
    setTimeout(() => {
        document.querySelector('input[name="quoted_price"]').dispatchEvent(new Event('input'));
    }, 100);
    
    new bootstrap.Modal(document.getElementById('quoteResponseModal')).show();
}

function editQuote() {
    sendQuote();
}

function markInProgress() {
    if (confirm('{{ __("Mark this request as in progress?") }}')) {
        updateStatus('in_progress');
    }
}

function rejectRequest() {
    if (confirm('{{ __("Are you sure you want to reject this quote request?") }}')) {
        updateStatus('rejected');
    }
}

function markCompleted() {
    if (confirm('{{ __("Mark this request as completed?") }}')) {
        updateStatus('completed');
    }
}

function updateStatus(status) {
    fetch(`{{ route('vendor.quote-requests.update-status', $quoteRequest->id) }}`, {
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

document.getElementById('quoteResponseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`{{ route('vendor.quote-requests.respond', $quoteRequest->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
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
});
</script>
@endpush