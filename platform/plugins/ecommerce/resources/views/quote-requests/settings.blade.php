@extends('core/base::layouts.master')

@section('title', 'Quote Settings')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Quote Request Settings</h4>
                        <div class="btn-group">
                            <a href="{{ route('admin.ecommerce.quote-form-builder.index') }}" class="btn btn-primary">
                                <i class="fas fa-tools"></i> Quote Form Builder
                            </a>
                            <a href="{{ route('quote-requests.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Requests
                            </a>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('quote-requests.settings.update') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- General Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3">General Settings</h5>
                                
                                <!-- Quick Access to Form Builder -->
                                <div class="mb-4 p-3 bg-light border rounded">
                                    <h6 class="mb-2">
                                        <i class="fas fa-tools text-primary"></i> Form Builder Access
                                    </h6>
                                    <p class="mb-2 text-muted small">Customize your quote form fields and layout</p>
                                    <a href="{{ route('admin.ecommerce.quote-form-builder.index') }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Open Quote Form Builder
                                    </a>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_quote_system" 
                                               name="enable_quote_system" value="1" 
                                               {{ $settings->enable_quote_system ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_quote_system">
                                            <strong>Enable Quote System</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Turn the entire quote request system on/off</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="quote_for_no_price_products" 
                                               name="quote_for_no_price_products" value="1" 
                                               {{ $settings->quote_for_no_price_products ? 'checked' : '' }}>
                                        <label class="form-check-label" for="quote_for_no_price_products">
                                            <strong>Show Quote Form for Products with No Price</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Automatically show quote form when product has no price set</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="require_login" 
                                               name="require_login" value="1" 
                                               {{ $settings->require_login ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_login">
                                            <strong>Require Customer Login</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Customers must be logged in to submit quote requests</small>
                                </div>

                                <div class="mb-3">
                                    <label for="response_time" class="form-label">Response Time Promise</label>
                                    <input type="text" class="form-control" id="response_time" name="response_time" 
                                           value="{{ $settings->response_time }}" placeholder="24 hours">
                                    <small class="text-muted">Displayed to customers (e.g., "24 hours", "1-2 business days")</small>
                                </div>

                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Notification Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                           value="{{ $settings->admin_email }}" placeholder="admin@example.com">
                                    <small class="text-muted">Email address to receive new quote notifications</small>
                                </div>
                            </div>

                            <!-- Email Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Email Notifications</h5>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="send_customer_confirmation" 
                                               name="send_customer_confirmation" value="1" 
                                               {{ $settings->send_customer_confirmation ? 'checked' : '' }}>
                                        <label class="form-check-label" for="send_customer_confirmation">
                                            <strong>Send Customer Confirmation Email</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Send confirmation email to customer when quote is submitted</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="send_admin_notification" 
                                               name="send_admin_notification" value="1" 
                                               {{ $settings->send_admin_notification ? 'checked' : '' }}>
                                        <label class="form-check-label" for="send_admin_notification">
                                            <strong>Send Admin Notification Email</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Send notification email to admin when new quote is submitted</small>
                                </div>

                                <div class="mb-3">
                                    <label for="max_file_uploads" class="form-label">Max File Uploads</label>
                                    <input type="number" class="form-control" id="max_file_uploads" name="max_file_uploads" 
                                           value="{{ $settings->max_file_uploads }}" min="0" max="20">
                                    <small class="text-muted">Maximum number of files customers can upload (0 to disable)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="allowed_file_types" class="form-label">Allowed File Types</label>
                                    <input type="text" class="form-control" id="allowed_file_types" name="allowed_file_types" 
                                           value="{{ $settings->allowed_file_types }}" placeholder="jpg,jpeg,png,pdf,doc,docx">
                                    <small class="text-muted">Comma-separated list of allowed file extensions</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Tax Settings -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">Tax Settings</h5>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_tax_on_quotes" 
                                               name="enable_tax_on_quotes" value="1" 
                                               {{ ($settings->enable_tax_on_quotes ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_tax_on_quotes">
                                            Enable Tax on Quotes
                                        </label>
                                    </div>
                                    <small class="text-muted">Apply tax calculations to quote items during checkout</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="quote_prices_include_tax" 
                                               name="quote_prices_include_tax" value="1" 
                                               {{ ($settings->quote_prices_include_tax ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="quote_prices_include_tax">
                                            Quote Prices Include Tax
                                        </label>
                                    </div>
                                    <small class="text-muted">When enabled, quoted prices already include tax and no additional tax will be calculated</small>
                                </div>

                                <div class="mb-3">
                                    <label for="quote_tax_calculation" class="form-label">Tax Calculation Method</label>
                                    <select class="form-select" id="quote_tax_calculation" name="quote_tax_calculation">
                                        <option value="auto" {{ ($settings->quote_tax_calculation ?? 'auto') === 'auto' ? 'selected' : '' }}>
                                            Auto (Use Product Tax Rules)
                                        </option>
                                        <option value="manual" {{ ($settings->quote_tax_calculation ?? 'auto') === 'manual' ? 'selected' : '' }}>
                                            Manual (Admin Sets Tax in Quote)
                                        </option>
                                        <option value="none" {{ ($settings->quote_tax_calculation ?? 'auto') === 'none' ? 'selected' : '' }}>
                                            No Tax on Quotes
                                        </option>
                                    </select>
                                    <small class="text-muted">Choose how tax should be calculated for quote items</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Categories & Products -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Categories Requiring Quotes</h5>
                                <div class="mb-3">
                                    <label class="form-label">Select categories that should always show quote form:</label>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($categories as $category)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="category_{{ $category->id }}" 
                                                       name="quote_categories[]" 
                                                       value="{{ $category->id }}"
                                                       {{ in_array($category->id, $settings->quote_categories ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="category_{{ $category->id }}">
                                                    {{ $category->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">All products in these categories will show the quote form</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">Specific Products Requiring Quotes</h5>
                                <div class="mb-3">
                                    <label for="quote_products" class="form-label">Search and select specific products:</label>
                                    <select class="form-select" id="quote_products" name="quote_products[]" multiple>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    {{ in_array($product->id, $settings->quote_products ?? []) ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Individual products that should show quote form regardless of category</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Form Fields Configuration -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">Form Fields Configuration</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                <th>Enabled</th>
                                                <th>Required</th>
                                                <th>Label</th>
                                                <th>For Tiles Only</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $formFields = $settings->form_fields ?? \Botble\Ecommerce\Models\QuoteSettings::getDefaultFormFields();
                                            @endphp
                                            @foreach($formFields as $fieldName => $fieldConfig)
                                                <tr>
                                                    <td><strong>{{ ucfirst(str_replace('_', ' ', $fieldName)) }}</strong></td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="form_fields[{{ $fieldName }}][enabled]" value="1"
                                                                   {{ ($fieldConfig['enabled'] ?? false) ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="form_fields[{{ $fieldName }}][required]" value="1"
                                                                   {{ ($fieldConfig['required'] ?? false) ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="form_fields[{{ $fieldName }}][label]" 
                                                               value="{{ $fieldConfig['label'] ?? '' }}" 
                                                               placeholder="Field label">
                                                    </td>
                                                    <td>
                                                        @if(isset($fieldConfig['for_tiles']))
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       name="form_fields[{{ $fieldName }}][for_tiles]" value="1"
                                                                       {{ ($fieldConfig['for_tiles'] ?? false) ? 'checked' : '' }}>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Custom Options -->
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="mb-3">Budget Ranges</h6>
                                @php $budgetRanges = $settings->budget_ranges ?? []; @endphp
                                @foreach($budgetRanges as $key => $label)
                                    <div class="mb-2">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" 
                                                   name="budget_ranges[{{ $key }}]" 
                                                   value="{{ $label }}" 
                                                   placeholder="Budget range label">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-4">
                                <h6 class="mb-3">Timeline Options</h6>
                                @php $timelineOptions = $settings->timeline_options ?? []; @endphp
                                @foreach($timelineOptions as $key => $label)
                                    <div class="mb-2">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" 
                                                   name="timeline_options[{{ $key }}]" 
                                                   value="{{ $label }}" 
                                                   placeholder="Timeline option label">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-4">
                                <h6 class="mb-3">Room Types (for Tiles)</h6>
                                @php $roomTypes = $settings->room_types ?? []; @endphp
                                @foreach($roomTypes as $key => $label)
                                    <div class="mb-2">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" 
                                                   name="room_types[{{ $key }}]" 
                                                   value="{{ $label }}" 
                                                   placeholder="Room type label">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="resetToDefaults()">
                                Reset to Defaults
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function removeOption(button) {
    button.closest('.mb-2').remove();
}

function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to defaults? This will overwrite your current configuration.')) {
        // Reset form to default values
        location.reload();
    }
}

// Initialize Select2 for products multi-select
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#quote_products').select2({
            placeholder: 'Search for products...',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endpush