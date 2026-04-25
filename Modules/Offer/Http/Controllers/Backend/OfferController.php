<?php

namespace Modules\Offer\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Offer\Http\Requests\OfferFormRequest;
use Modules\Offer\Models\Offer;
use Modules\Offer\Services\OfferAuditService;
use Modules\Offer\Traits\ManagesOfferPivots;

class OfferController extends Controller
{
    use ManagesOfferPivots;
    protected $module_title = 'offer.lbl_offers';
    protected $module_name = 'offers';

    public function __construct()
    {
        $this->middleware('can:view_offers')->only(['index', 'index_data', 'show']);
        $this->middleware('can:add_offers')->only(['create', 'store', 'duplicate']);
        $this->middleware('can:edit_offers')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_offers')->only(['destroy', 'bulk_action']);
    }

    public function index()
    {
        $module_title = __($this->module_title);
        $page_title = $module_title;

        return view('offer::backend.offers.index', compact('module_title', 'page_title'));
    }

    public function index_data(Request $request)
    {
        $query = Offer::query();

        // Vendor scoping
        if (auth()->user()->hasRole('vendor')) {
            $query->where('vendor_id', auth()->id());
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }
        if ($request->filled('has_coupon')) {
            $request->has_coupon == '1'
                ? $query->whereNotNull('code')
                : $query->whereNull('code');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'LIKE', "%{$search}%")
                  ->orWhere('title_ar', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('internal_name', 'LIKE', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 15);
        $data = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $data->items(),
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    public function create()
    {
        $module_title = __($this->module_title);
        $page_title = __('offer.lbl_create_offer');

        return view('offer::backend.offers.create', compact('module_title', 'page_title'));
    }

    public function store(OfferFormRequest $request)
    {
        $data = $request->validated();

        // Auto-set vendor for vendor role
        if (auth()->user()->hasRole('vendor')) {
            $data['vendor_id'] = auth()->id();
        }

        // Generate slug
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title_en']).'-'.Str::random(5);
        }

        // Extract pivot data before creating
        $pivots = $this->extractPivotData($data);

        $offer = Offer::create($data);

        // Sync pivots
        $this->syncOfferPivots($offer, $pivots['serviceIds'], $pivots['branchIds'], $pivots['categoryIds'], $pivots['segments'], $pivots['schedules']);

        // Banner image
        if ($request->hasFile('banner_image')) {
            $offer->addMediaFromRequest('banner_image')->toMediaCollection('offer_banners');
        }

        OfferAuditService::logCreated($offer);

        return response()->json(['status' => true, 'message' => __('offer.msg_created'), 'data' => $offer]);
    }

    public function show($id)
    {
        $offer = $this->findOfferScoped($id);
        $offer->load(['services.service', 'branches.branch', 'categories.category', 'customerSegments', 'schedules']);

        $module_title = __($this->module_title);
        $page_title = __('offer.lbl_offer_details');

        return view('offer::backend.offers.show', compact('module_title', 'page_title', 'offer'));
    }

    public function edit($id)
    {
        $offer = $this->findOfferScoped($id);
        $offer->load(['services', 'branches', 'categories', 'customerSegments', 'schedules']);

        $module_title = __($this->module_title);
        $page_title = __('offer.lbl_edit_offer');

        return view('offer::backend.offers.create', compact('module_title', 'page_title', 'offer'));
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

        return response()->json(['status' => true, 'message' => __('offer.msg_updated'), 'data' => $offer]);
    }

    public function destroy($id)
    {
        $offer = $this->findOfferScoped($id);
        OfferAuditService::logDeleted($offer);
        $offer->delete();

        return response()->json(['status' => true, 'message' => __('offer.msg_deleted')]);
    }

    public function update_status($id)
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

    public function bulk_action(Request $request)
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

        // Clone pivots using trait
        $this->cloneOfferPivots($offer, $newOffer);

        OfferAuditService::logCreated($newOffer);

        return response()->json(['status' => true, 'message' => __('offer.msg_duplicated'), 'data' => $newOffer]);
    }
}
