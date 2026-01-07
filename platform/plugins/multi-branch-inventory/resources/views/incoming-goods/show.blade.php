@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<style>
    .page-content {
        background: #f5f7fa;
    }
    
    .modern-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .modern-header h1 {
        margin: 0;
        font-size: 32px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .info-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        border: none;
    }
    
    .info-card .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 20px;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .info-card .card-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-card .card-header i {
        color: #667eea;
        font-size: 18px;
    }
    
    .info-card .card-body {
        padding: 25px;
    }
    
    .info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
    }
    
    .info-item {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 15px;
    }
    
    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    
    .info-value {
        font-size: 16px;
        font-weight: 500;
        color: #2c3e50;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-pending {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .status-received {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .status-processed {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .table-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .table-wrapper {
        overflow-x: auto;
    }
    
    .table {
        margin: 0;
    }
    
    .table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
    }
    
    .table th {
        padding: 15px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-info {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .summary-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .summary-row:last-child {
        border-bottom: none;
        font-size: 18px;
        font-weight: 600;
    }
    
    .btn-action {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .btn-back {
        background: #ecf0f1;
        color: #2c3e50;
    }
    
    .btn-back:hover {
        background: #e0e6ed;
        color: #2c3e50;
    }
    
    .btn-edit {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .btn-edit:hover {
        background: #ffe0b2;
    }
    
    .btn-process {
        background: linear-gradient(135deg, #50c878 0%, #35a863 100%);
        color: white;
    }
    
    .btn-process:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(80, 200, 120, 0.4);
        color: white;
    }
    
    .btn-delete {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .btn-delete:hover {
        background: #ffcdd2;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
</style>

<div class="page-content">
    <div class="modern-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <i class="fa fa-inbox"></i> 
                        {{ trans('Incoming Goods Details') }}
                    </h1>
                    <small style="color: rgba(255,255,255,0.8); margin-top: 10px; display: block;">
                        Reference: {{ $incomingGood->reference_number ?? 'N/A' }}
                    </small>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('incoming-goods.index') }}" class="btn-action btn-back">
                        <i class="fa fa-arrow-left"></i> {{ trans('Back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 6px; border: none;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 style="margin-top: 0;">{{ trans('Errors') }}</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" style="border-radius: 6px; border: none;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        <!-- Header Information -->
        <div class="info-card">
            <div class="card-header">
                <h5><i class="fa fa-info-circle"></i> {{ trans('Delivery Information') }}</h5>
                <span class="status-badge status-{{ $incomingGood->status }}">
                    {{ ucfirst($incomingGood->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">{{ trans('Branch') }}</div>
                        <div class="info-value">{{ $incomingGood->branch->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ trans('Supplier') }}</div>
                        <div class="info-value">{{ $incomingGood->supplier_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ trans('Reference Number') }}</div>
                        <div class="info-value">{{ $incomingGood->reference_number ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">{{ trans('Receiving Date') }}</div>
                        <div class="info-value">
                            {{ $incomingGood->receiving_date ? $incomingGood->receiving_date->format('M d, Y H:i') : 'N/A' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ trans('Received By') }}</div>
                        <div class="info-value">{{ $incomingGood->receivedByUser->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">{{ trans('Total Items') }}</div>
                        <div class="info-value">{{ $incomingGood->items->count() }} items</div>
                    </div>
                </div>

                @if($incomingGood->notes)
                    <div style="margin-top: 20px;">
                        <div class="info-label">{{ trans('Notes') }}</div>
                        <div style="padding: 12px; background: #f8f9fa; border-radius: 6px; color: #2c3e50;">
                            {{ $incomingGood->notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-card">
            <div style="padding: 20px; background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                <h5 style="margin: 0; font-weight: 600; color: #2c3e50;">
                    <i class="fa fa-list" style="color: #667eea; margin-right: 8px;"></i>
                    {{ trans('Items') }}
                </h5>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">{{ trans('#') }}</th>
                            <th style="width: 30%;">{{ trans('Product') }}</th>
                            <th style="width: 12%;">{{ trans('Expected Qty') }}</th>
                            <th style="width: 12%;">{{ trans('Received Qty') }}</th>
                            <th style="width: 12%;">{{ trans('Unit Cost') }}</th>
                            <th style="width: 12%;">{{ trans('Total Cost') }}</th>
                            <th style="width: 17%;">{{ trans('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomingGood->items as $index => $item)
                            <tr>
                                <td><strong>{{ $index + 1 }}</strong></td>
                                <td>
                                    <strong>{{ $item->product_name ?? 'N/A' }}</strong>
                                    @if($item->product)
                                        <br><small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ $item->quantity_expected ?? 0 }}</strong></td>
                                <td><strong>{{ $item->quantity_received ?? 0 }}</strong></td>
                                <td>${{ number_format($item->unit_cost ?? 0, 2) }}</td>
                                <td><strong>${{ number_format(($item->quantity_received ?? 0) * ($item->unit_cost ?? 0), 2) }}</strong></td>
                                <td>
                                    @if(($item->quantity_received ?? 0) == ($item->quantity_expected ?? 0))
                                        <span class="badge badge-info">{{ trans('Complete') }}</span>
                                    @elseif(($item->quantity_received ?? 0) > 0)
                                        <span class="badge" style="background: #fff3e0; color: #f57c00;">{{ trans('Partial') }}</span>
                                    @else
                                        <span class="badge" style="background: #ffebee; color: #d32f2f;">{{ trans('Pending') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <i class="fa fa-box-open fa-2x text-muted" style="margin-bottom: 10px;"></i>
                                    <p class="text-muted">{{ trans('No items found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="summary-box">
                <div class="summary-row">
                    <span>{{ trans('Total Items') }}:</span>
                    <span>{{ $incomingGood->items->count() }} items</span>
                </div>
                <div class="summary-row">
                    <span>{{ trans('Total Quantity') }}:</span>
                    <span>{{ $incomingGood->items->sum('quantity_received') }} units</span>
                </div>
                <!-- Total Value removed — showing only counts per new requirements -->
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons" style="margin-top: 30px;">
            <a href="{{ route('incoming-goods.index') }}" class="btn-action btn-back">
                <i class="fa fa-arrow-left"></i> {{ trans('Back to List') }}
            </a>

            @if($incomingGood->status === 'received')
                <a href="{{ route('incoming-goods.edit', $incomingGood->id) }}" class="btn-action btn-edit">
                    <i class="fa fa-edit"></i> {{ trans('Edit') }}
                </a>

                <form method="POST" action="{{ route('incoming-goods.process', $incomingGood->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-process" onclick="return confirm('{{ trans('Process this incoming goods to inventory?') }}')">
                        <i class="fa fa-check-circle"></i> {{ trans('Process to Inventory') }}
                    </button>
                </form>
            @endif

            <a href="{{ route('incoming-goods.duplicate', $incomingGood->id) }}" class="btn-action" style="background: #e3f2fd; color: #1976d2;">
                <i class="fa fa-copy"></i> {{ trans('Duplicate') }}
            </a>

            @if($incomingGood->status === 'received')
                <form method="POST" action="{{ route('incoming-goods.destroy', $incomingGood->id) }}" style="display: inline;" 
                      onsubmit="return confirm('{{ trans('Are you sure? This cannot be undone.') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action btn-delete">
                        <i class="fa fa-trash"></i> {{ trans('Delete') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@endsection
