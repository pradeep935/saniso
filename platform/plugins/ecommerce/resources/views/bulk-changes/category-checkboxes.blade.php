@php
    $categories = $data['categories'] ?? [];
    $selected = $data['selected'] ?? [];
@endphp

<div class="category-checkbox-wrapper" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
    <div class="form-check mb-2">
        <input type="checkbox" class="form-check-input" id="select-all-categories" style="margin-right: 8px;">
        <label class="form-check-label fw-bold" for="select-all-categories">
            Select All Categories
        </label>
    </div>
    <hr style="margin: 10px 0;">
    
    @if(empty($categories))
        <p class="text-muted">No categories found</p>
    @else
        @foreach($categories as $category)
            @include('plugins/ecommerce::bulk-changes.category-checkbox-item', [
                'category' => $category, 
                'selected' => $selected,
                'level' => 0
            ])
        @endforeach
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-categories');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    
    // Select/Deselect All functionality
    selectAllCheckbox.addEventListener('change', function() {
        categoryCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Update Select All checkbox based on individual selections
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(categoryCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(categoryCheckboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        });
    });
    
    // Initial state check
    const allChecked = Array.from(categoryCheckboxes).every(cb => cb.checked);
    const someChecked = Array.from(categoryCheckboxes).some(cb => cb.checked);
    selectAllCheckbox.checked = allChecked;
    selectAllCheckbox.indeterminate = someChecked && !allChecked;
});
</script>

<style>
.category-checkbox-wrapper .form-check {
    margin-bottom: 8px;
}

.category-checkbox-wrapper .category-level-1 {
    margin-left: 20px;
}

.category-checkbox-wrapper .category-level-2 {
    margin-left: 40px;
}

.category-checkbox-wrapper .category-level-3 {
    margin-left: 60px;
}

.category-checkbox-wrapper .form-check-input {
    margin-right: 8px;
}
</style>