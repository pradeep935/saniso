<?php

namespace Botble\Mollie\Http\Controllers\Settings;

use Botble\Mollie\Forms\PosProExtendedSettingForm;
use Botble\Mollie\Http\Requests\PosProExtendedSettingRequest;
use Botble\Setting\Http\Controllers\SettingController;

class ExtendedPosProSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/pos-pro::pos.settings.title'));

        return PosProExtendedSettingForm::create()->renderForm();
    }

    public function update(PosProExtendedSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}