<?php

namespace App\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function AddressList(Request $request)
    {
        // Patient API address operations are always scoped to the bearer
        // token. Never let a client choose another user_id for an IDOR.
        $user_address = Auth::user()->addresses;

        $addressCollection = AddressResource::collection($user_address);

        $message = __('users.address_list');

        return response()->json(['message' => $message, 'data' => $addressCollection, 'status' => true], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_primary' => ['sometimes', 'boolean'],
            'governorate_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
            'label' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:32'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ]);

        $newAddress = DB::transaction(function () use ($data) {
            $user = Auth::user();
            if (($data['is_primary'] ?? false) === true) {
                $user->addresses()->update(['is_primary' => false]);
            }

            return $user->addresses()->create($data);
        });

        $message = __('users.address_store');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

     public function RemoveAddress(Request $request)
     {
         $address_id = $request->validate(['id' => ['required', 'integer']])['id'];

         $address = Auth::user()->addresses()->whereKey($address_id)->first();

         if (! $address) {
             $message = __('users.address_not_found');

             return response()->json(['message' => $message, 'status' => false], 404);
         }

         $address->delete();

         $message = __('users.address_deleted');

         return response()->json(['message' => $message, 'status' => true], 200);
     }

     public function EditAddress(Request $request)
     {
         $address_id = $request->validate(['id' => ['required', 'integer']])['id'];

         $address = Auth::user()->addresses()->whereKey($address_id)->first();

         if (! $address) {
             $message = __('users.address_not_found');

             return response()->json(['message' => $message, 'status' => false], 404);
         }

         $data = $request->validate([
             'first_name' => ['nullable', 'string', 'max:100'],
             'last_name' => ['nullable', 'string', 'max:100'],
             'address_line_1' => ['required', 'string', 'max:255'],
             'address_line_2' => ['nullable', 'string', 'max:255'],
             'postal_code' => ['nullable', 'string', 'max:32'],
             'city' => ['required', 'string', 'max:100'],
             'state' => ['required', 'string', 'max:100'],
             'country' => ['required', 'string', 'max:100'],
             'is_primary' => ['sometimes', 'boolean'],
         ]);

         if (($data['is_primary'] ?? false) === true) {
             Auth::user()->addresses()->where('id', '!=', $address_id)->update(['is_primary' => false]);
         }

         $address->update($data);

         $message = __('users.address_updated');

         return response()->json(['message' => $message, 'status' => true], 200);
     }
}
