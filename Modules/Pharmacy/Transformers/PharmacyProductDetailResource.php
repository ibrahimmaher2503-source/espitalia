<?php

namespace Modules\Pharmacy\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyProductDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $base = (new PharmacyProductResource($this))->toArray($request);

        return array_merge($base, [
            'description' => $this->description,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->getNameAttribute(),
            ] : null,
            'subcategory' => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->getNameAttribute(),
            ] : null,
        ]);
    }
}
