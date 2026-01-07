<?php

namespace Botble\Mollie\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\SettingController as BaseSettingController;
use Illuminate\Http\Request;

class DebugSettingController extends BaseSettingController
{
    public function postEdit(Request $request): BaseHttpResponse
    {
        // Debug the request data
        \Log::info('POS Settings Debug - Request Data:', [
            'all_data' => $request->all(),
            'payment_methods' => $request->input('pos_pro_active_payment_methods'),
            'default_method' => $request->input('pos_pro_default_payment_method'),
        ]);

        return parent::postEdit($request);
    }
}