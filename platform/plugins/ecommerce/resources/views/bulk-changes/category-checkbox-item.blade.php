@php
    $isSelected = in_array($category['id'], $selected);
    $levelClass = 'category-level-' . ($level > 3 ? 3 : $level);
@endphp

<div class="form-check {{ $levelClass }}">
    <input 
        type="checkbox" 
        class="form-check-input category-checkbox" 
        name="category_checkboxes[]" 
        value="{{ $category['id'] }}"
        id="category-{{ $category['id'] }}"
        {{ $isSelected ? 'checked' : '' }}
        style="margin-right: 8px;">
    <label class="form-check-label" for="category-{{ $category['id'] }}">
        {{ $category['name'] }}
    </label>
</div>

@if(!empty($category['children']))
    @foreach($category['children'] as $child)
        @include('plugins/ecommerce::bulk-changes.category-checkbox-item', [
            'category' => $child, 
            'selected' => $selected,
            'level' => $level + 1
        ])
    @endforeach
@endif