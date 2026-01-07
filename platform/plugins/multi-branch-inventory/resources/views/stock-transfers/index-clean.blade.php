@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="page-title mb-1">
                    🚚 Stock Transfers
                </h1>
                <p class="text-muted mb-0">Manage inventory transfers between branches</p>
            </div>
            <div class="page-actions">
                @if(Route::has('stock-transfers.create'))
                    <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
                        ➕ New Transfer
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                            🕒 Pending
                        </option>
                        <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>
                            🚛 In Transit
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                            ✅ Completed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                            ❌ Cancelled
                        </option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="from_branch" class="form-label">From Branch</label>
                    <select name="from_branch" id="from_branch" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('from_branch') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="to_branch" class="form-label">To Branch</label>
                    <select name="to_branch" id="to_branch" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('to_branch') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            🔍 Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stock Transfers</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Transfer #</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td>
                                    <strong>#{{ $transfer->id }}</strong>
                                    @if($transfer->reference_number)
                                        <br><small class="text-muted">{{ $transfer->reference_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">📤</span>
                                        <div>
                                            <strong>{{ $transfer->fromBranch->name ?? 'N/A' }}</strong>
                                            @if($transfer->fromBranch)
                                                <br><small class="text-muted">{{ $transfer->fromBranch->code }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">📥</span>
                                        <div>
                                            <strong>{{ $transfer->toBranch->name ?? 'N/A' }}</strong>
                                            @if($transfer->toBranch)
                                                <br><small class="text-muted">{{ $transfer->toBranch->code }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $transfer->items_count ?? 0 }} items</span>
                                </td>
                                <td>
                                    @if($transfer->status === 'pending')
                                        <span class="badge bg-warning">🕒 Pending</span>
                                    @elseif($transfer->status === 'in_transit')
                                        <span class="badge bg-primary">🚛 In Transit</span>
                                    @elseif($transfer->status === 'completed')
                                        <span class="badge bg-success">✅ Completed</span>
                                    @elseif($transfer->status === 'cancelled')
                                        <span class="badge bg-danger">❌ Cancelled</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $transfer->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $transfer->requestedByUser->name ?? 'N/A' }}
                                    <br><small class="text-muted">{{ $transfer->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    {{ $transfer->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Transfer actions">
                                        @if(Route::has('stock-transfers.show'))
                                            <a href="{{ route('stock-transfers.show', $transfer->id) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                👁️ View
                                            </a>
                                        @endif
                                        
                                        @if($transfer->status === 'pending' && Route::has('stock-transfers.edit'))
                                            <a href="{{ route('stock-transfers.edit', $transfer->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit Transfer">
                                                ✏️ Edit
                                            </a>
                                        @endif

                                        @if($transfer->status === 'pending' && Route::has('stock-transfers.destroy'))
                                            <form method="POST" action="{{ route('stock-transfers.destroy', $transfer->id) }}" 
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this transfer?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Transfer">
                                                    🗑️ Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-exchange-alt display-4 text-muted mb-3"></i>
                                        <h5 class="mt-2">No stock transfers found</h5>
                                        <p class="text-muted">Create your first stock transfer to move inventory between branches.</p>
                                        @if(Route::has('stock-transfers.create'))
                                            <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
                                                ➕ Create First Transfer
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $transfers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection