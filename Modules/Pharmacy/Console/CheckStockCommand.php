<?php

namespace Modules\Pharmacy\Console;

use Illuminate\Console\Command;
use Modules\Pharmacy\Models\PharmacyInventory;
use Modules\Pharmacy\Models\PharmacyStockAlert;
use Modules\Pharmacy\Services\PharmacyNotificationService;

class CheckStockCommand extends Command
{
    protected $signature = 'pharmacy:check-stock';

    protected $description = 'Scan pharmacy inventory and create stock alerts for low/out-of-stock items';

    public function handle()
    {
        $this->info('Checking pharmacy stock levels...');

        $alertsCreated = 0;
        $alertsResolved = 0;
        $notificationService = new PharmacyNotificationService;

        PharmacyInventory::where('is_available', true)
            ->with('product', 'pharmacy')
            ->chunk(500, function ($inventories) use (&$alertsCreated, &$alertsResolved, $notificationService) {
        foreach ($inventories as $inventory) {
            $isLow = $inventory->stock_qty <= $inventory->low_stock_threshold && $inventory->stock_qty > 0;
            $isOut = $inventory->stock_qty === 0;

            if ($isOut) {
                $existing = PharmacyStockAlert::where('inventory_id', $inventory->id)
                    ->where('alert_type', PharmacyStockAlert::TYPE_OUT_OF_STOCK)
                    ->where('is_resolved', false)
                    ->first();

                if (! $existing) {
                    $alert = PharmacyStockAlert::create([
                        'pharmacy_id' => $inventory->pharmacy_id,
                        'inventory_id' => $inventory->id,
                        'alert_type' => PharmacyStockAlert::TYPE_OUT_OF_STOCK,
                        'threshold' => $inventory->low_stock_threshold,
                        'current_qty' => 0,
                    ]);
                    $notificationService->notifyStockAlert($alert);
                    $alertsCreated++;
                }
            } elseif ($isLow) {
                $existing = PharmacyStockAlert::where('inventory_id', $inventory->id)
                    ->where('alert_type', PharmacyStockAlert::TYPE_LOW_STOCK)
                    ->where('is_resolved', false)
                    ->first();

                if (! $existing) {
                    $alert = PharmacyStockAlert::create([
                        'pharmacy_id' => $inventory->pharmacy_id,
                        'inventory_id' => $inventory->id,
                        'alert_type' => PharmacyStockAlert::TYPE_LOW_STOCK,
                        'threshold' => $inventory->low_stock_threshold,
                        'current_qty' => $inventory->stock_qty,
                    ]);
                    $notificationService->notifyStockAlert($alert);
                    $alertsCreated++;
                }
            } else {
                // Stock is healthy — resolve any open alerts
                $resolved = PharmacyStockAlert::where('inventory_id', $inventory->id)
                    ->where('is_resolved', false)
                    ->get();

                foreach ($resolved as $alert) {
                    $alert->resolve();
                    $alertsResolved++;
                }
            }
        }
        }); // end chunk

        $this->info("Stock check complete. Created: {$alertsCreated}, Resolved: {$alertsResolved}");
    }
}
