<?php

namespace Botble\Marketplace\Http\Requests\Vendor;

use Botble\Base\Supports\Language;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class LanguageSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'locale' => ['sometimes', 'required', 'string', Rule::in(array_keys(Language::getAvailableLocales()))],
        ];
    }

    public function messages(): array
    {
        return [
            'locale.required' => __('Please select a language.'),
            'locale.in' => __('The selected language is invalid.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'locale' => __('Language'),
        ];
    }
}
