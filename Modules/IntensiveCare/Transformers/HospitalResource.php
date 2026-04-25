<?php

namespace Modules\IntensiveCare\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class HospitalResource extends JsonResource
{
    public function toArray($request)
    {
        // Compute price range from loaded departments
        $priceRange = null;
        if ($this->relationLoaded('icuDepartments') && $this->icuDepartments->isNotEmpty()) {
            $activeDepts = $this->icuDepartments->where('status', true);
            if ($activeDepts->isNotEmpty()) {
                $priceRange = [
                    'min' => number_format($activeDepts->min('daily_price'), 2, '.', ''),
                    'max' => number_format($activeDepts->max('daily_price'), 2, '.', ''),
                ];
            }
        }

        // Compute available beds total from loaded departments
        $availableBedsTotal = null;
        if ($this->relationLoaded('icuDepartments')) {
            $availableBedsTotal = $this->icuDepartments->where('status', true)->sum('available_beds');
        }

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'slug' => $this->slug,
            'hospital_type' => $this->hospital_type,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'license_number' => $this->license_number,
            'accepted_insurance' => $this->accepted_insurance,
            'rating' => $this->rating,
            'status' => $this->status,
            'governorate' => $this->whenLoaded('governorate', function () {
                return $this->governorate ? [
                    'id' => $this->governorate->id,
                    'name' => $this->governorate->name,
                ] : null;
            }),
            'city' => $this->whenLoaded('city', function () {
                return $this->getRelation('city') ? [
                    'id' => $this->getRelation('city')->id,
                    'name' => $this->getRelation('city')->name,
                ] : null;
            }),
            'logo_url' => $this->getFirstMediaUrl('logo') ?: null,
            'departments_count' => $this->when(
                $this->icu_departments_count !== null,
                $this->icu_departments_count
            ),
            'available_beds_total' => $this->when($availableBedsTotal !== null, $availableBedsTotal),
            'price_range' => $this->when($priceRange !== null, $priceRange),
            'gallery' => $this->when($this->relationLoaded('media'), function () {
                return $this->getMedia('gallery')->map(fn ($media) => $media->getUrl());
            }),
            'departments' => IcuDepartmentResource::collection($this->whenLoaded('icuDepartments')),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];

        // Conditionally include commission info for admin role
        $user = auth()->user();
        if ($user && $user->hasAnyRole(['admin', 'demo_admin'])) {
            $data['commission'] = $this->when($this->relationLoaded('commission') && $this->commission, function () {
                return [
                    'id' => $this->commission->id,
                    'title' => $this->commission->title,
                    'commission_type' => $this->commission->commission_type,
                    'commission_value' => $this->commission->commission_value,
                ];
            });
        }

        return $data;
    }
}
