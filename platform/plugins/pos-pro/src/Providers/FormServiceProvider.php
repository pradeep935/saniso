<?php

namespace Botble\PosPro\Providers;

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\Fields\CheckboxField;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Models\Product;

class FormServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
            if ($data instanceof Product && $data->is_variation == 0) {
                $isAvailableInPos = $data->is_available_in_pos ?? true;

                $form
                    ->add(
                        'is_available_in_pos',
                        CheckboxField::class,
                        CheckboxFieldOption::make()
                            ->label(trans('plugins/pos-pro::pos.is_available_in_pos.label'))
                            ->checked($isAvailableInPos)
                            ->helperText(trans('plugins/pos-pro::pos.is_available_in_pos.helper'))
                    );
            }

            return $form;
        }, 120, 2);

        add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($screen, $request, $data) {
            if ($data instanceof Product) {

                if ($data->is_variation == 1) {
                    return;
                }

                $data->is_available_in_pos = (bool) $request->input('is_available_in_pos');
                $data->save();
            }
        }, 120, 3);
    }
}
