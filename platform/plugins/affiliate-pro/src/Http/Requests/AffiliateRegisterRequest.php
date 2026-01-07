<?php

namespace Botble\AffiliatePro\Http\Requests;

use Botble\Support\Http\Requests\Request;

class AffiliateRegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'terms' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => trans('plugins/affiliate-pro::affiliate.terms_accepted_required'),
        ];
    }
}
