<?php

namespace Modules\Location\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class GovernorateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
