<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('plugins/ecommerce::ecommerce.form_preview') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 2rem 0;
        }
        .preview-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .form-label.required::after {
            content: ' *';
            color: #dc3545;
        }
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-container">
            <h2 class="mb-4">{{ trans('plugins/ecommerce::ecommerce.form_preview') }}</h2>
            
            @if($fields->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No form fields configured yet. <a href="{{ route('project-form-builder.create') }}">Add some fields</a> to see the preview.
                </div>
            @else
                <form id="project-request-preview-form" class="row">
                    @foreach($fields as $field)
                        <div class="{{ $field->width }} mb-3">
                            <label for="field_{{ $field->id }}" class="form-label {{ $field->required ? 'required' : '' }}">
                                {{ $field->label }}
                            </label>
                            
                            @switch($field->type)
                                @case('text')
                                @case('email')
                                @case('tel')
                                @case('url')
                                @case('number')
                                    <input 
                                        type="{{ $field->type }}" 
                                        class="form-control" 
                                        id="field_{{ $field->id }}"
                                        name="field_{{ $field->id }}"
                                        placeholder="{{ $field->placeholder }}"
                                        {{ $field->required ? 'required' : '' }}
                                    >
                                    @break

                                @case('textarea')
                                    <textarea 
                                        class="form-control" 
                                        id="field_{{ $field->id }}"
                                        name="field_{{ $field->id }}"
                                        placeholder="{{ $field->placeholder }}"
                                        rows="4"
                                        {{ $field->required ? 'required' : '' }}
                                    ></textarea>
                                    @break

                                @case('select')
                                    <select 
                                        class="form-select" 
                                        id="field_{{ $field->id }}"
                                        name="field_{{ $field->id }}"
                                        {{ $field->required ? 'required' : '' }}
                                    >
                                        <option value="">{{ $field->placeholder ?: 'Choose an option...' }}</option>
                                        @if($field->options)
                                            @foreach(explode("\n", $field->options) as $option)
                                                @if(trim($option))
                                                    <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                    @break

                                @case('radio')
                                    @if($field->options)
                                        @foreach(explode("\n", $field->options) as $index => $option)
                                            @if(trim($option))
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="radio" 
                                                        name="field_{{ $field->id }}"
                                                        id="field_{{ $field->id }}_{{ $index }}"
                                                        value="{{ trim($option) }}"
                                                        {{ $field->required ? 'required' : '' }}
                                                    >
                                                    <label class="form-check-label" for="field_{{ $field->id }}_{{ $index }}">
                                                        {{ trim($option) }}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                    @break

                                @case('checkbox')
                                    @if($field->options)
                                        @foreach(explode("\n", $field->options) as $index => $option)
                                            @if(trim($option))
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="checkbox" 
                                                        name="field_{{ $field->id }}[]"
                                                        id="field_{{ $field->id }}_{{ $index }}"
                                                        value="{{ trim($option) }}"
                                                    >
                                                    <label class="form-check-label" for="field_{{ $field->id }}_{{ $index }}">
                                                        {{ trim($option) }}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                    @break

                                @case('file')
                                    <input 
                                        type="file" 
                                        class="form-control" 
                                        id="field_{{ $field->id }}"
                                        name="field_{{ $field->id }}"
                                        {{ $field->required ? 'required' : '' }}
                                    >
                                    @break

                                @case('date')
                                    <input 
                                        type="date" 
                                        class="form-control" 
                                        id="field_{{ $field->id }}"
                                        name="field_{{ $field->id }}"
                                        {{ $field->required ? 'required' : '' }}
                                    >
                                    @break

                                @case('time')
                                    <input 
                                        type="time" 
                                        class="form-control" 
                                        id="field_{{ $field->id }}"
                                        name="field_{{ $field->id }}"
                                        {{ $field->required ? 'required' : '' }}
                                    >
                                    @break

                                @case('datetime-local')
                                    <input 
                                        type="datetime-local" 
                                        class="form-control" 
                                        id="field_{{ $field->id }}"
                                        name="field_{{ $field->id }}"
                                        {{ $field->required ? 'required' : '' }}
                                    >
                                    @break
                            @endswitch
                            
                            @if($field->help_text)
                                <div class="help-text">{{ $field->help_text }}</div>
                            @endif
                        </div>
                    @endforeach
                    
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            {{ trans('plugins/ecommerce::ecommerce.submit_project_request') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('project-request-preview-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('This is a preview form. Actual submission is disabled.');
        });
    </script>
</body>
</html>