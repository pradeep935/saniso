@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<!-- Multi-Branch Inventory CSS -->
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/inventory-main.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4 gradient-header">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2">Edit Branch Inventory Settings</h2>
                            <p class="mb-0 opacity-75">{{ $branchInventory->product->name }} - {{ $branchInventory->branch->name }}</p>
                        </div>
                        <div>
                            <a href="{{ route('branch-inventory.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Back to Inventory
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card modern-card">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Inventory Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('branch-inventory.update', $branchInventory) }}">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Minimum Stock Level</label>
                                        <input type="number" 
                                               name="minimum_stock" 
                                               class="form-control modern-form-control" 
                                               value="{{ old('minimum_stock', $branchInventory->minimum_stock) }}"
                                               min="0">
                                        <small class="text-muted">Alert when stock falls below this level</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Maximum Stock Level</label>
                                        <input type="number" 
                                               name="maximum_stock" 
                                               class="form-control modern-form-control" 
                                               value="{{ old('maximum_stock', $branchInventory->maximum_stock) }}"
                                               min="0">
                                        <small class="text-muted">Optional maximum stock limit</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Local Price Override</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   name="local_price" 
                                                   class="form-control" 
                                                   value="{{ old('local_price', $branchInventory->local_price) }}"
                                                   step="0.01"
                                                   min="0">
                                        </div>
                                        <small class="text-muted">Override product price for this branch</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Storage Location</label>
                                        <input type="text" 
                                               name="storage_location" 
                                               class="form-control" 
                                               value="{{ old('storage_location', $branchInventory->storage_location) }}"
                                               class="modern-form-control"
                                               placeholder="e.g., Aisle A, Shelf 3">
                                        <small class="text-muted">Physical location in the branch</small>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Visibility Settings</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="visible_online" 
                                                       id="visible_online"
                                                       class="form-check-input" 
                                                       value="1"
                                                       {{ old('visible_online', $branchInventory->visible_online) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="visible_online">
                                                    <i class="fas fa-globe text-primary me-2"></i>Visible Online
                                                </label>
                                                <small class="d-block text-muted">Show on website</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="visible_in_pos" 
                                                       id="visible_in_pos"
                                                       class="form-check-input" 
                                                       value="1"
                                                       {{ old('visible_in_pos', $branchInventory->visible_in_pos) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="visible_in_pos">
                                                    <i class="fas fa-cash-register text-success me-2"></i>Visible in POS
                                                </label>
                                                <small class="d-block text-muted">Show in point of sale</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="only_visible_in_pos" 
                                                       id="only_visible_in_pos"
                                                       class="form-check-input" 
                                                       value="1"
                                                       {{ old('only_visible_in_pos', $branchInventory->only_visible_in_pos) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="only_visible_in_pos">
                                                    <i class="fas fa-eye-slash text-warning me-2"></i>POS Only
                                                </label>
                                                <small class="d-block text-muted">Hide from website</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Settings
                                    </button>
                                    <a href="{{ route('branch-inventory.index') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Current Stock Info -->
                <div class="col-lg-4">
                    <div class="card modern-card">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Current Stock Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-success mb-1">{{ number_format($branchInventory->quantity_on_hand) }}</h4>
                                        <small class="text-muted">On Hand</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-warning mb-1">{{ number_format($branchInventory->quantity_reserved) }}</h4>
                                        <small class="text-muted">Reserved</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info mb-1">{{ number_format($branchInventory->quantity_available) }}</h4>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">Product SKU</small>
                                <strong>{{ $branchInventory->sku ?: 'N/A' }}</strong>
                            </div>

                            @if($branchInventory->ean)
                            <div class="mb-3">
                                <small class="text-muted d-block">EAN Code</small>
                                <strong>{{ $branchInventory->ean }}</strong>
                            </div>
                            @endif

                            <div class="mb-3">
                                <small class="text-muted d-block">Cost Price</small>
                                <strong>${{ number_format($branchInventory->cost_price ?: 0, 2) }}</strong>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Selling Price</small>
                                <strong>${{ number_format($branchInventory->selling_price ?: $branchInventory->product->price, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Actions -->
                    <div class="card mt-3 modern-card">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Stock Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('branch-inventory.show', $branchInventory) }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-eye me-2"></i>View Details & History
                            </a>
                            <button type="button" class="btn btn-outline-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                                <i class="fas fa-plus-minus me-2"></i>Adjust Stock Quantity
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('branch-inventory.adjust-stock', $branchInventory) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" value="{{ number_format($branchInventory->quantity_on_hand) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select name="adjustment_type" class="form-select" required>
                            <option value="">Select adjustment type</option>
                            <option value="add">Add Stock</option>
                            <option value="subtract">Remove Stock</option>
                            <option value="set">Set Exact Amount</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Reason for adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection