<ul {!! $options !!}>
    @foreach ($menu_nodes->loadMissing('metadata') as $key => $row)
        <li
            @if ($row->has_child || $row->css_class || $row->active) class="@if ($row->has_child) has-children @endif
            @if ($row->css_class) {{ $row->css_class }} @endif
            @if ($row->active) current-menu-item @endif"
            @endif>
            <a
                href="{{ url($row->url) }}"
                @if ($row->target !== '_self') target="{{ $row->target }}" @endif
            >
                {!! $row->icon_html !!}{{ $row->title }}
                @if ($row->has_child)
                    <span class="sub-toggle">
                        <span class="svg-icon">
                            <svg>
                                <use
                                    href="#svg-icon-chevron-down"
                                    xlink:href="#svg-icon-chevron-down"
                                ></use>
                            </svg>
                        </span>
                    </span>
                @endif
            </a>
            @if ($row->has_child)
                <div class="mega-menu-panel">
                    <ul class="mega-menu-columns">
                        @foreach ($row->child as $child)
                            <li>
                                <a href="{{ url($child->url) }}">{!! $child->icon_html !!}{{ $child->title }}</a>
                                @if ($child->has_child)
                                    <ul class="mega-menu__list">
                                        @foreach ($child->child as $subchild)
                                            <li><a href="{{ url($subchild->url) }}">{!! $subchild->icon_html !!}{{ $subchild->title }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </li>
    @endforeach
</ul>
