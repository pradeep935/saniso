<div class="widget-featured-brands py-5">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <div class="col-auto">
                        <h2 class="mb-0 py-2">{{ $shortcode->title }}</h2>
                        @if ($shortcode->subtitle)
                            <p class="mb-0">{{ $shortcode->subtitle }}</p>
                        @endif
                    </div>
                </div>
                <div class="featured-brands__body arrows-top-right">

                    <style>
                    /* Target ONLY the featured brands slick track */
.featured-brands__body .slick-track {
    display: flex !important;
    align-items: stretch !important;
}

.featured-brands__body .slick-slide {
    display: flex !important;
    height: auto;
}

.featured-brands__body .featured-brand-item {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    justify-content: space-between;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08), 0 1.5px 4px rgba(0,0,0,0.04);
    border: 0.1rem solid #ddd;
    border-radius: 0.4rem;
    background: #fff;
    transition: box-shadow 0.2s, border-color 0.2s;
    margin: 0 8px;
}

.featured-brands__body .brand-item-body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    padding: 0 !important;
    margin: 0 !important;
}

.featured-brands__body .brand__text {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    flex-grow: 1;
    padding: 1rem;
    font-size: 12px;
}

.featured-brands__body .brand__thumb {
    flex-shrink: 0;
}

.featured-brands__body .brand__desc-wrapper {
    margin-top: 0.5rem;
    text-align: left;
}

.featured-brands__body .img-fluid-eq .img-fluid-eq__wrap img {
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    height: auto;
}

                    </style>

                    <div class="featured-brands-body slick-slides-carousel"
                         data-slick="{{ json_encode([
                            'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                            'appendArrows' => '.arrows-wrapper',
                            'arrows' => true,
                            'dots' => false,
                            'autoplay' => $shortcode->is_autoplay == 'yes',
                            'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
                            'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 3000,
                            'speed' => 800,
                            'slidesToShow' => $shortcode->slides_to_show ?: 4,
                            'slidesToScroll' => 1,
                            'swipeToSlide' => true,
                            'responsive' => [
                                [
                                    'breakpoint' => 1024,
                                    'settings' => [
                                        'slidesToShow' => max(1, ($shortcode->slides_to_show ?: 4) - 2),
                                    ],
                                ],
                                [
                                    'breakpoint' => 767,
                                    'settings' => [
                                        'arrows' => true,
                                        'dots' => false,
                                        'slidesToShow' => 1,
                                        'slidesToScroll' => 1,
                                        'centerMode' => false,
                                    ],
                                ],
                            ],
                        ]) }}">
                        @foreach ($brands as $brand)
                            <div class="featured-brand-item">
                                <div class="brand-item-body mx-2 py-4 px-2">
                                    <a href="{{ $brand->url }}">
                                        <div class="brand__thumb mb-3 img-fluid-eq">
                                            <div class="img-fluid-eq__dummy"></div>
                                            <div class="img-fluid-eq__wrap">
                                                <img class="mx-auto"
                                                     src="{{ RvMedia::getImageUrl($brand->logo, null, false, RvMedia::getDefaultImage()) }}"
                                                     alt="{{ $brand->name }}">
                                            </div>
                                        </div>
                                    </a>
                                    <div @class(['brand__text', 'text-center' => ! $brand->description])>
                                        <a href="{{ $brand->url }}">
                                            <span class="h6 fw-bold text-secondary text-uppercase brand__name">
                                                {{ $brand->name }}
                                            </span>
                                        </a>
                                        @if ($brand->description)
                                            <div class="brand__desc-wrapper">
                                                {!! BaseHelper::clean($brand->description, null) !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="arrows-wrapper"></div>

                </div>
            </div>
        </div>
    </div>
</div>
