{{-- Branch Selection for Product Form - Only shows when storehouse management is enabled --}}
<div class="multi-branch-inventory-section multi-branch-inventory-section-hidden">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label class="control-label">
                    <strong>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.select_branches') }}</strong>
                </label>
                
                <!-- Select All Option -->
                <div class="mb-3">
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="select_all_branches"
                            onclick="toggleAllBranches(this)"
                        >
                        <label class="form-check-label fw-bold" for="select_all_branches">
                            <i class="fas fa-check-double"></i> Select All Branches
                        </label>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Total quantity will be distributed across selected branches
                    </small>
                </div>
            
            <div class="form-group">
                <div class="row">
                    @foreach($branches as $branch)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <div class="form-check mb-2">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input branch-checkbox" 
                                            id="branch_{{ $branch->id }}" 
                                            name="branch_ids[]" 
                                            value="{{ $branch->id }}"
                                            @if(in_array($branch->id, $selectedBranches)) checked @endif
                                            onchange="updateSelectAll()"
                                        >
                                        <label class="form-check-label fw-medium" for="branch_{{ $branch->id }}">
                                            <i class="fas fa-building text-primary"></i> {{ $branch->name }}
                                            @if($branch->is_main_branch)
                                                <span class="badge bg-primary ms-1">Main</span>
                                            @endif
                                        </label>
                                    </div>
                                    
                                    <!-- Branch-specific quantity input -->
                                    <div class="branch-quantity-section" id="quantity_section_{{ $branch->id }}" 
                                     class=\"{{ in_array($branch->id, $selectedBranches) ? 'branch-quantity-section-visible' : 'branch-quantity-section-hidden' }}\">
                                        <div class="row g-2">
                                            <div class="col-8">
                                                <label class="form-label small">Branch Quantity</label>
                                                <input 
                                                    type="number" 
                                                    class="form-control form-control-sm branch-quantity-input" 
                                                    name="branch_quantities[{{ $branch->id }}]" 
                                                    placeholder="Auto from total"
                                                    min="0"
                                                    step="1"
                                                    value="{{ $selectedBranches && isset($branchQuantities[$branch->id]) ? $branchQuantities[$branch->id] : '' }}"
                                                    onchange="updateTotalQuantity()"
                                                >
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">Min Stock</label>
                                                <input 
                                                    type="number" 
                                                    class="form-control form-control-sm" 
                                                    name="branch_min_quantities[{{ $branch->id }}]" 
                                                    placeholder="0"
                                                    min="0"
                                                    step="1"
                                                    value="{{ $selectedBranches && isset($branchMinQuantities[$branch->id]) ? $branchMinQuantities[$branch->id] : '' }}"
                                                >
                                            </div>
                                        </div>
                                        <small class="text-muted">Leave quantity blank to auto-distribute from total quantity</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.select_branches_help') }}
                </small>
            </div>
        </div>
    </div>
</div>
</div>



<script>
function toggleAllBranches(selectAllCheckbox) {
    const branchCheckboxes = document.querySelectorAll('.branch-checkbox');
    const isChecked = selectAllCheckbox.checked;
    
    branchCheckboxes.forEach(function(checkbox) {
        checkbox.checked = isChecked;
        toggleQuantitySection(checkbox);
    });
    
    if (isChecked) {
        distributeQuantityToBranches();
    }
}

function updateSelectAll() {
    const branchCheckboxes = document.querySelectorAll('.branch-checkbox');
    const selectAllCheckbox = document.getElementById('select_all_branches');
    
    if (!selectAllCheckbox) return;
    
    const totalCheckboxes = branchCheckboxes.length;
    const checkedCheckboxes = document.querySelectorAll('.branch-checkbox:checked').length;
    
    selectAllCheckbox.checked = (checkedCheckboxes === totalCheckboxes);
    selectAllCheckbox.indeterminate = (checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
}

function toggleQuantitySection(checkbox) {
    const branchId = checkbox.value;
    const quantitySection = document.getElementById('quantity_section_' + branchId);
    
    if (checkbox.checked) {
        quantitySection.classList.remove('branch-quantity-section-hidden');
        quantitySection.classList.add('branch-quantity-section-visible');
        distributeQuantityToBranches();
    } else {
        quantitySection.classList.remove('branch-quantity-section-visible');
        quantitySection.classList.add('branch-quantity-section-hidden');
        // Clear quantity values when branch is deselected
        const inputs = quantitySection.querySelectorAll('input[type="number"]');
        inputs.forEach(input => input.value = '');
        updateTotalQuantity();
    }
}

function distributeQuantityToBranches() {
    const totalQuantityInput = document.querySelector('#quantity, input[name="quantity"]');
    const checkedBranches = document.querySelectorAll('.branch-checkbox:checked');
    
    if (!totalQuantityInput || checkedBranches.length === 0) return;
    
    const totalQuantity = parseInt(totalQuantityInput.value) || 0;
    const quantityPerBranch = Math.floor(totalQuantity / checkedBranches.length);
    const remainder = totalQuantity % checkedBranches.length;
    
    checkedBranches.forEach(function(checkbox, index) {
        const branchId = checkbox.value;
        const quantityInput = document.querySelector(`input[name="branch_quantities[${branchId}]"]`);
        if (quantityInput && quantityInput.value === '') {
            // Add remainder to first branch
            const quantity = quantityPerBranch + (index === 0 ? remainder : 0);
            quantityInput.value = quantity;
        }
    });
}

function updateTotalQuantity() {
    const branchQuantityInputs = document.querySelectorAll('.branch-quantity-input');
    const totalQuantityInput = document.querySelector('#quantity, input[name="quantity"]');
    
    if (!totalQuantityInput) return;
    
    let total = 0;
    branchQuantityInputs.forEach(function(input) {
        const branchCheckbox = document.querySelector(`input[value="${input.name.match(/\d+/)[0]}"]`);
        if (branchCheckbox && branchCheckbox.checked && input.value) {
            total += parseInt(input.value) || 0;
        }
    });
    
    totalQuantityInput.value = total;
}

function toggleBranchInventorySection() {
    const storehouseCheckbox = document.querySelector('.storehouse-management-status');
    const branchSection = document.querySelector('.multi-branch-inventory-section');
    
    if (!storehouseCheckbox || !branchSection) return;
    
    if (storehouseCheckbox.checked) {
        branchSection.classList.remove('multi-branch-inventory-section-hidden');
        distributeQuantityToBranches();
    } else {
        branchSection.classList.add('multi-branch-inventory-section-hidden');
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    const branchCheckboxes = document.querySelectorAll('.branch-checkbox');
    const storehouseCheckbox = document.querySelector('.storehouse-management-status');
    const totalQuantityInput = document.querySelector('#quantity, input[name="quantity"]');
    
    // Initialize branch inventory section visibility
    toggleBranchInventorySection();
    
    // Listen for storehouse management changes
    if (storehouseCheckbox) {
        storehouseCheckbox.addEventListener('change', toggleBranchInventorySection);
    }
    
    // Listen for total quantity changes
    if (totalQuantityInput) {
        totalQuantityInput.addEventListener('input', function() {
            distributeQuantityToBranches();
        });
    }
    
    // Add listeners to branch checkboxes
    branchCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            toggleQuantitySection(this);
            updateSelectAll();
        });
    });
    
    // Add listeners to branch quantity inputs
    document.querySelectorAll('.branch-quantity-input').forEach(function(input) {
        input.addEventListener('input', updateTotalQuantity);
    });
    
    // Initialize the select all state
    updateSelectAll();
});
</script>

