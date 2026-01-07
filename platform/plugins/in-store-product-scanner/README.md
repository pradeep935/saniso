# In-Store Product Scanner

Browser-based in-store product scanner plugin for Botble CMS.

Installation

1. Copy the `in-store-product-scanner` folder into `platform/plugins/`.
2. Run migrations: `php artisan migrate`.
3. Publish config (optional): `php artisan vendor:publish --tag=config` or copy `config/scanner.php` to your config folder.
4. Clear views and caches: `php artisan view:clear && php artisan cache:clear`.
5. Visit the scanner page: `/store-scan`.

Notes

- The plugin uses the browser `BarcodeDetector` API when available. For wider support include a JS decoder library if needed.
- API endpoint is `POST /api/store-scan/lookup` (throttled).
- Admin settings are available at `Settings → In-Store Scanner` (requires migration table).
