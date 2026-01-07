<?php

namespace Botble\Marketplace\Http\Controllers\Vendor;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Forms\Vendor\LanguageSettingForm;
use Botble\Marketplace\Http\Requests\Vendor\LanguageSettingRequest;
use Illuminate\Support\Facades\Auth;

class LanguageSettingController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Settings'));

        /**
         * @var Customer $customer
         */
        $customer = auth('customer')->user();

        return LanguageSettingForm::createFromModel($customer)->renderForm();
    }

    public function update(LanguageSettingRequest $request)
    {
        /**
         * @var Customer $customer
         */
        $customer = auth('customer')->user();

        if (!$customer) {
            return redirect()->route('customer.login')->withErrors([
                'auth' => __('Please login to continue.')
            ]);
        }

        // Store the locale in customer metadata
        $locale = $request->input('locale');
        if ($locale) {
            $customer->setMetaData('locale', $locale);
            $customer->save();
        }

        // Update the current session locale
        if ($locale) {
            app()->setLocale($locale);
            $request->setLocale($locale);
            
            // Ensure session is saved properly
            $request->session()->put('locale', $locale);
            $request->session()->save();
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.vendor.language-settings.index'))
            ->withUpdatedSuccessMessage();
    }
}
