@php
    use Botble\Ecommerce\Models\ProductCategory;
    
    // Recursive function to load categories with UNLIMITED depth
    function loadCategoriesWithAllChildren() {
        return ProductCategory::query()
            ->where('status', 'published')
            ->where('parent_id', 0)
            ->with(['children' => function($query) {
                $query->where('status', 'published')
                      ->orderBy('order')
                      ->with(['children' => function($subQuery) {
                          $subQuery->where('status', 'published')
                                    ->orderBy('order')
                                    ->with(['children' => function($subSubQuery) {
                                        $subSubQuery->where('status', 'published')
                                                    ->orderBy('order')
                                                    ->with(['children' => function($level4Query) {
                                                        $level4Query->where('status', 'published')
                                                                    ->orderBy('order')
                                                                    ->with(['children' => function($level5Query) {
                                                                        $level5Query->where('status', 'published')
                                                                                    ->orderBy('order')
                                                                                    ->with(['children']);
                                                                    }]);
                                                    }]);
                                    }]);
                      }]);
            }])
            ->orderBy('order')
            ->get();
    }
    
    // Get all categories in flat structure for easy access
    function getAllCategoriesFlat() {
        return ProductCategory::query()
            ->where('status', 'published')
            ->orderBy('order')
            ->get()
            ->keyBy('id');
    }
    
    // Recursive function to generate slides for unlimited depth
    function generateCategorySlides($categories, $allCategoriesFlat, $parentId = null, $level = 0) {
        $slides = '';
        
        foreach ($categories as $category) {
            if ($category->children && $category->children->isNotEmpty()) {
                // Determine parent slide ID
                $parentSlideId = $parentId ? "slide-{$parentId}" : 'slide-main';
                
                // Create slide for this category
                $slides .= '<div class="category-slide" id="slide-' . $category->id . '">';
                $slides .= '<div class="slide-header">';
                $slides .= '<button class="back-btn" data-slide-to="' . $parentSlideId . '">← ' . __('Back') . '</button>';
                $slides .= '<h5>' . e($category->name) . ' <small>(Level ' . ($level + 1) . ')</small></h5>';
                $slides .= '</div>';
                
                $slides .= '<ul class="bb-product-filter-items">';
                
                // View All option
                $slides .= '<li class="bb-product-filter-item">';
                $slides .= '<a href="' . $category->url . '" class="bb-product-filter-link view-all-link" data-id="' . $category->id . '">';
                $slides .= '<span>' . __('View All in :category', ['category' => $category->name]) . '</span>';
                $slides .= '</a>';
                $slides .= '</li>';
                
                // Child categories
                foreach ($category->children as $child) {
                    $slides .= '<li class="bb-product-filter-item">';
                    $slides .= '<div class="category-item-row">';
                    
                    // Child category link
                    $slides .= '<a href="' . $child->url . '" class="bb-product-filter-link" data-id="' . $child->id . '">';
                    $slides .= '<span>' . e($child->name) . '</span>';
                    $slides .= '</a>';
                    
                    // Arrow button if child has children
                    if ($child->children && $child->children->isNotEmpty()) {
                        $slides .= '<button type="button" class="slide-nav-btn" data-slide-to="slide-' . $child->id . '">';
                        $slides .= '<span class="slide-arrow">→</span>';
                        $slides .= '</button>';
                    }
                    
                    $slides .= '</div>';
                    $slides .= '</li>';
                }
                
                $slides .= '</ul>';
                $slides .= '</div>';
                
                // Recursively generate slides for children
                $slides .= generateCategorySlides($category->children, $allCategoriesFlat, $category->id, $level + 1);
            }
        }
        
        return $slides;
    }
    
    // Load all categories
    $allCategories = loadCategoriesWithAllChildren();
    $allCategoriesFlat = getAllCategoriesFlat();
@endphp

