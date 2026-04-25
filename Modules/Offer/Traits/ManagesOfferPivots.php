<?php

namespace Modules\Offer\Traits;

use Modules\Offer\Models\Offer;

trait ManagesOfferPivots
{
    protected function findOfferScoped($id): Offer
    {
        $query = Offer::query();
        if (auth()->user()->hasRole('vendor')) {
            $query->where('vendor_id', auth()->id());
        }

        return $query->findOrFail($id);
    }

    protected function syncOfferPivots(Offer $offer, array $serviceIds, array $branchIds, array $categoryIds, array $segments, array $schedules): void
    {
        $now = now();

        // Services
        $offer->services()->delete();
        foreach ($serviceIds as $sid) {
            $offer->services()->create(['service_id' => $sid, 'created_at' => $now]);
        }

        // Branches
        $offer->branches()->delete();
        foreach ($branchIds as $bid) {
            $offer->branches()->create(['branch_id' => $bid, 'created_at' => $now]);
        }

        // Categories
        $offer->categories()->delete();
        foreach ($categoryIds as $cid) {
            $offer->categories()->create(['category_id' => $cid, 'created_at' => $now]);
        }

        // Customer Segments
        $offer->customerSegments()->delete();
        foreach ($segments as $seg) {
            $offer->customerSegments()->create([
                'segment_type' => $seg['segment_type'],
                'segment_value' => $seg['segment_value'],
                'created_at' => $now,
            ]);
        }

        // Schedules
        $offer->schedules()->delete();
        foreach ($schedules as $sch) {
            $offer->schedules()->create([
                'day_of_week' => $sch['day_of_week'] ?? null,
                'start_time' => $sch['start_time'] ?? null,
                'end_time' => $sch['end_time'] ?? null,
                'created_at' => $now,
            ]);
        }
    }

    protected function extractPivotData(array &$data): array
    {
        $serviceIds = $data['service_ids'] ?? [];
        $branchIds = $data['branch_ids'] ?? [];
        $categoryIds = $data['category_ids'] ?? [];
        $segments = $data['customer_segments'] ?? [];
        $schedules = $data['schedules'] ?? [];

        unset($data['service_ids'], $data['branch_ids'], $data['category_ids'], $data['customer_segments'], $data['schedules'], $data['banner_image']);

        return compact('serviceIds', 'branchIds', 'categoryIds', 'segments', 'schedules');
    }

    protected function cloneOfferPivots(Offer $source, Offer $target): void
    {
        $now = now();

        foreach ($source->services as $s) {
            $target->services()->create(['service_id' => $s->service_id, 'created_at' => $now]);
        }
        foreach ($source->branches as $b) {
            $target->branches()->create(['branch_id' => $b->branch_id, 'created_at' => $now]);
        }
        foreach ($source->categories as $c) {
            $target->categories()->create(['category_id' => $c->category_id, 'created_at' => $now]);
        }
        foreach ($source->customerSegments as $seg) {
            $target->customerSegments()->create([
                'segment_type' => $seg->segment_type,
                'segment_value' => $seg->segment_value,
                'created_at' => $now,
            ]);
        }
        foreach ($source->schedules as $sch) {
            $target->schedules()->create([
                'day_of_week' => $sch->day_of_week,
                'start_time' => $sch->start_time,
                'end_time' => $sch->end_time,
                'created_at' => $now,
            ]);
        }
    }
}
