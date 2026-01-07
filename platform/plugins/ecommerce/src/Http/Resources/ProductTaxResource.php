<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Models\Tax;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTaxResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var Tax $this */
        return [
            'id' => $this->id,
            'title' => $this->title,
            'percentage' => $this->percentage,
            'status' => $this->status,
        ];
    }
}
