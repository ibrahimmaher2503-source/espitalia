<?php

namespace Modules\Wallet\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Wallet\Models\Wallet;
use Auth;
use Modules\Wallet\Models\WalletHistory;
use Modules\Wallet\Transformers\WalletHistoryResource;

class walletcontroller extends Controller
{
    public function getPatientWallet(Request $request){
        $user = Auth::user();
        $walletAamount = 0;
        $wallet = Wallet::where('user_id',$user->id)->first();

        if($wallet !== null){
            $walletAamount = $wallet->amount;
        }
        
        return response()->json([
            'wallet_amount' => $walletAamount,
            'status' => true,
            'message' => __('messages.wallet_balance'),
        ], 200);
    }

    public function getWalletHistory(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);

        // Wallet history is private financial data. The bearer token is the
        // authority; a caller cannot select another wallet owner.
        $user_id = auth()->id();

        $wallet_history = WalletHistory::with('wallet')->where('user_id', $user_id);
        
        $wallet_history = $wallet_history->orderBy('updated_at', 'desc')->paginate($perPage);
        
        $data = WalletHistoryResource::collection($wallet_history);
   
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => __('appointment.wallet_history'),
        ], 200);
    }
}
