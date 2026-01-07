<div class="mb-3">
    <label class="form-label">{{ __('Title') }}</label>
    <input
        class="form-control"
        name="title"
        type="text"
        value="{{ Arr::get($attributes, 'title') }}"
        placeholder="{{ __('Title') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Subtitle') }}</label>
    <input
        class="form-control"
        name="subtitle"
        type="text"
        value="{{ Arr::get($attributes, 'subtitle') }}"
        placeholder="{{ __('Subtitle') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Limit') }}</label>
    <input
        class="form-control"
        name="limit"
        type="number"
        value="{{ Arr::get($attributes, 'limit', 12) }}"
        placeholder="{{ __('Number of categories to display') }}"
        min="1"
        max="50"
    >
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">{{ __('Columns on Extra Large Devices') }} <small class="text-muted">(1700px+)</small></label>
            <select class="form-select" name="columns_xl">
                @foreach([3, 4, 5, 6, 7, 8, 9, 10, 12] as $col)
                    <option value="{{ $col }}" @if(Arr::get($attributes, 'columns_xl', 8) == $col) selected @endif>{{ $col }} {{ __('columns') }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">{{ __('Columns on Large Devices') }} <small class="text-muted">(1200-1699px)</small></label>
            <select class="form-select" name="columns_lg">
                @foreach([3, 4, 5, 6, 7, 8] as $col)
                    <option value="{{ $col }}" @if(Arr::get($attributes, 'columns_lg', 6) == $col) selected @endif>{{ $col }} {{ __('columns') }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">{{ __('Columns on Medium Devices') }} <small class="text-muted">(992-1199px)</small></label>
            <select class="form-select" name="columns_md">
                @foreach([2, 3, 4, 5, 6] as $col)
                    <option value="{{ $col }}" @if(Arr::get($attributes, 'columns_md', 4) == $col) selected @endif>{{ $col }} {{ __('columns') }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">{{ __('Columns on Small Devices') }} <small class="text-muted">(768-991px)</small></label>
            <select class="form-select" name="columns_sm">
                @foreach([1, 2, 3, 4] as $col)
                    <option value="{{ $col }}" @if(Arr::get($attributes, 'columns_sm', 3) == $col) selected @endif>{{ $col }} {{ __('columns') }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">{{ __('Columns on Extra Small Devices') }} <small class="text-muted">(576-767px)</small></label>
            <select class="form-select" name="columns_xs">
                @foreach([1, 2, 3] as $col)
                    <option value="{{ $col }}" @if(Arr::get($attributes, 'columns_xs', 2) == $col) selected @endif>{{ $col }} {{ __('columns') }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">{{ __('Columns on Mobile') }} <small class="text-muted">(<576px)</small></label>
            <select class="form-select" name="columns_xxs">
                @foreach([1, 2] as $col)
                    <option value="{{ $col }}" @if(Arr::get($attributes, 'columns_xxs', 1) == $col) selected @endif>{{ $col }} {{ __('columns') }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<hr class="my-4">

<div class="mb-3">
    <label class="form-label">{{ __('Show product count?') }}</label>
    <select class="form-select" name="show_product_count">
        <option value="no" @if(Arr::get($attributes, 'show_product_count', 'no') == 'no') selected @endif>{{ trans('core/base::base.no') }}</option>
        <option value="yes" @if(Arr::get($attributes, 'show_product_count', 'no') == 'yes') selected @endif>{{ trans('core/base::base.yes') }}</option>
    </select>
    <small class="form-text text-muted">{{ __('Display the number of products in each category') }}</small>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show view all button?') }}</label>
    <select class="form-select" name="show_view_all">
        <option value="no" @if(Arr::get($attributes, 'show_view_all', 'no') == 'no') selected @endif>{{ trans('core/base::base.no') }}</option>
        <option value="yes" @if(Arr::get($attributes, 'show_view_all', 'no') == 'yes') selected @endif>{{ trans('core/base::base.yes') }}</option>
    </select>
</div>

<div class="mb-3" id="view-all-text-field" style="display: {{ Arr::get($attributes, 'show_view_all', 'no') == 'yes' ? 'block' : 'none' }};">
    <label class="form-label">{{ __('View all button text') }}</label>
    <input
        class="form-control"
        name="view_all_text"
        type="text"
        value="{{ Arr::get($attributes, 'view_all_text') }}"
        placeholder="{{ __('View All Categories') }}"
    >
</div>

<div class="mb-3" id="view-all-url-field" style="display: {{ Arr::get($attributes, 'show_view_all', 'no') == 'yes' ? 'block' : 'none' }};">
    <label class="form-label">{{ __('View all button URL') }}</label>
    <input
        class="form-control"
        name="view_all_url"
        type="url"
        value="{{ Arr::get($attributes, 'view_all_url') }}"
        placeholder="{{ __('https://example.com/categories') }}"
    >
    <small class="form-text text-muted">{{ __('URL where the view all button should link to') }}</small>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showViewAllSelect = document.querySelector('select[name="show_view_all"]');
    const viewAllTextField = document.getElementById('view-all-text-field');
    const viewAllUrlField = document.getElementById('view-all-url-field');
    
    if (showViewAllSelect) {
        showViewAllSelect.addEventListener('change', function() {
            const isVisible = this.value === 'yes';
            viewAllTextField.style.display = isVisible ? 'block' : 'none';
            viewAllUrlField.style.display = isVisible ? 'block' : 'none';
        });
    }
});
</script>

<style>
.form-label small.text-muted {
    font-weight: normal;
    font-size: 0.875em;
}

.row .col-md-6 .mb-3 {
    margin-bottom: 1rem;
}

hr.my-4 {
    margin: 1.5rem 0;
    border-color: #e9ecef;
}
</style>
