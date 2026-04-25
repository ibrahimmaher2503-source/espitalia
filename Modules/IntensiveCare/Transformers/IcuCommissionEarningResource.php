<?php

namespace Modules\IntensiveCare\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class IcuCommissionEarningResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'admission_request' => $this->when(
                $this->relationLoaded('admissionRequest') && $this->admissionRequest,
                function () {
                    return [
                        'id' => $this->admissionRequest->id,
                        'request_number' => $this->admissionRequest->request_number,
                    ];
                }
            ),
            'hospital' => $this->when(
                $this->relationLoaded('hospital') && $this->hospital,
                function () {
                    return [
                        'id' => $this->hospital->id,
                        'name' => $this->hospital->name,
                    ];
                }
            ),
            'commission_id' => $this->commission_id,
            'order_subtotal' => $this->order_subtotal,
            'commission_type' => $this->commission_type,
            'commission_value' => $this->commission_value,
            'commission_amount' => $this->commission_amount,
            'hospital_receivable' => $this->hospital_receivable,
            'status' => $this->status,
            'payout_id' => $this->payout_id,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}
