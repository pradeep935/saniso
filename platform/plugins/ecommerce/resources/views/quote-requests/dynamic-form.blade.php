<style>
    {{ $css }}
    
    /* Simple styling for dynamic form integration */
    .quote-form-container {
        padding: 0;
    }
    
    .quote-form-container .form-group {
        margin-bottom: 15px;
    }
    
    .quote-form-container .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    .quote-form-container .form-control,
    .quote-form-container .form-select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }
    
    .quote-form-container .form-control:focus,
    .quote-form-container .form-select:focus {
        border-color: #123779;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .quote-form-container .form-check {
        margin-bottom: 8px;
    }
    
    .quote-form-container .form-check-label {
        font-size: 0.9rem;
        color: #495057;
    }
    
    .quote-form-container .invalid-feedback {
        font-size: 0.85rem;
        margin-top: 3px;
    }
    
    .quote-form-container .form-text {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 3px;
    }
    
    .quote-form-container .text-danger {
        color: #dc3545 !important;
    }
    
    /* Headings */
    .quote-form-container h1,
    .quote-form-container h2,
    .quote-form-container h3,
    .quote-form-container h4,
    .quote-form-container h5,
    .quote-form-container h6 {
        color: #ffffffff;
        font-weight: 600;
        margin: 15px 0 10px 0;
        font-size: 0.95rem;
    }
    
    .quote-form-container h1:first-child,
    .quote-form-container h2:first-child,
    .quote-form-container h3:first-child,
    .quote-form-container h4:first-child,
    .quote-form-container h5:first-child,
    .quote-form-container h6:first-child {
        margin-top: 0;
    }
    
    /* Dividers */
    .quote-form-container hr {
        border: none;
        border-top: 1px solid #dee2e6;
        margin: 15px 0;
    }
</style>

