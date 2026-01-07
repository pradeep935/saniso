<?php

namespace Platform\InStoreProductScanner\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Platform\InStoreProductScanner\Http\Requests\ScanRequest;
use Platform\InStoreProductScanner\Services\ProductLookupService;

class ScanController extends Controller
{
    protected ProductLookupService $lookup;

    public function __construct(ProductLookupService $lookup)
    {
        $this->lookup = $lookup;
    }

    public function lookup(ScanRequest $request)
    {
        $code = trim($request->input('code'));
        $cacheKey = 'instore_scan_' . md5($code);

        $result = Cache::remember($cacheKey, config('scanner.cache_ttl', 60), function () use ($code) {
            try {
                return $this->lookup->findByCode($code);
            } catch (\Throwable $e) {
                Log::error('InStoreScanner lookup error: ' . $e->getMessage());
                return null;
            }
        });

        if (! $result) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json(['data' => $result]);
    }
}
