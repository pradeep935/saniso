@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.reports') }}</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">{{ trans('Inventory reports and analytics across all branches') }}</p>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-primary">
                                        <i class="fa fa-building"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">{{ $branches->count() }}</h4>
                                        <div class="info">
                                            <strong class="amount">Active Branches</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-info">
                                        <i class="fa fa-boxes"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">{{ $totalProducts }}</h4>
                                        <div class="info">
                                            <strong class="amount">Total Products</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">{{ $lowStockItems }}</h4>
                                        <div class="info">
                                            <strong class="amount">Low Stock Items</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-success">
                                        <i class="fa fa-truck"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">{{ $pendingTransfers }}</h4>
                                        <div class="info">
                                            <strong class="amount">Pending Transfers</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Filter -->
                    <div class="form-group mb-3">
                        <label class="control-label">Filter by Branch:</label>
                        <select class="form-control" id="branch-filter">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reports Tabs -->
                    <div class="tabbable-custom">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a href="#low-stock-tab" class="nav-link active" data-bs-toggle="tab">Low Stock Items</a>
                            </li>
                            <li class="nav-item">
                                <a href="#stock-levels-tab" class="nav-link" data-bs-toggle="tab">Stock Levels</a>
                            </li>
                            <li class="nav-item">
                                <a href="#transfers-tab" class="nav-link" data-bs-toggle="tab">Transfer History</a>
                            </li>
                            <li class="nav-item">
                                <a href="#movements-tab" class="nav-link" data-bs-toggle="tab">Recent Movements</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Low Stock Tab -->
                            <div class="tab-pane active" id="low-stock-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="low-stock-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Branch</th>
                                                <th>Current Stock</th>
                                                <th>Min Stock</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be populated via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Stock Levels Tab -->
                            <div class="tab-pane" id="stock-levels-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="stock-levels-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Branch</th>
                                                <th>Available</th>
                                                <th>Reserved</th>
                                                <th>Total</th>
                                                <th>Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be populated via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Transfers Tab -->
                            <div class="tab-pane" id="transfers-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="transfers-table">
                                        <thead>
                                            <tr>
                                                <th>Transfer ID</th>
                                                <th>From Branch</th>
                                                <th>To Branch</th>
                                                <th>Status</th>
                                                <th>Items</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be populated via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Recent Movements Tab -->
                            <div class="tab-pane" id="movements-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Branch</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Reference</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentMovements as $movement)
                                                <tr>
                                                    <td>{{ $movement->product->name ?? 'N/A' }}</td>
                                                    <td>{{ $movement->branch->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $movement->type === 'in' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($movement->type) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $movement->quantity_changed }}</td>
                                                    <td>{{ $movement->reference_type }}</td>
                                                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('footer')
<script>
$(document).ready(function() {
    // Load initial data
    loadLowStockData();
    
    // Branch filter change
    $('#branch-filter').on('change', function() {
        const branchId = $(this).val();
        loadLowStockData(branchId);
        loadStockLevelsData(branchId);
        loadTransfersData(branchId);
    });
    
    // Tab switching
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('href');
        const branchId = $('#branch-filter').val();
        
        switch(target) {
            case '#low-stock-tab':
                loadLowStockData(branchId);
                break;
            case '#stock-levels-tab':
                loadStockLevelsData(branchId);
                break;
            case '#transfers-tab':
                loadTransfersData(branchId);
                break;
        }
    });
    
    function loadLowStockData(branchId = '') {
        $.get('{{ route("inventory-reports.low-stock") }}', { branch_id: branchId })
            .done(function(data) {
                let html = '';
                data.forEach(function(item) {
                    html += `<tr>
                        <td>${item.product ? item.product.name : 'N/A'}</td>
                        <td>${item.branch ? item.branch.name : 'N/A'}</td>
                        <td><span class="badge badge-warning">${item.quantity}</span></td>
                        <td>${item.min_quantity}</td>
                        <td><span class="badge badge-danger">Low Stock</span></td>
                    </tr>`;
                });
                $('#low-stock-table tbody').html(html);
            });
    }
    
    function loadStockLevelsData(branchId = '') {
        $.get('{{ route("inventory-reports.stock-levels") }}', { branch_id: branchId })
            .done(function(data) {
                let html = '';
                data.forEach(function(item) {
                    html += `<tr>
                        <td>${item.product ? item.product.name : 'N/A'}</td>
                        <td>${item.branch ? item.branch.name : 'N/A'}</td>
                        <td>${item.quantity_available}</td>
                        <td>${item.quantity_reserved}</td>
                        <td>${item.quantity}</td>
                        <td>${item.location || 'Not Set'}</td>
                    </tr>`;
                });
                $('#stock-levels-table tbody').html(html);
            });
    }
    
    function loadTransfersData(branchId = '') {
        $.get('{{ route("inventory-reports.transfer-history") }}', { branch_id: branchId })
            .done(function(data) {
                let html = '';
                data.data.forEach(function(transfer) {
                    const statusBadge = getStatusBadge(transfer.status);
                    html += `<tr>
                        <td>#${transfer.id}</td>
                        <td>${transfer.from_branch ? transfer.from_branch.name : 'N/A'}</td>
                        <td>${transfer.to_branch ? transfer.to_branch.name : 'N/A'}</td>
                        <td>${statusBadge}</td>
                        <td>${transfer.items ? transfer.items.length : 0} items</td>
                        <td>${new Date(transfer.created_at).toLocaleDateString()}</td>
                    </tr>`;
                });
                $('#transfers-table tbody').html(html);
            });
    }
    
    function getStatusBadge(status) {
        const badges = {
            'pending': 'badge-warning',
            'approved': 'badge-info',
            'shipped': 'badge-primary',
            'received': 'badge-success',
            'cancelled': 'badge-danger'
        };
        return `<span class="badge ${badges[status] || 'badge-secondary'}">${status}</span>`;
    }
});
</script>
@endpush