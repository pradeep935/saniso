@extends(MarketplaceHelper::viewPath('dashboard.layouts.master'))

@section('title', __('Quote Requests'))

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">{{ __('Quote Requests') }}</h4>
                    <p class="text-muted mb-0">{{ __('Manage quote requests for your products') }}</p>
                </div>
            </div>

            <!-- Filter Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 bg-info bg-opacity-10 h-100">
                        <div class="card-body text-center">
                            <div class="h2 text-info mb-1">{{ $quoteRequests->where('status', 'pending')->count() }}</div>
                            <div class="text-muted small">{{ __('Pending') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-warning bg-opacity-10 h-100">
                        <div class="card-body text-center">
                            <div class="h2 text-warning mb-1">{{ $quoteRequests->where('status', 'in_progress')->count() }}</div>
                            <div class="text-muted small">{{ __('In Progress') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-success bg-opacity-10 h-100">
                        <div class="card-body text-center">
                            <div class="h2 text-success mb-1">{{ $quoteRequests->where('status', 'quoted')->count() }}</div>
                            <div class="text-muted small">{{ __('Quoted') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-primary bg-opacity-10 h-100">
                        <div class="card-body text-center">
                            <div class="h2 text-primary mb-1">{{ $quoteRequests->where('status', 'completed')->count() }}</div>
                            <div class="text-muted small">{{ __('Completed') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                   placeholder="{{ __('Customer name, email, company...') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" name="status">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                <option value="quoted" {{ request('status') == 'quoted' ? 'selected' : '' }}>{{ __('Quoted') }}</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>{{ __('Accepted') }}</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">{{ __('Filter') }}</button>
                            <a href="{{ route('vendor.quote-requests.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quote Requests Table -->
            <div class="card">
                <div class="card-body">
                    @if($quoteRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('ID') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th>{{ __('Budget') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quoteRequests as $request)
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary">#{{ $request->id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($request->product && $request->product->image)
                                                        <img src="{{ RvMedia::getImageUrl($request->product->image, 'thumb') }}" 
                                                             alt="{{ $request->product->name }}" 
                                                             class="me-2 rounded" 
                                                             width="40" height="40">
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $request->product->name ?? __('Product Deleted') }}</div>
                                                        @if($request->product)
                                                            <small class="text-muted">ID: {{ $request->product->id }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $request->customer_name }}</div>
                                                    <div class="small text-muted">{{ $request->customer_email }}</div>
                                                    @if($request->customer_company)
                                                        <div class="small text-info">{{ $request->customer_company }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ number_format($request->quantity) }}</span>
                                            </td>
                                            <td>
                                                @if($request->budget_range)
                                                    <span class="badge bg-info">{{ $request->budget_range_label }}</span>
                                                @else
                                                    <span class="text-muted">{{ __('Not specified') }}</span>
                                                @endif
                                            </td>
                                            <td>
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
                                                <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                                    {{ $request->status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>{{ $request->created_at->format('M j, Y') }}</div>
                                                <small class="text-muted">{{ $request->created_at->format('g:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('vendor.quote-requests.show', $request->id) }}" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="{{ __('View Details') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(in_array($request->status, ['pending', 'in_progress']))
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success"
                                                                onclick="quickRespond({{ $request->id }})"
                                                                title="{{ __('Send Quote') }}">
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
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $quoteRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-quote-left fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Quote Requests Found') }}</h5>
                            <p class="text-muted">
                                @if(request()->filled(['search', 'status']))
                                    {{ __('No quote requests match your search criteria.') }}
                                @else
                                    {{ __('You haven\'t received any quote requests for your products yet.') }}
                                @endif
                            </p>
                            @if(request()->filled(['search', 'status']))
                                <a href="{{ route('vendor.quote-requests.index') }}" class="btn btn-primary">
                                    {{ __('View All Requests') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Response Modal -->
    <div class="modal fade" id="quickResponseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Send Quote') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="quickResponseForm">
                    <div class="modal-body">
                        <input type="hidden" id="quote_request_id" name="quote_request_id">
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Quoted Price') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="quoted_price" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Quote Details') }}</label>
                            <textarea class="form-control" name="quote_details" rows="3" 
                                      placeholder="{{ __('Additional details about your quote, delivery time, terms...') }}"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Internal Notes') }}</label>
                            <textarea class="form-control" name="admin_notes" rows="2" 
                                      placeholder="{{ __('Private notes (not visible to customer)') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Send Quote') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function quickRespond(quoteId) {
    document.getElementById('quote_request_id').value = quoteId;
    new bootstrap.Modal(document.getElementById('quickResponseModal')).show();
}

document.getElementById('quickResponseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const quoteId = formData.get('quote_request_id');
    
    fetch(`{{ route('vendor.quote-requests.respond', '') }}/${quoteId}`, {
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