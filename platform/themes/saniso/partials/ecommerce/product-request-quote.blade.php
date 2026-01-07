@php
    $productId = $product->id ?? null;
    $productName = $product->name ?? '';
    $currentUser = auth('customer')->user();
@endphp

<!-- Request Quote Button -->
<div class="product-quote-section mb-4">
    <div class="quote-price-notice mb-3">
        <div class="alert alert-info d-flex align-items-center">
            <span class="svg-icon me-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                </svg>
            </span>
            <div>
                <strong>{{ __('Custom Pricing Available') }}</strong>
                <br>
                <small>{{ __('This product requires a custom quote based on your specific requirements.') }}</small>
            </div>
        </div>
    </div>
    
    <button
        type="button"
        class="btn btn-primary btn-lg w-100 request-quote-button"
        data-bs-toggle="modal"
        data-bs-target="#productQuoteModal"
    >
        <span class="svg-icon me-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
            </svg>
        </span>
        {{ __('Request Custom Quote') }}
    </button>
    
    <div class="quote-features mt-3">
        <div class="row text-center">
            <div class="col-4">
                <div class="feature-item">
                    <span class="svg-icon text-primary mb-1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                    </span>
                    <div class="small">{{ __('Free Quote') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="feature-item">
                    <span class="svg-icon text-primary mb-1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                    </span>
                    <div class="small">{{ __('24h Response') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="feature-item">
                    <span class="svg-icon text-primary mb-1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                    </span>
                    <div class="small">{{ __('Expert Advice') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quote Request Modal -->
<div class="modal fade" id="productQuoteModal" tabindex="-1" aria-labelledby="productQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content quote-form-container">
            <div class="modal-header">
                <h5 class="modal-title" id="productQuoteModalLabel">
                    {{ __('Request Quote for') }}: {{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $formService = app(\App\Services\QuoteFormService::class);
                    $existingData = [];
                    if ($currentUser) {
                        $existingData['customer_name'] = $currentUser->name;
                        $existingData['customer_email'] = $currentUser->email;
                    }
                @endphp

                @if($formService->isFormBuilderEnabled())
                    {!! $formService->renderForm($productId, $existingData) !!}
                @else
                    <!-- Fallback to default form if form builder is not configured -->
                    <form id="product-quote-form" class="product-quote-form" action="{{ route('public.quote-requests.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $productId }}">
                    
                    <div class="row g-4">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h6 class="section-title">{{ __('Your Information') }}</h6>
                            
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="customer_name" 
                                    name="customer_name" 
                                    value="{{ $currentUser->name ?? '' }}" 
                                    required
                                    placeholder="{{ __('Enter your full name') }}"
                                >
                            </div>
                            
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="customer_email" 
                                    name="customer_email" 
                                    value="{{ $currentUser->email ?? '' }}" 
                                    required
                                    placeholder="{{ __('Enter your email address') }}"
                                >
                            </div>
                            
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">{{ __('Phone Number') }}</label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="customer_phone" 
                                    name="customer_phone" 
                                    placeholder="{{ __('Enter your phone number') }}"
                                >
                            </div>
                            
                            <div class="mb-3">
                                <label for="customer_company" class="form-label">{{ __('Company Name') }}</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="customer_company" 
                                    name="customer_company" 
                                    placeholder="{{ __('Enter your company name') }}"
                                >
                            </div>
                        </div>
                        
                        <!-- Project Requirements -->
                        <div class="col-md-6">
                            <h6 class="section-title">{{ __('Project Requirements') }}</h6>
                            
                            <div class="mb-3">
                                <label for="quantity" class="form-label">{{ __('Quantity Needed') }} <span class="text-danger">*</span></label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="quantity" 
                                    name="quantity" 
                                    min="1" 
                                    required
                                    placeholder="{{ __('Enter quantity') }}"
                                >
                            </div>
                            
                            @php
                                $categories = $product->categories;
                                $isTilesCategory = false;
                                foreach ($categories as $category) {
                                    $categoryName = strtolower($category->name);
                                    $categorySlug = strtolower($category->slug);
                                    if (str_contains($categoryName, 'tile') || str_contains($categoryName, 'flooring') || 
                                        str_contains($categorySlug, 'tile') || str_contains($categorySlug, 'flooring')) {
                                        $isTilesCategory = true;
                                        break;
                                    }
                                }
                                
                                // Check if area_size is required in settings
                                $quoteSettings = \Botble\Ecommerce\Models\QuoteSettings::getInstance();
                                $formFields = $quoteSettings->getEnabledFormFields();
                                $areaSizeRequired = ($formFields['area_size']['required'] ?? false);
                            @endphp
                            
                            @if ($isTilesCategory || $areaSizeRequired)
                                <!-- Tile-specific fields -->
                                <div class="mb-3">
                                    <label for="area_size" class="form-label">{{ __('Area Size (sq ft/sq m)') }} <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="area_size" 
                                        name="area_size" 
                                        required
                                        placeholder="{{ __('e.g., 500 sq ft or 50 sq m') }}"
                                    >
                                </div>
                                
                                <div class="mb-3">
                                    <label for="room_type" class="form-label">{{ __('Room/Application Type') }}</label>
                                    <select class="form-select" id="room_type" name="room_type">
                                        <option value="">{{ __('Select room type') }}</option>
                                        <option value="bathroom">{{ __('Bathroom') }}</option>
                                        <option value="kitchen">{{ __('Kitchen') }}</option>
                                        <option value="living_room">{{ __('Living Room') }}</option>
                                        <option value="bedroom">{{ __('Bedroom') }}</option>
                                        <option value="commercial">{{ __('Commercial Space') }}</option>
                                        <option value="outdoor">{{ __('Outdoor/Patio') }}</option>
                                        <option value="other">{{ __('Other') }}</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="installation_needed" class="form-label">{{ __('Installation Required?') }}</label>
                                    <select class="form-select" id="installation_needed" name="installation_needed">
                                        <option value="">{{ __('Select option') }}</option>
                                        <option value="yes">{{ __('Yes, I need installation') }}</option>
                                        <option value="no">{{ __('No, materials only') }}</option>
                                        <option value="consultation">{{ __('Need consultation') }}</option>
                                    </select>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label for="budget_range" class="form-label">{{ __('Budget Range') }}</label>
                                <select class="form-select" id="budget_range" name="budget_range">
                                    <option value="">{{ __('Select budget range') }}</option>
                                    <option value="under_1000">{{ __('Under $1,000') }}</option>
                                    <option value="1000_5000">{{ __('$1,000 - $5,000') }}</option>
                                    <option value="5000_10000">{{ __('$5,000 - $10,000') }}</option>
                                    <option value="10000_25000">{{ __('$10,000 - $25,000') }}</option>
                                    <option value="over_25000">{{ __('Over $25,000') }}</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="timeline" class="form-label">{{ __('Project Timeline') }}</label>
                                <select class="form-select" id="timeline" name="timeline">
                                    <option value="">{{ __('Select timeline') }}</option>
                                    <option value="urgent">{{ __('ASAP (1-2 weeks)') }}</option>
                                    <option value="month">{{ __('Within a month') }}</option>
                                    <option value="quarter">{{ __('Within 3 months') }}</option>
                                    <option value="flexible">{{ __('Flexible timing') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Details -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="section-title">{{ __('Additional Details') }}</h6>
                            
                            <div class="mb-3">
                                <label for="project_description" class="form-label">{{ __('Project Description') }}</label>
                                <textarea 
                                    class="form-control" 
                                    id="project_description" 
                                    name="project_description" 
                                    rows="4"
                                    placeholder="{{ __('Please describe your project, specific requirements, or any questions you have...') }}"
                                ></textarea>
                            </div>
                            
                            @if ($isTilesCategory || ($formFields['special_requirements']['enabled'] ?? false))
                                <div class="mb-3">
                                    <label for="special_requirements" class="form-label">{{ __('Special Requirements') }}</label>
                                    <div class="form-check-container">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="waterproof" name="special_requirements[]" value="waterproof">
                                            <label class="form-check-label" for="waterproof">{{ __('Waterproof/Wet area application') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="heated_floor" name="special_requirements[]" value="heated_floor">
                                            <label class="form-check-label" for="heated_floor">{{ __('Heated floor compatibility') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="slip_resistant" name="special_requirements[]" value="slip_resistant">
                                            <label class="form-check-label" for="slip_resistant">{{ __('Slip-resistant surface') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="custom_cutting" name="special_requirements[]" value="custom_cutting">
                                            <label class="form-check-label" for="custom_cutting">{{ __('Custom cutting/sizing needed') }}</label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="newsletter_subscribe" name="newsletter_subscribe" value="1">
                                <label class="form-check-label" for="newsletter_subscribe">
                                    {{ __('Subscribe to our newsletter for updates and special offers') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    </form>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                @php
                    $formService = app(\App\Services\QuoteFormService::class);
                @endphp
                @if($formService->isFormBuilderEnabled())
                    <button type="submit" form="dynamic-quote-form" class="btn btn-primary quote-submit-btn" id="quote-submit-btn">
                @else
                    <button type="submit" form="product-quote-form" class="btn btn-primary quote-submit-btn" id="quote-submit-btn">
                @endif
                    <span class="svg-icon me-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                        </svg>
                    </span>
                    {{ __('Send Quote Request') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quote Form Styles -->
<style>
/* Quote Features Section */
.quote-features {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.quote-features .feature-item {
    text-align: center;
    padding: 8px;
}

.quote-features .svg-icon {
    display: inline-block;
    width: 24px;
    height: 24px;
    margin-bottom: 5px;
    color: #0d6efd;
}

.quote-features .feature-item .small {
    font-weight: 500;
    color: #495057;
    font-size: 0.85rem;
}

/* Modal Enhancements - Clean & Simple */
.quote-form-container {
    background: white;
    box-shadow: none !important;
    border: none !important;
    border-radius: 12px;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 60px);
}

.modal-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
    color: white;
    border-bottom: none;
    padding: 20px 25px;
    border-radius: 12px 12px 0 0;
}

.modal-title {
    font-weight: 600;
    font-size: 1.2rem;
    margin: 0;
}

.btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

.btn-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 25px;
    background: white;
}

.modal-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 20px 25px;
    border-radius: 0 0 12px 12px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

/* Better Form Layout - Perfect 50/50 Split */
.row.g-4 {
    margin: 0;
}

.row.g-4 > .col-md-6 {
    padding: 0 15px;
}

.row.g-4 > .col-md-6:first-child {
    border-right: none;
    padding-right: 20px;
}

.row.g-4 > .col-md-6:last-child {
    padding-left: 20px;
}

/* Form Text Spacing */
.form-text {
    margin-top: 8px !important;
    margin-bottom: 15px !important;
    font-size: 0.875rem;
    color: #6c757d;
}

.form-control + .form-text,
.form-select + .form-text {
    margin-top: 6px !important;
}

/* Section Titles - Clean Design */
.section-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 8px;
    border-bottom: 2px solid #0d6efd;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.section-title::before {
    content: '';
    width: 4px;
    height: 20px;
    background: #0d6efd;
    margin-right: 10px;
    border-radius: 2px;
}

/* Form Controls - Clean & Modern */
.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 12px 15px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    transform: translateY(-1px);
}

.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
}

/* Checkbox Styling */
.form-check {
    margin-bottom: 10px;
}

.form-check-input {
    margin-top: 0.3em;
}

.form-check-label {
    font-size: 0.9rem;
    color: #495057;
    margin-left: 5px;
}

.form-check-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
    margin-bottom: 15px;
}

.form-check-container .form-check {
    margin-bottom: 8px;
}

.form-check-container .form-check:last-child {
    margin-bottom: 0;
}

/* Button Styling */
.quote-submit-btn {
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
    border: none;
    padding: 12px 25px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.quote-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(13, 110, 253, 0.3);
}

.request-quote-button {
    transition: all 0.3s ease;
}

.request-quote-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

/* Modal Size - Optimized */
.modal-lg {
    max-width: 900px;
}

/* Better Spacing */
.mb-3 {
    margin-bottom: 1.5rem !important;
}

/* Alert Styling */
.product-quote-section .alert {
    border-left: 4px solid #0d6efd;
    border-radius: 8px;
    font-size: 0.9rem;
}

/* Notification Styling */
.quote-notification {
    border-radius: 8px;
    font-size: 0.9rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-lg {
        max-width: 95%;
        margin: 15px auto;
    }
    
    .modal-dialog-centered {
        min-height: calc(100vh - 30px);
    }
    
    .modal-header, .modal-body, .modal-footer {
        padding: 15px 20px;
    }
    
    .row.g-4 > .col-md-6:first-child {
        border-right: none;
        border-bottom: none;
        padding-right: 15px;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    
    .row.g-4 > .col-md-6:last-child {
        padding-left: 15px;
        padding-top: 0;
    }
    
    .quote-features {
        padding: 12px;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-footer .btn {
        width: 100%;
    }
}

/* Additional Details Section */
.row.mt-4 {
    margin-top: 2rem !important;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

/* Form Validation */
.invalid-feedback {
    font-size: 0.85rem;
    margin-top: 5px;
}

/* Loading State */
.spinner-border-sm {
    width: 14px;
    height: 14px;
}
</style>

<!-- Quote Form JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle both static form and dynamic form
    const staticQuoteForm = document.getElementById('product-quote-form');
    const dynamicQuoteForm = document.getElementById('dynamic-quote-form');
    const submitButton = document.getElementById('quote-submit-btn');
    
    // Determine which form is active
    const activeForm = staticQuoteForm || dynamicQuoteForm;
    
    if (activeForm && submitButton) {
        // Handle AJAX submission for both forms
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            handleAjaxSubmission(activeForm);
        });
        
        // Prevent default form submission for both forms
        activeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAjaxSubmission(activeForm);
        });
    }
    
    function validateForm(form) {
        const requiredFields = form.querySelectorAll('[required], .required');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }
    
    function handleAjaxSubmission(form) {
        if (!validateForm(form)) {
            showNotification('{{ __("Please fill in all required fields.") }}', 'error');
            return;
        }
        
        // Submit form via AJAX
        const formData = new FormData(form);
        const originalText = submitButton.innerHTML;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Sending...") }}';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.message || '{{ __("An error occurred") }}');
            }
            
            // Success
            showNotification(data.message || '{{ __("Thank you! Your quote request has been sent successfully. We will contact you within 24 hours.") }}', 'success');
            
            // Close modal and reset form
            const modal = bootstrap.Modal.getInstance(document.getElementById('productQuoteModal'));
            if (modal) {
                modal.hide();
            }
            form.reset();
        })
        .catch(error => {
            console.error('Quote submission error:', error);
            showNotification(error.message || '{{ __("Sorry, there was an error sending your request. Please try again or contact us directly.") }}', 'error');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    }
    
    // Notification function
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.quote-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible quote-notification`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-radius: 8px;
        `;
        notification.innerHTML = `
            <strong>${type === 'success' ? '✓ Success!' : type === 'error' ? '✗ Error!' : 'ℹ Info!'}</strong><br>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 6 seconds
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.remove();
            }
        }, 6000);
    }
});
</script>