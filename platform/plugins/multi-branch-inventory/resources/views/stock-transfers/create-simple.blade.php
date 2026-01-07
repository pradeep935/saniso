@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary me-3">
                ← Back to Stock Transfers
            </a>
            <div>
                <h1 class="page-title mb-1">➕ Create Stock Transfer</h1>
                <p class="text-muted mb-0">Transfer inventory between branches</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transfer Information</h3>
        </div>
        <form method="POST" action="{{ route('stock-transfers.store') }}">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="from_branch_id" class="form-label">From Branch *</label>
                            <select class="form-select @error('from_branch_id') is-invalid @enderror" 
                                    id="from_branch_id" name="from_branch_id" required>
                                <option value="">Select source branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('from_branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="to_branch_id" class="form-label">To Branch *</label>
                            <select class="form-select @error('to_branch_id') is-invalid @enderror" 
                                    id="to_branch_id" name="to_branch_id" required>
                                <option value="">Select destination branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('to_branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                   id="reference_number" name="reference_number" value="{{ old('reference_number') }}"
                                   placeholder="Optional reference number">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="transfer_date" class="form-label">Transfer Date *</label>
                            <input type="date" class="form-control @error('transfer_date') is-invalid @enderror" 
                                   id="transfer_date" name="transfer_date" 
                                   value="{{ old('transfer_date', now()->format('Y-m-d')) }}" required>
                            @error('transfer_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3" 
                              placeholder="Optional notes about this transfer">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Transfer Items Section -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Transfer Items</h5>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">
                            ➕ Add Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="items-table">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Product</th>
                                    <th width="15%">SKU</th>
                                    <th width="15%">EAN</th>
                                    <th width="10%">Available</th>
                                    <th width="10%">Quantity</th>
                                    <th width="5%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="transfer-items">
                                <tr id="no-items-row">
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Click "Add Item" to select products for transfer
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        ✅ Create Transfer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="product-search" 
                           placeholder="Search by name, SKU, or EAN...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>EAN</th>
                                <th>Available</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="product-list">
                            <!-- Products will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript for product selection -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addItemBtn = document.getElementById('add-item');
    const itemsContainer = document.getElementById('transfer-items');
    const noItemsRow = document.getElementById('no-items-row');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const productSearch = document.getElementById('product-search');
    const productList = document.getElementById('product-list');
    const fromBranchSelect = document.getElementById('from_branch_id');
    
    let itemCount = 0;
    let selectedItems = new Set(); // Track selected product IDs

    addItemBtn.addEventListener('click', function() {
        if (!fromBranchSelect.value) {
            alert('Please select a source branch first');
            return;
        }
        loadProducts();
        productModal.show();
    });

    function loadProducts(searchTerm = '') {
        if (!fromBranchSelect.value) {
            productList.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Please select a source branch first</td></tr>';
            return;
        }

        // Show loading state
        productList.innerHTML = '<tr><td colspan="5" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading products...</td></tr>';

        // Make API call to get branch-specific products
        const apiUrl = `{{ route('stock-transfers.get-products') }}?from_branch_id=${fromBranchSelect.value}&search=${encodeURIComponent(searchTerm)}`;
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(products => {
                if (products.error) {
                    throw new Error(products.error);
                }
                
                // Filter out already selected products
                const filteredProducts = products.filter(product => !selectedItems.has(product.id));

                productList.innerHTML = filteredProducts.map(product => `
                    <tr>
                        <td>
                            <strong>${product.name}</strong>
                            <br><small class="text-muted">ID: ${product.id}</small>
                        </td>
                        <td><code>${product.sku || 'N/A'}</code></td>
                        <td><code>${product.ean || 'N/A'}</code></td>
                        <td>
                            <span class="badge bg-${product.available > 10 ? 'success' : product.available > 5 ? 'warning' : 'danger'}">
                                ${product.available} units
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary select-product" 
                                    data-product='${JSON.stringify(product)}'>
                                Select
                            </button>
                        </td>
                    </tr>
                `).join('');

                if (filteredProducts.length === 0) {
                    productList.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                ${searchTerm ? 'No products found matching your search in this branch' : 'No products available in this branch or all products already selected'}
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
                productList.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger py-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading products: ${error.message}
                        </td>
                    </tr>
                `;
            });
    }

    // Product search functionality
    productSearch.addEventListener('input', function() {
        loadProducts(this.value);
    });

    // Select product functionality
    productList.addEventListener('click', function(e) {
        if (e.target.classList.contains('select-product')) {
            const product = JSON.parse(e.target.getAttribute('data-product'));
            addProductToTransfer(product);
            productModal.hide();
            productSearch.value = '';
        }
    });

    function addProductToTransfer(product) {
        itemCount++;
        selectedItems.add(product.id);

        const itemHtml = `
            <tr id="item-${itemCount}">
                <td class="text-center">${itemCount}</td>
                <td>
                    <strong>${product.name}</strong>
                    <br><small class="text-muted">ID: ${product.id}</small>
                    <input type="hidden" name="items[${itemCount}][product_id]" value="${product.id}">
                    <input type="hidden" name="items[${itemCount}][product_name]" value="${product.name}">
                </td>
                <td><code>${product.sku}</code></td>
                <td><code>${product.ean}</code></td>
                <td>
                    <span class="badge bg-${product.available > 10 ? 'success' : product.available > 5 ? 'warning' : 'danger'}">
                        ${product.available}
                    </span>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           name="items[${itemCount}][quantity]" 
                           min="1" max="${product.available}" 
                           value="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-item" 
                            data-item="item-${itemCount}" data-product-id="${product.id}">
                        🗑️
                    </button>
                </td>
            </tr>
        `;

        if (noItemsRow) {
            noItemsRow.remove();
        }
        
        itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
    }

    // Remove item functionality
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const itemId = e.target.getAttribute('data-item');
            const productId = parseInt(e.target.getAttribute('data-product-id'));
            const itemElement = document.getElementById(itemId);
            
            if (itemElement) {
                itemElement.remove();
                selectedItems.delete(productId);
                
                // Show no items message if all items removed
                if (selectedItems.size === 0) {
                    itemsContainer.innerHTML = `
                        <tr id="no-items-row">
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Click "Add Item" to select products for transfer
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }
        }
    });

    // Prevent same branch selection
    const toBranch = document.getElementById('to_branch_id');

    function validateBranches() {
        if (fromBranchSelect.value && toBranch.value && fromBranchSelect.value === toBranch.value) {
            alert('Source and destination branches cannot be the same!');
            toBranch.value = '';
        }
    }

    fromBranchSelect.addEventListener('change', function() {
        validateBranches();
        // Clear selected items when changing source branch
        selectedItems.clear();
        itemsContainer.innerHTML = `
            <tr id="no-items-row">
                <td colspan="7" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Click "Add Item" to select products for transfer
                    </div>
                </td>
            </tr>
        `;
    });
    
    toBranch.addEventListener('change', validateBranches);
});
</script>
@endsection