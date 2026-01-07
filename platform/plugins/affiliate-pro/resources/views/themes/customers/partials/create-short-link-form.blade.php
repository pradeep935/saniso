{{--
    Create Short Link Form Partial

    This partial can be included in multiple pages to provide a consistent
    short link creation form across the affiliate dashboard.

    Required variables:
    - $affiliate: The affiliate model

    Optional variables:
    - $formId: Custom form ID (default: 'create-short-link-form')
    - $showCard: Whether to wrap in a card (default: true)
    - $cardTitle: Custom card title (default: trans('plugins/affiliate-pro::affiliate.create_short_link'))
    - $showManageLink: Whether to show manage links button (default: false)
--}}

@php
    $formId = $formId ?? 'create-short-link-form';
    $showCard = $showCard ?? true;
    $cardTitle = $cardTitle ?? trans('plugins/affiliate-pro::affiliate.create_short_link');
    $showManageLink = $showManageLink ?? false;
@endphp

@if($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
    @if($showCard)
        <div class="bb-customer-card create-short-link-card">
            <div class="bb-customer-card-header">
                <div class="bb-customer-card-title">
                    <span class="value">{{ $cardTitle }}</span>
                </div>
                <div class="bb-customer-card-status">
                    @if($showManageLink)
                        <a href="{{ route('affiliate-pro.short-links') }}" class="btn btn-sm btn-primary">
                            {{ trans('plugins/affiliate-pro::affiliate.manage_links') }}
                        </a>
                    @else
                        <x-core::icon name="ti ti-plus" class="text-primary" />
                    @endif
                </div>
            </div>
            <div class="bb-customer-card-body">
    @endif

    <form id="{{ $formId }}" action="{{ route('affiliate-pro.short-links.store') }}" method="POST" class="create-short-link-form">
        @csrf

        {{-- Dashboard Layout: Horizontal form --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="{{ $formId }}-link-type" class="form-label">
                    <x-core::icon name="ti ti-category" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.link_type') }}
                </label>
                <select id="{{ $formId }}-link-type" name="link_type" class="form-select link-type-select">
                    <option value="custom">{{ trans('plugins/affiliate-pro::affiliate.custom_url') }}</option>
                    <option value="product">{{ trans('plugins/affiliate-pro::affiliate.product') }}</option>
                    <option value="homepage">{{ trans('plugins/affiliate-pro::affiliate.homepage') }}</option>
                </select>
            </div>

            <div class="col-md-8 mb-3 product-select-container" style="display: none;">
                <label for="{{ $formId }}-product-id" class="form-label">
                    <x-core::icon name="ti ti-package" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.select_product') }}
                </label>
                <select id="{{ $formId }}-product-id" name="product_id" class="form-select product-select" data-ajax-url="{{ route('affiliate-pro.ajax.search-products') }}">
                    <option value="">{{ trans('plugins/affiliate-pro::affiliate.search_products_placeholder') }}</option>
                </select>
                <div class="form-text">
                    <x-core::icon name="ti ti-info-circle" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.type_to_search_products') }}
                </div>
            </div>

            <div class="col-md-8 mb-3 custom-url-container">
                <label for="{{ $formId }}-destination-url" class="form-label">
                    <x-core::icon name="ti ti-world" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.destination_url') }}
                </label>
                <input type="url" id="{{ $formId }}-destination-url" name="destination_url" class="form-control" placeholder="https://example.com/page?param=value">
                <div class="form-text">
                    <x-core::icon name="ti ti-info-circle" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.affiliate_code_auto_added') }}
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <label for="{{ $formId }}-link-title" class="form-label">
                    <x-core::icon name="ti ti-tag" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.link_title') }}
                    <small class="text-muted">({{ trans('plugins/affiliate-pro::affiliate.optional') }})</small>
                </label>
                <input type="text" id="{{ $formId }}-link-title" name="title" class="form-control" placeholder="{{ trans('plugins/affiliate-pro::affiliate.my_short_link') }}">
                <div class="form-text">
                    <x-core::icon name="ti ti-info-circle" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.descriptive_title_help') }}
                </div>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary create-short-link-btn">
                    <x-core::icon name="ti ti-plus" class="me-1" />
                    {{ trans('plugins/affiliate-pro::affiliate.create_short_link') }}
                </button>
            </div>
        </div>
    </form>

    {{-- Short Link Result Display --}}
    <div id="{{ $formId }}-result" class="short-link-result mt-4" style="display: none;">
        <div class="alert alert-success alert-permanent">
            <div class="d-flex align-items-center mb-3">
                <x-core::icon name="ti ti-circle-check" class="text-success me-2" />
                <strong>{{ trans('plugins/affiliate-pro::affiliate.short_link_created') }}</strong>
            </div>
            <div class="mb-3">
                <label for="{{ $formId }}-new-short-url" class="form-label">{{ trans('plugins/affiliate-pro::affiliate.your_short_link') }}:</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="{{ $formId }}-new-short-url" readonly>
                    <button class="btn btn-outline-primary" type="button"
                            data-bb-toggle="clipboard"
                            data-clipboard-target="#{{ $formId }}-new-short-url"
                            data-clipboard-message="{{ trans('plugins/affiliate-pro::affiliate.copied_to_clipboard') }}"
                            title="{{ trans('plugins/affiliate-pro::affiliate.copy_url') }}">
                        <x-core::icon name="ti ti-copy" data-clipboard-icon="true" />
                        <x-core::icon name="ti ti-check" data-clipboard-success-icon="true" class="text-success d-none" />
                    </button>
                </div>
            </div>
            <div class="form-text">
                <x-core::icon name="ti ti-info-circle" class="me-1" />
                {{ trans('plugins/affiliate-pro::affiliate.share_link_earn_commission') }}
            </div>
        </div>
    </div>

    @if($showCard)
            </div>
        </div>
    @endif
@endif

{{-- Form-specific JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('{{ $formId }}');
    if (!form) return;

    const linkTypeSelect = form.querySelector('.link-type-select');
    const customUrlContainer = form.querySelector('.custom-url-container');
    const productSelectContainer = form.querySelector('.product-select-container');
    const productSelect = form.querySelector('.product-select');

    // Initialize Select2 for product selection with Ajax
    if (productSelect && typeof $ !== 'undefined' && $.fn.select2) {
        $(productSelect).select2({
            ajax: {
                url: productSelect.dataset.ajaxUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results || [],
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    };
                },
                cache: true,
                transport: function (params, success, failure) {
                    var $request = $.ajax(params);
                    $request.then(success);
                    $request.fail(failure);
                    return $request;
                }
            },
            placeholder: '{{ trans('plugins/affiliate-pro::affiliate.search_products_placeholder') }}',
            minimumInputLength: 0,
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: false,
            dropdownCssClass: 'select2-dropdown-constrained',
            escapeMarkup: function (markup) {
                return markup;
            },
            templateResult: function(product) {
                if (product.loading) {
                    return $('<div class="select2-result-loading">{{ trans('plugins/affiliate-pro::affiliate.loading_products') }}...</div>');
                }

                if (!product.id) {
                    return $('<div>' + (product.text || product.name || 'Unknown') + '</div>');
                }

                return $(
                    '<div class="select2-result-product">' +
                    '<div class="select2-result-product__title">' + (product.name || product.text) + '</div>' +
                    '<div class="select2-result-product__price">' + (product.formatted_price || '') + '</div>' +
                    '</div>'
                );
            },
            templateSelection: function(product) {
                if (product.id && product.name) {
                    return product.name;
                }
                return product.text || '{{ trans('plugins/affiliate-pro::affiliate.search_products_placeholder') }}';
            },
            language: {
                noResults: function() {
                    return '{{ trans('plugins/affiliate-pro::affiliate.no_products_found') }}';
                },
                searching: function() {
                    return '{{ trans('plugins/affiliate-pro::affiliate.searching_products') }}...';
                },
                loadingMore: function() {
                    return '{{ trans('plugins/affiliate-pro::affiliate.loading_more_products') }}...';
                },
                inputTooShort: function() {
                    return '{{ trans('plugins/affiliate-pro::affiliate.type_to_search_products') }}';
                }
            }
        });

        // Handle selection events
        $(productSelect).on('select2:select', function (e) {
            var data = e.params.data;
            console.log('Product selected:', data);
        });

        $(productSelect).on('select2:unselect', function (e) {
            console.log('Product unselected');
        });
    } else if (productSelect) {
        // Fallback styling for basic select if Select2 is not available
        productSelect.style.backgroundColor = '#ffffff';
        productSelect.style.border = '1px solid #ced4da';
        productSelect.style.borderRadius = '0.375rem';
        productSelect.style.padding = '8px 12px';
        productSelect.style.fontSize = '14px';
        productSelect.style.color = '#495057';
        productSelect.style.minHeight = '38px';

        console.warn('Select2 not available, using basic select styling');
    }

    // Handle link type changes
    if (linkTypeSelect) {
        linkTypeSelect.addEventListener('change', function() {
            const linkType = this.value;

            if (linkType === 'product') {
                if (customUrlContainer) customUrlContainer.style.display = 'none';
                if (productSelectContainer) productSelectContainer.style.display = 'block';
            } else if (linkType === 'homepage') {
                if (customUrlContainer) customUrlContainer.style.display = 'none';
                if (productSelectContainer) productSelectContainer.style.display = 'none';
            } else {
                if (customUrlContainer) customUrlContainer.style.display = 'block';
                if (productSelectContainer) productSelectContainer.style.display = 'none';
            }
        });
    }

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = form.querySelector('.create-short-link-btn');
        const originalText = submitBtn.innerHTML;
        const resultDiv = document.getElementById('{{ $formId }}-result');
        const newShortUrlInput = document.getElementById('{{ $formId }}-new-short-url');

        // Show loading state
        submitBtn.innerHTML = '<i class="ti ti-loader"></i> ' + (window.affiliateTranslations?.creating || 'Creating...');
        submitBtn.disabled = true;

        // Prepare form data
        const formData = new FormData(form);

        // Submit form
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.message || 'An error occurred');
            }

            // Show success result
            if (newShortUrlInput && data.data?.short_url) {
                newShortUrlInput.value = data.data.short_url;
                if (resultDiv) {
                    resultDiv.style.display = 'block';
                    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }

            // Reset form
            form.reset();
            if (linkTypeSelect) linkTypeSelect.dispatchEvent(new Event('change'));

            // Trigger custom event for other scripts to listen to
            window.dispatchEvent(new CustomEvent('shortLinkCreated', {
                detail: { shortLink: data.data, formId: '{{ $formId }}' }
            }));
        })
        .catch(error => {
            console.error('Error:', error);
            alert(window.affiliateTranslations?.errorOccurred || 'An error occurred. Please try again.');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>