@if ($allCategories->isNotEmpty())
    <div class="bb-product-filter category-slider" id="categoryFilterPanel">
        <h4 class="bb-product-filter-title">{{ __('Categories') }}</h4>

        <div class="bb-product-filter-content">
            <div class="category-slider-wrapper">
                
                {{-- Slide 1: Main Categories --}}
                <div class="category-slide active" id="slide-main">
                    <ul class="bb-product-filter-items">
                        {{-- All Categories Link --}}
                        <li class="bb-product-filter-item">
                            <a href="{{ route('public.products') }}" class="bb-product-filter-link @if(request()->route()->getName() === 'public.products' && empty(request('categories'))) active @endif">
                                <span>{{ __('All Categories') }}</span>
                            </a>
                        </li>
                        
                        {{-- Main Categories --}}
                        @foreach ($allCategories as $category)
                            <li class="bb-product-filter-item">
                                <div class="category-item-row">
                                    {{-- Category Name Link (goes to products) --}}
                                    <a href="{{ $category->url }}" class="bb-product-filter-link @if(request()->url() === $category->url) active @endif" data-id="{{ $category->id }}">
                                        <span>{{ $category->name }}</span>
                                    </a>
                                    
                                    {{-- Arrow Icon (opens slider) --}}
                                    @if ($category->children && $category->children->isNotEmpty())
                                        <button type="button" class="slide-nav-btn" data-slide-to="slide-{{ $category->id }}">
                                            <span class="slide-arrow">→</span>
                                        </button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Generate ALL slides recursively for UNLIMITED depth --}}
                {!! generateCategorySlides($allCategories, $allCategoriesFlat, null, 0) !!}

            </div>
        </div>
    </div>

    <style>
    /* Category Slider Styles */
    .category-slider-wrapper {
        position: relative;
        overflow: hidden;
        min-height: 200px;
    }
    
    .category-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        opacity: 0;
        visibility: hidden;
    }
    
    .category-slide.active {
        transform: translateX(0);
        opacity: 1;
        visibility: visible;
        position: relative;
    }
    
    .slide-header {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .back-btn {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        font-size: 14px;
        padding: 5px 10px;
        border-radius: 4px;
        transition: background 0.2s;
    }
    
    .back-btn:hover {
        background: #f5f5f5;
        color: #333;
    }
    
    .slide-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    
    .slide-header h5 small {
        color: #666;
        font-weight: normal;
        font-size: 12px;
        margin-left: 8px;
    }
    
    /* Category Item Row - separates name from arrow */
    .category-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 8px;
        padding: 4px 0;
    }
    
    .category-item-row .bb-product-filter-link {
        flex: 1;
        display: block;
    }
    
    /* Ensure the item row doesn't interfere with button clicks */
    .bb-product-filter-item {
        position: relative;
    }
    
    /* Slide Navigation Button (arrow icon) */
    .slide-nav-btn {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 4px;
        transition: all 0.2s;
        flex-shrink: 0;
        min-width: 36px;
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        position: relative;
    }
    
    .slide-nav-btn:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        transform: scale(1.05);
    }
    
    .slide-nav-btn:active {
        background: #dee2e6;
        transform: scale(0.95);
    }
    
    .slide-arrow {
        color: #495057;
        font-weight: bold;
        font-size: 16px;
        line-height: 1;
        pointer-events: none;
    }
    
    .slide-nav-btn:hover .slide-arrow {
        color: #212529;
    }
    
    .view-all-link {
        background: #f8f9fa !important;
        font-weight: 600 !important;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initCategorySlider();
    });

    function initCategorySlider() {
        console.log('🎯 Initializing Category Slider...');
        
        const sliderWrapper = document.querySelector('.category-slider-wrapper');
        if (!sliderWrapper) return;
        
        const slides = sliderWrapper.querySelectorAll('.category-slide');
        const slideNavButtons = sliderWrapper.querySelectorAll('.slide-nav-btn');
        const backButtons = sliderWrapper.querySelectorAll('.back-btn');
        
        // Handle slide navigation (arrow button clicks)
        console.log('Found slide navigation buttons:', slideNavButtons.length);
        
        slideNavButtons.forEach(function(button, index) {
            console.log('Setting up button', index, 'with slide target:', button.getAttribute('data-slide-to'));
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('🎯 Arrow button clicked!');
                
                const targetSlideId = this.getAttribute('data-slide-to');
                const categoryItem = this.closest('.bb-product-filter-item');
                const categoryName = categoryItem ? categoryItem.querySelector('.bb-product-filter-link span').textContent : 'Unknown';
                
                console.log('Target slide ID:', targetSlideId);
                console.log('Category name:', categoryName);
                
                if (targetSlideId) {
                    showSlide(targetSlideId);
                    console.log('✅ Navigated to slider:', categoryName);
                } else {
                    console.log('❌ No target slide ID found');
                }
            });
            
            // Add visual feedback on mouse events
            button.addEventListener('mouseenter', function() {
                console.log('🖱️ Mouse entered arrow button');
            });
            
            button.addEventListener('mousedown', function() {
                console.log('👆 Arrow button pressed');
            });
        });
        
        // Handle back button clicks
        backButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetSlideId = this.getAttribute('data-slide-to');
                showSlide(targetSlideId);
                console.log('🔙 Back to previous level');
            });
        });
        
        // Show specific slide
        function showSlide(slideId) {
            slides.forEach(function(slide) {
                slide.classList.remove('active');
            });
            
            const targetSlide = document.getElementById(slideId);
            if (targetSlide) {
                targetSlide.classList.add('active');
            }
        }
        
        // Auto-navigate to show active category
        autoNavigateToActive();
        
        function autoNavigateToActive() {
            const activeLink = sliderWrapper.querySelector('.bb-product-filter-link.active');
            if (!activeLink || activeLink.getAttribute('href') === '{{ route("public.products") }}') {
                return; // Stay on main slide for "All Categories"
            }
            
            // Find which slide contains the active link
            const activeSlide = activeLink.closest('.category-slide');
            if (activeSlide && activeSlide.id !== 'slide-main') {
                // Navigate through the slides to show the active category
                const slideId = activeSlide.id;
                
                // For subcategory slides, also show parent slide path
                if (slideId.startsWith('slide-') && slideId !== 'slide-main') {
                    showSlide(slideId);
                    console.log('🎯 Auto-navigated to active category slide:', slideId);
                }
            }
        }
        
        console.log('✅ Category Slider initialized');
        
        // Test function to verify arrow buttons work
        window.testArrowButton = function() {
            const firstArrow = document.querySelector('.slide-nav-btn');
            if (firstArrow) {
                console.log('🧪 Testing first arrow button...');
                firstArrow.click();
            } else {
                console.log('❌ No arrow buttons found for testing');
            }
        };
        
        console.log('💡 You can test arrow buttons by running: testArrowButton()');
    }
    </script>
@endif