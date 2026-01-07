@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::affiliate.promotional_materials'))

@section('content')
    <div class="affiliate-dashboard">
        <div class="affiliate-card">
            <div class="affiliate-card-header">
                <div class="affiliate-card-title">
                    {{ trans('plugins/affiliate-pro::affiliate.promotional_materials') }}
                </div>
            </div>
            <div class="affiliate-card-body">
                <p class="mb-4">{{ trans('plugins/affiliate-pro::affiliate.use_promotional_materials') }}</p>

                <div class="affiliate-card-list">
                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                {{ trans('plugins/affiliate-pro::affiliate.your_affiliate_link') }}
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            <div class="affiliate-link-box">
                                <div class="affiliate-link-title">{{ trans('plugins/affiliate-pro::affiliate.share_link') }}</div>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ url('?aff=' . $affiliate->affiliate_code) }}" id="affiliate-link" readonly>
                                    <button class="btn btn-primary" type="button" data-copy-affiliate-link>
                                        <x-core::icon name="ti ti-copy" /> {{ trans('plugins/affiliate-pro::affiliate.copy') }}
                                    </button>
                                </div>
                            </div>

                            <div class="social-share">
                                <div class="social-share-title">{{ trans('plugins/affiliate-pro::affiliate.share_on_social_media') }}</div>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('?aff=' . $affiliate->affiliate_code)) }}" target="_blank" class="facebook" title="Share on Facebook">
                                    <x-core::icon name="ti ti-brand-facebook" />
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url('?aff=' . $affiliate->affiliate_code)) }}&text={{ urlencode('Check out this awesome store!') }}" target="_blank" class="twitter" title="Share on Twitter">
                                    <x-core::icon name="ti ti-brand-twitter" />
                                </a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url('?aff=' . $affiliate->affiliate_code)) }}" target="_blank" class="linkedin" title="Share on LinkedIn">
                                    <x-core::icon name="ti ti-brand-linkedin" />
                                </a>
                                <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(url('?aff=' . $affiliate->affiliate_code)) }}&description={{ urlencode('Check out this awesome store!') }}" target="_blank" class="pinterest" title="Share on Pinterest">
                                    <x-core::icon name="ti ti-brand-pinterest" />
                                </a>
                                <a href="https://api.whatsapp.com/send?text={{ urlencode('Check out this awesome store! ' . url('?aff=' . $affiliate->affiliate_code)) }}" target="_blank" class="whatsapp" title="Share on WhatsApp">
                                    <x-core::icon name="ti ti-brand-whatsapp" />
                                </a>
                                <a href="https://t.me/share/url?url={{ urlencode(url('?aff=' . $affiliate->affiliate_code)) }}&text={{ urlencode('Check out this awesome store!') }}" target="_blank" class="telegram" title="Share on Telegram">
                                    <x-core::icon name="ti ti-brand-telegram" />
                                </a>
                                <a href="mailto:?subject={{ urlencode('Check out this awesome store!') }}&body={{ urlencode('I thought you might be interested in this: ' . url('?aff=' . $affiliate->affiliate_code)) }}" class="email" title="Share via Email">
                                    <x-core::icon name="ti ti-mail" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                {{ trans('plugins/affiliate-pro::affiliate.qr_code') }}
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            <div class="qr-code-container">
                                <div class="qr-code-image">
                                    <img src="data:image/svg+xml;base64,{{ app(\Botble\AffiliatePro\Services\QrCodeService::class)->getAffiliateQrCode($affiliate) }}"
                                        alt="QR Code" class="img-fluid">
                                </div>
                                <div class="qr-code-actions">
                                    <a href="data:image/svg+xml;base64,{{ app(\Botble\AffiliatePro\Services\QrCodeService::class)->getAffiliateQrCode($affiliate) }}" download="affiliate-qr-code.svg" class="btn btn-primary">
                                        <x-core::icon name="ti ti-download" /> {{ trans('plugins/affiliate-pro::affiliate.download_qr_code') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="affiliate-card mt-4">
                    <div class="affiliate-card-header">
                        <div class="affiliate-card-title">
                            {{ trans('plugins/affiliate-pro::affiliate.promotional_banners') }}
                        </div>
                    </div>
                    <div class="affiliate-card-body">
                        @if(count($banners) > 0)
                            <div class="row">
                                @foreach($banners as $banner)
                                    @continue(! $banner['image'])

                                    <div class="col-md-6 mb-4">
                                        <div class="affiliate-card">
                                            <div class="affiliate-card-header">
                                                <div class="affiliate-card-title">
                                                    {{ $banner['name'] }}
                                                </div>
                                            </div>
                                            <div class="affiliate-card-body">
                                                <div class="mb-3 text-center">
                                                    <img src="{{ RvMedia::getImageUrl($banner['image']) }}" alt="{{ $banner['name'] }}" class="img-fluid banner-image" style="max-height: 200px;">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="banner-html-{{ $loop->index }}">{{ trans('plugins/affiliate-pro::affiliate.banner_html_code') }}</label>
                                                    <textarea class="form-control" id="banner-html-{{ $loop->index }}" rows="3" readonly>{{ $banner['html'] }}</textarea>
                                                </div>
                                                <button class="btn btn-sm btn-primary" type="button" data-copy-banner-html="{{ $loop->index }}">
                                                    <x-core::icon name="ti ti-copy" /> {{ trans('plugins/affiliate-pro::affiliate.copy_html') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_banners_available') }}</p>
                                <small class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.contact_admin_for_banners') }}</small>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="affiliate-card mt-4">
                    <div class="affiliate-card-header">
                        <div class="affiliate-card-title">
                            {{ trans('plugins/affiliate-pro::affiliate.text_links') }}
                        </div>
                    </div>
                    <div class="affiliate-card-body">
                        <p class="mb-4">{{ trans('plugins/affiliate-pro::affiliate.text_links_description') }}</p>

                        <div class="affiliate-card-list">
                            <div class="affiliate-card">
                                <div class="affiliate-card-header">
                                    <div class="affiliate-card-title">
                                        {{ trans('plugins/affiliate-pro::affiliate.general_link') }}
                                    </div>
                                </div>
                                <div class="affiliate-card-body">
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<a href='{{ url('?aff=' . $affiliate->affiliate_code) }}'>{{ trans('plugins/affiliate-pro::affiliate.visit_our_store') }}</a>" id="text-link-1" readonly>
                                        <button class="btn btn-primary" type="button" data-copy-text-link="1">
                                            <x-core::icon name="ti ti-copy" /> {{ trans('plugins/affiliate-pro::affiliate.copy') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="affiliate-card">
                                <div class="affiliate-card-header">
                                    <div class="affiliate-card-title">
                                        {{ trans('plugins/affiliate-pro::affiliate.special_offer_link') }}
                                    </div>
                                </div>
                                <div class="affiliate-card-body">
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<a href='{{ url('?aff=' . $affiliate->affiliate_code) }}'>{{ trans('plugins/affiliate-pro::affiliate.check_special_offers') }}</a>" id="text-link-2" readonly>
                                        <button class="btn btn-primary" type="button" data-copy-text-link="2">
                                            <x-core::icon name="ti ti-copy" /> {{ trans('plugins/affiliate-pro::affiliate.copy') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript translations --}}
    <script>
    window.affiliateTranslations = window.affiliateTranslations || {};
    window.affiliateTranslations = {
        copiedToClipboard: '{{ trans("plugins/affiliate-pro::affiliate.copied_to_clipboard") }}',
        couponCopied: '{{ trans("plugins/affiliate-pro::affiliate.coupon_copied") }}',
        copyFailed: '{{ trans("plugins/affiliate-pro::affiliate.copy_failed") }}',
        htmlCopied: '{{ trans("plugins/affiliate-pro::affiliate.html_copied") }}'
    };
    </script>
@endsection


