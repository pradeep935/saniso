<?php

namespace Botble\AffiliatePro\Http\Controllers\Settings;

use Botble\AffiliatePro\Forms\Settings\AffiliateSettingForm;
use Botble\AffiliatePro\Http\Controllers\BaseController;
use Botble\AffiliatePro\Http\Requests\Settings\AffiliateSettingRequest;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Arr;

class AffiliateSettingController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/affiliate-pro::affiliate.name'))
            ->add(trans('plugins/affiliate-pro::settings.title'), route('affiliate-pro.settings'));
    }
    public function index(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/affiliate-pro::settings.title'));

        $productCategories = ProductCategory::query()->get();

        $form = $formBuilder->create(AffiliateSettingForm::class);

        return view('plugins/affiliate-pro::settings.index', compact('productCategories', 'form'));
    }

    public function update(AffiliateSettingRequest $request, BaseHttpResponse $response)
    {
        $data = $request->except(['_token']);

        $categoryCommission = Arr::get($data, 'commission_by_category', []);

        if (! empty($categoryCommission) && $request->input('enable_commission_for_each_category')) {
            $newCategoryCommission = [];

            foreach ($categoryCommission as $item) {
                if (empty($item['categories'])) {
                    continue;
                }

                // Process categories data
                $categories = $item['categories'];

                // If it's a string, try to decode it as JSON
                if (is_string($categories)) {
                    $decodedCategories = json_decode($categories, true);

                    // If it's valid JSON, use the decoded value
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $categories = $decodedCategories;
                    } else {
                        // If it's not valid JSON, try to split by comma
                        $categories = explode(',', $categories);
                    }
                }

                // Extract category IDs
                $categoryIds = [];
                if (is_array($categories)) {
                    foreach ($categories as $category) {
                        if (is_array($category) && isset($category['id'])) {
                            $categoryIds[] = $category['id'];
                        } elseif (is_numeric($category)) {
                            $categoryIds[] = $category;
                        }
                    }
                }

                if (empty($categoryIds)) {
                    continue;
                }

                $newCategoryCommission[] = [
                    'commission_percentage' => $item['commission_percentage'],
                    'categories' => json_encode($categoryIds),
                ];
            }

            $categoryCommission = $newCategoryCommission;
        }

        $data['commission_by_category'] = json_encode($categoryCommission);

        foreach ($data as $settingKey => $settingValue) {
            Setting::set('affiliate_' . $settingKey, $settingValue);
        }

        Setting::save();

        return $response
            ->setPreviousUrl(route('affiliate-pro.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
