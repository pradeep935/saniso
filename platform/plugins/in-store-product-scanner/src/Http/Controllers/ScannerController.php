<?php

namespace Platform\InStoreProductScanner\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index(Request $request)
    {
        return view('in-store-product-scanner::scanner');
    }
}
