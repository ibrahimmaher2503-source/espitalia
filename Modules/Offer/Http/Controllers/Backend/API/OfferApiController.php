<?php

namespace Modules\Offer\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Offer\Http\Requests\OfferFormRequest;
use Modules\Offer\Models\Offer;
use Modules\Offer\Models\OfferUsage;
use Modules\Offer\Services\OfferAuditService;
use Modules\Offer\Traits\ManagesOfferPivots;
use Modules\Offer\Transformers\OfferDetailResource;
use Modules\Offer\Transformers\OfferResource;
use Modules\Offer\Transformers\OfferUsageResource;

class OfferApiController extends Controller
{
    use ManagesOfferPivots;
    public function __construct()
    {
        $this->middleware('can:view_offers')->only(['index', 'show', 'usageLogs', 'allUsageLogs']);
        $this->middleware('can:add_offers')->only(['store', 'duplicate']);
        $this->middleware('can:edit_offers')->only(['update', 'updateStatus']);
        $this->middleware('can:delete_offers')->only(['destroy', 'bulkAction']);
    }

    public function index(Request $request)
    {
        $query = Offer::query();

        if (auth()->user()->hasRole('vendor')) {
            $query->where('vendor_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $data = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'data' => OfferResource::collection($data->items()),
            'meta' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $offer = $this->findOfferScoped($id);
        $offer->load(['services.service', 'branches.branch', 'categories.category', 'customerSegments', 'schedules']);

        return response()->json([
            'status' => true,
            'data' => new OfferDetailResource($offer),
        ]);
    }

    public function store(OfferFormRequest $request)
    {
        $data = $request->validated();

        if (auth()->user()->hasRole('vendor')) {
            $data['vendor_id'] = auth()->id();
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title_en']).'-'.Str::random(5);
        }

        $pivots = $this->extractPivotData($data);

        $offer = Offer::create($data);
        $this->syncOfferPivots($offer, $pivots['serviceIds'], $pivots['branchIds'], $pivots['categoryIds'], $pivots['segments'], $pivots['schedules']);

        if ($request->hasFile('banner_image')) {
            $offer->addMediaFromRequest('banner_image')->toMediaCollection('offer_banners');
        }

        OfferAuditService::logCreated($offer);

        return response()->json([
            'status' => true,
            'message' => __('offer.msg_created'),
            'data' => new OfferDetailResource($offer->load(['services', 'branches', 'categories', 'customerSegments', 'schedules'])),
        ], 201);
    }

    public function update(OfferFormRequest $request, $id)
    {
        $offer = $this->findOfferScoped($id);
        $oldValues = $offer->toArray();

        $data = $request->validated();
        $pivots = $this->extractPivotData($data);

        $offer->update($data);
        $this->syncOfferPivots($offer, $pivots['serviceIds'], $pivots['branchIds'], $pivots['categoryIds'], $pivots['segments'], $pivots['schedules']);

        if ($request->hasFile('banner_image')) {
            $offer->clearMediaCollection('offer_banners');
            $offer->addMediaFromRequest('banner_image')->toMediaCollection('offer_banners');
        }

        OfferAuditService::logUpdated($offer, $oldValues);

        return response()->json([
            'status' => true,
            'message' => __('offer.msg_updated'),
            'data' => new OfferDetailResource($offer->fresh(['services', 'branches', 'categories', 'customerSegments', 'schedules'])),
        ]);
    }

    public function destroy($id)
    {
        $offer = $this->findOfferScoped($id);
        OfferAuditService::logDeleted($offer);
        $offer->delete();

        return response()->json(['status' => true, 'message' => __('offer.msg_deleted')]);
    }

    public function updateStatus($id)
    {
        $offer = $this->findOfferScoped($id);
        $oldStatus = $offer->status;

        // Guard: cannot re-activate expired or exhausted offers
        if (in_array($offer->status, [Offer::STATUS_EXPIRED, Offer::STATUS_EXHAUSTED])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot re-activate an '.$offer->status.' offer. Extend the date or raise the usage limit first.',
            ], 422);
        }

        if ($offer->status === Offer::STATUS_ACTIVE) {
            $offer->update(['status' => Offer::STATUS_PAUSED, 'is_active' => false]);
        } else {
            $offer->update(['status' => Offer::STATUS_ACTIVE, 'is_active' => true]);
        }

        OfferAuditService::logStatusChanged($offer, $oldStatus, $offer->status);

        return response()->json(['status' => true, 'message' => __('offer.msg_status_updated')]);
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action_type', 'delete');

        $query = Offer::whereIn('id', $ids);
        if (auth()->user()->hasRole('vendor')) {
            $query->where('vendor_id', auth()->id());
        }

        switch ($action) {
            case 'activate':
                $query->whereNotIn('status', [Offer::STATUS_EXPIRED, Offer::STATUS_EXHAUSTED])
                    ->update(['status' => Offer::STATUS_ACTIVE, 'is_active' => true]);
                break;
            case 'pause':
                $query->update(['status' => Offer::STATUS_PAUSED, 'is_active' => false]);
                break;
            case 'delete':
                $query->delete();
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function usageLogs(Request $request, $id)
    {
        $data = OfferUsage::where('offer_id', $id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'data' => OfferUsageResource::collection($data->items()),
            'meta' => [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ]);
    }

    public function allUsageLogs(Request $request)
    {
        $query = OfferUsage::with(['offer', 'user']);

        if ($request->filled('offer_id')) {
            $query->where('offer_id', $request->offer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'data' => OfferUsageResource::collection($data->items()),
            'meta' => [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ]);
    }

    public function duplicate($id)
    {
        $offer = $this->findOfferScoped($id);
        $offer->load(['services', 'branches', 'categories', 'customerSegments', 'schedules']);

        $newOffer = $offer->replicate(['used_count', 'slug', 'code']);
        $newOffer->slug = Str::slug($offer->title_en).'-copy-'.Str::random(5);
        $newOffer->status = Offer::STATUS_DRAFT;
        $newOffer->is_active = false;
        $newOffer->used_count = 0;
        $newOffer->save();

        $this->cloneOfferPivots($offer, $newOffer);

        OfferAuditService::logCreated($newOffer);

        return response()->json([
            'status' => true,
            'message' => __('offer.msg_duplicated'),
            'data' => new OfferDetailResource($newOffer->load(['services', 'branches', 'categories'])),
        ], 201);
    }
}
