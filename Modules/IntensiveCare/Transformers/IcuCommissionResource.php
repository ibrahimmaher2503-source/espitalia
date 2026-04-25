<?php

namespace Modules\IntensiveCare\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class IcuCommissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'commission_type' => $this->commission_type,
            'commission_value' => $this->commission_value,
            'status' => $this->status,
            'hospitals_count' => $this->when(
                $this->hospitals_count !== null,
                $this->hospitals_count
            ),
            'total_earned' => $this->when(
                $this->total_earned !== null,
                fn () => number_format((float) $this->total_earned, 2, '.', '')
            ),
        ];
    }
}
