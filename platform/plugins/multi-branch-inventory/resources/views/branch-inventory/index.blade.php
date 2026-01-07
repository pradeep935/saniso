@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Multi-Branch Inventory CSS -->
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/inventory-main.css') }}">
@endpush

@section('content')
    <div class="modern-inventory">
        <!-- Enhanced Header -->
        <div class="gradient-header text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="mb-2">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.inventory') }}</h1>
                    <p class="mb-0 text-white-75">Manage your multi-branch inventory with real-time synchronization</p>
                </div>
                <div class="d-flex gap-3 flex-wrap">
                    <button type="button" class="btn btn-success modern-btn" id="toggle-excel-mode">
                        <i class="fas fa-table me-2"></i> <span id="excel-mode-text">Enable Excel Mode</span>
                    </button>
                    <a href="{{ route('branch-inventory.adjust-stock-form') }}" class="btn btn-light modern-btn">
                        <i class="fas fa-plus-circle me-2"></i> {{ trans('Adjust Stock') }}
                    </a>
                    <a href="{{ route('branch-inventory.bulk-edit') }}" class="btn btn-warning modern-btn text-white">
                        <i class="fas fa-layer-group me-2"></i> Bulk Edit</a>
                    </a>
                    <a href="{{ route('incoming-goods.create') }}" class="btn btn-success modern-btn">
                        <i class="fas fa-truck me-2"></i> {{ trans('Receive Goods') }}
                    </a>
                    <a href="{{ route('stock-transfers.create') }}" class="btn btn-info modern-btn">
                        <i class="fas fa-exchange-alt me-2"></i> {{ trans('Transfer Stock') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="filter-card mb-4">
            <div class="card-body p-4">
                <form method="GET" id="filterForm">
                    <div class="row g-4 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label for="branch_id" class="form-label fw-bold text-dark mb-2">
                                <i class="fas fa-building me-2 text-primary"></i>{{ trans('Branch') }}
                            </label>
                            <select name="branch_id" id="branch_id" class="form-select modern-form-control" onchange="this.form.submit()">
                                <option value="">{{ trans('All Branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <label for="search" class="form-label fw-bold text-dark mb-2">
                                <i class="fas fa-search me-2 text-info"></i>{{ trans('Search') }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" id="search" class="form-control modern-form-control border-start-0 search-input" 
                                       value="{{ request('search') }}" placeholder="Product name, SKU or EAN...">
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label for="stock_status" class="form-label fw-bold text-dark mb-2">
                                <i class="fas fa-chart-bar me-2 text-warning"></i>{{ trans('Stock Status') }}
                            </label>
                            <select name="stock_status" id="stock_status" class="form-select modern-form-control">
                                <option value="">{{ trans('All Products') }}</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>
                                    {{ trans('In Stock') }}
                                </option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                                    {{ trans('Low Stock') }}
                                </option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>
                                    {{ trans('Out of Stock') }}
                                </option>
                                <option value="not_in_inventory" {{ request('stock_status') == 'not_in_inventory' ? 'selected' : '' }}>
                                    Not in Branch Inventory
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary modern-btn flex-fill">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('branch-inventory.index') }}" class="btn btn-outline-secondary modern-btn">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        @if($selectedBranch)
            <div class="alert branch-selection-alert">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center branch-selection-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    <div>
                        <strong class="branch-selection-text">{{ trans('Selected Branch') }}:</strong>
                        <span class="fw-bold">{{ $selectedBranch->name }} ({{ $selectedBranch->code }})</span>
                        <br><small class="text-muted">Viewing inventory for this location</small>
                    </div>
                </div>
            </div>
        @endif

        @if($selectedBranch && isset($stats))
        <!-- Modern Statistics Dashboard -->
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card h-100 total-products">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="me-3">
                            <div class="stats-icon">
                                <i class="fas fa-shopping-bag fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small text-white-75 mb-1 stats-text">Total Products Available</div>
                            <div class="h2 mb-0 fw-bold">{{ number_format($stats['total_products'] ?? 0) }}</div>
                            @if(isset($stats['total_in_inventory']))
                                <small class="text-white-75">{{ number_format($stats['total_in_inventory']) }} in inventory</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card h-100 low-stock">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="me-3">
                            <div class="stats-icon">
                                <i class="fas fa-exclamation-triangle fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small mb-1 stats-text">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.low_stock_products') }}</div>
                            <div class="h2 mb-0 fw-bold">{{ number_format($stats['low_stock_items'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card h-100 out-of-stock">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="me-3">
                            <div class="stats-icon">
                                <i class="fas fa-times-circle fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small mb-1 stats-text">Out of Stock</div>
                            <div class="h2 mb-0 fw-bold">{{ number_format($stats['out_of_stock'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card stats-card h-100 inventory-value">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="me-3">
                            <div class="stats-icon">
                                <i class="fas fa-boxes fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small mb-1 stats-text">Total Items</div>
                            <div class="h2 mb-0 fw-bold">{{ number_format($stats['total_items'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Modern Inventory Table -->
        <div class="card modern-table">
            <div class="card-header bg-white border-0 table-header">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-box-open me-2 text-primary"></i>Inventory Items
                    @if($inventory->total() > 0)
                        <span class="badge bg-primary ms-2">{{ number_format($inventory->total()) }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0" id="inventory-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-box me-2"></i>{{ trans('Product') }}</th>
                                <th><i class="fas fa-barcode me-2"></i>{{ trans('SKU') }}</th>
                                <th><i class="fas fa-building me-2"></i>{{ trans('Branch') }}</th>
                                <th class="text-center"><i class="fas fa-warehouse me-2"></i>{{ trans('On Hand') }}</th>
                                <th class="text-center"><i class="fas fa-check-circle me-2"></i>{{ trans('Available') }}</th>
                                <th class="text-center"><i class="fas fa-lock me-2"></i>{{ trans('Reserved') }}</th>
                                <th class="text-center"><i class="fas fa-chart-line me-2"></i>{{ trans('Min Stock') }}</th>
                                <th class="text-center"><i class="fas fa-traffic-light me-2"></i>{{ trans('Status') }}</th>
                                <th class="text-end"><i class="fas fa-dollar-sign me-2"></i>{{ trans('Cost Price') }}</th>
                                <th class="text-end"><i class="fas fa-tag me-2"></i>{{ trans('Selling Price') }}</th>
                                <th class="text-center"><i class="fas fa-cogs me-2"></i>{{ trans('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $item)
                                <tr class="{{ !$item->has_branch_inventory ? 'table-light' : ($item->quantity_on_hand <= $item->minimum_stock ? 'table-warning' : '') }}" 
                                    class="{{ !$item->has_branch_inventory ? 'inventory-row-no-branch' : ($item->quantity_on_hand <= $item->minimum_stock ? 'inventory-row-low-stock' : '') }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                @if($item->product?->image)
                                                    <img src="{{ $item->product->image }}" alt="Product" class="product-img">
                                                @else
                                                    <div class="product-placeholder">
                                                        <i class="fas fa-box text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="product-name">
                                                    {{ $item->product?->name ?? 'Unknown Product' }}
                                                    @if(!$item->has_branch_inventory)
                                                        <span class="badge bg-lable ms-2 not-in-inventory">Not in Inventory</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $item->product?->sku ?? 'N/A' }}</small>
                                                @if(!$item->has_branch_inventory)
                                                    <br><small class="text-info"><i class="fas fa-info-circle me-1"></i>Available to add to branch inventory</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark sku-badge">{{ $item->sku }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary branch-badge">{{ $item->branch->name }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->has_branch_inventory)
                                            <div class="quantity-cell" data-inventory-id="{{ $item->id }}" data-field="quantity_on_hand">
                                                <span class="quantity-display status-badge {{ $item->quantity_on_hand > 0 ? 'bg-success' : 'bg-danger' }} text-white">
                                                    {{ number_format($item->quantity_on_hand) }}
                                                </span>
                                                <input type="number" class="quantity-input form-control form-control-sm text-center d-none" 
                                                       value="{{ $item->quantity_on_hand }}" min="0" class="quantity-input">
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->has_branch_inventory)
                                            <div class="quantity-cell" data-inventory-id="{{ $item->id }}" data-field="quantity_available">
                                                <span class="quantity-display status-badge {{ $item->quantity_available > 0 ? 'bg-info' : 'bg-warning' }} text-white">
                                                    {{ number_format($item->quantity_available) }}
                                                </span>
                                                <input type="number" class="quantity-input form-control form-control-sm text-center d-none" 
                                                       value="{{ $item->quantity_available }}" min="0" class="quantity-input">
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->has_branch_inventory && $item->quantity_reserved > 0)
                                            <span class="status-badge bg-warning text-white">{{ number_format($item->quantity_reserved) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->has_branch_inventory)
                                            <div class="quantity-cell" data-inventory-id="{{ $item->id }}" data-field="minimum_stock">
                                                <span class="quantity-display fw-bold text-dark">{{ number_format($item->minimum_stock) }}</span>
                                                <input type="number" class="quantity-input form-control form-control-sm text-center d-none" 
                                                       value="{{ $item->minimum_stock }}" min="0" class="quantity-input">
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$item->has_branch_inventory)
                                            <span class="status-badge bg-secondary text-white">Not Added</span>
                                        @elseif($item->quantity_on_hand <= 0)
                                            <span class="status-badge bg-danger text-white">{{ trans('Out of Stock') }}</span>
                                        @elseif($item->quantity_on_hand <= $item->minimum_stock)
                                            <span class="status-badge bg-warning text-white">{{ trans('Low Stock') }}</span>
                                        @else
                                            <span class="status-badge bg-success text-white">{{ trans('In Stock') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-dark">${{ number_format($item->cost_price ?? 0, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary">${{ number_format($item->selling_price ?? 0, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->has_branch_inventory)
                                            <div class="d-flex action-buttons justify-content-center">
                                                <button type="button" class="btn btn-sm btn-outline-info modern-btn" 
                                                        onclick="showInventoryDetails({{ $item->id }})" class="btn-rounded">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary modern-btn" 
                                                        onclick="openAdjustStockModal({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity_on_hand }})" class="btn-rounded">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success modern-btn" 
                                                        onclick="openTransferModal({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity_on_hand }})" class="btn-rounded">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                            </div>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success modern-btn" 
                                                    onclick="addToBranchInventory({{ $item->product_id }}, '{{ $item->product->name }}', {{ $selectedBranch->id }})" 
                                                    class="btn-rounded">
                                                <i class="fas fa-plus me-1"></i>Add to Inventory
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center empty-state">
                                        <div class="empty-state">
                                            <div class="mb-4">
                                                <i class="fas fa-box-open text-muted empty-icon"></i>
                                            </div>
                                            <h4 class="text-muted mb-2 empty-title">{{ trans('No inventory items found') }}</h4>
                                            <p class="text-muted mb-4">{{ trans('Try adjusting your filters or add some products to inventory') }}</p>
                                            <a href="{{ route('branch-inventory.bulk-update') }}" class="btn btn-primary modern-btn">
                                                <i class="fas fa-plus me-2"></i>Add Inventory
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($inventory->hasPages())
                <div class="card-footer bg-white border-0 card-footer-custom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $inventory->firstItem() }} to {{ $inventory->lastItem() }} of {{ $inventory->total() }} results
                        </div>
                        <div>
                            {{ $inventory->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modern Adjust Stock Modal -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-custom">
                <form id="adjustStockForm" method="POST">
                    @csrf
                    <div class="modal-header modal-header-gradient">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="modal-icon">
                                    <i class="fas fa-edit fa-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="modal-title mb-0 modal-title-custom">{{ trans('Adjust Stock') }}</h4>
                                <small class="text-white-75">Update inventory levels for this product</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ trans('Product') }}:</label>
                            <p id="modalProductName" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('Current Stock') }}:</label>
                            <p id="modalCurrentStock" class="form-control-static"></p>
                            <div id="modalStockDetails" class="modal-stock-details">
                                <small class="text-muted">
                                    <strong>Ecommerce Total:</strong> <span id="modalEcommerceStock">-</span> units<br>
                                    <strong>Branch Stock:</strong> <span id="modalBranchStock">-</span> units
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="adjustmentType">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.adjustment_type') }}:</label>
                            <select name="adjustment_type" id="adjustmentType" class="form-control" required>
                                <option value="add">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.add_stock') }}</option>
                                <option value="subtract">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.subtract_stock') }}</option>
                                <option value="set">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.set_exact_amount') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="adjustmentQuantity">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.quantity') }}:</label>
                            <input type="number" name="quantity" id="adjustmentQuantity" class="form-control" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="adjustmentReason">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.reason') }}:</label>
                            <textarea name="reason" id="adjustmentReason" class="form-control" rows="3" 
                                      placeholder="Enter reason for stock adjustment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('core/base::forms.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.adjust_stock') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer Stock Modal -->
    <div class="modal fade" id="transferStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="transferStockForm" method="POST" action="{{ route('stock-transfers.quick-transfer') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">{{ trans('Quick Stock Transfer') }}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="transferProductId">
                        <input type="hidden" name="from_branch_id" id="transferFromBranch">
                        
                        <div class="form-group">
                            <label>{{ trans('Product') }}:</label>
                            <p id="transferProductName" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('Available Stock') }}:</label>
                            <p id="transferAvailableStock" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label for="transferToBranch">{{ trans('Transfer To Branch') }}:</label>
                            <select name="to_branch_id" id="transferToBranch" class="form-control" required>
                                <option value="">{{ trans('Select Branch') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transferQuantity">{{ trans('Quantity to Transfer') }}:</label>
                            <input type="number" name="quantity" id="transferQuantity" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="transferNotes">{{ trans('Notes') }}:</label>
                            <textarea name="notes" id="transferNotes" class="form-control" rows="2" 
                                      placeholder="Optional transfer notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('Cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ trans('Transfer Stock') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('footer')
<!-- Multi-Branch Inventory JavaScript -->
<script src="{{ asset('vendor/plugins/multi-branch-inventory/js/inventory-main.js') }}"></script>
<script>
// Initialize route variables for JavaScript
window.routes = {
    updateQuantity: '{{ route("multi-branch-inventory.branch-inventory.update-quantity") }}',
    adjustStock: '{{ route("multi-branch-inventory.branch-inventory.adjust", ":id") }}',
    inventoryDetails: '{{ route("multi-branch-inventory.branch-inventory.show", ":id") }}',
    inventoryShow: '{{ route("multi-branch-inventory.branch-inventory.show", ":id") }}',
    addProductToBranch: '{{ route("multi-branch-inventory.branch-inventory.add-product") }}'
};
</script>
@endpush
@endsection

@section('javascript')
    <script>
        function openAdjustStockModal(inventoryId, productName, currentStock) {
            $('#modalProductName').text(productName);
            $('#modalCurrentStock').text(currentStock + ' units');
            $('#adjustStockForm').attr('action', '/admin/branch-inventory/' + inventoryId + '/adjust-stock');
            
            // Fetch detailed stock information
            $.ajax({
                url: '/admin/branch-inventory/' + inventoryId + '/details',
                method: 'GET',
                success: function(response) {
                    if (response && response.product_name) {
                        // This is the old format, get ecommerce quantity
                        $.ajax({
                            url: '{{ route("branch-inventory.details", ["id" => ":product_id"]) }}'.replace(':product_id', response.product_id),
                            method: 'GET',
                            data: { branch_id: response.branch_id },
                            success: function(detailResponse) {
                                if (detailResponse.success) {
                                    $('#modalEcommerceStock').text(detailResponse.data.ecommerce_quantity);
                                    $('#modalBranchStock').text(detailResponse.data.branch_quantity);
                                }
                            }
                        });
                    } else {
                        $('#modalEcommerceStock').text('-');
                        $('#modalBranchStock').text(currentStock);
                    }
                },
                error: function() {
                    $('#modalEcommerceStock').text('-');
                    $('#modalBranchStock').text(currentStock);
                }
            });
            
            $('#adjustStockModal').modal('show');
        }

        function openTransferModal(inventoryId, productName, availableStock) {
            // Get the branch ID and product ID from the inventory item
            $.get('/admin/branch-inventory/' + inventoryId + '/details', function(data) {
                $('#transferProductId').val(data.product_id);
                $('#transferFromBranch').val(data.branch_id);
                $('#transferProductName').text(productName);
                $('#transferAvailableStock').text(availableStock + ' units');
                $('#transferQuantity').attr('max', availableStock);
                
                // Remove current branch from transfer options
                $('#transferToBranch option').each(function() {
                    $(this).prop('disabled', $(this).val() == data.branch_id);
                });
                
                $('#transferStockModal').modal('show');
            });
        }

        function showInventoryDetails(inventoryId) {
            window.location.href = '/admin/branch-inventory/' + inventoryId;
        }

        // Auto-submit search form on enter
        $('#search').on('keypress', function(e) {
            if (e.which === 13) {
                $(this).closest('form').submit();
            }
        });

        // Auto-submit stock status filter
        $('#stock_status').on('change', function() {
            $(this).closest('form').submit();
        });

        // Excel-like inline editing functionality
        let isExcelMode = false;
        let updateTimeout;

        $('#toggle-excel-mode').on('click', function() {
            isExcelMode = !isExcelMode;
            
            if (isExcelMode) {
                // Enable Excel mode
                $(this).removeClass('btn-success').addClass('btn-warning');
                $('#excel-mode-text').text('Disable Excel Mode');
                
                // Show input fields, hide display spans
                $('.quantity-cell').each(function() {
                    $(this).find('.quantity-display').addClass('d-none');
                    $(this).find('.quantity-input').removeClass('d-none');
                });
                
                // Show auto-save indicator
                if (!$('#auto-save-indicator').length) {
                    $('body').append('<div id="auto-save-indicator" class="auto-save-indicator"><div class="alert alert-success"><i class="fas fa-sync-alt fa-spin me-2"></i>Auto-saving...</div></div>');
                }
                
            } else {
                // Disable Excel mode
                $(this).removeClass('btn-warning').addClass('btn-success');
                $('#excel-mode-text').text('Enable Excel Mode');
                
                // Hide input fields, show display spans
                $('.quantity-cell').each(function() {
                    $(this).find('.quantity-display').removeClass('d-none');
                    $(this).find('.quantity-input').addClass('d-none');
                });
            }
        });

        // Auto-save functionality with 1-second delay (like default ecommerce)
        $(document).on('input', '.quantity-input', function() {
            const $input = $(this);
            const $cell = $input.closest('.quantity-cell');
            const inventoryId = $cell.data('inventory-id');
            const field = $cell.data('field');
            const value = $input.val();
            
            // Clear previous timeout
            if (updateTimeout) {
                clearTimeout(updateTimeout);
            }
            
            // Set new timeout for 1 second delay
            updateTimeout = setTimeout(function() {
                updateInventoryField(inventoryId, field, value, $cell);
            }, 1000);
        });

        function updateInventoryField(inventoryId, field, value, $cell) {
            $('#auto-save-indicator').show();
            
            $.ajax({
                url: '{{ route("branch-inventory.update-quantity") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    inventory_id: inventoryId,
                    field: field,
                    value: value
                },
                success: function(response) {
                    if (response.success) {
                        // Update the display span with new value
                        const $display = $cell.find('.quantity-display');
                        $display.text(parseInt(value).toLocaleString());
                        
                        // Update badge color based on value and field
                        if (field === 'quantity_on_hand') {
                            $display.removeClass('bg-success bg-danger').addClass(value > 0 ? 'bg-success' : 'bg-danger');
                        } else if (field === 'quantity_available') {
                            $display.removeClass('bg-info bg-warning').addClass(value > 0 ? 'bg-info' : 'bg-warning');
                        }
                        
                        // Show success feedback
                        showAutoSaveSuccess();
                    } else {
                        showAutoSaveError(response.message);
                    }
                },
                error: function(xhr) {
                    showAutoSaveError('Failed to update inventory');
                },
                complete: function() {
                    $('#auto-save-indicator').hide();
                }
            });
        }

        function showAutoSaveSuccess() {
            const $indicator = $('#auto-save-indicator');
            $indicator.html('<div class="alert alert-success"><i class="fas fa-check me-2"></i>Saved!</div>').show();
            setTimeout(() => $indicator.hide(), 2000);
        }

        function showAutoSaveError(message) {
            const $indicator = $('#auto-save-indicator');
            $indicator.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' + message + '</div>').show();
            setTimeout(() => $indicator.hide(), 3000);
        }

        // Form validation
        $('#adjustStockForm').on('submit', function(e) {
            const quantity = parseInt($('#adjustmentQuantity').val());
            const type = $('#adjustmentType').val();
            const currentStock = parseInt($('#modalCurrentStock').text());

            if (type === 'subtract' && quantity > currentStock) {
                e.preventDefault();
                alert('Cannot subtract more than available stock!');
                return false;
            }

            if (quantity <= 0) {
                e.preventDefault();
                alert('Quantity must be greater than 0!');
                return false;
            }
        });

        $('#transferStockForm').on('submit', function(e) {
            const quantity = parseInt($('#transferQuantity').val());
            const maxQuantity = parseInt($('#transferQuantity').attr('max'));

            if (quantity > maxQuantity) {
                e.preventDefault();
                alert('Cannot transfer more than available stock!');
                return false;
            }
        });
    </script>
@endsection