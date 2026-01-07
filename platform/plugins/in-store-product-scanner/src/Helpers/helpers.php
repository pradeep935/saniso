<?php

if (! function_exists('instore_scanner_setting')) {
    function instore_scanner_setting(string $key, $default = null)
    {
        $row = \Illuminate\Support\Facades\DB::table('instore_scanner_settings')->where('key', $key)->first();
        return $row ? json_decode($row->value, true) : $default;
    }
}
