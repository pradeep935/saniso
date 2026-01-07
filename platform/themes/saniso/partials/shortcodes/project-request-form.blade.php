@php
    $formFields = \Botble\Ecommerce\Models\ProjectFormField::where('enabled', true)
        ->orderBy('sort_order')
        ->get();
@endphp

<div class="project-request-form-wrapper">
    @if($formFields->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No form fields have been configured. Please contact an administrator.
        </div>
    @else
        <form id="projectRequestForm" action="{{ route('public.project-requests.store') }}" method="POST" enctype="multipart/form-data" class="row">
            @csrf
            
            <!-- Success Message -->
            <div id="form-success-message" class="alert alert-success d-none">
                <i class="fas fa-check-circle me-2"></i>
                <span id="success-text">Your project request has been submitted successfully!</span>
            </div>
            
            <!-- Error Message -->
            <div id="form-error-message" class="alert alert-danger d-none">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="error-text">Please correct the errors below and try again.</span>
            </div>
            
            @foreach($formFields as $field)
                <div class="{{ $field->field_width }} mb-3">
                    <label for="field_{{ $field->id }}" class="form-label {{ $field->required ? 'required' : '' }}">
                        {{ $field->label }}
                        @if($field->required)
                            <span class="text-danger">*</span>
                        @endif
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
                                name="fields[{{ $field->id }}]"
                                placeholder="{{ $field->placeholder }}"
                                {{ $field->required ? 'required' : '' }}
                                @if($field->validation)
                                    data-validation="{{ $field->validation }}"
                                @endif
                            >
                            @break

                        @case('textarea')
                            <textarea 
                                class="form-control" 
                                id="field_{{ $field->id }}"
                                name="fields[{{ $field->id }}]"
                                placeholder="{{ $field->placeholder }}"
                                rows="4"
                                {{ $field->required ? 'required' : '' }}
                                @if($field->validation)
                                    data-validation="{{ $field->validation }}"
                                @endif
                            ></textarea>
                            @break

                        @case('select')
                            <select 
                                class="form-select" 
                                id="field_{{ $field->id }}"
                                name="fields[{{ $field->id }}]"
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
                                                name="fields[{{ $field->id }}]"
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
                                                name="fields[{{ $field->id }}][]"
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
                                name="files[{{ $field->id }}]"
                                {{ $field->required ? 'required' : '' }}
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                            >
                            <small class="text-muted">Allowed formats: PDF, DOC, DOCX, JPG, PNG, GIF (Max: 10MB)</small>
                            @break

                        @case('date')
                            <input 
                                type="date" 
                                class="form-control" 
                                id="field_{{ $field->id }}"
                                name="fields[{{ $field->id }}]"
                                {{ $field->required ? 'required' : '' }}
                            >
                            @break

                        @case('time')
                            <input 
                                type="time" 
                                class="form-control" 
                                id="field_{{ $field->id }}"
                                name="fields[{{ $field->id }}]"
                                {{ $field->required ? 'required' : '' }}
                            >
                            @break

                        @case('datetime-local')
                            <input 
                                type="datetime-local" 
                                class="form-control" 
                                id="field_{{ $field->id }}"
                                name="fields[{{ $field->id }}]"
                                {{ $field->required ? 'required' : '' }}
                            >
                            @break
                    @endswitch
                    
                    @if($field->help_text)
                        <div class="form-text">{{ $field->help_text }}</div>
                    @endif
                    
                    <div class="invalid-feedback"></div>
                </div>
            @endforeach
            
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="submit-spinner"></span>
                    {{ trans('plugins/ecommerce::ecommerce.submit_project_request') }}
                </button>
            </div>
        </form>
    @endif
</div>

@push('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('projectRequestForm');
            const submitBtn = document.getElementById('submit-btn');
            const spinner = document.getElementById('submit-spinner');
            const successMessage = document.getElementById('form-success-message');
            const errorMessage = document.getElementById('form-error-message');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                    successMessage.classList.add('d-none');
                    errorMessage.classList.add('d-none');
                    
                    // Clear previous errors
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                    // Create FormData for file uploads
                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            // Show validation errors
                            if (data.errors) {
                                Object.keys(data.errors).forEach(key => {
                                    const field = form.querySelector(`[name="${key}"], [name="${key}[]"]`);
                                    if (field) {
                                        field.classList.add('is-invalid');
                                        const feedback = field.parentNode.querySelector('.invalid-feedback');
                                        if (feedback) {
                                            feedback.textContent = data.errors[key][0];
                                        }
                                    }
                                });
                            }
                            
                            errorMessage.querySelector('#error-text').textContent = data.message || 'Please correct the errors and try again.';
                            errorMessage.classList.remove('d-none');
                        } else {
                            // Success
                            successMessage.querySelector('#success-text').textContent = data.message || 'Your project request has been submitted successfully!';
                            successMessage.classList.remove('d-none');
                            
                            // Reset form
                            form.reset();
                            
                            // Close modal if in modal
                            const modal = document.getElementById('projectRequestModal');
                            if (modal) {
                                setTimeout(() => {
                                    bootstrap.Modal.getInstance(modal)?.hide();
                                }, 2000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        errorMessage.querySelector('#error-text').textContent = 'An unexpected error occurred. Please try again.';
                        errorMessage.classList.remove('d-none');
                    })
                    .finally(() => {
                        // Hide loading state
                        submitBtn.disabled = false;
                        spinner.classList.add('d-none');
                    });
                });
            }
        });
    </script>
@endpush

<style>
.project-request-form-wrapper {
    max-width: 100%;
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
}

.project-request-form-wrapper .form-control,
.project-request-form-wrapper .form-select {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.project-request-form-wrapper .form-control:focus,
.project-request-form-wrapper .form-select:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.project-request-form-wrapper .form-control.is-invalid,
.project-request-form-wrapper .form-select.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

.project-request-form-wrapper .invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.project-request-form-wrapper .form-check-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.project-request-form-wrapper .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.project-request-form-wrapper .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.project-request-form-wrapper .alert {
    border: none;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.project-request-form-wrapper .alert-success {
    background-color: #d1edff;
    color: #0c5460;
}

.project-request-form-wrapper .alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}
</style>