<div class="quote-form-container">
    <form method="POST" action="{{ route('public.quote-requests.store') }}" enctype="multipart/form-data" id="dynamic-quote-form">
        @csrf
        
        @if($productId)
            <input type="hidden" name="product_id" value="{{ $productId }}">
        @endif

        <div class="row">
            @foreach($fields as $field)
                @if($field->type === 'heading')
                    <div class="{{ $field->field_width }}">
                        {!! $field->renderField() !!}
                    </div>
                @elseif($field->type === 'divider')
                    <div class="col-12">
                        {!! $field->renderField() !!}
                    </div>
                @elseif($field->type === 'html')
                    <div class="{{ $field->field_width }}">
                        {!! $field->renderField() !!}
                    </div>
                @elseif($field->type === 'hidden')
                    {!! $field->renderField() !!}
                @else
                    <div class="form-group {{ $field->field_width }}">
                        @if($field->type !== 'checkbox' || !$field->options)
                            <label for="{{ $field->name }}" class="form-label">
                                {{ $field->label }}
                                @if($field->required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                        @endif
                        
                        @if($field->type === 'text' || $field->type === 'email' || $field->type === 'number' || $field->type === 'tel' || $field->type === 'url' || $field->type === 'password')
                            @php
                                $attributes = $field->field_attributes ?: [];
                                $attrString = '';
                                foreach ($attributes as $key => $value) {
                                    $attrString .= " {$key}=\"{$value}\"";
                                }
                                $classes = 'form-control ' . ($field->css_classes ?: '');
                                if ($field->required) $classes .= ' required';
                                $value = old($field->name, $existingData[$field->name] ?? $field->default_value);
                            @endphp
                            <input type="{{ $field->type }}" 
                                   name="{{ $field->name }}" 
                                   id="{{ $field->name }}" 
                                   class="{{ $classes }} @error($field->name) is-invalid @enderror" 
                                   placeholder="{{ $field->placeholder }}" 
                                   value="{{ $value }}"
                                   {!! $attrString !!}
                                   @if($field->required) required @endif>

                        @elseif($field->type === 'textarea')
                            @php
                                $attributes = $field->field_attributes ?: [];
                                $attrString = '';
                                foreach ($attributes as $key => $value) {
                                    $attrString .= " {$key}=\"{$value}\"";
                                }
                                $classes = 'form-control ' . ($field->css_classes ?: '');
                                if ($field->required) $classes .= ' required';
                                $value = old($field->name, $existingData[$field->name] ?? $field->default_value);
                            @endphp
                            <textarea name="{{ $field->name }}" 
                                      id="{{ $field->name }}" 
                                      class="{{ $classes }} @error($field->name) is-invalid @enderror" 
                                      placeholder="{{ $field->placeholder }}"
                                      {!! $attrString !!}
                                      @if($field->required) required @endif>{{ $value }}</textarea>

                        @elseif($field->type === 'select')
                            @php
                                $classes = 'form-control form-select ' . ($field->css_classes ?: '');
                                if ($field->required) $classes .= ' required';
                                $selectedValue = old($field->name, $existingData[$field->name] ?? $field->default_value);
                            @endphp
                            <select name="{{ $field->name }}" 
                                    id="{{ $field->name }}" 
                                    class="{{ $classes }} @error($field->name) is-invalid @enderror"
                                    @if($field->required) required @endif>
                                @if($field->placeholder)
                                    <option value="">{{ $field->placeholder }}</option>
                                @endif
                                @if($field->options)
                                    @foreach($field->options as $option)
                                        <option value="{{ $option['value'] }}" 
                                                @if($option['value'] == $selectedValue) selected @endif>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                        @elseif($field->type === 'checkbox')
                            @php
                                $selectedValues = old($field->name, $existingData[$field->name] ?? (array) $field->default_value);
                                if (!is_array($selectedValues)) {
                                    $selectedValues = [$selectedValues];
                                }
                            @endphp
                            @if($field->options)
                                @foreach($field->options as $option)
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="{{ $field->name }}[]" 
                                               value="{{ $option['value'] }}" 
                                               id="{{ $field->name }}_{{ $option['value'] }}" 
                                               class="form-check-input @error($field->name) is-invalid @enderror"
                                               @if(in_array($option['value'], $selectedValues)) checked @endif>
                                        <label class="form-check-label" for="{{ $field->name }}_{{ $option['value'] }}">
                                            {{ $option['label'] }}
                                        </label>
                                    </div>
                                @endforeach
                            @else
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="{{ $field->name }}" 
                                           id="{{ $field->name }}" 
                                           class="form-check-input @error($field->name) is-invalid @enderror" 
                                           value="1"
                                           @if(old($field->name, $existingData[$field->name] ?? $field->default_value)) checked @endif>
                                    <label class="form-check-label" for="{{ $field->name }}">
                                        {{ $field->label }}
                                        @if($field->required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                </div>
                            @endif

                        @elseif($field->type === 'radio')
                            @php
                                $selectedValue = old($field->name, $existingData[$field->name] ?? $field->default_value);
                            @endphp
                            @if($field->options)
                                @foreach($field->options as $option)
                                    <div class="form-check">
                                        <input type="radio" 
                                               name="{{ $field->name }}" 
                                               value="{{ $option['value'] }}" 
                                               id="{{ $field->name }}_{{ $option['value'] }}" 
                                               class="form-check-input @error($field->name) is-invalid @enderror"
                                               @if($option['value'] == $selectedValue) checked @endif
                                               @if($field->required) required @endif>
                                        <label class="form-check-label" for="{{ $field->name }}_{{ $option['value'] }}">
                                            {{ $option['label'] }}
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                        @elseif($field->type === 'file')
                            @php
                                $attributes = $field->field_attributes ?: [];
                                $attrString = '';
                                foreach ($attributes as $key => $value) {
                                    if ($key === 'accept') {
                                        $attrString .= " accept=\"{$value}\"";
                                    } elseif ($key === 'multiple' && $value) {
                                        $attrString .= " multiple";
                                    }
                                }
                                $classes = 'form-control ' . ($field->css_classes ?: '');
                                if ($field->required) $classes .= ' required';
                            @endphp
                            <input type="file" 
                                   name="{{ $field->name }}" 
                                   id="{{ $field->name }}" 
                                   class="{{ $classes }} @error($field->name) is-invalid @enderror"
                                   {!! $attrString !!}
                                   @if($field->required) required @endif>

                        @elseif(in_array($field->type, ['date', 'time', 'datetime-local', 'color', 'range']))
                            @php
                                $attributes = $field->field_attributes ?: [];
                                $attrString = '';
                                foreach ($attributes as $key => $value) {
                                    $attrString .= " {$key}=\"{$value}\"";
                                }
                                $classes = 'form-control ' . ($field->css_classes ?: '');
                                if ($field->required) $classes .= ' required';
                                $value = old($field->name, $existingData[$field->name] ?? $field->default_value);
                            @endphp
                            <input type="{{ $field->type }}" 
                                   name="{{ $field->name }}" 
                                   id="{{ $field->name }}" 
                                   class="{{ $classes }} @error($field->name) is-invalid @enderror" 
                                   value="{{ $value }}"
                                   {!! $attrString !!}
                                   @if($field->required) required @endif>
                        @endif
                        
                        @error($field->name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        @if($field->help_text)
                            <div class="form-text">{{ $field->help_text }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </form>
</div>

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any dynamic form behaviors here
    const form = document.getElementById('dynamic-quote-form');
    
    // Form validation enhancement
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('.required');
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    
    // File upload validation
    const fileInputs = form.querySelectorAll('input[type="file"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file size if max_size is specified
                const maxSize = this.getAttribute('data-max-size');
                if (maxSize && file.size > parseInt(maxSize) * 1024) {
                    alert('File size exceeds maximum allowed size.');
                    this.value = '';
                }
            }
        });
    });
});
</script>
@endpush