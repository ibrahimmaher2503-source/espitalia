<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\IntensiveCare\Models\IcuCommissionEarning;
use Modules\IntensiveCare\Transformers\IcuCommissionResource;

class IcuCommissionsApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user->hasAnyRole(['admin', 'demo_admin'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to perform this action',
                ], 403);
            }

            $query = IcuCommission::withCount('hospitals');

            // Add total_earned via subquery (sum of commission_amount from earnings)
            $query->addSelect([
                'icu_commissions.*',
                'total_earned' => IcuCommissionEarning::selectRaw('COALESCE(SUM(commission_amount), 0)')
                    ->whereColumn('commission_id', 'icu_commissions.id')
                    ->where('status', '!=', IcuCommissionEarning::STATUS_CANCELLED),
            ]);

            // Search filter on title
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('title', 'LIKE', "%{$search}%");
            }

            $perPage = min((int) ($request->per_page ?? 15), 50);
            $commissions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return IcuCommissionResource::collection($commissions)->additional([
                'status' => true,
                'message' => 'Commission template list',
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }
}
