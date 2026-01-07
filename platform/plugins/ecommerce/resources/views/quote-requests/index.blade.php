@extends('core/base::layouts.master')

@section('title', 'Quote Requests')

@push('styles')
<style>
.quote-actions {
    min-width: 120px !important;
    white-space: nowrap;
}
.quote-actions .btn {
    margin: 2px;
    font-size: 12px;
}
.table td {
    vertical-align: middle;
}
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Total Requests</h5>
                                    <h2 class="mb-0">{{ $stats['total'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-invoice fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Pending</h5>
                                    <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">In Progress</h5>
                                    <h2 class="mb-0">{{ $stats['in_progress'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-cog fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">This Month</h5>
                                    <h2 class="mb-0">{{ $stats['this_month'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Quote Requests</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('quote-requests.settings') }}" class="btn btn-outline-primary">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <a href="{{ route('quote-requests.export', request()->query()) }}" class="btn btn-outline-success">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach(\Botble\Ecommerce\Models\QuoteRequest::STATUSES as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" id="search" class="form-control" placeholder="Customer name, email..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Quote Requests Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Quantity</th>
                                    <th>Budget</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quoteRequests as $request)
                                    <tr>
                                        <td>
                                            <strong>#{{ $request->id }}</strong>
                                        </td>
                                        <td>
                                            @if($request->product)
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $request->product->image ? RvMedia::getImageUrl($request->product->image, 'thumb') : RvMedia::getDefaultImage() }}" 
                                                         alt="{{ $request->product->name }}" 
                                                         class="me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <div>
                                                        <div class="fw-bold">{{ Str::limit($request->product->name, 30) }}</div>
                                                        <small class="text-muted">{{ $request->product->sku ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Product not found</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $request->customer_name }}</div>
                                                @if($request->customer_company)
                                                    <small class="text-muted">{{ $request->customer_company }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div>{{ $request->customer_email }}</div>
                                                @if($request->customer_phone)
                                                    <small class="text-muted">{{ $request->customer_phone }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $request->quantity }}</span>
                                            @if($request->area_size)
                                                <br><small class="text-muted">{{ $request->area_size }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->budget_range)
                                                <small class="text-muted">{{ $request->budget_range }}</small>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'in_progress' => 'info',
                                                    'quoted' => 'primary',
                                                    'accepted' => 'success',
                                                    'rejected' => 'danger',
                                                    'completed' => 'success'
                                                ];
                                                $color = $statusColors[$request->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $request->status_label }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $request->created_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                                        </td>
                                        <td class="quote-actions">
                                            <a href="{{ route('quote-requests.show', $request) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                👁️ View
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger delete-quote" 
                                                    data-quote-id="{{ $request->id }}" 
                                                    onclick="deleteRequest({{ $request->id }})" 
                                                    title="Delete">
                                                🗑️ Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <div>No quote requests found</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($quoteRequests->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $quoteRequests->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
    // Debug function to test if script loads
    console.log('Quote requests script loaded');
    
    // Event delegation approach
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up delete handlers');
        
        // Add event listeners to all delete buttons
        document.querySelectorAll('.delete-quote').forEach(function(button) {
            button.addEventListener('click', function(e) {
                const id = this.getAttribute('data-quote-id');
                console.log('Delete button clicked for ID:', id);
                deleteRequest(id);
            });
        });
    });
    
    function deleteRequest(id) {
        console.log('Delete function called with ID:', id);
        
        if (confirm('Are you sure you want to delete this quote request? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/ecommerce/quote-requests/${id}`;
            form.style.display = 'none';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
                console.log('CSRF token added:', csrfToken.getAttribute('content'));
            } else {
                // Fallback: try to get token from any form on the page
                const existingToken = document.querySelector('input[name="_token"]');
                if (existingToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = existingToken.value;
                    form.appendChild(csrfInput);
                    console.log('CSRF token from form:', existingToken.value);
                } else {
                    console.warn('No CSRF token found!');
                }
            }
            
            // Add method spoofing for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            console.log('Form created, submitting to:', form.action);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
@endsection