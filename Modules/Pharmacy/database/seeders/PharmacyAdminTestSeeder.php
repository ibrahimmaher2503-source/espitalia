<?php

namespace Modules\Pharmacy\Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyCommission;
use Modules\Pharmacy\Models\PharmacyCommissionEarning;
use Modules\Pharmacy\Models\PharmacyInventory;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Models\PharmacyOrderItem;
use Modules\Pharmacy\Models\PharmacyPayout;
use Modules\Pharmacy\Models\PharmacyProduct;

class PharmacyAdminTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Commission plans ──────────────────────────────────────────────
        $percentageCommission = PharmacyCommission::firstOrCreate(
            ['title' => 'Standard 10%'],
            ['commission_type' => PharmacyCommission::TYPE_PERCENTAGE, 'commission_value' => 10.00, 'status' => true]
        );
        $fixedCommission = PharmacyCommission::firstOrCreate(
            ['title' => 'Fixed 5 KWD'],
            ['commission_type' => PharmacyCommission::TYPE_FIXED, 'commission_value' => 5.00, 'status' => true]
        );
        $premiumCommission = PharmacyCommission::firstOrCreate(
            ['title' => 'Premium 15%'],
            ['commission_type' => PharmacyCommission::TYPE_PERCENTAGE, 'commission_value' => 15.00, 'status' => true]
        );
        // Inactive plan — to test filtering active plans
        PharmacyCommission::firstOrCreate(
            ['title' => 'Legacy 8%'],
            ['commission_type' => PharmacyCommission::TYPE_PERCENTAGE, 'commission_value' => 8.00, 'status' => false]
        );

        $commissionCycle = [$percentageCommission, $fixedCommission, $premiumCommission];

        // ── 2. Additional pharmacies ─────────────────────────────────────────
        $extraPharmacies = [
            ['name' => 'MediPlus Pharmacy',  'phone' => '+965-11111111', 'address' => 'Jahra, Kuwait',     'latitude' => 29.3367, 'longitude' => 47.6581, 'delivery_fee' => 1.50],
            ['name' => 'CareRx Pharmacy',    'phone' => '+965-22222222', 'address' => 'Ahmadi, Kuwait',    'latitude' => 29.0764, 'longitude' => 48.0842, 'delivery_fee' => 2.00],
            ['name' => 'HealthHub Pharmacy', 'phone' => '+965-33333333', 'address' => 'Farwaniya, Kuwait', 'latitude' => 29.2758, 'longitude' => 47.9582, 'delivery_fee' => 2.75],
            ['name' => 'PharmaGo Pharmacy',  'phone' => '+965-44444444', 'address' => 'Mubarak, Kuwait',   'latitude' => 29.4001, 'longitude' => 47.9003, 'delivery_fee' => 3.50],
        ];
        foreach ($extraPharmacies as $pd) {
            Pharmacy::firstOrCreate(['name' => $pd['name']], array_merge($pd, ['is_active' => true]));
        }

        // One inactive pharmacy — to test status toggle
        Pharmacy::firstOrCreate(
            ['name' => 'Inactive Demo Pharmacy'],
            ['phone' => '+965-99999999', 'address' => 'Test Area', 'latitude' => 29.3000, 'longitude' => 47.9000, 'delivery_fee' => 5.00, 'is_active' => false]
        );

        // ── 3. Assign commission plans to pharmacies ─────────────────────────
        $pharmacies = Pharmacy::where('is_active', true)->get();
        foreach ($pharmacies as $idx => $pharmacy) {
            $pharmacy->update(['commission_id' => $commissionCycle[$idx % 3]->id]);
        }

        // ── 4. Pharmacy staff users (one per active pharmacy) ─────────────────
        $staffDefs = [
            ['email' => 'pharmacy@espitalia.com',  'first_name' => 'أحمد',    'last_name' => 'الصيدلي', 'mobile' => '+966-55-1234567'],
            ['email' => 'pharmacy2@espitalia.com', 'first_name' => 'سارة',    'last_name' => 'العمري',  'mobile' => '+966-55-2234567'],
            ['email' => 'pharmacy3@espitalia.com', 'first_name' => 'محمد',    'last_name' => 'الراشد',  'mobile' => '+966-55-3234567'],
            ['email' => 'pharmacy4@espitalia.com', 'first_name' => 'نورة',    'last_name' => 'الحربي',  'mobile' => '+966-55-4234567'],
            ['email' => 'pharmacy5@espitalia.com', 'first_name' => 'عبدالله', 'last_name' => 'السالم',  'mobile' => '+966-55-5234567'],
            ['email' => 'pharmacy6@espitalia.com', 'first_name' => 'ريم',     'last_name' => 'الزهراني','mobile' => '+966-55-6234567'],
        ];

        foreach ($pharmacies as $idx => $pharmacy) {
            if (! isset($staffDefs[$idx])) {
                break;
            }
            $def = $staffDefs[$idx];
            $staffUser = User::firstOrCreate(
                ['email' => $def['email']],
                [
                    'first_name'        => $def['first_name'],
                    'last_name'         => $def['last_name'],
                    'password'          => Hash::make('12345678'),
                    'mobile'            => $def['mobile'],
                    'date_of_birth'     => '1990-01-01',
                    'country'           => 194,
                    'state'             => 3812,
                    'city'              => 41432,
                    'pincode'           => '12345',
                    'gender'            => 'male',
                    'email_verified_at' => Carbon::now(),
                    'user_type'         => 'pharmacy',
                    'pharmacy_id'       => $pharmacy->id,
                ]
            );
            $staffUser->update(['pharmacy_id' => $pharmacy->id]);
            if (! $staffUser->hasRole('pharmacy')) {
                $staffUser->assignRole('pharmacy');
            }
        }

        // ── 5. Customer users (3) with addresses ─────────────────────────────
        $customerDefs = [
            ['email' => 'phcustomer@espitalia.com',  'first_name' => 'عميل',   'last_name' => 'الصيدلية', 'mobile' => '+966-55-9876543'],
            ['email' => 'phcustomer2@espitalia.com', 'first_name' => 'فاطمة',  'last_name' => 'العلي',    'mobile' => '+966-55-9876544'],
            ['email' => 'phcustomer3@espitalia.com', 'first_name' => 'خالد',   'last_name' => 'المنصور',  'mobile' => '+966-55-9876545'],
        ];

        $customers = [];
        foreach ($customerDefs as $cd) {
            $customer = User::firstOrCreate(
                ['email' => $cd['email']],
                array_merge($cd, [
                    'password'          => Hash::make('12345678'),
                    'date_of_birth'     => '1990-01-01',
                    'country'           => 194,
                    'state'             => 3812,
                    'city'              => 41432,
                    'pincode'           => '12345',
                    'gender'            => 'male',
                    'email_verified_at' => Carbon::now(),
                    'user_type'         => 'user',
                ])
            );
            if (! $customer->hasRole('user')) {
                $customer->assignRole('user');
            }
            $address = Address::where('addressable_type', User::class)
                ->where('addressable_id', $customer->id)->first();
            if (! $address) {
                $address = new Address([
                    'address_line_1' => 'Block 5, Street 15',
                    'city'           => '41432',
                    'state'          => '3812',
                    'country'        => '194',
                    'is_primary'     => true,
                ]);
                $customer->address()->save($address);
                $address = $address->fresh();
            }
            $customers[] = ['user' => $customer, 'address' => $address];
        }

        // ── 6. Full inventory for every active pharmacy ───────────────────────
        $allProducts = PharmacyProduct::all();
        foreach ($pharmacies as $pharmacy) {
            foreach ($allProducts as $product) {
                PharmacyInventory::firstOrCreate(
                    ['pharmacy_id' => $pharmacy->id, 'product_id' => $product->id],
                    [
                        'price'        => round(rand(200, 5000) / 100, 2), // 2.00 – 50.00
                        'stock_qty'    => rand(10, 200),
                        'is_available' => true,
                    ]
                );
            }

            // Edge-case items for inventory panel testing
            $inv = PharmacyInventory::where('pharmacy_id', $pharmacy->id)->get();
            if ($inv->count() >= 1) {
                $inv[0]->update(['stock_qty' => 0]);                      // out of stock
            }
            if ($inv->count() >= 2) {
                $inv[1]->update(['is_available' => false]);               // manually disabled
            }
            if ($inv->count() >= 3) {
                $inv[2]->update(['stock_qty' => 2, 'is_available' => true]); // low stock
            }
        }

        // ── 7. Orders in every status for each active pharmacy ────────────────
        $allStatuses = [
            PharmacyOrder::STATUS_PENDING,
            PharmacyOrder::STATUS_CONFIRMED,
            PharmacyOrder::STATUS_PREPARING,
            PharmacyOrder::STATUS_ON_THE_WAY,
            PharmacyOrder::STATUS_DELIVERED,
            PharmacyOrder::STATUS_DELIVERED,   // two delivered orders → two earnings
            PharmacyOrder::STATUS_CANCELLED,
        ];

        $deliveredOrdersByPharmacy = [];

        foreach ($pharmacies as $phIdx => $pharmacy) {
            // Collect any delivered orders already seeded
            $existing = PharmacyOrder::where('pharmacy_id', $pharmacy->id)
                ->where('status', PharmacyOrder::STATUS_DELIVERED)->get()->all();

            if (PharmacyOrder::where('pharmacy_id', $pharmacy->id)->exists()) {
                $deliveredOrdersByPharmacy[$pharmacy->id] = $existing;
                continue;
            }

            $inventory = PharmacyInventory::where('pharmacy_id', $pharmacy->id)
                ->where('stock_qty', '>', 0)
                ->where('is_available', true)
                ->with('product')
                ->take(3)
                ->get();

            if ($inventory->isEmpty()) {
                continue;
            }

            $customer      = $customers[$phIdx % count($customers)]['user'];
            $address       = $customers[$phIdx % count($customers)]['address'];
            $deliveryFee   = (float) ($pharmacy->delivery_fee ?? 2.50);
            $deliveredRows = [];

            foreach ($allStatuses as $sIdx => $status) {
                $subtotal  = 0;
                $itemsData = [];

                foreach ($inventory as $inv) {
                    $qty      = rand(1, 3);
                    $price    = (float) $inv->price;
                    $subtotal += $price * $qty;
                    $itemsData[] = [
                        'product_id'   => $inv->product_id,
                        'product_name' => $inv->product->name_en ?? 'Product',
                        'quantity'     => $qty,
                        'price'        => $price,
                    ];
                }

                $order = PharmacyOrder::create([
                    'user_id'        => $customer->id,
                    'pharmacy_id'    => $pharmacy->id,
                    'address_id'     => $address->id,
                    'subtotal'       => round($subtotal, 2),
                    'delivery_fee'   => $deliveryFee,
                    'total'          => round($subtotal + $deliveryFee, 2),
                    'payment_method' => $sIdx % 2 === 0 ? 'cash_on_delivery' : 'online',
                    'status'         => $status,
                ]);

                foreach ($itemsData as $item) {
                    PharmacyOrderItem::create(array_merge($item, ['order_id' => $order->id]));
                }

                if ($status === PharmacyOrder::STATUS_DELIVERED) {
                    $deliveredRows[] = $order;
                }
            }

            $deliveredOrdersByPharmacy[$pharmacy->id] = $deliveredRows;
        }

        // ── 8. Commission earnings for every delivered order ──────────────────
        foreach ($pharmacies as $pharmacy) {
            $commission     = $pharmacy->refresh()->commission;
            $deliveredOrders = $deliveredOrdersByPharmacy[$pharmacy->id] ?? [];

            foreach ($deliveredOrders as $order) {
                if (PharmacyCommissionEarning::where('order_id', $order->id)->exists()) {
                    continue;
                }

                $subtotal = (float) $order->subtotal;
                if ($commission && $commission->commission_type === PharmacyCommission::TYPE_PERCENTAGE) {
                    $commissionAmount = round($subtotal * (float) $commission->commission_value / 100, 2);
                } elseif ($commission) {
                    $commissionAmount = min((float) $commission->commission_value, $subtotal);
                } else {
                    $commissionAmount = 0;
                }

                PharmacyCommissionEarning::create([
                    'order_id'            => $order->id,
                    'pharmacy_id'         => $pharmacy->id,
                    'commission_id'       => $commission ? $commission->id : null,
                    'order_subtotal'      => $subtotal,
                    'commission_type'     => $commission ? $commission->commission_type : 'percentage',
                    'commission_value'    => $commission ? $commission->commission_value : 0,
                    'commission_amount'   => $commissionAmount,
                    'pharmacy_receivable' => round($subtotal - $commissionAmount, 2),
                    'status'              => PharmacyCommissionEarning::STATUS_UNPAID,
                ]);
            }
        }

        // ── 9. Payouts — first 3 pharmacies get one paid payout each ─────────
        //    The remaining earnings stay unpaid so admin can test the payout flow.
        foreach ($pharmacies->take(3) as $pidx => $pharmacy) {
            $earning = PharmacyCommissionEarning::where('pharmacy_id', $pharmacy->id)
                ->where('status', PharmacyCommissionEarning::STATUS_UNPAID)
                ->first();

            if (! $earning) {
                continue;
            }

            $payoutDate = Carbon::today()->subDays(($pidx + 1) * 3);

            $payout = PharmacyPayout::firstOrCreate(
                ['pharmacy_id' => $pharmacy->id, 'payment_date' => $payoutDate->toDateString()],
                [
                    'total_amount'     => $earning->pharmacy_receivable,
                    'total_commission' => $earning->commission_amount,
                    'payment_method'   => $pidx % 2 === 0
                        ? PharmacyPayout::PAYMENT_METHOD_BANK_TRANSFER
                        : PharmacyPayout::PAYMENT_METHOD_CASH,
                    'notes' => 'Sample payout for admin testing — pharmacy #' . ($pidx + 1),
                ]
            );

            $earning->update([
                'status'    => PharmacyCommissionEarning::STATUS_PAID,
                'payout_id' => $payout->id,
            ]);
        }
    }
}
