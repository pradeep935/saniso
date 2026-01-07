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
    if (!data) return showError('Not found');
    result.innerHTML = '';

    const wrapper = document.createElement('div');
    wrapper.className = 'scan-result';

    const title = document.createElement('h2');
    title.textContent = data.name || 'Product';
    wrapper.appendChild(title);

    const info = document.createElement('div');
    info.className = 'info';
    info.innerHTML = `<div><strong>SKU:</strong> ${data.sku || '-'}</div><div><strong>Barcode:</strong> ${data.barcode || '-'}</div>`;
    wrapper.appendChild(info);

    const prices = document.createElement('div');
    prices.className = 'prices';
    prices.innerHTML = `<div>Price: ${data.price || '-'}</div>` + (data.sale_price ? `<div>Sale: ${data.sale_price} (${data.discount_percentage}% off)</div>` : '');
    wrapper.appendChild(prices);

    const stock = document.createElement('div');
    stock.className = 'stock';
    stock.textContent = 'Stock: ' + (data.in_stock !== null ? data.in_stock : 'N/A');
    wrapper.appendChild(stock);

    if (data.image) {
      const img = document.createElement('img');
      img.src = data.image;
      img.alt = data.name || 'product image';
      img.className = 'product-image';
      wrapper.appendChild(img);
    }

    if (Array.isArray(data.variants) && data.variants.length) {
      const sel = document.createElement('select');
      sel.id = 'variantSelect';
      data.variants.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v.id;
        opt.textContent = (v.sku || v.id) + ' — ' + (v.price || '-');
        opt.dataset.price = v.price || '';
        opt.dataset.stock = v.in_stock || '';
        sel.appendChild(opt);
      });
      sel.addEventListener('change', () => {
        const selected = sel.options[sel.selectedIndex];
        const p = selected.dataset.price;
        const s = selected.dataset.stock;
        prices.innerHTML = `<div>Price: ${p || '-'}</div>`;
        stock.textContent = 'Stock: ' + (s || 'N/A');
      });
      wrapper.appendChild(sel);
    }

    result.appendChild(wrapper);
  }

  function showError(msg) {
    result.innerHTML = `<div class="error">${msg}</div>`;
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
                  stopCamera();
                  sendScan(code);
                }
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
    setCameraStatus('Camera stopped');
    startBtn && (startBtn.hidden = false);
    stopBtn && (stopBtn.hidden = true);
  }

  if (startBtn) startBtn.addEventListener('click', startCamera);
  if (stopBtn) stopBtn.addEventListener('click', stopCamera);

  // Auto-start camera if enabled in config
  if (window.InStoreScannerConfig && window.InStoreScannerConfig.enable_camera) {
    startCamera();
  }
})();
