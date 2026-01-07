// Legacy placeholder service worker.
// We now use /service-worker.js for offline behavior. This file intentionally
// does nothing to avoid conflicting registrations. If you previously registered
// /sw.js, please update the theme to register /service-worker.js instead.

self.addEventListener('install', (event) => {
  // Skip doing any heavy work here — registration should use /service-worker.js
  console.info('Placeholder /sw.js installed; using /service-worker.js instead');
  self.skipWaiting();
});

self.addEventListener('fetch', () => {
  // Return nothing; this placeholder avoids serving HTML for asset requests.
});

