<?php

namespace Botble\AffiliatePro\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Http\Request;

class SettingsController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::settings.name'), route('affiliate-pro.settings'));
    }
    public function index()
    {
        $this->pageTitle(trans('plugins/affiliate-pro::settings.name'));

        return view('plugins/affiliate-pro::settings.index');
    }

    public function update(Request $request, BaseHttpResponse $response, SettingStore $settingStore)
    {
        $data = $request->except(['_token']);

        foreach ($data as $settingKey => $settingValue) {
            $settingStore->set('affiliate_' . $settingKey, $settingValue);
        }

        $settingStore->save();

        return $response
            ->setPreviousUrl(route('affiliate-pro.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
