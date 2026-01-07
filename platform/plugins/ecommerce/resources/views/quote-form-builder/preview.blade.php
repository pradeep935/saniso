<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Form Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        {{ $css }}
        
        .preview-header {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-check {
            margin-bottom: 0.5rem;
        }
        
        .form-heading {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .form-divider {
            margin: 2rem 0;
            border-color: #e1e5e9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-header text-center">
            <h1>Quote Form Preview</h1>
            <p class="text-muted">This is how your quote form will appear to customers</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="quote-form-container">
                    <form>
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
                                    
                                    {!! $field->renderField() !!}
                                    
                                    @if($field->help_text)
                                        <div class="form-text">{{ $field->help_text }}</div>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        <div class="col-12">
                            <button type="submit" class="btn btn-submit">
                                Submit Quote Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Form Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Field Count</h6>
                                <p>{{ $fields->count() }} fields configured</p>
                                
                                <h6>Field Types Used</h6>
                                <ul>
                                    @foreach($fields->groupBy('type') as $type => $typeFields)
                                        <li>{{ ucfirst($type) }}: {{ $typeFields->count() }} field(s)</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Required Fields</h6>
                                <p>{{ $fields->where('required', true)->count() }} required fields</p>
                                
                                <h6>Form Width Distribution</h6>
                                <ul>
                                    @foreach($fields->groupBy('field_width') as $width => $widthFields)
                                        <li>{{ $width }}: {{ $widthFields->count() }} field(s)</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>