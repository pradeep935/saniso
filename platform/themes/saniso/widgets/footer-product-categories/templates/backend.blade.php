<div class="widget-input-group">
    <label for="widget-title">{{ __('Title') }}</label>
    <input type="text" class="form-control" name="title" value="{{ $config['title'] ?? '' }}" placeholder="{{ __('Footer Categories') }}">
</div>

<div class="widget-input-group">
    <label for="widget-subtitle">{{ __('Subtitle') }}</label>
    <input type="text" class="form-control" name="subtitle" value="{{ $config['subtitle'] ?? '' }}" placeholder="{{ __('Browse our categories') }}">
</div>

<div class="widget-input-group">
    <label for="widget-display_type">{{ __('Display Type') }}</label>
    <select name="display_type" class="form-control" id="widget-display-type">
        <option value="top_sale" {{ ($config['display_type'] ?? 'top_sale') == 'top_sale' ? 'selected' : '' }}>{{ __('Top Sale Categories') }}</option>
        <option value="top_product" {{ ($config['display_type'] ?? 'top_sale') == 'top_product' ? 'selected' : '' }}>{{ __('Top Product Categories') }}</option>
        <option value="new_added" {{ ($config['display_type'] ?? 'top_sale') == 'new_added' ? 'selected' : '' }}>{{ __('New Added Categories') }}</option>
        <option value="custom" {{ ($config['display_type'] ?? 'top_sale') == 'custom' ? 'selected' : '' }}>{{ __('Custom Categories') }}</option>
    </select>
</div>

