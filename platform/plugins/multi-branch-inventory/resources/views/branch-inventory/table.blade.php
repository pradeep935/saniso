@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/plugins/multi-branch-inventory/css/table.css') }}">
@endpush

@section('content')
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-table mr-2"></i>
                    Branch Inventory Management
                </h1>
                <p class="mb-0 opacity-75">Excel-like inventory management similar to default ecommerce system</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('branch-inventory.index') }}" class="btn btn-light">
                    <i class="fas fa-list mr-2"></i>
                    Detailed View
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-primary">{{ number_format($stats['total_products']) }}</div>
                <div class="stats-label">Total Products</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-success">{{ number_format($stats['products_in_branch']) }}</div>
                <div class="stats-label">In Branch</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-warning">{{ number_format($stats['low_stock_items']) }}</div>
                <div class="stats-label">Low Stock</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-danger">{{ number_format($stats['out_of_stock']) }}</div>
                <div class="stats-label">Out of Stock</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card text-center">
                <div class="stats-value text-info">{{ number_format($stats['total_items'] ?? 0) }}</div>
                <div class="stats-label">Total Items</div>
            </div>
        </div>
    </div>

    <!-- Branch Filter -->
    <div class="filter-card">
        <form method="GET" class="row align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="fas fa-building mr-2"></i>
                    Select Branch
                </label>
                <select name="branch_id" class="form-select" onchange="this.form.submit()">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" 
                                {{ $selectedBranch && $selectedBranch->id == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} ({{ $branch->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <div class="text-end">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Click on quantity fields to edit. Changes auto-save after 1 second.
                    </small>
                </div>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="inventory-container">
        <div class="table-header">
            <h5 class="mb-0">
                <i class="fas fa-boxes mr-2"></i>
                {{ $selectedBranch ? $selectedBranch->name . ' Inventory' : 'Branch Inventory' }}
            </h5>
        </div>

        <div class="table-responsive">
            {!! $inventoryTable->renderTable() !!}
        </div>
    </div>
</div>

<!-- Add to Inventory Modal -->
<div class="modal fade" id="addToInventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product to Branch Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addToInventoryForm">
                    <input type="hidden" id="modalProductId" name="product_id">
                    <input type="hidden" id="modalBranchId" name="branch_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Initial Quantity</label>
                        <input type="number" class="form-control" name="quantity_on_hand" min="0" step="1" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cost Price</label>
                        <input type="number" class="form-control" name="cost_price" min="0" step="0.01" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Selling Price</label>
                        <input type="number" class="form-control" name="selling_price" min="0" step="0.01" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Minimum Stock</label>
                        <input type="number" class="form-control" name="minimum_stock" min="0" step="1" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addToInventory()">Add to Inventory</button>
            </div>
        </div>
    </div>
</div>

@push('footer')
<script>
$(document).ready(function() {
    let saveTimeout;

    // Auto-save quantity changes (similar to default ecommerce)
    $(document).on('input', '.branch-inventory-editable', function() {
        clearTimeout(saveTimeout);
        const $field = $(this);
        const inventoryId = $field.data('inventory-id');
        const field = $field.data('field');
        const value = $field.val();

        // Add visual feedback
        $field.removeClass('success error').addClass('saving');

        // Auto-save after 1 second delay
        saveTimeout = setTimeout(function() {
            updateInventoryField(inventoryId, field, value, $field);
        }, 1000);
    });

    // Add to inventory button click
    $(document).on('click', '.add-to-inventory-btn', function() {
        const productId = $(this).data('product-id');
        const branchId = $(this).data('branch-id');
        
        $('#modalProductId').val(productId);
        $('#modalBranchId').val(branchId);
        $('#addToInventoryModal').modal('show');
    });

    function updateInventoryField(inventoryId, field, value, $field) {
        $('#loadingOverlay').show();

        $.ajax({
            url: '{{ route("branch-inventory.update-quantity") }}',
            method: 'POST',
            data: {
                inventory_id: inventoryId,
                field: field,
                value: value,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $field.removeClass('saving error').addClass('success');
                    
                    // Update available quantity if exists
                    const $row = $field.closest('tr');
                    const $availableSpan = $row.find('small:contains("Available")');
                    if ($availableSpan.length && response.data.quantity_available !== undefined) {
                        $availableSpan.text('Available: ' + response.data.quantity_available);
                    }
                    
                    showNotification('✓ Quantity updated successfully', 'success');
                    
                    // Reset styling after 2 seconds
                    setTimeout(function() {
                        $field.removeClass('success');
                    }, 2000);
                } else {
                    $field.removeClass('saving').addClass('error');
                    showNotification(response.message || 'Update failed', 'error');
                }
            },
            error: function(xhr) {
                $field.removeClass('saving').addClass('error');
                const response = xhr.responseJSON;
                showNotification(response?.message || 'Update failed', 'error');
            },
            complete: function() {
                $('#loadingOverlay').hide();
            }
        });
    }

    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const notification = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed notification-fixed">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(notification);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 3000);
    }

    // Global function for add to inventory
    window.addToInventory = function() {
        const formData = $('#addToInventoryForm').serialize();
        
        $.ajax({
            url: '{{ route("branch-inventory.add-product-to-branch") }}',
            method: 'POST',
            data: formData + '&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    $('#addToInventoryModal').modal('hide');
                    showNotification('✓ Product added to inventory successfully', 'success');
                    
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(response.message || 'Failed to add product', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showNotification(response?.message || 'Failed to add product', 'error');
            }
        });
    };
});
</script>
@endpush
@endsection