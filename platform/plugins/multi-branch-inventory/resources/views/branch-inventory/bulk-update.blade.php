@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<!-- Multi-Branch Inventory Bulk Operations CSS -->
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/bulk-operations.css') }}">
@endpush

@section('content')
<div class="modern-bulk-interface">
    <!-- Enhanced Header -->
    <div class="gradient-header text-white">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="mb-2">
                    <i class="fas fa-layer-group me-3"></i>{{ trans('plugins/multi-branch-inventory::branch-inventory.bulk_update_title') }}
                </h1>
                <p class="mb-0 text-white-75">Manage inventory across multiple branches efficiently</p>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('branch-inventory.index') }}" class="btn btn-light modern-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                </a>
                <a href="{{ route('branches.index') }}" class="btn btn-outline-light modern-btn">
                    <i class="fas fa-building me-2"></i>Manage Branches
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="modern-card">
                <div class="card-header bg-white border-0 card-padding">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-filter me-2 text-primary"></i>Branch & Product Selection
                    </h5>
                </div>
                <div class="card-body card-padding">
                <!-- Modern Branch Selection -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-4">
                        <label class="form-label fw-bold text-dark mb-3">
                            <i class="fas fa-building me-2 text-primary"></i>{{ trans('plugins/multi-branch-inventory::branch.select_branch') }}
                        </label>
                        <select name="branch_id" id="branch-selector" class="form-select modern-form-control">
                            <option value="">{{ trans('plugins/multi-branch-inventory::branch-inventory.all_branches') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-8 d-flex align-items-end gap-3">
                        <button type="button" id="load-products" class="btn btn-primary modern-btn">
                            <i class="fas fa-sync-alt me-2"></i> {{ trans('plugins/multi-branch-inventory::branch-inventory.load_products') }}
                        </button>
                        <button type="button" id="manage-all-branches" class="btn btn-info modern-btn">
                            <i class="fas fa-sitemap me-2"></i> {{ trans('plugins/multi-branch-inventory::branch-inventory.manage_all_branches') }}
                        </button>
                        <div class="ms-auto">
                            <div class="d-flex align-items-center gap-2 text-muted">
                                <i class="fas fa-info-circle"></i>
                                <small>Select a branch to manage specific inventory or choose "All Branches" for overview</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Structure Display -->
                <div class="navigation-breadcrumb mb-4 p-3 bg-light rounded">
                    <h6 class="mb-2">{{ trans('plugins/multi-branch-inventory::branch-inventory.navigation_structure') }}:</h6>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('branches.index') }}">{{ trans('plugins/multi-branch-inventory::branch.menu_name') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('products.index') }}">{{ trans('plugins/ecommerce::products.name') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ trans('plugins/multi-branch-inventory::branch-inventory.inventory_management') }}</li>
                        </ol>
                    </nav>
                    <small class="text-muted">
                        {{ trans('plugins/multi-branch-inventory::branch-inventory.structure_help') }}
                    </small>
                </div>

                @if($selectedBranchId)
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        {{ trans('plugins/multi-branch-inventory::branch-inventory.managing_branch') }}: 
                        <strong>{{ $branches->find($selectedBranchId)->name ?? 'Unknown Branch' }}</strong>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fa fa-sitemap"></i>
                        {{ trans('plugins/multi-branch-inventory::branch-inventory.managing_all_branches_note') }}
                    </div>
                @endif

                <!-- Bulk Update Form -->
                <form id="bulk-update-form" method="POST" action="{{ route('branch-inventory.bulk-update.process') }}">
                    @csrf
                    <input type="hidden" name="selected_branch_id" value="{{ $selectedBranchId }}">
                    
                    <div class="table-responsive">
                        <table class="table table-striped" id="products-table">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th width="10%">{{ trans('plugins/ecommerce::products.form.image') }}</th>
                                    <th width="15%">{{ trans('plugins/ecommerce::products.form.name') }}</th>
                                    <th width="10%">{{ trans('plugins/ecommerce::products.form.sku') }}</th>
                                    <th width="10%">{{ trans('plugins/multi-branch-inventory::branch-inventory.ecommerce_quantity') }}</th>
                                    @if($selectedBranchId)
                                        <th width="12%">{{ trans('plugins/multi-branch-inventory::branch-inventory.branch_stock') }}</th>
                                        <th width="12%">{{ trans('plugins/multi-branch-inventory::branch-inventory.new_quantity') }}</th>
                                        <th width="10%">{{ trans('plugins/ecommerce::products.form.cost_price') }}</th>
                                        <th width="10%">{{ trans('plugins/ecommerce::products.form.price') }}</th>
                                    @else
                                        <th width="15%">{{ trans('plugins/multi-branch-inventory::branch-inventory.total_branch_stock') }}</th>
                                    @endif
                                    <th width="6%">{{ trans('plugins/multi-branch-inventory::branch-inventory.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php
                                        $branchInventory = $product->branchInventories->first();
                                        $totalBranchStock = $product->branchInventories->sum('quantity_on_hand');
                                    @endphp
                                    <tr data-product-id="{{ $product->id }}">
                                        <td>
                                            <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
                                        </td>
                                        <td>
                                            @if($product->image)
                                                <img src="{{ RvMedia::getImageUrl($product->image, 'thumb') }}" alt="{{ $product->name }}" class="img-fluid product-image">
                                            @else
                                                <div class="no-image">
                                                    <i class="fa fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">${{ number_format($product->price, 2) }}</small>
                                        </td>
                                        <td>{{ $product->sku }}</td>
                                        <td>
                                            <span class="badge badge-primary">{{ $product->quantity }}</span>
                                        </td>
                                        @if($selectedBranchId)
                                            <td>
                                                <span class="badge {{ $branchInventory && $branchInventory->quantity_on_hand > 0 ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $branchInventory ? $branchInventory->quantity_on_hand : 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                <input type="hidden" name="updates[{{ $product->id }}][branch_id]" value="{{ $selectedBranchId }}">
                                                <input type="hidden" name="updates[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                                <input type="number" 
                                                       name="updates[{{ $product->id }}][quantity_on_hand]" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       value="{{ $branchInventory ? $branchInventory->quantity_on_hand : 0 }}" 
                                                       min="0" 
                                                       placeholder="0">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       name="updates[{{ $product->id }}][cost_price]" 
                                                       class="form-control form-control-sm" 
                                                       value="{{ $branchInventory ? $branchInventory->cost_price : 0 }}" 
                                                       min="0" 
                                                       step="0.01" 
                                                       placeholder="0.00">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       name="updates[{{ $product->id }}][selling_price]" 
                                                       class="form-control form-control-sm" 
                                                       value="{{ $branchInventory ? $branchInventory->selling_price : $product->price }}" 
                                                       min="0" 
                                                       step="0.01" 
                                                       placeholder="0.00">
                                            </td>
                                        @else
                                            <td>
                                                <span class="badge badge-info">{{ $totalBranchStock }}</span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ trans('plugins/multi-branch-inventory::branch-inventory.across_branches', ['count' => $product->branchInventories->count()]) }}
                                                </small>
                                            </td>
                                        @endif
                                        <td>
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary" title="{{ trans('core/base::tables.edit') }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $selectedBranchId ? '9' : '6' }}" class="text-center">
                                            {{ trans('plugins/multi-branch-inventory::branch-inventory.no_products_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($selectedBranchId && $products->count() > 0)
                        <div class="form-actions text-right mt-3">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                {{ trans('core/base::forms.reset') }}
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> {{ trans('plugins/multi-branch-inventory::branch-inventory.update_selected') }}
                            </button>
                        </div>
                    @endif
                </form>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-3">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    
    // Branch selector change
    $('#branch-selector').change(function() {
        var branchId = $(this).val();
        var url = '{{ route("branch-inventory.bulk-update") }}';
        if (branchId) {
            url += '?branch_id=' + branchId;
        }
        window.location.href = url;
    });
    
    // Load products button
    $('#load-products').click(function() {
        var branchId = $('#branch-selector').val();
        var url = '{{ route("branch-inventory.bulk-update") }}';
        if (branchId) {
            url += '?branch_id=' + branchId;
        }
        window.location.href = url;
    });
    
    // Manage all branches button
    $('#manage-all-branches').click(function() {
        window.location.href = '{{ route("branch-inventory.bulk-update") }}';
    });
    
    // Select all checkbox
    $('#select-all').change(function() {
        $('.product-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Individual checkbox change
    $('.product-checkbox').change(function() {
        var totalCheckboxes = $('.product-checkbox').length;
        var checkedCheckboxes = $('.product-checkbox:checked').length;
        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Form validation before submit
    $('#bulk-update-form').submit(function(e) {
        var hasChecked = $('.product-checkbox:checked').length > 0;
        var hasChanges = false;
        
        $('.quantity-input').each(function() {
            if ($(this).val() != $(this).attr('data-original-value')) {
                hasChanges = true;
            }
        });
        
        if (!hasChecked) {
            e.preventDefault();
            alert('{{ trans("plugins/multi-branch-inventory::branch-inventory.select_products_first") }}');
            return false;
        }
        
        // Remove unchecked products from form submission
        $('.product-checkbox:not(:checked)').each(function() {
            var productId = $(this).val();
            $('input[name*="[' + productId + ']"]').remove();
        });
        
        // Confirm submission
        if (!confirm('{{ trans("plugins/multi-branch-inventory::branch-inventory.confirm_bulk_update") }}')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Store original values for change detection
    $('.quantity-input').each(function() {
        $(this).attr('data-original-value', $(this).val());
    });
});

function resetForm() {
    $('.quantity-input').each(function() {
        $(this).val($(this).attr('data-original-value'));
    });
    $('.product-checkbox').prop('checked', false);
    $('#select-all').prop('checked', false);
}
</script>
@endsection