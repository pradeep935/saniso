@php
    $groupedCategories = ProductCategoryHelper::getProductCategoriesWithUrl()->groupBy('parent_id');
    $mainCategories = $groupedCategories->get(0);
@endphp

@if($mainCategories)
    @foreach ($mainCategories as $category)
        @php
            $hasChildren = $groupedCategories->has($category->id);
        @endphp

        <li @if ($hasChildren) class="cat-has-children cat-has-megamenu" @endif>
            <a href="{{ route('public.single', $category->url) }}">
                {{-- Display category thumbnail if available --}}
                @if ($category->icon_image)
                    <img
                        src="{{ RvMedia::getImageUrl($category->icon_image) }}"
                        alt="{{ $category->name }}"
                        width="62"
                        height="42"
                        style="vertical-align: middle; margin-right: 8px;"
                    >
                @elseif ($category->icon)
                    <i class="{{ $category->icon }}" style="margin-right: 8px;"></i>
                @endif
                <span class="ms-1">{{ $category->name }}</span>
            </a>
            @if ($hasChildren)
                @php
                    $subCategories = $groupedCategories->get($category->id);
                @endphp

                <div class="cat-megamenu-panel">
                    <ul class="cat-megamenu-masonry">
                        @if($subCategories)
                            @foreach ($subCategories as $subCategory)
                                <li>
                                    <a href="{{ route('public.single', $subCategory->url) }}">
                                        <span  style="font-weight:600;font-size:15px;">{{ $subCategory->name }}</span>
                                    </a>
                                    @php
                                        $children = $groupedCategories->get($subCategory->id);
                                    @endphp
                                    @if($children)
                                        <ul class="cat-megamenu__list">
                                            @foreach ($children as $child)
                                                <li>
                                                    <a href="{{ route('public.single', $child->url) }}">
                                                        <span>{{ $child->name }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            @endif
        </li>
    @endforeach
@endif
