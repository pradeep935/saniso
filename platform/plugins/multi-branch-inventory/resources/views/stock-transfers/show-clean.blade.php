@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Stock Transfer #{{ $stockTransfer->id }}
                    </h4>
                    <div>
                        <span class="badge 
                            @if($stockTransfer->status == 'completed') bg-success
                            @elseif($stockTransfer->status == 'cancelled') bg-danger
                            @elseif($stockTransfer->status == 'approved') bg-info
                            @else bg-warning
                            @endif fs-6 px-3 py-2">
                            {{ ucfirst($stockTransfer->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Transfer Details -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Transfer Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">From Branch</label>
                                            <div class="fw-bold">
                                                {{ $stockTransfer->fromBranch->name ?? 'N/A' }}
                                                @if($stockTransfer->fromBranch->code)
                                                    <small class="text-muted">({{ $stockTransfer->fromBranch->code }})</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">To Branch</label>
                                            <div class="fw-bold">
                                                {{ $stockTransfer->toBranch->name ?? 'N/A' }}
                                                @if($stockTransfer->toBranch->code)
                                                    <small class="text-muted">({{ $stockTransfer->toBranch->code }})</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Reference Number</label>
                                            <div class="fw-bold">
                                                <code>{{ $stockTransfer->reference_number ?: 'N/A' }}</code>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Requested By</label>
                                            <div class="fw-bold">{{ $stockTransfer->requestedByUser->name ?? 'System' }}</div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Created Date</label>
                                            <div class="fw-bold">{{ $stockTransfer->created_at ? $stockTransfer->created_at->format('M d, Y H:i') : 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted">Total Items</label>
                                            <div class="fw-bold">{{ $stockTransfer->items->count() }} item(s)</div>
                                        </div>
                                    </div>

                                    @if($stockTransfer->notes)
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="form-label text-muted">Notes</label>
                                            <div class="alert alert-light">{{ $stockTransfer->notes }}</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Transfer Items -->
                            <div class="card border-0 shadow-sm mt-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Transfer Items</h5>
                                </div>
                                <div class="card-body">
                                    @if($stockTransfer->items->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="40%">Product</th>
                                                        <th width="15%" class="text-center">Requested</th>
                                                        <th width="15%" class="text-center">Approved</th>
                                                        <th width="15%" class="text-center">Picked</th>
                                                        <th width="15%" class="text-center">Received</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($stockTransfer->items as $item)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <i class="fas fa-cube text-primary fs-4"></i>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-bold">{{ $item->product->name ?? 'Unknown Product' }}</div>
                                                                        <div class="text-muted small">
                                                                            ID: {{ $item->product->id ?? 'N/A' }}
                                                                            @if($item->product->sku)
                                                                                | SKU: <code>{{ $item->product->sku }}</code>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge bg-info">{{ $item->quantity_requested ?? 0 }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($item->quantity_approved)
                                                                    <span class="badge bg-success">{{ $item->quantity_approved }}</span>
                                                                @else
                                                                    <span class="badge bg-secondary">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($item->quantity_picked)
                                                                    <span class="badge bg-warning">{{ $item->quantity_picked }}</span>
                                                                @else
                                                                    <span class="badge bg-secondary">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($item->quantity_received)
                                                                    <span class="badge bg-primary">{{ $item->quantity_received }}</span>
                                                                @else
                                                                    <span class="badge bg-secondary">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Items Found</h5>
                                            <p class="text-muted">This transfer has no items.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions & Summary -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Back to List
                                        </a>

                                        @if($stockTransfer->status == 'pending')
                                            <a href="{{ route('stock-transfers.edit', $stockTransfer->id) }}" class="btn btn-primary">
                                                <i class="fas fa-edit me-2"></i>Edit Transfer
                                            </a>
                                            
                                            <form action="{{ route('stock-transfers.approve', $stockTransfer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100" 
                                                        onclick="return confirm('Approve this transfer?')">
                                                    <i class="fas fa-check me-2"></i>Approve
                                                </button>
                                            </form>

                                            <form action="{{ route('stock-transfers.destroy', $stockTransfer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger w-100" 
                                                        onclick="return confirm('Delete this transfer?')">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        @endif

                                        @if($stockTransfer->status == 'approved')
                                            <form action="{{ route('stock-transfers.start-picking', $stockTransfer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning w-100" 
                                                        onclick="return confirm('Start picking process?')">
                                                    <i class="fas fa-hand-paper me-2"></i>Start Picking
                                                </button>
                                            </form>
                                        @endif

                                        @if($stockTransfer->status == 'picking')
                                            <form action="{{ route('stock-transfers.ship', $stockTransfer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-info w-100" 
                                                        onclick="return confirm('Ship this transfer?')">
                                                    <i class="fas fa-shipping-fast me-2"></i>Ship Transfer
                                                </button>
                                            </form>
                                        @endif

                                        @if($stockTransfer->status == 'shipped')
                                            <form action="{{ route('stock-transfers.receive', $stockTransfer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100" 
                                                        onclick="return confirm('Confirm receipt and complete transfer?')">
                                                    <i class="fas fa-check-double me-2"></i>Receive & Complete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="card border-0 shadow-sm mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-primary mb-1">{{ $stockTransfer->items->count() }}</h4>
                                                <small class="text-muted">Total Items</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-info mb-1">{{ $stockTransfer->items->sum('quantity_requested') }}</h4>
                                            <small class="text-muted">Total Qty</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection