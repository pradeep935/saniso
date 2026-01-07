<?php

namespace Platform\InStoreProductScanner\Facades;

use Illuminate\Support\Facades\Facade;

class ProductScanner extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Platform\InStoreProductScanner\Services\ProductLookupService::class;
    }
}
