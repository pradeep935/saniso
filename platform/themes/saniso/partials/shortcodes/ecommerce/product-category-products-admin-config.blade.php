<div class="mb-3">
    <label class="form-label">{{ __('Select category') }}</label>
    <select name="category_id" class="form-select">
        {!! ProductCategoryHelper::renderProductCategoriesSelect(Arr::get($attributes, 'category_id')) !!}
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Limit number of categories') }}</label>
    <input
        class="form-control"
        name="number_of_categories"
        type="number"
        value="{{ Arr::get($attributes, 'number_of_categories', 3) }}"
        placeholder="{{ __('Default: 3') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Limit number of products') }}</label>
    <input
        class="form-control"
        name="limit"
        type="number"
        value="{{ Arr::get($attributes, 'limit') }}"
        placeholder="{{ __('Unlimited by default') }}"
    >
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}

<div class="mb-3">
    <label class="form-label">{{ __('Large Screen Columns (1400px+)') }}</label>
    <select name="large_screen_columns" class="form-select">
        <option value="8" @if(Arr::get($attributes, 'large_screen_columns', '6') == '8') selected @endif>8 Columns</option>
        <option value="7" @if(Arr::get($attributes, 'large_screen_columns', '6') == '7') selected @endif>7 Columns</option>
        <option value="6" @if(Arr::get($attributes, 'large_screen_columns', '6') == '6') selected @endif>6 Columns (Default)</option>
        <option value="5" @if(Arr::get($attributes, 'large_screen_columns', '6') == '5') selected @endif>5 Columns</option>
        <option value="4" @if(Arr::get($attributes, 'large_screen_columns', '6') == '4') selected @endif>4 Columns</option>
        <option value="3" @if(Arr::get($attributes, 'large_screen_columns', '6') == '3') selected @endif>3 Columns</option>
        <option value="2" @if(Arr::get($attributes, 'large_screen_columns', '6') == '2') selected @endif>2 Columns</option>
        <option value="1" @if(Arr::get($attributes, 'large_screen_columns', '6') == '1') selected @endif>1 Column</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Desktop Columns (1200px - 1399px)') }}</label>
    <select name="desktop_columns" class="form-select">
        <option value="8" @if(Arr::get($attributes, 'desktop_columns', '5') == '8') selected @endif>8 Columns</option>
        <option value="7" @if(Arr::get($attributes, 'desktop_columns', '5') == '7') selected @endif>7 Columns</option>
        <option value="6" @if(Arr::get($attributes, 'desktop_columns', '5') == '6') selected @endif>6 Columns</option>
        <option value="5" @if(Arr::get($attributes, 'desktop_columns', '5') == '5') selected @endif>5 Columns (Default)</option>
        <option value="4" @if(Arr::get($attributes, 'desktop_columns', '5') == '4') selected @endif>4 Columns</option>
        <option value="3" @if(Arr::get($attributes, 'desktop_columns', '5') == '3') selected @endif>3 Columns</option>
        <option value="2" @if(Arr::get($attributes, 'desktop_columns', '5') == '2') selected @endif>2 Columns</option>
        <option value="1" @if(Arr::get($attributes, 'desktop_columns', '5') == '1') selected @endif>1 Column</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Tablet Columns (1024px - 1199px)') }}</label>
    <select name="tablet_columns" class="form-select">
        <option value="6" @if(Arr::get($attributes, 'tablet_columns', '4') == '6') selected @endif>6 Columns</option>
        <option value="5" @if(Arr::get($attributes, 'tablet_columns', '4') == '5') selected @endif>5 Columns</option>
        <option value="4" @if(Arr::get($attributes, 'tablet_columns', '4') == '4') selected @endif>4 Columns (Default)</option>
        <option value="3" @if(Arr::get($attributes, 'tablet_columns', '4') == '3') selected @endif>3 Columns</option>
        <option value="2" @if(Arr::get($attributes, 'tablet_columns', '4') == '2') selected @endif>2 Columns</option>
        <option value="1" @if(Arr::get($attributes, 'tablet_columns', '4') == '1') selected @endif>1 Column</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Mobile Columns (768px - 1023px)') }}</label>
    <select name="mobile_columns" class="form-select">
        <option value="4" @if(Arr::get($attributes, 'mobile_columns', '3') == '4') selected @endif>4 Columns</option>
        <option value="3" @if(Arr::get($attributes, 'mobile_columns', '3') == '3') selected @endif>3 Columns (Default)</option>
        <option value="2" @if(Arr::get($attributes, 'mobile_columns', '3') == '2') selected @endif>2 Columns</option>
        <option value="1" @if(Arr::get($attributes, 'mobile_columns', '3') == '1') selected @endif>1 Column</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Small Mobile Columns (below 768px)') }}</label>
    <select name="small_mobile_columns" class="form-select">
        <option value="3" @if(Arr::get($attributes, 'small_mobile_columns', '2') == '3') selected @endif>3 Columns</option>
        <option value="2" @if(Arr::get($attributes, 'small_mobile_columns', '2') == '2') selected @endif>2 Columns (Default)</option>
        <option value="1" @if(Arr::get($attributes, 'small_mobile_columns', '2') == '1') selected @endif>1 Column</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Product Title') }}</label>
    <select name="show_title" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_title', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_title') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Rating') }}</label>
    <select name="show_rating" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_rating', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_rating') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Price') }}</label>
    <select name="show_price" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_price', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_price') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Description') }}</label>
    <select name="show_description" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_description', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_description') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Store Information') }}</label>
    <select name="show_store_info" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_store_info', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_store_info') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Product Labels') }}</label>
    <select name="show_labels" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_labels', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_labels') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Add to Cart Button') }}</label>
    <select name="show_add_to_cart" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_add_to_cart', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_add_to_cart') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Show Wishlist Button') }}</label>
    <select name="show_wishlist" class="form-select">
        <option value="yes" @if(Arr::get($attributes, 'show_wishlist', 'yes') == 'yes') selected @endif>{{ __('Yes') }}</option>
        <option value="no" @if(Arr::get($attributes, 'show_wishlist') == 'no') selected @endif>{{ __('No') }}</option>
    </select>
</div>
