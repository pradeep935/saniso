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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productQuoteModalLabel">
                    {{ __('Request Quote for') }}: {{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="product-quote-form" class="product-quote-form" action="{{ route('public.quote-requests.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $productId }}">
                    
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">{{ __('Your Information') }}</h6>
                            
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
                            <h6 class="text-muted mb-3">{{ __('Project Requirements') }}</h6>
                            
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
                                    if (in_array(strtolower($category->name), ['tiles', 'tile', 'flooring']) || 
                                        in_array(strtolower($category->slug), ['tiles', 'tile', 'flooring'])) {
                                        $isTilesCategory = true;
                                        break;
                                    }
                                }
                            @endphp
                            
                            @if ($isTilesCategory)
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
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">{{ __('Additional Details') }}</h6>
                            
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
                            
                            @if ($isTilesCategory)
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" form="product-quote-form" class="btn btn-primary">
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
.quote-features .feature-item {
    text-align: center;
}

.quote-features .svg-icon {
    display: inline-block;
    width: 24px;
    height: 24px;
}

.form-check-container .form-check {
    margin-bottom: 0.5rem;
}

.request-quote-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.modal-lg {
    max-width: 800px;
}

.product-quote-section .alert {
    border-left: 4px solid #0d6efd;
}
</style>

<!-- Quote Form JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quoteForm = document.getElementById('product-quote-form');
    
    if (quoteForm) {
        quoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const requiredFields = quoteForm.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                alert('{{ __("Please fill in all required fields.") }}');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(quoteForm);
            const submitButton = document.querySelector('button[form="product-quote-form"][type="submit"]');
            const originalText = submitButton.innerHTML;
            
            console.log('Quote form submission started');
            console.log('Form action:', quoteForm.action);
            console.log('Form data:', Object.fromEntries(formData));
            console.log('Submit button found:', submitButton);
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Sending...") }}';
            
            fetch(quoteForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.error) {
                    throw new Error(data.message || '{{ __("An error occurred") }}');
                }
                
                // Success
                alert('{{ __("Thank you! Your quote request has been sent successfully. We will contact you within 24 hours.") }}');
                
                // Close modal and reset form
                const modal = bootstrap.Modal.getInstance(document.getElementById('productQuoteModal'));
                modal.hide();
                quoteForm.reset();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("Sorry, there was an error sending your request. Please try again or contact us directly.") }}');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
});
</script>