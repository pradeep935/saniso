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
    
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-primary-gradient {
        background: linear-gradient(135deg, #50c878 0%, #35a863 100%);
        border: none;
        color: white;
        padding: 12px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(80, 200, 120, 0.4);
        color: white;
    }
    
    .filter-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .filter-card h5 {
        margin-bottom: 15px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        display: block;
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 6px;
        font-size: 13px;
    }
    
    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d4d9e3;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    
    .filter-group input:focus,
    .filter-group select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .btn-reset {
        background: #ecf0f1;
        border: 1px solid #d4d9e3;
        color: #2c3e50;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-reset:hover {
        background: #e0e6ed;
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
    
    .table tbody tr {
        border-bottom: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .table td {
        padding: 15px;
        vertical-align: middle;
    }
    
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-info {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .badge-success {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .badge-warning {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .badge-danger {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    .status-pending {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .status-received {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .status-cancelled {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .btn-group {
        display: flex;
        gap: 5px;
    }
    
    .btn-action {
        padding: 6px 10px;
        border-radius: 4px;
        border: none;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-view {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .btn-view:hover {
        background: #bbdefb;
    }
    
    .btn-edit {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .btn-edit:hover {
        background: #ffe0b2;
    }
    
    .btn-success-small {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .btn-success-small:hover {
        background: #c8e6c9;
    }
    
    .btn-danger-small {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .btn-danger-small:hover {
        background: #ffcdd2;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        color: #bdc3c7;
        margin-bottom: 15px;
    }
    
    .empty-state h4 {
        color: #7f8c8d;
        margin: 15px 0;
    }
    
    .empty-state p {
        color: #95a5a6;
        margin-bottom: 20px;
    }
    
    .pagination {
        margin-top: 20px;
        justify-content: center;
    }
</style>

<div class="page-content">
    <div class="modern-header">
        <div class="container-fluid">
            <h1><i class="fa fa-inbox"></i> {{ trans('Incoming Goods Management') }}</h1>
            <div class="action-buttons">
                <a href="{{ route('incoming-goods.create') }}" class="btn-primary-gradient">
                    <i class="fa fa-plus"></i> {{ trans('Receive New Goods') }}
                </a>
                <a href="{{ route('temporary-products.index') }}" class="btn-primary-gradient" style="background: linear-gradient(135deg, #ffb86b 0%, #ff8a00 100%);">
                    <i class="fa fa-box-open"></i> {{ trans('New Goods (Temporary)') }}
                </a>
                <a href="{{ route('incoming-goods.bulk-receive') }}" class="btn-primary-gradient" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                    <i class="fa fa-truck-loading"></i> {{ trans('Bulk Receive') }}
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="filter-card">
            <h5><i class="fa fa-filter"></i> {{ trans('Search & Filter') }}</h5>
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="branch_id">{{ trans('Branch') }}</label>
                        <select name="branch_id" id="branch_id">
                            <option value="">{{ trans('All Branches') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">{{ trans('Status') }}</label>
                        <select name="status" id="status">
                            <option value="">{{ trans('All Statuses') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ trans('Pending') }}</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>{{ trans('Received') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ trans('Cancelled') }}</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="supplier">{{ trans('Supplier') }}</label>
                        <input type="text" name="supplier" id="supplier" placeholder="Supplier name..." 
                               value="{{ request('supplier') }}">
                    </div>
                    
                    <div class="filter-group">
                        <label for="reference">{{ trans('Reference') }}</label>
                        <input type="text" name="reference" id="reference" placeholder="Reference number..." 
                               value="{{ request('reference') }}">
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">
                            <i class="fa fa-search"></i> {{ trans('Filter') }}
                        </button>
                        <a href="{{ route('incoming-goods.index') }}" class="btn-reset">
                            <i class="fa fa-refresh"></i> {{ trans('Reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-wrapper">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 8%;">{{ trans('ID') }}</th>
                            <th style="width: 12%;">{{ trans('Branch') }}</th>
                            <th style="width: 12%;">{{ trans('Supplier') }}</th>
                            <th style="width: 12%;">{{ trans('Reference') }}</th>
                            <th style="width: 12%;">{{ trans('Received Date') }}</th>
                            <th style="width: 10%;">{{ trans('Items') }}</th>
                            <!-- Total Cost column removed -->
                            <th style="width: 10%;">{{ trans('Status') }}</th>
                            <th style="width: 12%;">{{ trans('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomingGoods as $goods)
                            <tr>
                                <td>
                                    <strong>#{{ $goods->id }}</strong>
                                    @if($goods->reference_number)
                                        <br><small class="text-muted">{{ $goods->reference_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $goods->branch->name }}</span>
                                </td>
                                <td>{{ $goods->supplier_name ?: 'N/A' }}</td>
                                <td>{{ $goods->reference_number ?: 'N/A' }}</td>
                                <td>
                                    <strong>{{ $goods->receiving_date ? $goods->receiving_date->format('M d, Y') : 'Not received' }}</strong>
                                    @if($goods->receiving_date)
                                        <br><small class="text-muted">{{ $goods->receiving_date->format('H:i') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $goods->items->count() }}</span>
                                    <br><small>{{ $goods->items->sum('quantity_received') }} units</small>
                                </td>
                                <!-- Total Cost removed -->
                                <td>
                                    @switch($goods->status)
                                        @case('pending')
                                            <span class="status-badge status-pending">{{ trans('Pending') }}</span>
                                            @break
                                        @case('received')
                                            <span class="status-badge status-received">{{ trans('Received') }}</span>
                                            @break
                                        @case('cancelled')
                                            <span class="status-badge status-cancelled">{{ trans('Cancelled') }}</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('incoming-goods.show', $goods->id) }}" 
                                           class="btn-action btn-view" title="{{ trans('View Details') }}">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        
                                        @if($goods->status === 'pending')
                                            <a href="{{ route('incoming-goods.edit', $goods->id) }}" 
                                               class="btn-action btn-edit" title="{{ trans('Edit') }}">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" action="{{ route('incoming-goods.process', $goods->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-action btn-success-small" title="{{ trans('Mark as Received') }}"
                                                        onclick="return confirm('{{ trans('Mark this as received?') }}')">
                                                    <i class="fa fa-check"></i> Receive
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <div class="dropdown" style="display: inline-block;">
                                            <button class="btn-action" style="background: #ecf0f1; color: #2c3e50;" type="button" 
                                                    data-toggle="dropdown" title="{{ trans('More Actions') }}">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li><a href="{{ route('incoming-goods.duplicate', $goods->id) }}">
                                                    <i class="fa fa-copy"></i> {{ trans('Duplicate') }}
                                                </a></li>
                                                @if($goods->status === 'pending')
                                                    <li class="divider"></li>
                                                    <li><a href="#" onclick="deleteGoods({{ $goods->id }})">
                                                        <i class="fa fa-trash"></i> <span class="text-danger">{{ trans('Delete') }}</span>
                                                    </a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fa fa-inbox fa-3x"></i>
                                        <h4>{{ trans('No incoming goods found') }}</h4>
                                        <p>{{ trans('Start by receiving your first shipment') }}</p>
                                        <a href="{{ route('incoming-goods.create') }}" class="btn-primary-gradient">
                                            <i class="fa fa-plus"></i> {{ trans('Receive New Goods') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($incomingGoods->hasPages())
                <div class="p-4">
                    {{ $incomingGoods->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('javascript')
    <script>
        function deleteGoods(goodsId) {
            if (confirm('{{ trans('Are you sure you want to delete this incoming goods record?') }}')) {
                $.ajax({
                    url: '/admin/incoming-goods/' + goodsId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        location.reload();
                    },
                    error: function() {
                        alert('{{ trans('Error deleting record') }}');
                    }
                });
            }
        }

        // Auto-submit filters on change
        $('#branch_id, #status').on('change', function() {
            $(this).closest('form').submit();
        });
    </script>
@endsection
