@extends('core/base::layouts.master')

@section('content')
    <div class="page-content">
        <div class="page-header">
            <h1>{{ isset($field) ? 'Edit Field' : 'Add New Field' }}</h1>
            <div class="page-header-actions">
                <a href="{{ route('admin.ecommerce.quote-form-builder.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Fields
                </a>
            </div>
        </div>

        <div class="page-body">
            <form method="POST" action="{{ isset($field) ? route('admin.ecommerce.quote-form-builder.update', $field->id) : route('admin.ecommerce.quote-form-builder.store') }}">
                @csrf
                @if(isset($field))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Field Configuration</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Field Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                                   value="{{ old('name', $field->name ?? '') }}" required>
                                            <small class="form-text text-muted">Used as HTML name attribute. Should be unique and lowercase.</small>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="label">Field Label <span class="text-danger">*</span></label>
                                            <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" 
                                                   value="{{ old('label', $field->label ?? '') }}" required>
                                            <small class="form-text text-muted">Displayed to users above the field.</small>
                                            @error('label')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="type">Field Type <span class="text-danger">*</span></label>
                                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                                <option value="">Select Field Type</option>
                                                @foreach($fieldTypes as $value => $label)
                                                    <option value="{{ $value }}" {{ old('type', $field->type ?? '') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="field_width">Field Width</label>
                                            <select name="field_width" id="field_width" class="form-control">
                                                <option value="col-12" {{ old('field_width', $field->field_width ?? 'col-12') == 'col-12' ? 'selected' : '' }}>Full Width (100%)</option>
                                                <option value="col-6" {{ old('field_width', $field->field_width ?? '') == 'col-6' ? 'selected' : '' }}>Half Width (50%)</option>
                                                <option value="col-4" {{ old('field_width', $field->field_width ?? '') == 'col-4' ? 'selected' : '' }}>One Third (33%)</option>
                                                <option value="col-3" {{ old('field_width', $field->field_width ?? '') == 'col-3' ? 'selected' : '' }}>One Quarter (25%)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="placeholder">Placeholder Text</label>
                                            <input type="text" name="placeholder" id="placeholder" class="form-control" 
                                                   value="{{ old('placeholder', $field->placeholder ?? '') }}">
                                            <small class="form-text text-muted">Shown inside the field when empty.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="default_value">Default Value</label>
                                            <input type="text" name="default_value" id="default_value" class="form-control" 
                                                   value="{{ old('default_value', $field->default_value ?? '') }}">
                                            <small class="form-text text-muted">Pre-filled value for the field.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $field->description ?? '') }}</textarea>
                                    <small class="form-text text-muted">Additional information about this field.</small>
                                </div>

                                <div class="form-group">
                                    <label for="help_text">Help Text</label>
                                    <input type="text" name="help_text" id="help_text" class="form-control" 
                                           value="{{ old('help_text', $field->help_text ?? '') }}">
                                    <small class="form-text text-muted">Shown below the field to help users.</small>
                                </div>

                                <!-- Options for select, radio, checkbox fields -->
                                <div id="field-options" style="display: none;">
                                    <div class="form-group">
                                        <label>Field Options</label>
                                        <div id="options-container">
                                            @if(isset($field) && $field->options)
                                                @foreach($field->options as $index => $option)
                                                    <div class="option-row row mb-2">
                                                        <div class="col-md-5">
                                                            <input type="text" name="options[labels][]" placeholder="Label" 
                                                                   class="form-control" value="{{ $option['label'] }}">
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="text" name="options[values][]" placeholder="Value" 
                                                                   class="form-control" value="{{ $option['value'] }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="button" class="btn btn-danger btn-sm remove-option">Remove</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <button type="button" id="add-option" class="btn btn-secondary btn-sm">Add Option</button>
                                    </div>
                                </div>

                                <!-- Field Attributes -->
                                <div class="form-group">
                                    <label>Field Attributes</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="min">Min Value/Length</label>
                                            <input type="text" name="field_attributes[min]" id="min" class="form-control" 
                                                   value="{{ old('field_attributes.min', $field->field_attributes['min'] ?? '') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="max">Max Value/Length</label>
                                            <input type="text" name="field_attributes[max]" id="max" class="form-control" 
                                                   value="{{ old('field_attributes.max', $field->field_attributes['max'] ?? '') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="step">Step (for numbers)</label>
                                            <input type="text" name="field_attributes[step]" id="step" class="form-control" 
                                                   value="{{ old('field_attributes.step', $field->field_attributes['step'] ?? '') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="accept">Accept (for files)</label>
                                            <input type="text" name="field_attributes[accept]" id="accept" class="form-control" 
                                                   value="{{ old('field_attributes.accept', $field->field_attributes['accept'] ?? '') }}"
                                                   placeholder=".jpg,.png,.pdf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Field Settings</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="required" id="required" class="custom-control-input" 
                                               value="1" {{ old('required', $field->required ?? false) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="required">Required Field</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="enabled" id="enabled" class="custom-control-input" 
                                               value="1" {{ old('enabled', $field->enabled ?? true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="enabled">Enable Field</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="validation_rules">Validation Rules</label>
                                    <input type="text" name="validation_rules" id="validation_rules" class="form-control" 
                                           value="{{ old('validation_rules', $field->validation_rules ?? '') }}"
                                           placeholder="e.g., min:3|max:50">
                                    <small class="form-text text-muted">Laravel validation rules (separated by |)</small>
                                </div>

                                <div class="form-group">
                                    <label for="css_classes">CSS Classes</label>
                                    <input type="text" name="css_classes" id="css_classes" class="form-control" 
                                           value="{{ old('css_classes', $field->css_classes ?? '') }}"
                                           placeholder="custom-class another-class">
                                    <small class="form-text text-muted">Additional CSS classes for styling</small>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-save"></i> {{ isset($field) ? 'Update Field' : 'Create Field' }}
                                </button>
                                @if(isset($field))
                                    <a href="{{ route('admin.ecommerce.quote-form-builder.index') }}" class="btn btn-secondary btn-block">
                                        <i class="fa fa-copy"></i> Back to List
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const optionsContainer = document.getElementById('field-options');
    
    // Show/hide options based on field type
    function toggleOptions() {
        const type = typeSelect.value;
        if (['select', 'radio', 'checkbox'].includes(type)) {
            optionsContainer.style.display = 'block';
        } else {
            optionsContainer.style.display = 'none';
        }
    }
    
    typeSelect.addEventListener('change', toggleOptions);
    toggleOptions(); // Initial call

    // Add option functionality
    document.getElementById('add-option').addEventListener('click', function() {
        const container = document.getElementById('options-container');
        const optionRow = document.createElement('div');
        optionRow.className = 'option-row row mb-2';
        optionRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="options[labels][]" placeholder="Label" class="form-control">
            </div>
            <div class="col-md-5">
                <input type="text" name="options[values][]" placeholder="Value" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-option">Remove</button>
            </div>
        `;
        container.appendChild(optionRow);
    });

    // Remove option functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-option')) {
            e.target.closest('.option-row').remove();
        }
    });

    // Auto-generate name from label
    document.getElementById('label').addEventListener('input', function() {
        const nameField = document.getElementById('name');
        if (!nameField.value) {
            nameField.value = this.value.toLowerCase()
                                      .replace(/[^a-z0-9]/g, '_')
                                      .replace(/_+/g, '_')
                                      .replace(/^_|_$/g, '');
        }
    });
});
</script>
@endpush