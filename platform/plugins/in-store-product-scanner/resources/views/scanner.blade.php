<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Store Scanner - Saniso</title>
  @php
    $cssSource = base_path('platform/plugins/in-store-product-scanner/resources/assets/css/scanner.css');
    $jsSource = base_path('platform/plugins/in-store-product-scanner/resources/assets/js/scanner.js');
    $cssVer = file_exists($cssSource) ? filemtime($cssSource) : time();
    $jsVer = file_exists($jsSource) ? filemtime($jsSource) : time();
  @endphp
  <link rel="stylesheet" href="{{ asset('vendor/plugins/in-store-product-scanner/css/scanner.css') }}?v={{ $cssVer }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@0.263.1/font/lucide.css">
</head>
<body class="scanner-page">
  <!-- Background Ambience -->
  <div class="background-gradient"></div>
  <div class="background-noise"></div>

  <div class="scanner-container">
    <!-- Header Navigation -->
    <nav class="scanner-nav">
      <div class="nav-content">
        <div class="logo-section">
          <div class="logo-icon">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            </svg>
          </div>
          <div class="logo-text" aria-hidden="true">
            <!-- site name intentionally hidden for a clean logo-only header -->
            <div class="app-name">ShopScan AI</div>
            <div class="app-subtitle">Store Scanner</div>
          </div>
        </div>
        <div class="nav-status" id="navStatus">Ready to scan</div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="scanner-content">
      <!-- Camera Section -->
      <div class="camera-section">
        <div id="camera-area" class="camera-area">
          <video id="video" autoplay playsinline></video>
          <canvas id="canvas" hidden></canvas>
          
          <!-- Animated Scanner Overlay -->
          <div class="scanner-overlay" aria-hidden="true">
            <div class="scanner-frame">
              <div class="corner corner-tl"></div>
              <div class="corner corner-tr"></div>
              <div class="corner corner-bl"></div>
              <div class="corner corner-br"></div>
              <div class="scanner-laser"></div>
              <div class="scan-pulse"></div>
            </div>
            <div class="scan-instruction">Align barcode or product within frame</div>
          </div>
        </div>

        <!-- Controls Section -->
        <div class="controls-section">
          <div class="input-group">
            <input 
              id="manualInput" 
              type="text"
              placeholder="Search product or scan barcode..." 
              autocomplete="off"
              class="manual-input"
            >
            <button id="manualSubmit" class="btn btn-search" title="Search product">
              <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              Lookup
            </button>
          </div>

          <!-- start/stop moved to floating control for better UX on small screens -->

          <div id="loader" class="loader-spinner" hidden>
            <div class="spinner"></div>
            <span>Processing scan...</span>
          </div>
          <div id="cameraStatus" class="status-text"></div>
          <div id="errorMessage" class="error-message" role="status" aria-live="polite"></div>
        </div>
      </div>

      <!-- Sidebar -->
      <aside class="scanner-sidebar">
        <!-- Result Panel -->
        <div id="result" class="result-panel" aria-live="polite">
          <div class="empty-state">
            <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p>Scan a product to get started</p>
          </div>
        </div>

        <!-- Result Actions -->
        <div class="result-actions" style="margin-top:16px;">
          <button id="scanAgain" type="button" class="btn btn-primary" hidden>
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Scan Again
          </button>
          <button id="doneScanning" type="button" class="btn btn-secondary" hidden>
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Done
          </button>
        </div>

        <!-- Running Offers -->
        <section class="offers-section" style="margin-top:20px;">
          <h3 class="section-title">Running Offers</h3>
          <div id="offersList" class="offers-list">
            <div class="offer-card">
              <div class="offer-badge">Offer</div>
              <p>10% off bestsellers — Use CODE: STORE10</p>
            </div>
            <div class="offer-card">
              <div class="offer-badge">Offer</div>
              <p>Free shipping over $50</p>
            </div>
          </div>
        </section>

        <!-- Scan History -->
        <section class="history-section" style="margin-top:20px;">
          <h3 class="section-title">Recent Scans</h3>
          <ul id="scanHistory" class="history-list"></ul>
        </section>
      </aside>
    </div>
  </div>

  <!-- Floating start/stop controls (visible above content) -->
  <div class="floating-controls" role="region" aria-label="Scanner controls">
    <button id="startScan" type="button" class="btn btn-primary" title="Start camera">
      <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 15.536c2.5-2.5 2.5-6.572 0-9.072M8.464 8.464c-2.5 2.5-2.5 6.572 0 9.072"></path>
      </svg>
      Scan
    </button>
    <button id="stopScan" type="button" class="btn btn-danger" hidden title="Stop camera">
      <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
      Stop
    </button>
  </div>

  <script>window.InStoreScannerConfig = @json(config('scanner'));</script>
  <script src="{{ asset('vendor/plugins/in-store-product-scanner/js/scanner.js') }}?v={{ $jsVer }}"></script>
</body>
</html>
