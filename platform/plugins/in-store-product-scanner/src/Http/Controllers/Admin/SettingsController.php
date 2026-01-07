<?php

namespace Platform\InStoreProductScanner\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $rows = DB::table('instore_scanner_settings')->get()->pluck('value', 'key')->map(function ($v) {
            return json_decode($v, true);
        })->toArray();

        return view('in-store-product-scanner::admin.settings', ['settings' => $rows]);
    }

    public function save(Request $request)
    {
        $data = $request->only(['enable', 'enable_camera', 'enable_usb_scanner', 'barcode_field', 'custom_meta_key', 'show_stock', 'page_slug', 'ui_theme']);

        foreach ($data as $k => $v) {
            DB::table('instore_scanner_settings')->updateOrInsert(['key' => $k], ['value' => json_encode($v), 'updated_at' => now()]);
        }

        return redirect()->back()->with('success', 'Settings saved');
    }
}
