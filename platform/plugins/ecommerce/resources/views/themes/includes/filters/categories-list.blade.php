@php
    $categoriesRequest ??= [];
    $categoryId ??= 0;
    $urlCurrent ??= url()->current();

    if (!isset($groupedCategories)) {
        $groupedCategories = $categories->groupBy('parent_id');
    }

    $currentCategories = $groupedCategories->get($parentId ?? 0);
@endphp

@if ($currentCategories)
    {{-- Main Category Level --}}
    <div class="category-filter-wrapper">
        <div class="category-filter-level category-filter-level-main active" id="category-filter-main">
            <ul class="bb-product-filter-items">
                @if (! empty($categoriesRequest))
                    <li class="bb-product-filter-item">
                        <a href="{{ $currentMainFilterUrl ?? route('public.products') }}" @class(['bb-product-filter-link', 'active' => empty($categoriesRequest)])>
                            <x-core::icon name="ti ti-chevron-left" />
                            {{ __('All categories') }}
                        </a>
                    </li>
                @endif

                @foreach ($currentCategories as $category)
                    @php
                        $hasChildren = $groupedCategories->has($category->id);
                    @endphp
                    <li class="bb-product-filter-item">
                        @if ($hasChildren)
                            {{-- Category with children - navigate to sublevel --}}
                            <a href="javascript:void(0)" 
                               class="bb-product-filter-link category-filter-nav-link" 
                               data-target="category-filter-{{ $category->id }}">
                                @if (! $category->parent_id)
                                    @if ($category->icon_image)
                                        {{ RvMedia::image($category->icon_image, $category->name) }}
                                    @elseif ($category->icon)
                                        {!! BaseHelper::renderIcon($category->icon) !!}
                                    @else
                                        <x-core::icon name="ti ti-folder" />
                                    @endif
                                @endif
                                <span>{{ $category->name }}</span>
                                <span class="category-filter-arrow">
                                    <x-core::icon name="ti ti-chevron-right" />
                                </span>
                            </a>
                        @else
                            {{-- Category without children - direct selection --}}
                            <a href="#" 
                               @class(['bb-product-filter-link', 'active' => $categoryId == $category->id])
                               data-id="{{ $category->id }}"
                               data-category-name="{{ $category->name }}">
                                @if (! $category->parent_id)
                                    @if ($category->icon_image)
                                        {{ RvMedia::image($category->icon_image, $category->name) }}
                                    @elseif ($category->icon)
                                        {!! BaseHelper::renderIcon($category->icon) !!}
                                    @else
                                        <x-core::icon name="ti ti-folder" />
                                    @endif
                                @endif
                                <span>{{ $category->name }}</span>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Category Sub-Levels (First Level Children) --}}
        @foreach ($currentCategories as $category)
            @if ($groupedCategories->has($category->id))
                <div class="category-filter-level category-filter-level-sub" id="category-filter-{{ $category->id }}">
                    <div class="category-filter-level-header">
                        <button class="category-filter-back-btn" data-target="category-filter-main" type="button">
                            <x-core::icon name="ti ti-chevron-left" />
                            <span>{{ __('Back') }}</span>
                        </button>
                        <h5>{{ $category->name }}</h5>
                    </div>
                    <ul class="bb-product-filter-items">
                        {{-- View All Link for Parent Category --}}
                        <li class="bb-product-filter-item bb-product-filter-item-view-all">
                            <a href="#" 
                               class="bb-product-filter-link" 
                               data-id="{{ $category->id }}"
                               data-category-name="{{ $category->name }}">
                                <x-core::icon name="ti ti-eye" />
                                <span>{{ __('View All in :category', ['category' => $category->name]) }}</span>
                            </a>
                        </li>
                        
                        {{-- Child Categories --}}
                        @foreach ($groupedCategories->get($category->id) as $child)
                            @php
                                $childHasChildren = $groupedCategories->has($child->id);
                            @endphp
                            <li class="bb-product-filter-item">
                                @if ($childHasChildren)
                                    {{-- Child with grandchildren - navigate to next level --}}
                                    <a href="javascript:void(0)" 
                                       class="bb-product-filter-link category-filter-nav-link" 
                                       data-target="category-filter-{{ $child->id }}">
                                        <span>{{ $child->name }}</span>
                                        <span class="category-filter-arrow">
                                            <x-core::icon name="ti ti-chevron-right" />
                                        </span>
                                    </a>
                                @else
                                    {{-- Child without grandchildren - direct selection --}}
                                    <a href="#" 
                                       @class(['bb-product-filter-link', 'active' => $categoryId == $child->id])
                                       data-id="{{ $child->id }}"
                                       data-category-name="{{ $child->name }}">
                                        <span>{{ $child->name }}</span>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Grandchild Categories (Second Level Children) --}}
                @foreach ($groupedCategories->get($category->id) as $child)
                    @if ($groupedCategories->has($child->id))
                        <div class="category-filter-level category-filter-level-sub" id="category-filter-{{ $child->id }}">
                            <div class="category-filter-level-header">
                                <button class="category-filter-back-btn" data-target="category-filter-{{ $category->id }}" type="button">
                                    <x-core::icon name="ti ti-chevron-left" />
                                    <span>{{ __('Back to') }} {{ $category->name }}</span>
                                </button>
                                <h5>{{ $child->name }}</h5>
                            </div>
                            <ul class="bb-product-filter-items">
                                {{-- View All Link for Child Category --}}
                                <li class="bb-product-filter-item bb-product-filter-item-view-all">
                                    <a href="#" 
                                       class="bb-product-filter-link" 
                                       data-id="{{ $child->id }}"
                                       data-category-name="{{ $child->name }}">
                                        <x-core::icon name="ti ti-eye" />
                                        <span>{{ __('View All in :category', ['category' => $child->name]) }}</span>
                                    </a>
                                </li>
                                
                                {{-- Grandchild Categories --}}
                                @foreach ($groupedCategories->get($child->id) as $grandchild)
                                    @php
                                        $grandchildHasChildren = $groupedCategories->has($grandchild->id);
                                    @endphp
                                    <li class="bb-product-filter-item">
                                        @if ($grandchildHasChildren)
                                            {{-- Grandchild with great-grandchildren - navigate to next level --}}
                                            <a href="javascript:void(0)" 
                                               class="bb-product-filter-link category-filter-nav-link" 
                                               data-target="category-filter-{{ $grandchild->id }}">
                                                <span>{{ $grandchild->name }}</span>
                                                <span class="category-filter-arrow">
                                                    <x-core::icon name="ti ti-chevron-right" />
                                                </span>
                                            </a>
                                        @else
                                            {{-- Grandchild without great-grandchildren - direct selection --}}
                                            <a href="#" 
                                               @class(['bb-product-filter-link', 'active' => $categoryId == $grandchild->id])
                                               data-id="{{ $grandchild->id }}"
                                               data-category-name="{{ $grandchild->name }}">
                                                <span>{{ $grandchild->name }}</span>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Great-Grandchild Categories (Third Level Children) --}}
                        @foreach ($groupedCategories->get($child->id) as $grandchild)
                            @if ($groupedCategories->has($grandchild->id))
                                <div class="category-filter-level category-filter-level-sub" id="category-filter-{{ $grandchild->id }}">
                                    <div class="category-filter-level-header">
                                        <button class="category-filter-back-btn" data-target="category-filter-{{ $child->id }}" type="button">
                                            <x-core::icon name="ti ti-chevron-left" />
                                            <span>{{ __('Back to') }} {{ $child->name }}</span>
                                        </button>
                                        <h5>{{ $grandchild->name }}</h5>
                                    </div>
                                    <ul class="bb-product-filter-items">
                                        {{-- View All Link for Grandchild Category --}}
                                        <li class="bb-product-filter-item bb-product-filter-item-view-all">
                                            <a href="#" 
                                               class="bb-product-filter-link" 
                                               data-id="{{ $grandchild->id }}"
                                               data-category-name="{{ $grandchild->name }}">
                                                <x-core::icon name="ti ti-eye" />
                                                <span>{{ __('View All in :category', ['category' => $grandchild->name]) }}</span>
                                            </a>
                                        </li>
                                        
                                        {{-- Great-Grandchild Categories --}}
                                        @foreach ($groupedCategories->get($grandchild->id) as $greatGrandchild)
                                            <li class="bb-product-filter-item">
                                                <a href="#" 
                                                   @class(['bb-product-filter-link', 'active' => $categoryId == $greatGrandchild->id])
                                                   data-id="{{ $greatGrandchild->id }}"
                                                   data-category-name="{{ $greatGrandchild->name }}">
                                                    <span>{{ $greatGrandchild->name }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>

    {{-- Mobile Menu Style CSS for Category Filter --}}
    <style>
    /* Category Filter Levels */
    .category-filter-wrapper {
        position: relative;
        overflow: hidden;
        min-height: 200px;
    }
    
    .category-filter-level {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        background: #fff;
    }
    
    .category-filter-level.active {
        transform: translateX(0);
    }
    
    .category-filter-level-header {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .category-filter-back-btn {
        background: none;
        border: none;
        display: flex;
        align-items: center;
        gap: 6px;
        color: #666;
        font-size: 14px;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .category-filter-back-btn:hover {
        background: #f8f9fa;
        color: #333;
    }
    
    .category-filter-level-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    
    .category-filter-arrow {
        margin-left: auto;
        color: #999;
        transition: all 0.2s ease;
    }
    
    .category-filter-nav-link:hover .category-filter-arrow {
        color: #333;
        transform: translateX(2px);
    }
    
    .bb-product-filter-item-view-all .bb-product-filter-link {
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .bb-product-filter-item-view-all .bb-product-filter-link:hover {
        background: #e9ecef;
    }
    
    .bb-product-filter-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        text-decoration: none;
        color: inherit;
        border-radius: 6px;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .bb-product-filter-link:hover {
        background: rgba(var(--primary-color-rgb, 13, 110, 253), 0.1);
        text-decoration: none;
        transform: translateX(2px);
    }
    
    .bb-product-filter-link.active {
        background: rgba(var(--primary-color-rgb, 13, 110, 253), 0.15);
        font-weight: 600;
    }
    
    .bb-product-filter-link span {
        flex: 1;
    }
    </style>

    {{-- Mobile Menu Style JavaScript for Category Filter --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎯 Category Filter: Initializing mobile menu style navigation...');
        
        // Handle category filter navigation
        function showCategoryFilterLevel(targetId) {
            console.log('📂 Showing level:', targetId);
            
            document.querySelectorAll('.category-filter-level').forEach(level => {
                level.classList.remove('active');
            });
            
            const targetLevel = document.getElementById(targetId);
            if (targetLevel) {
                targetLevel.classList.add('active');
                console.log('✅ Level shown:', targetId);
            } else {
                console.warn('⚠️ Level not found:', targetId);
            }
        }
        
        // Handle navigation links (categories with children)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.category-filter-nav-link')) {
                e.preventDefault();
                const link = e.target.closest('.category-filter-nav-link');
                const target = link.getAttribute('data-target');
                
                console.log('🔗 Navigation link clicked:', {
                    target: target,
                    categoryName: link.textContent.trim()
                });
                
                if (target) {
                    showCategoryFilterLevel(target);
                }
            }
        });
        
        // Handle back buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.category-filter-back-btn')) {
                e.preventDefault();
                const button = e.target.closest('.category-filter-back-btn');
                const target = button.getAttribute('data-target');
                
                console.log('🔙 Back button clicked:', {
                    target: target,
                    buttonText: button.textContent.trim()
                });
                
                if (target) {
                    showCategoryFilterLevel(target);
                }
            }
        });
        
        // Handle category selection for filtering (only for selectable categories)
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.bb-product-filter-link:not(.category-filter-nav-link)');
            if (link) {
                const categoryId = link.getAttribute('data-id');
                const categoryName = link.getAttribute('data-category-name') || link.textContent.trim();
                const form = link.closest('form.bb-product-form-filter');
                
                console.log('🎯 Category selected for filtering:', {
                    categoryId: categoryId,
                    categoryName: categoryName,
                    hasForm: !!form
                });
                
                if (form && categoryId) {
                    e.preventDefault();
                    
                    // Update form data
                    let categoryInput = form.querySelector('input[name="categories"]');
                    if (!categoryInput) {
                        categoryInput = document.createElement('input');
                        categoryInput.type = 'hidden';
                        categoryInput.name = 'categories';
                        form.appendChild(categoryInput);
                    }
                    categoryInput.value = categoryId;
                    
                    // Update visual states
                    form.querySelectorAll('.bb-product-filter-link').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    
                    // Remove pagination
                    const pageInputs = form.querySelectorAll('input[name="page"], input[name="per-page"]');
                    pageInputs.forEach(input => input.remove());
                    
                    console.log('📤 Submitting form with category:', categoryId);
                    
                    // Submit form
                    form.submit();
                } else if (!categoryId) {
                    console.warn('⚠️ No category ID found for selection');
                } else if (!form) {
                    console.warn('⚠️ No form found for category filtering');
                }
            }
        });
        
        // Initialize with main level
        showCategoryFilterLevel('category-filter-main');
        
        // Debug function
        window.debugCategoryFilter = function() {
            console.log('=== 🐛 Category Filter Debug ===');
            console.log('Available levels:', Array.from(document.querySelectorAll('.category-filter-level')).map(l => l.id));
            console.log('Active level:', document.querySelector('.category-filter-level.active')?.id);
            console.log('Navigation links:', document.querySelectorAll('.category-filter-nav-link').length);
            console.log('Selectable links:', document.querySelectorAll('.bb-product-filter-link:not(.category-filter-nav-link)').length);
            console.log('=================================');
        };
        
        console.log('✅ Category Filter: Mobile menu style navigation initialized');
    });
    </script>
@endif
