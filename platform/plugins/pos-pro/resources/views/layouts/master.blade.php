@php
    use Botble\Language\Facades\Language;

    $mobileDetect = new \Detection\MobileDetect();

    $isMobile = $mobileDetect->isMobile();
@endphp

<x-core::layouts.base body-class="border-top-wide border-primary d-flex flex-column">
    <x-slot:title>
        @yield('title')
    </x-slot:title>

    <div class="pos-container">
        <!-- Floating exit fullscreen button (visible only in fullscreen mode) -->
        <button id="exit-fullscreen-floating" class="btn btn-icon btn-light position-fixed" aria-label="Exit fullscreen mode">
            <x-core::icon name="ti ti-minimize" />
            <span class="ms-2 d-none d-sm-inline">{{ trans('plugins/pos-pro::pos.exit_fullscreen') }}</span>
        </button>

        <!-- Mobile menu toggle button (visible only on small screens) -->
        <div class="d-md-none d-flex justify-content-between align-items-center p-3 mobile-header">
            <div class="d-flex align-items-center">
                <x-core::button
                    tag="a"
                    href="{{ route('dashboard.index') }}"
                    color="secondary"
                    icon="ti ti-arrow-left"
                    class="me-3 btn-sm"
                >
                    {{ __('Back') }}
                </x-core::button>
                <h1 class="h4 mb-0">@yield('header-title', 'POS System')</h1>
            </div>
            <button
                type="button"
                class="btn btn-icon btn-primary"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobile-menu-offcanvas"
                aria-controls="mobile-menu-offcanvas"
            >
                <x-core::icon name="ti ti-menu-2" />
            </button>
        </div>

        <!-- Off-canvas menu for mobile -->
        <x-core::offcanvas
            id="mobile-menu-offcanvas"
            class="mobile-menu-offcanvas"
            placement="end"
            backdrop="true"
            style="--bb-offcanvas-width: 85%"
        >
            <x-core::offcanvas.header class="mobile-menu-header">
                <x-core::offcanvas.title>
                    <x-core::icon name="ti ti-menu-2" class="me-2" />
                    {{ __('Menu') }}
                </x-core::offcanvas.title>
                <x-core::offcanvas.close-button />
            </x-core::offcanvas.header>
            <x-core::offcanvas.body>
                <!-- User Profile Card at the top -->
                <div class="user-profile-card mb-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-wrapper me-3">
                            <span class="avatar avatar-md" style="background-image: url({{ auth()->user()->avatar_url }});"></span>
                        </div>
                        <div class="user-info">
                            <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                            <p class="text-muted mb-0 small">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="mobile-menu-items">
                    <!-- Main Menu Items -->
                    <div class="menu-section">
                        <a href="{{ route('pos-pro.index') }}" class="menu-item">
                            <div class="menu-item-icon">
                                <x-core::icon name="ti ti-devices" />
                            </div>
                            <div class="menu-item-content">
                                <div class="menu-item-title">{{ trans('plugins/pos-pro::pos.pos') }}</div>
                                <div class="menu-item-description">{{ trans('plugins/pos-pro::pos.manage_pos') }}</div>
                            </div>
                        </a>

                        <a href="{{ route('pos-pro.reports.index') }}" class="menu-item">
                            <div class="menu-item-icon">
                                <x-core::icon name="ti ti-chart-bar" />
                            </div>
                            <div class="menu-item-content">
                                <div class="menu-item-title">{{ trans('plugins/pos-pro::pos.reports.title') }}</div>
                                <div class="menu-item-description">{{ trans('plugins/pos-pro::pos.reports.description') }}</div>
                            </div>
                        </a>

                        <a href="{{ route('pos-pro.settings.edit') }}" class="menu-item">
                            <div class="menu-item-icon">
                                <x-core::icon name="ti ti-settings" />
                            </div>
                            <div class="menu-item-content">
                                <div class="menu-item-title">{{ trans('plugins/pos-pro::pos.settings.title') }}</div>
                                <div class="menu-item-description">{{ trans('plugins/pos-pro::pos.settings.description') }}</div>
                            </div>
                        </a>
                    </div>

                    <!-- Settings Row -->
                    <div class="menu-item settings-row">
                        <div class="d-flex align-items-center w-100 justify-content-between">
                            <!-- Currency Switcher -->
                            @if (get_all_currencies()->count() > 1)
                                <div class="nav-item me-3">
                                    @include('plugins/pos-pro::partials.currency-switcher')
                                </div>
                            @endif

                            <!-- Language Switcher - only show if multiple languages are available -->
                            @if (is_plugin_active('language') && count(Language::getActiveLanguage()) > 1 && $isMobile)
                                <div class="nav-item me-3">
                                    @include('plugins/pos-pro::partials.language-switcher')
                                </div>
                            @endif

                            <!-- Dark/Light Mode Toggle -->
                            <div class="nav-item me-3">
                                @include('core/base::layouts.partials.theme-toggle')
                            </div>
                        </div>
                    </div>

                    <!-- User Actions -->
                    <div class="menu-item user-actions-row">
                        <div class="d-flex justify-content-between w-100">
                            <a href="{{ route('users.profile.view', auth()->id()) }}" class="btn btn-outline-primary flex-grow-1 me-2">
                                <x-core::icon name="ti ti-user" class="me-1" />
                                {{ __('Profile') }}
                            </a>
                            <a href="{{ route('access.logout') }}" class="btn btn-outline-danger flex-grow-1">
                                <x-core::icon name="ti ti-logout" class="me-1" />
                                {{ __('Logout') }}
                            </a>
                        </div>
                    </div>
                </div>
            </x-core::offcanvas.body>
        </x-core::offcanvas>

        <!-- Header with back button (visible on medium screens and up) -->
        <div class="navbar-expand-md d-none d-md-block">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar navbar-light">
                    <div class="container-fluid d-block">
                        <div class="d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center">
                                <x-core::button
                                    tag="a"
                                    href="{{ route('dashboard.index') }}"
                                    color="secondary"
                                    icon="ti ti-arrow-left"
                                    class="me-3"
                                >
                                    {{ __('Back to Dashboard') }}
                                </x-core::button>
                                <h1 class="h3 mb-0">@yield('header-title', 'POS System')</h1>
                            </div>
                            <div class="d-flex align-items-center">
                                <!-- Currency Switcher -->
                                @if (get_all_currencies()->count() > 1)
                                    <div class="nav-item me-3">
                                        @include('plugins/pos-pro::partials.currency-switcher')
                                    </div>
                                @endif

                                <!-- Language Switcher - only show if multiple languages are available -->
                                @if (is_plugin_active('language') && count(Language::getActiveLanguage()) > 1)
                                    <div class="nav-item me-3">
                                        @include('plugins/pos-pro::partials.language-switcher')
                                    </div>
                                @endif

                                <!-- Dark/Light Mode Toggle -->
                                <div class="nav-item me-3">
                                    @include('core/base::layouts.partials.theme-toggle')
                                </div>

                                <!-- Full Screen Toggle -->
                                <div class="nav-item me-3">
                                    <button id="fullscreen-toggle"
                                        class="nav-link px-0 d-flex align-items-center gap-2"
                                        aria-label="Toggle fullscreen mode"
                                        data-fullscreen-text="{{ trans('plugins/pos-pro::pos.fullscreen') }}"
                                        data-exit-fullscreen-text="{{ trans('plugins/pos-pro::pos.exit_fullscreen') }}">
                                        <x-core::icon name="ti ti-maximize" id="fullscreen-icon" />
                                        <span class="d-none d-md-inline" id="fullscreen-text">{{ trans('plugins/pos-pro::pos.fullscreen') }}</span>
                                    </button>
                                </div>

                                <div class="nav-item dropdown">
                                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                                        <span class="avatar avatar-sm" style="background-image: url({{ auth()->user()->avatar_url }});"></span>
                                        <div class="d-none d-xl-block ps-2">
                                            <div>{{ auth()->user()->name }}</div>
                                            <div class="mt-1 small text-muted">{{ auth()->user()->email }}</div>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                        <a href="{{ route('users.profile.view', auth()->id()) }}" class="dropdown-item">
                                            <x-core::icon name="ti ti-user" class="me-2" />
                                            {{ __('Profile') }}
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('access.logout') }}" class="dropdown-item">
                                            <x-core::icon name="ti ti-logout" class="me-2" />
                                            {{ __('Logout') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid py-4">
            @yield('content')
        </div>
    </div>

    @push('header')
        <link href="{{ asset('vendor/core/plugins/pos-pro/css/app.css') }}?v=1.0.5" rel="stylesheet">
        <link href="{{ asset('vendor/core/plugins/pos-pro/css/responsive.css') }}?v=1.0.5" rel="stylesheet">
    @endpush

    @push('footer')
        <script src="{{ asset('vendor/core/plugins/pos-pro/js/variables.js') }}?v=1.0.5"></script>
        <script src="{{ asset('vendor/core/plugins/pos-pro/js/app.js') }}?v=1.0.5"></script>
        <script src="{{ asset('vendor/core/plugins/pos-pro/js/barcode-scanner.js') }}?v=1.0.5"></script>
        <script src="{{ asset('vendor/core/plugins/pos-pro/js/responsive.js') }}?v=1.0.5"></script>
    @endpush
</x-core::layouts.base>
