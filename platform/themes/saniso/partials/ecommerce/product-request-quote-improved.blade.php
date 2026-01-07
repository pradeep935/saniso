@php
    $productId = $product->id ?? 0;
@endphp

<!-- Product Quote Request Section -->
<div class="product-quote-section bg-light p-4 rounded-3 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3 mb-lg-0">
                <div class="quote-icon me-3">
                    <i class="fas fa-envelope-open-text text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1 text-dark">{{ __('Need a Custom Quote?') }}</h4>
                    <p class="mb-0 text-muted">{{ __('Get personalized pricing and expert advice for your project') }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <button
                class="btn btn-primary btn-lg request-quote-button w-100 w-lg-auto"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#productQuoteModal"
                title="{{ __('Request Quote') }}"
            >
                <i class="fas fa-quote-left me-2"></i>
                <span class="request-quote-text">{{ __('Request Quote') }}</span>
            </button>
        </div>
    </div>

    <!-- Quick Benefits -->
    <div class="row mt-4 text-center">
        <div class="col-4">
            <div class="feature-item">
                <i class="fas fa-clock text-primary mb-2 d-block" style="font-size: 1.5rem;"></i>
                <div class="small fw-bold">{{ __('Quick Response') }}</div>
                <div class="small text-muted">{{ __('24h Response') }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="feature-item">
                <i class="fas fa-dollar-sign text-primary mb-2 d-block" style="font-size: 1.5rem;"></i>
                <div class="small fw-bold">{{ __('Best Price') }}</div>
                <div class="small text-muted">{{ __('Competitive') }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="feature-item">
                <i class="fas fa-user-tie text-primary mb-2 d-block" style="font-size: 1.5rem;"></i>
                <div class="small fw-bold">{{ __('Expert Help') }}</div>
                <div class="small text-muted">{{ __('Professional') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Quote Request Modal -->
<div class="modal fade" id="productQuoteModal" tabindex="-1" aria-labelledby="productQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="productQuoteModalLabel">
                    <i class="fas fa-quote-left me-2"></i>
                    {{ __('Request Quote') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Header with product info -->
                <div class="bg-light border-bottom p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1 text-dark fw-bold">{{ $product->name }}</h6>
                            <p class="mb-0 text-muted small">{{ __('Product ID') }}: #{{ $product->id }}</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-2 mt-md-0">
                            <span class="badge bg-success fs-6">{{ __('Price on Request') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Benefits Banner -->
                <div class="bg-info bg-opacity-10 p-3">
                    <div class="row text-center g-3">
                        <div class="col-lg-3 col-sm-6">
                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                <i class="fas fa-clock text-info me-2"></i>
                                <small class="fw-bold">{{ __('24h Response Time') }}</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                <i class="fas fa-handshake text-info me-2"></i>
                                <small class="fw-bold">{{ __('Best Price Guarantee') }}</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                <i class="fas fa-user-tie text-info me-2"></i>
                                <small class="fw-bold">{{ __('Expert Consultation') }}</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                <i class="fas fa-shield-alt text-info me-2"></i>
                                <small class="fw-bold">{{ __('Quality Assurance') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4">
                    <form id="product-quote-form" class="product-quote-form" action="{{ route('public.quote-requests.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $productId }}">
                        
                        <div class="row g-4">
                            <!-- Customer Information -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-primary bg-opacity-10 border-0">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="fas fa-user me-2 text-primary"></i>{{ __('Your Information') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="customer_name" class="form-label fw-semibold">
                                                    {{ __('Full Name') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="customer_name" 
                                                       name="customer_name" required placeholder="{{ __('Enter your full name') }}">
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="customer_email" class="form-label fw-semibold">
                                                    {{ __('Email Address') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="email" class="form-control form-control-lg" id="customer_email" 
                                                       name="customer_email" required placeholder="{{ __('your@email.com') }}">
                                            </div>
                                            
                                            <div class="col-sm-6">
                                                <label for="customer_phone" class="form-label fw-semibold">{{ __('Phone') }}</label>
                                                <input type="tel" class="form-control form-control-lg" id="customer_phone" 
                                                       name="customer_phone" placeholder="{{ __('Your phone number') }}">
                                            </div>
                                            
                                            <div class="col-sm-6">
                                                <label for="customer_company" class="form-label fw-semibold">{{ __('Company') }}</label>
                                                <input type="text" class="form-control form-control-lg" id="customer_company" 
                                                       name="customer_company" placeholder="{{ __('Company name') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Details -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-success bg-opacity-10 border-0">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="fas fa-clipboard-list me-2 text-success"></i>{{ __('Project Details') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <label for="quantity" class="form-label fw-semibold">
                                                    {{ __('Quantity') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" class="form-control form-control-lg" id="quantity" 
                                                       name="quantity" min="1" value="1" required>
                                            </div>
                                            
                                            <div class="col-sm-6">
                                                <label for="budget_range" class="form-label fw-semibold">{{ __('Budget Range') }}</label>
                                                <select class="form-select form-select-lg" id="budget_range" name="budget_range">
                                                    <option value="">{{ __('Select budget') }}</option>
                                                    <option value="under_500">{{ __('Under $500') }}</option>
                                                    <option value="500_1000">{{ __('$500 - $1,000') }}</option>
                                                    <option value="1000_5000">{{ __('$1,000 - $5,000') }}</option>
                                                    <option value="5000_10000">{{ __('$5,000 - $10,000') }}</option>
                                                    <option value="10000_25000">{{ __('$10,000 - $25,000') }}</option>
                                                    <option value="over_25000">{{ __('Over $25,000') }}</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="timeline" class="form-label fw-semibold">{{ __('Project Timeline') }}</label>
                                                <select class="form-select form-select-lg" id="timeline" name="timeline">
                                                    <option value="">{{ __('Select timeline') }}</option>
                                                    <option value="urgent">{{ __('ASAP (1-2 weeks)') }}</option>
                                                    <option value="month">{{ __('Within a month') }}</option>
                                                    <option value="quarter">{{ __('Within 3 months') }}</option>
                                                    <option value="flexible">{{ __('Flexible timing') }}</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="project_description" class="form-label fw-semibold">{{ __('Project Description') }}</label>
                                                <textarea class="form-control" id="project_description" name="project_description" 
                                                          rows="3" placeholder="{{ __('Describe your project requirements, installation needs, or special considerations...') }}"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Options -->
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-8">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input form-check-input-lg" type="checkbox" 
                                                           id="newsletter_subscribe" name="newsletter_subscribe" value="1">
                                                    <label class="form-check-label fw-semibold" for="newsletter_subscribe">
                                                        {{ __('Keep me updated with special offers and product news') }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <small class="text-muted">
                                                    <i class="fas fa-shield-alt me-1"></i>{{ __('Privacy protected') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-0 p-4">
                <div class="d-flex w-100 gap-3">
                    <button type="button" class="btn btn-outline-secondary btn-lg flex-fill" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                    </button>
                    <button type="submit" form="product-quote-form" class="btn btn-primary btn-lg flex-fill">
                        <i class="fas fa-paper-plane me-2"></i>
                        {{ __('Send Quote Request') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Quote Form Styles -->
<style>
.product-quote-section {
    transition: all 0.3s ease;
}

.product-quote-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.feature-item {
    text-align: center;
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-1px);
}

.request-quote-button {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.request-quote-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.modal-xl {
    max-width: 1200px;
}

.form-control-lg, .form-select-lg {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control-lg:focus, .form-select-lg:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.form-check-input-lg {
    width: 1.5rem;
    height: 1.5rem;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

@media (max-width: 768px) {
    .modal-xl {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }
    
    .product-quote-section {
        padding: 1.5rem !important;
    }
    
    .feature-item {
        margin-bottom: 1rem;
    }
    
    .modal-footer .d-flex {
        flex-direction: column;
        gap: 0.75rem !important;
    }
    
    .modal-footer .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .card-body .row.g-3 {
        gap: 1rem !important;
    }
    
    .form-control-lg, .form-select-lg {
        font-size: 1rem;
        padding: 0.75rem;
    }
}
</style>