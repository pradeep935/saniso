@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::affiliate.become_affiliate'))

@php
    use Botble\Base\Facades\BaseHelper;
@endphp

@section('content')
    {{-- Hero Section --}}
    <div class="affiliate-registration-hero mb-4 bg-info">
        <div class="hero-content">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title text-white">
                        <x-core::icon name="ti ti-users" class="me-2 text-white" />
                        {{ trans('plugins/affiliate-pro::affiliate.become_affiliate') }}
                    </h1>
                    <p class="hero-description text-white">
                        {{ trans('plugins/affiliate-pro::affiliate.join_program_description') }}
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="hero-icon ">
                        <x-core::icon name="ti ti-chart-line" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Benefits Section --}}
    <div class="affiliate-benefits-section mb-4">
        <h3 class="section-title">
            <x-core::icon name="ti ti-gift" class="me-2" />
            {{ trans('plugins/affiliate-pro::affiliate.benefits') }}
        </h3>
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <x-core::icon name="ti ti-coins" />
                    </div>
                    <h6>{{ trans('plugins/affiliate-pro::affiliate.earn_commission_benefit') }}</h6>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <x-core::icon name="ti ti-share" />
                    </div>
                    <h6>{{ trans('plugins/affiliate-pro::affiliate.access_promotional_materials') }}</h6>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <x-core::icon name="ti ti-chart-bar" />
                    </div>
                    <h6>{{ trans('plugins/affiliate-pro::affiliate.track_performance_realtime') }}</h6>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <x-core::icon name="ti ti-wallet" />
                    </div>
                    <h6>{{ trans('plugins/affiliate-pro::affiliate.receive_timely_payments') }}</h6>
                </div>
            </div>
        </div>
    </div>

    {{-- Registration Form --}}
    <div class="affiliate-registration-form">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <x-core::icon name="ti ti-user-plus" class="me-2" />
                    {{ trans('plugins/affiliate-pro::affiliate.apply') }}
                </h4>
            </div>
            <div class="card-body">
                @php
                    $affiliateRulesContent = \Botble\AffiliatePro\Facades\AffiliateHelper::getSetting('rules_content');

                    if (is_plugin_active('language')) {
                        $currentLocale = \Botble\Language\Facades\Language::getCurrentLocale();
                        $defaultLanguage = \Botble\Language\Facades\Language::getDefaultLanguage();
                        $defaultLocale = $defaultLanguage['lang_locale'] ?? 'en';

                        if ($currentLocale === $defaultLocale) {
                            $affiliateRulesContent = \Botble\AffiliatePro\Facades\AffiliateHelper::getSetting('rules_content');
                        } else {
                            $affiliateRulesContent = \Botble\AffiliatePro\Facades\AffiliateHelper::getSetting("rules_content_{$currentLocale}");

                            if (empty($affiliateRulesContent)) {
                                $affiliateRulesContent = \Botble\AffiliatePro\Facades\AffiliateHelper::getSetting('rules_content');
                            }
                        }
                    }
                @endphp

                @if($affiliateRulesContent)
                    <div class="affiliate-rules-section mb-4">
                        <h5 class="rules-title">
                            <x-core::icon name="ti ti-file-text" class="me-2" />
                            {{ trans('plugins/affiliate-pro::affiliate.rules') }}
                        </h5>
                        <div class="rules-content">
                            {!! BaseHelper::clean($affiliateRulesContent) !!}
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('affiliate-pro.register.post') }}" class="affiliate-registration-form-inner">
                    @csrf

                    <div class="terms-agreement-section">
                        <div class="form-check-wrapper">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" value="1" {{ old('terms') ? 'checked' : '' }}>
                                <label class="form-check-label" for="terms">
                                    {{ trans('plugins/affiliate-pro::affiliate.terms_agreement') }}
                                </label>
                            </div>
                            @if ($errors->has('terms'))
                                <div class="error-message">
                                    <x-core::icon name="ti ti-alert-circle" class="me-1" />
                                    <small>{{ $errors->first('terms') }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <x-core::icon name="ti ti-send" class="me-2" />
                            {{ trans('plugins/affiliate-pro::affiliate.apply') }}
                            <span class="btn-loading d-none">
                                <x-core::icon name="ti ti-loader" class="spin" />
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JavaScript for enhanced interactions --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.affiliate-registration-form-inner');
        const submitBtn = form.querySelector('button[type="submit"]');
        const loadingSpan = submitBtn.querySelector('.btn-loading');
        const termsCheckbox = document.getElementById('terms');

        // Handle form submission
        form.addEventListener('submit', function(e) {
            if (!termsCheckbox.checked) {
                e.preventDefault();

                // Highlight the terms section
                const termsSection = document.querySelector('.terms-agreement-section');
                termsSection.style.borderColor = '#dc3545';
                termsSection.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';

                // Show error message if not already shown
                let errorMsg = document.querySelector('.terms-error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'terms-error-message error-message mt-2';
                    errorMsg.innerHTML = '<small>⚠️ {{ trans("plugins/affiliate-pro::affiliate.terms_required") }}</small>';
                    document.querySelector('.form-check-wrapper').appendChild(errorMsg);
                }

                // Scroll to terms section
                termsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });

                return false;
            }

            // Show loading state
            submitBtn.disabled = true;
            loadingSpan.classList.remove('d-none');
            submitBtn.querySelector('.me-2').style.display = 'none';
        });

        // Handle terms checkbox change
        termsCheckbox.addEventListener('change', function() {
            const termsSection = document.querySelector('.terms-agreement-section');
            const errorMsg = document.querySelector('.terms-error-message');

            if (this.checked) {
                termsSection.style.borderColor = '';
                termsSection.style.backgroundColor = '';
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });

        // Add hover effects to benefit cards
        const benefitCards = document.querySelectorAll('.benefit-card');
        benefitCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(-5px)';
            });
        });
    });
    </script>
@endsection
