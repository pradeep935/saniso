@php
    $layoutType = $shortcode->layout_type ?: 'slider';
    $columnsDesktop = $shortcode->columns_desktop ?: 3;
    $columnsTablet = $shortcode->columns_tablet ?: 2;
    $columnsMobile = $shortcode->columns_mobile ?: 1;
    $backgroundColor = $shortcode->background_color ?: '#ffffff';
    $textColor = $shortcode->text_color ?: '#333333';
    $textAlign = $shortcode->text_align ?: 'center';
    $titleFontSize = $shortcode->title_font_size ?: '24px';
    $descriptionFontSize = $shortcode->description_font_size ?: '16px';
    $isAutoplay = $shortcode->is_autoplay === 'yes';
    $autoplaySpeed = $shortcode->autoplay_speed ?: 3000;
    $showArrows = $shortcode->show_arrows !== 'no';
    $showDots = $shortcode->show_dots !== 'no';
    
    $uniqueId = 'video-block-' . uniqid();
@endphp

<div class="advanced-video-block py-5" id="{{ $uniqueId }}" style="background-color: {{ $backgroundColor }}; color: {{ $textColor }};">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                @if($title || $description)
                    <div class="video-block-header mb-4" style="text-align: {{ $textAlign }};">
                        @if($title)
                            <h2 class="video-block-title mb-3" style="font-size: {{ $titleFontSize }}; color: {{ $textColor }};">
                                {{ $title }}
                            </h2>
                        @endif
                        @if($description)
                            <div class="video-block-description" style="font-size: {{ $descriptionFontSize }}; color: {{ $textColor }};">
                                {!! BaseHelper::clean($description) !!}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="video-block-content">
                    @if($layoutType === 'slider')
                        <div class="video-slider-wrapper arrows-top-right">
                            <div class="video-slider slick-slides-carousel"
                                 data-slick="{{ json_encode([
                                    'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                                    'appendArrows' => '.arrows-wrapper',
                                    'arrows' => $showArrows,
                                    'dots' => $showDots,
                                    'autoplay' => $isAutoplay,
                                    'infinite' => true,
                                    'autoplaySpeed' => (int)$autoplaySpeed,
                                    'speed' => 800,
                                    'slidesToShow' => $columnsDesktop,
                                    'slidesToScroll' => 1,
                                    'swipeToSlide' => true,
                                    'lazyLoad' => 'ondemand',
                                    'adaptiveHeight' => false,
                                    'responsive' => [
                                        [
                                            'breakpoint' => 1024,
                                            'settings' => [
                                                'slidesToShow' => $columnsTablet,
                                            ],
                                        ],
                                        [
                                            'breakpoint' => 767,
                                            'settings' => [
                                                'arrows' => $showArrows,
                                                'dots' => $showDots,
                                                'slidesToShow' => $columnsMobile,
                                                'slidesToScroll' => 1,
                                                'centerMode' => false,
                                            ],
                                        ],
                                    ],
                                ]) }}">
                                @foreach($videos as $video)
                                    <div class="video-slide-item">
                                        @include('theme.saniso::partials.shortcodes.video-item', ['video' => $video, 'textAlign' => $textAlign, 'textColor' => $textColor])
                                    </div>
                                @endforeach
                            </div>
                            @if($showArrows)
                                <div class="arrows-wrapper"></div>
                            @endif
                        </div>
                    @else
                        <div class="video-grid-wrapper">
                            <div class="video-grid" 
                                 style="--cols-desktop: {{ $columnsDesktop }}; --cols-tablet: {{ $columnsTablet }}; --cols-mobile: {{ $columnsMobile }};">
                                @foreach($videos as $video)
                                    <div class="video-grid-item">
                                        @include('theme.saniso::partials.shortcodes.video-item', ['video' => $video, 'textAlign' => $textAlign, 'textColor' => $textColor])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Video Modal --}}
<div id="videoModal" class="video-modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="videoModalLabel">
    <div class="video-modal-overlay" onclick="closeVideoModal()"></div>
    <div class="video-modal-container">
        <div class="video-modal-header">
            <h3 class="video-modal-title" id="videoModalLabel"></h3>
            <button class="video-modal-close" onclick="closeVideoModal()" aria-label="Close video modal">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <div class="video-modal-body"></div>
    </div>
</div>

{{-- Load CSS and JavaScript at the end --}}
<link rel="stylesheet" href="{{ asset('themes/saniso/css/advanced-video-block.css') }}?v={{ time() }}">
<script src="{{ asset('themes/saniso/js/advanced-video-block.js') }}?v={{ time() }}"></script>

{{-- Critical CSS for immediate styling --}}
<style>
.video-slider-wrapper {
    position: relative;
}

.video-slider .slick-track {
    display: flex !important;
    align-items: stretch;
}

.video-slider .slick-slide {
    height: auto;
    display: flex !important;
    align-items: stretch;
}

.video-slider .slick-slide > div {
    padding: 0 10px;
    width: 100%;
    display: flex;
    align-items: stretch;
}

.video-slide-item {
    display: flex !important;
    align-items: stretch;
    height: 100% !important;
}

.video-item {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    height: 100%;
    display: flex;
    flex-direction: column;
    width: 100%;
}

.video-thumbnail-wrapper {
    position: relative;
    background: #000;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
    aspect-ratio: 16/9;
    flex-shrink: 0;
}

.video-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.95);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    z-index: 10;
}

.video-play-button::after {
    content: '';
    width: 0;
    height: 0;
    border-left: 15px solid #333;
    border-top: 10px solid transparent;
    border-bottom: 10px solid transparent;
    margin-left: 3px;
}

.video-content {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    text-align: left;
}

.video-title {
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
    flex-shrink: 0;
}

.video-description {
    font-size: 0.9rem;
    line-height: 1.5;
    opacity: 0.8;
    margin: 0;
    flex-grow: 1;
}

.video-type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 5px 8px;
    border-radius: 15px;
    font-size: 0.8rem;
    z-index: 2;
}

.video-player-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    z-index: 5;
}

.video-player-container iframe,
.video-player-container video {
    width: 100%;
    height: 100%;
    border: none;
}

/* Grid layout equal height */
.video-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(var(--cols-desktop, 3), 1fr);
    align-items: stretch;
}

.video-grid .video-grid-item {
    display: flex;
    align-items: stretch;
}

.video-grid .video-item {
    margin: 0;
    width: 100%;
}
</style>

{{-- Initialize slider after loading --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Stop any auto-playing videos first
        setTimeout(function() {
            if (typeof stopAllVideos === 'function') {
                stopAllVideos();
            }
        }, 500);
        
        // Wait for jQuery and Slick to be available
        function initializeVideoSliders() {
            if (typeof jQuery !== 'undefined' && jQuery.fn.slick) {
                jQuery('.video-slider').each(function() {
                    const $slider = jQuery(this);
                    const slickData = $slider.data('slick');
                    
                    if (slickData && !$slider.hasClass('slick-initialized')) {
                        try {
                            $slider.slick(slickData);
                        } catch (e) {
                            console.error('Slick slider error:', e);
                        }
                    }
                });
            } else {
                // Retry after 100ms if jQuery/Slick not ready
                setTimeout(initializeVideoSliders, 100);
            }
        }
        
        // Start initialization
        setTimeout(initializeVideoSliders, 100);
    });
</script>