<div class="widget-input-group" id="custom-categories-section" style="display: {{ ($config['display_type'] ?? 'top_sale') == 'custom' ? 'block' : 'none' }};">
    <label for="widget-category_ids">{{ __('Select Categories') }}</label>
    <select name="category_ids[]" class="form-control" multiple id="widget-category-select">
        @php
            try {
                $allCategories = \Botble\Ecommerce\Models\ProductCategory::where('status', 'published')->orderBy('name')->get();
            } catch (\Exception $e) {
                $allCategories = collect();
            }
        @endphp
        @foreach($allCategories as $category)
            <option value="{{ $category->id }}" {{ in_array($category->id, $config['category_ids'] ?? []) ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">{{ __('Hold Ctrl/Cmd to select multiple categories') }}</small>
</div>

<div class="widget-input-group">
    <label for="widget-limit">{{ __('Limit') }}</label>
    <input type="number" class="form-control" name="limit" value="{{ $config['limit'] ?? 10 }}" min="1" max="50" placeholder="10">
</div>

<div class="widget-input-group">
    <label for="widget-layout">{{ __('Layout') }}</label>
    <select name="layout" class="form-control">
        <option value="carousel" {{ ($config['layout'] ?? 'carousel') == 'carousel' ? 'selected' : '' }}>{{ __('Carousel') }}</option>
        <option value="grid" {{ ($config['layout'] ?? 'carousel') == 'grid' ? 'selected' : '' }}>{{ __('Grid') }}</option>
    </select>
</div>

<div class="widget-input-group">
    <label for="widget-show_on_mobile_only">{{ __('Show on mobile only') }}</label>
    <select name="show_on_mobile_only" class="form-control">
        <option value="no" {{ ($config['show_on_mobile_only'] ?? 'no') == 'no' ? 'selected' : '' }}>{{ __('No') }}</option>
        <option value="yes" {{ ($config['show_on_mobile_only'] ?? 'no') == 'yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
    </select>
</div>

<hr>
<h6>{{ __('Carousel Settings') }}</h6>

<div class="widget-input-group">
    <label for="widget-is_autoplay">{{ __('Is autoplay?') }}</label>
    <select name="is_autoplay" class="form-control">
        <option value="yes" {{ ($config['is_autoplay'] ?? 'yes') == 'yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
        <option value="no" {{ ($config['is_autoplay'] ?? 'yes') == 'no' ? 'selected' : '' }}>{{ __('No') }}</option>
    </select>
</div>

<div class="widget-input-group">
    <label for="widget-is_infinite">{{ __('Loop?') }}</label>
    <select name="is_infinite" class="form-control">
        <option value="yes" {{ ($config['is_infinite'] ?? 'yes') == 'yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
        <option value="no" {{ ($config['is_infinite'] ?? 'yes') == 'no' ? 'selected' : '' }}>{{ __('No') }}</option>
    </select>
</div>

<div class="widget-input-group">
    <label for="widget-autoplay_speed">{{ __('Autoplay speed') }}</label>
    <select name="autoplay_speed" class="form-control">
        <option value="1000" {{ ($config['autoplay_speed'] ?? 3000) == 1000 ? 'selected' : '' }}>1s</option>
        <option value="2000" {{ ($config['autoplay_speed'] ?? 3000) == 2000 ? 'selected' : '' }}>2s</option>
        <option value="3000" {{ ($config['autoplay_speed'] ?? 3000) == 3000 ? 'selected' : '' }}>3s</option>
        <option value="4000" {{ ($config['autoplay_speed'] ?? 3000) == 4000 ? 'selected' : '' }}>4s</option>
        <option value="5000" {{ ($config['autoplay_speed'] ?? 3000) == 5000 ? 'selected' : '' }}>5s</option>
    </select>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="widget-input-group">
            <label for="widget-slides_to_show">{{ __('Slides (Desktop)') }}</label>
            <select name="slides_to_show" class="form-control">
                @for($i = 2; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ ($config['slides_to_show'] ?? 4) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-input-group">
            <label for="widget-slides_to_show_tablet">{{ __('Slides (Tablet)') }}</label>
            <select name="slides_to_show_tablet" class="form-control">
                @for($i = 1; $i <= 6; $i++)
                    <option value="{{ $i }}" {{ ($config['slides_to_show_tablet'] ?? 3) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-input-group">
            <label for="widget-slides_to_show_mobile">{{ __('Slides (Mobile)') }}</label>
            <select name="slides_to_show_mobile" class="form-control">
                @for($i = 1; $i <= 4; $i++)
                    <option value="{{ $i }}" {{ ($config['slides_to_show_mobile'] ?? 2) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
</div>

<hr>
<h6>{{ __('Grid Settings') }}</h6>

<div class="row">
    <div class="col-md-6">
        <div class="widget-input-group">
            <label for="widget-columns_xl">{{ __('Columns (XL)') }}</label>
            <select name="columns_xl" class="form-control">
                @for($i = 3; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ ($config['columns_xl'] ?? 8) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="widget-input-group">
            <label for="widget-columns_lg">{{ __('Columns (LG)') }}</label>
            <select name="columns_lg" class="form-control">
                @for($i = 3; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ ($config['columns_lg'] ?? 6) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="widget-input-group">
            <label for="widget-columns_md">{{ __('Columns (MD)') }}</label>
            <select name="columns_md" class="form-control">
                @for($i = 2; $i <= 6; $i++)
                    <option value="{{ $i }}" {{ ($config['columns_md'] ?? 4) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="widget-input-group">
            <label for="widget-columns_sm">{{ __('Columns (SM)') }}</label>
            <select name="columns_sm" class="form-control">
                @for($i = 1; $i <= 4; $i++)
                    <option value="{{ $i }}" {{ ($config['columns_sm'] ?? 3) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="widget-input-group">
            <label for="widget-columns_xs">{{ __('Columns (XS)') }}</label>
            <select name="columns_xs" class="form-control">
                @for($i = 1; $i <= 3; $i++)
                    <option value="{{ $i }}" {{ ($config['columns_xs'] ?? 2) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="widget-input-group">
            <label for="widget-columns_xxs">{{ __('Columns (Mobile)') }}</label>
            <select name="columns_xxs" class="form-control">
                <option value="1" {{ ($config['columns_xxs'] ?? 1) == 1 ? 'selected' : '' }}>1</option>
                <option value="2" {{ ($config['columns_xxs'] ?? 1) == 2 ? 'selected' : '' }}>2</option>
            </select>
        </div>
    </div>
</div>

<hr>
<h6>{{ __('Additional Options') }}</h6>

<div class="widget-input-group">
    <label for="widget-show_product_count">{{ __('Show product count') }}</label>
    <select name="show_product_count" class="form-control">
        <option value="no" {{ ($config['show_product_count'] ?? 'no') == 'no' ? 'selected' : '' }}>{{ __('No') }}</option>
        <option value="yes" {{ ($config['show_product_count'] ?? 'no') == 'yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
    </select>
</div>

<style>
.widget-input-group {
    margin-bottom: 15px;
}

.widget-input-group label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

.widget-input-group hr {
    margin: 20px 0;
}

.widget-input-group h6 {
    color: #666;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 15px;
}

#widget-category-select {
    min-height: 120px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const displayTypeSelect = document.getElementById('widget-display-type');
    const customCategoriesSection = document.getElementById('custom-categories-section');
    
    if (displayTypeSelect && customCategoriesSection) {
        displayTypeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customCategoriesSection.style.display = 'block';
            } else {
                customCategoriesSection.style.display = 'none';
            }
        });
    }
});
</script>
