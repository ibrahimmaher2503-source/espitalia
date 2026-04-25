<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Services\PharmacyMatchingService;

class PharmacyMatchingApiController extends Controller
{
    protected $matchingService;

    public function __construct(PharmacyMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Find available pharmacies for user's cart
     */
    public function index(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        // Verify address belongs to user
        $address = \App\Models\Address::where('id', $request->address_id)
            ->where('addressable_id', auth()->id())
            ->where('addressable_type', 'App\Models\User')
            ->firstOrFail();

        $result = $this->matchingService->findAvailablePharmacies(
            auth()->id(),
            $request->address_id
        );

        return response()->json([
            'status' => true,
            'data' => $result['pharmacies'],
            'message' => $result['message'],
        ]);
    }
}
