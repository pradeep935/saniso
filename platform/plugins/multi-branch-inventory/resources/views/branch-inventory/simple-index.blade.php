@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Simple Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">Inventory Management</h3>
                            <p class="text-muted mb-0">View and manage all product inventory across branches</p>
                        </div>
                        @if($selectedBranch)
                            <span class="badge bg-primary fs-6">{{ $selectedBranch->name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Simple Stats -->
            @if($selectedBranch)
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center py-2">
                            <h5 class="mb-0">{{ number_format($stats['total_products']) }}</h5>
                            <small class="text-muted">Total Products</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center py-2">
                            <h5 class="mb-0">{{ number_format($stats['total_in_inventory']) }}</h5>
                            <small class="text-muted">In Inventory</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center py-2">
                            <h5 class="mb-0 text-warning">{{ number_format($stats['low_stock_items']) }}</h5>
                            <small class="text-muted">Low Stock</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center py-2">
                            <h5 class="mb-0 text-danger">{{ number_format($stats['out_of_stock']) }}</h5>
                            <small class="text-muted">Out of Stock</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Simple Filters -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranch && $selectedBranch->id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Search Products</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control" placeholder="Search by name or SKU...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Stock Status</label>
                            <select name="stock_status" class="form-select">
                                <option value="">All Items</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Simple Inventory Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            @if($selectedBranch)
                                {{ $selectedBranch->name }} - Products
                            @else
                                All Products
                            @endif
                        </h5>
                        <small class="text-muted">{{ $inventory->total() }} products</small>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($inventory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Available</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventory as $item)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $item->product->name }}</strong>
                                                    <br><small class="text-muted">ID: {{ $item->product->id }}</small>
                                                </div>
                                            </td>

                                            <td>
                                                <span>{{ $item->sku ?: $item->product->sku ?: 'N/A' }}</span>
                                            </td>

                                            <td class="text-center">
                                                @if($item->has_branch_inventory)
                                                    <input type="number" 
                                                           value="{{ $item->quantity_on_hand }}" 
                                                           class="form-control form-control-sm" 
                                                           class="w-80"
                                                           data-inventory-id="{{ $item->id }}"
                                                           min="0">
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if($item->has_branch_inventory)
                                                    <span class="fw-bold">{{ $item->quantity_available }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if(!$item->has_branch_inventory)
                                                    <span class="badge bg-secondary">Not Added</span>
                                                @elseif($item->quantity_available <= 0)
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @elseif($item->quantity_available <= $item->minimum_stock && $item->minimum_stock > 0)
                                                    <span class="badge bg-warning">Low Stock</span>
                                                @else
                                                    <span class="badge bg-success">In Stock</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if($item->has_branch_inventory)
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary btn-sm" 
                                                                onclick="editInventory({{ $item->id }})" 
                                                                title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info btn-sm" 
                                                                onclick="adjustStock({{ $item->id }})" 
                                                                title="Adjust Stock">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <button class="btn btn-success btn-sm" 
                                                            onclick="addToInventory({{ $item->product->id }})" 
                                                            title="Add to Inventory">
                                                        <i class="fas fa-plus"></i> Add
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box-open text-muted icon-large"></i>
                            <h5 class="mt-3 text-muted">No inventory items found</h5>
                            <p class="text-muted">Try adjusting your filters or add products to this branch.</p>
                        </div>
                    @endif
                </div>

                @if($inventory->hasPages())
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $inventory->firstItem() }} to {{ $inventory->lastItem() }} of {{ $inventory->total() }} results
                            </div>
                            {{ $inventory->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add to Branch Modal -->
<div class="modal fade" id="addToBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product to Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addToBranchForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="productId" name="product_id">
                    <input type="hidden" id="branchId" name="branch_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Initial Quantity</label>
                        <input type="number" name="quantity_on_hand" class="form-control" value="0" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Minimum Stock Level</label>
                        <input type="number" name="minimum_stock" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add to Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('footer')
<script>
    // Auto-submit form when branch is changed
    $('select[name="branch_id"]').on('change', function() {
        $(this).closest('form').submit();
    });

    // Add to branch function
    function addToBranch(productId, branchId) {
        $('#productId').val(productId);
        $('#branchId').val(branchId);
        $('#addToBranchModal').modal('show');
    }

    // Handle add to branch form submission
    $('#addToBranchForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("branch-inventory.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addToBranchModal').modal('hide');
                window.location.reload();
            },
            error: function(xhr) {
                alert('Error adding product to branch: ' + xhr.responseJSON.message);
            }
        });
    });

    // Simple functions for inventory management
    function addToInventory(productId) {
        $('#productId').val(productId);
        $('#branchId').val({{ $selectedBranch ? $selectedBranch->id : 'null' }});
        $('#addToBranchModal').modal('show');
    }

    function editInventory(inventoryId) {
        window.location.href = '/admin/branch-inventory/' + inventoryId + '/edit';
    }

    function adjustStock(inventoryId) {
        // Simple redirect to adjust stock form
        window.location.href = '/admin/branch-inventory/adjust-stock-form?inventory_id=' + inventoryId;
    }

    // Auto-save quantity changes
    $('input[type="number"]').on('change', function() {
        const input = $(this);
        const inventoryId = input.data('inventory-id');
        const newQuantity = input.val();
        
        if (inventoryId) {
            $.ajax({
                url: '{{ route("branch-inventory.update-quantity") }}',
                method: 'POST',
                data: {
                    inventory_id: inventoryId,
                    field: 'quantity_on_hand',
                    value: newQuantity,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Update available quantity display
                        input.closest('tr').find('td:nth-child(4) span').text(response.data.quantity_available);
                        
                        // Show success feedback
                        input.addClass('border-success');
                        setTimeout(() => input.removeClass('border-success'), 2000);
                    }
                },
                error: function(xhr) {
                    alert('Failed to update quantity: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    input.focus();
                }
            });
        }
    });
</script>
@endpush