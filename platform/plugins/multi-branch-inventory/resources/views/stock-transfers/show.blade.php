@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<!-- Multi-Branch Inventory Stock Transfers CSS -->
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/stock-transfers.css') }}">
@endpush

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>{{ trans('Stock Transfer') }} #{{ $stockTransfer->id }}</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">{{ trans('View stock transfer details and status') }}</p>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Transfer Details -->
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ trans('Transfer Details') }}</h4>
                                    <div class="float-right">
                                        <span class="badge badge-{{ $stockTransfer->status == 'completed' ? 'success' : ($stockTransfer->status == 'cancelled' ? 'danger' : 'info') }}">
                                            {{ ucfirst($stockTransfer->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.from_branch') }}:</strong><br>
                                               {{ $stockTransfer->fromBranch->name ?? 'N/A' }} ({{ $stockTransfer->fromBranch->code ?? '' }})</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.to_branch') }}:</strong><br>
                                               {{ $stockTransfer->toBranch->name ?? 'N/A' }} ({{ $stockTransfer->toBranch->code ?? '' }})</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>{{ trans('Reference Number') }}:</strong><br>
                                               {{ $stockTransfer->reference_number ?: 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>{{ trans('Requested By') }}:</strong><br>
                                               {{ $stockTransfer->requestedByUser->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    @if($stockTransfer->notes)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><strong>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.notes') }}:</strong><br>
                                               {{ $stockTransfer->notes }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Transfer Items -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4>{{ trans('Transfer Items') }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.product') }}</th>
                                                    <th>{{ trans('Requested') }}</th>
                                                    <th>{{ trans('Approved') }}</th>
                                                    <th>{{ trans('Picked') }}</th>
                                                    <th>{{ trans('Received') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($stockTransfer->items as $item)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $item->product->name ?? 'Unknown Product' }}</strong>
                                                            @if($item->product->sku)
                                                                <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $item->quantity_requested ?? 0 }}</td>
                                                        <td>{{ $item->quantity_approved ?? '-' }}</td>
                                                        <td>{{ $item->quantity_picked ?? '-' }}</td>
                                                        <td>{{ $item->quantity_received ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            {{ trans('No items in this transfer') }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Actions Card -->
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.actions') }}</h4>
                                </div>
                                <div class="card-body">
                                    @if($stockTransfer->status == 'pending')
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('stock-transfers.edit', $stockTransfer->id) }}" class="btn btn-primary btn-block">
                                                <i class="fa fa-edit"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.edit') }}
                                            </a>
                                            
                                            <form method="POST" action="{{ route('stock-transfers.approve', $stockTransfer->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-block" 
                                                        onclick="return confirm('Are you sure you want to approve this transfer?')">
                                                    <i class="fa fa-check"></i> {{ trans('Approve') }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('stock-transfers.cancel', $stockTransfer->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-block" 
                                                        onclick="return confirm('Are you sure you want to cancel this transfer?')">
                                                    <i class="fa fa-times"></i> {{ trans('Cancel') }}
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($stockTransfer->status == 'approved')
                                        <form method="POST" action="{{ route('stock-transfers.start-picking', $stockTransfer->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-block" 
                                                    onclick="return confirm('Start picking process for this transfer?')">
                                                <i class="fa fa-list"></i> {{ trans('Start Picking') }}
                                            </button>
                                        </form>
                                    @elseif($stockTransfer->status == 'picking')
                                        <form method="POST" action="{{ route('stock-transfers.ship', $stockTransfer->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-block" 
                                                    onclick="return confirm('Mark this transfer as shipped?')">
                                                <i class="fa fa-truck"></i> {{ trans('Ship') }}
                                            </button>
                                        </form>
                                    @elseif($stockTransfer->status == 'shipped')
                                        <form method="POST" action="{{ route('stock-transfers.receive', $stockTransfer->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-block" 
                                                    onclick="return confirm('Mark this transfer as received?')">
                                                <i class="fa fa-check-circle"></i> {{ trans('Receive') }}
                                            </button>
                                        </form>
                                    @endif

                                    <hr>
                                    
                                    <form method="POST" action="{{ route('stock-transfers.duplicate', $stockTransfer->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-block">
                                            <i class="fa fa-copy"></i> {{ trans('Duplicate') }}
                                        </button>
                                    </form>

                                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-light btn-block">
                                        <i class="fa fa-arrow-left"></i> {{ trans('Back to List') }}
                                    </a>
                                </div>
                            </div>

                            <!-- Transfer Timeline -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4>{{ trans('Transfer Timeline') }}</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="timeline">
                                        <li class="timeline-item">
                                            <span class="timeline-point bg-success"></span>
                                            <div class="timeline-content">
                                                <h6>{{ trans('Requested') }}</h6>
                                                <p class="text-muted mb-0">{{ $stockTransfer->requested_at ? $stockTransfer->requested_at->format('M d, Y H:i') : $stockTransfer->created_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        </li>
                                        
                                        @if($stockTransfer->approved_at)
                                        <li class="timeline-item">
                                            <span class="timeline-point bg-info"></span>
                                            <div class="timeline-content">
                                                <h6>{{ trans('Approved') }}</h6>
                                                <p class="text-muted mb-0">{{ $stockTransfer->approved_at->format('M d, Y H:i') }}</p>
                                                @if($stockTransfer->approvedByUser)
                                                    <small class="text-muted">by {{ $stockTransfer->approvedByUser->name }}</small>
                                                @endif
                                            </div>
                                        </li>
                                        @endif

                                        @if($stockTransfer->picked_at)
                                        <li class="timeline-item">
                                            <span class="timeline-point bg-warning"></span>
                                            <div class="timeline-content">
                                                <h6>{{ trans('Picked') }}</h6>
                                                <p class="text-muted mb-0">{{ $stockTransfer->picked_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        </li>
                                        @endif

                                        @if($stockTransfer->shipped_at)
                                        <li class="timeline-item">
                                            <span class="timeline-point bg-primary"></span>
                                            <div class="timeline-content">
                                                <h6>{{ trans('Shipped') }}</h6>
                                                <p class="text-muted mb-0">{{ $stockTransfer->shipped_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        </li>
                                        @endif

                                        @if($stockTransfer->received_at)
                                        <li class="timeline-item">
                                            <span class="timeline-point bg-success"></span>
                                            <div class="timeline-content">
                                                <h6>{{ trans('Received') }}</h6>
                                                <p class="text-muted mb-0">{{ $stockTransfer->received_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection