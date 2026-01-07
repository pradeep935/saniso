/* Basic scanner logic using BarcodeDetector API with manual input fallback */
(function () {
  'use strict';

  const video = document.getElementById('video');
  const canvas = document.getElementById('canvas');
  const manualInput = document.getElementById('manualInput');
  const manualSubmit = document.getElementById('manualSubmit');
  const loader = document.getElementById('loader');
  const result = document.getElementById('result');

  const apiUrl = '/api/store-scan/lookup';

  // Guard variables to avoid repeated scans and race conditions
  let lastCode = null;
  let lastTime = 0;
  let isFetching = false;
  let abortController = null;

  // Detection stability control: require repeated detections before accepting
  let detectorCandidate = null;
  let detectorCount = 0;
  const REQUIRED_DETECTIONS = 2; // number of consecutive detections to accept
  let scanAccepted = false;

  function showLoader(show) {
    loader.hidden = !show;
  }

  function playBeep() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const o = ctx.createOscillator();
      const g = ctx.createGain();
      o.type = 'sine';
      o.frequency.value = 900;
      o.connect(g);
      g.connect(ctx.destination);
      o.start(0);
      g.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.15);
      setTimeout(() => { o.stop(); ctx.close(); }, 200);
    } catch (e) {
      // ignore
    }
  }

  async function sendScan(code) {
    if (!code) return;

    // Normalize code (trim, remove whitespace/newlines)
    code = String(code).trim();

    // Prevent duplicate submission within a short window
    const now = Date.now();
    if (code === lastCode && now - lastTime < 1200) {
      return;
    }

    // If a request is in-flight, abort it (we prefer latest scan)
    if (isFetching && abortController) {
      try { abortController.abort(); } catch (e) { /* ignore */ }
    }

    lastCode = code;
    lastTime = now;
    isFetching = true;
    showLoader(true);
    clearResult();

    abortController = new AbortController();
    const signal = abortController.signal;

    try {
      const resp = await fetch(apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ code: code }),
        signal
      });

      if (!resp.ok) {
        const err = await resp.json().catch(() => ({ error: 'Unknown error' }));
        showError(err.error || 'Product not found');
        return;
      }

      const json = await resp.json();
      renderResult(json.data);
      playBeep();
      // Stop camera after displaying a result to avoid continuous scanning
      try { stopCamera(); } catch (e) { /* ignore */ }
      scanAccepted = true;
    } catch (e) {
      if (e.name === 'AbortError') {
        // request aborted because a new scan arrived — ignore
      } else {
        showError('Network error');
      }
    } finally {
      isFetching = false;
      abortController = null;
      showLoader(false);
    }
  }

  function renderResult(data) {
    if (!data) return showError('Product not found');
    result.innerHTML = '';

    const wrapper = document.createElement('div');
    wrapper.className = 'scan-result';

    // Header section with brand and title
    const header = document.createElement('div');
    header.className = 'scan-result-header';

    if (data.brand) {
      const brandEl = document.createElement('div');
      brandEl.className = 'brand-name';
      brandEl.textContent = data.brand;
      header.appendChild(brandEl);
    }

    const title = document.createElement('h2');
    title.textContent = data.name || 'Product';
    header.appendChild(title);
    wrapper.appendChild(header);

    // Info section: SKU and Barcode
    const info = document.createElement('div');
    info.className = 'info';
    
    const skuDiv = document.createElement('div');
    const skuLabel = document.createElement('strong');
    skuLabel.textContent = 'SKU';
    skuDiv.appendChild(skuLabel);
    const skuValue = document.createElement('div');
    skuValue.textContent = data.sku || '-';
    skuDiv.appendChild(skuValue);
    info.appendChild(skuDiv);

    const barcodeDiv = document.createElement('div');
    const barLabel = document.createElement('strong');
    barLabel.textContent = 'Barcode';
    barcodeDiv.appendChild(barLabel);
    const barValue = document.createElement('div');
    barValue.textContent = data.barcode || '-';
    barcodeDiv.appendChild(barValue);
    info.appendChild(barcodeDiv);

    wrapper.appendChild(info);

    // Prices section
    const prices = document.createElement('div');
    prices.className = 'prices';
    
    const priceDiv = document.createElement('div');
    priceDiv.textContent = data.price ? '$' + parseFloat(data.price).toFixed(2) : '-';
    prices.appendChild(priceDiv);

    if (data.sale_price) {
      const saleDiv = document.createElement('div');
      const discount = data.discount_percentage ? ' (' + data.discount_percentage + '% off)' : '';
      saleDiv.textContent = 'Sale: $' + parseFloat(data.sale_price).toFixed(2) + discount;
      prices.appendChild(saleDiv);
    }
    wrapper.appendChild(prices);

    // Stock status
    const stock = document.createElement('div');
    stock.className = 'stock';
    const stockStatus = data.in_stock !== null 
      ? (data.in_stock > 0 ? '✓ In Stock (' + data.in_stock + ')' : '✗ Out of Stock')
      : 'N/A';
    stock.textContent = stockStatus;
    wrapper.appendChild(stock);

    // Product image
    if (data.image) {
      const img = document.createElement('img');
      img.src = data.image;
      img.alt = data.name || 'product image';
      img.className = 'product-image';
      wrapper.appendChild(img);
    }

    // Variants dropdown if available
    if (Array.isArray(data.variants) && data.variants.length) {
      const label = document.createElement('label');
      label.textContent = 'Select Variant:';
      wrapper.appendChild(label);

      const sel = document.createElement('select');
      sel.id = 'variantSelect';
      
      const defaultOpt = document.createElement('option');
      defaultOpt.value = '';
      defaultOpt.textContent = '-- Choose variant --';
      sel.appendChild(defaultOpt);

      data.variants.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v.id;
        opt.textContent = (v.sku || v.id) + ' — $' + parseFloat(v.price || 0).toFixed(2);
        opt.dataset.price = v.price || '';
        opt.dataset.stock = v.in_stock || '';
        sel.appendChild(opt);
      });

      sel.addEventListener('change', () => {
        if (sel.selectedIndex > 0) {
          const selected = sel.options[sel.selectedIndex];
          const p = selected.dataset.price;
          const s = selected.dataset.stock;
          // update price and stock safely
          priceDiv.textContent = p ? '$' + parseFloat(p).toFixed(2) : '-';
          stock.textContent = s ? (parseInt(s) > 0 ? '✓ In Stock (' + s + ')' : '✗ Out of Stock') : 'N/A';
        }
      });
      wrapper.appendChild(sel);
    }

    result.appendChild(wrapper);

    // Show post-scan controls with updated styling
    const scanAgainBtn = document.getElementById('scanAgain');
    const doneBtn = document.getElementById('doneScanning');
    if (scanAgainBtn && doneBtn) {
      scanAgainBtn.hidden = false;
      doneBtn.hidden = false;
    }

    // Update nav status
    const navStatus = document.querySelector('.nav-status');
    if (navStatus) navStatus.textContent = 'Product: ' + (data.name || 'Unknown');

    // Save to local history and render history
    try {
      saveToHistory({ id: data.id, name: data.name, sku: data.sku, barcode: data.barcode, image: data.image || '' });
      renderHistory();
    } catch (e) { /* ignore history errors */ }
  }

  // Local storage history (last 10 scans)
  function saveToHistory(item) {
    if (!window.localStorage) return;
    const key = 'instore_scan_history_v1';
    const raw = localStorage.getItem(key);
    let list = raw ? JSON.parse(raw) : [];
    // Remove existing same barcode
    list = list.filter(i => i.barcode !== item.barcode);
    list.unshift(Object.assign({ ts: Date.now() }, item));
    if (list.length > 10) list = list.slice(0, 10);
    localStorage.setItem(key, JSON.stringify(list));
  }

  function renderHistory() {
    const key = 'instore_scan_history_v1';
    const raw = localStorage.getItem(key);
    const list = raw ? JSON.parse(raw) : [];
    const el = document.getElementById('scanHistory');
    if (!el) return;
    el.innerHTML = '';
    if (list.length === 0) {
      el.innerHTML = '<li style="color: #94a3b8; text-align: center; padding: 1rem;">No recent scans</li>';
      return;
    }
    list.forEach(item => {
      const li = document.createElement('li');
      li.textContent = item.name || item.sku || item.barcode || 'Product';
      li.addEventListener('click', function() {
        manualInput.value = item.barcode || item.sku || '';
        sendScan(item.barcode || item.sku);
      });
      el.appendChild(li);
    });
  }

  function showError(msg) {
    result.innerHTML = '';
    const errorDiv = document.createElement('div');
    errorDiv.style.cssText = 'padding: 1.5rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 1rem; color: #fca5a5; font-weight: 600; text-align: center;';
    errorDiv.textContent = msg;
    result.appendChild(errorDiv);
  }

  function clearResult() {
    result.innerHTML = '';
  }

  manualSubmit.addEventListener('click', function () {
    if (manualInput.value.trim()) sendScan(manualInput.value.trim());
  });

  manualInput.addEventListener('keydown', function (e) {
    // Many USB scanners send Enter after scan
    if (e.key === 'Enter') {
      e.preventDefault();
      if (manualInput.value.trim()) sendScan(manualInput.value.trim());
    }
  });

  // Camera control: start/stop, status, and single-scan behavior
  let cameraStream = null;
  const startBtn = document.getElementById('startScan');
  const stopBtn = document.getElementById('stopScan');
  const cameraStatus = document.getElementById('cameraStatus');
  const errorMessage = document.getElementById('errorMessage');

  function setCameraStatus(text) {
    if (cameraStatus) cameraStatus.textContent = text || '';
  }

  function setError(text) {
    if (errorMessage) errorMessage.textContent = text || '';
  }

  // Normalize barcode: strip non-digit for EAN/UPC; keep for QR
  function normalizeCode(raw) {
    if (!raw) return null;
    const s = String(raw).trim();
    if (/[A-Za-z]/.test(s)) return s;
    const digits = s.replace(/[^0-9]/g, '');
    return digits || s;
  }

  async function startCamera() {
    setError('');
    if (!window.navigator.mediaDevices) {
      setError('Camera not available');
      return;
    }
    try {
      const constraints = { video: { facingMode: 'environment' } };
      const stream = await navigator.mediaDevices.getUserMedia(constraints);
      cameraStream = stream;
      video.srcObject = stream;
      video.play();
      setCameraStatus('Camera active');
      startBtn && (startBtn.hidden = true);
      stopBtn && (stopBtn.hidden = false);

      // add scanning visual state and ensure camera area visible
      const cameraArea = document.getElementById('camera-area');
      if (cameraArea) {
        cameraArea.classList.add('scanning');
        cameraArea.hidden = false;
      }

      const supportedFormats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'qr_code'];

      if ((window.BarcodeDetector || window.WebKitBarcodeDetector || window.MozBarcodeDetector)) {
        const Detector = window.BarcodeDetector || window.WebKitBarcodeDetector || window.MozBarcodeDetector;
        let detector;
        try {
          detector = new Detector({ formats: supportedFormats });
        } catch (e) {
          try { detector = new Detector(); } catch (err) { detector = null; }
        }

        if (detector) {
          const detectLoop = async () => {
            if (!cameraStream) return;
            try {
              const barcodes = await detector.detect(video);
                    if (barcodes && barcodes.length) {
                      const raw = barcodes[0].rawValue;
                      const code = normalizeCode(raw);
                      if (code) {
                        // require stability: accept only after REQUIRED_DETECTIONS of same code
                        if (detectorCandidate === code) {
                          detectorCount++;
                        } else {
                          detectorCandidate = code;
                          detectorCount = 1;
                        }

                        if (!scanAccepted && detectorCount >= REQUIRED_DETECTIONS) {
                          // accept this scan
                          stopCamera();
                          scanAccepted = true;
                          sendScan(code);
                        }
                      }
                    } else {
                      // reset candidate when nothing detected
                      detectorCandidate = null;
                      detectorCount = 0;
                    }
            } catch (err) {
              console.debug('BarcodeDetector error', err);
            }
            if (cameraStream) requestAnimationFrame(detectLoop);
          };
          detectLoop();
        }
      }
    } catch (e) {
      setError('Camera permission denied or error');
      setCameraStatus('');
      console.warn(e);
    }
  }

  function stopCamera() {
    if (cameraStream) {
      cameraStream.getTracks().forEach(t => t.stop());
      cameraStream = null;
    }
    if (video) {
      try { video.pause(); video.srcObject = null; } catch (e) { }
    }
    // hide camera area after stopping to focus on result
    const cameraArea = document.getElementById('camera-area');
    if (cameraArea) cameraArea.hidden = true;
    if (cameraArea) cameraArea.classList.remove('scanning');
    setCameraStatus('Camera stopped');
    startBtn && (startBtn.hidden = false);
    stopBtn && (stopBtn.hidden = true);
  }

  if (startBtn) startBtn.addEventListener('click', startCamera);
  if (stopBtn) stopBtn.addEventListener('click', stopCamera);

  // post-scan controls
  const scanAgainBtn = document.getElementById('scanAgain');
  const doneBtn = document.getElementById('doneScanning');
  if (scanAgainBtn) {
    scanAgainBtn.addEventListener('click', function () {
      // reset scanner state and restart camera
      detectorCandidate = null;
      detectorCount = 0;
      scanAccepted = false;
      const resultEl = document.getElementById('result');
      if (resultEl) resultEl.innerHTML = '';
      scanAgainBtn.hidden = true;
      if (doneBtn) doneBtn.hidden = true;
      startCamera();
    });
  }
  if (doneBtn) {
    doneBtn.addEventListener('click', function () {
      // finalize scanning session: keep result visible but disable scan controls
      if (scanAgainBtn) scanAgainBtn.hidden = true;
      if (startBtn) startBtn.hidden = true;
      if (stopBtn) stopBtn.hidden = true;
      if (doneBtn) doneBtn.hidden = true;
    });
  }

  // Auto-start camera only if enabled and explicitly allowed by config
  if (window.InStoreScannerConfig && window.InStoreScannerConfig.enable_camera && window.InStoreScannerConfig.auto_start_camera) {
    startCamera();
  }

  // render history on load
  try { renderHistory(); } catch (e) { /* ignore */ }
})();
