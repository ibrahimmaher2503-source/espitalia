<?php

namespace Modules\Pharmacy\Console;

use Illuminate\Console\Command;
use Modules\Pharmacy\Models\PharmacyExpiryAlert;
use Modules\Pharmacy\Models\PharmacyProductBatch;
use Modules\Pharmacy\Services\PharmacyNotificationService;

class CheckExpiryCommand extends Command
{
    protected $signature = 'pharmacy:check-expiry';

    protected $description = 'Scan pharmacy batches for expiring/expired items and create alerts';

    public function handle()
    {
        $this->info('Checking batch expiry dates...');

        $alertsCreated = 0;
        $batchesDeactivated = 0;
        $notificationService = new PharmacyNotificationService;
        $thresholdDays = [90, 60, 30, 15, 7];

        // Handle expired batches
        PharmacyProductBatch::where('is_active', true)
            ->where('expiry_date', '<=', now()->toDateString())
            ->with('inventory')
            ->chunk(200, function ($expiredBatches) use (&$alertsCreated, &$batchesDeactivated, $notificationService) {
        foreach ($expiredBatches as $batch) {
            $batch->update(['is_active' => false]);

            // Decrement inventory stock atomically, preventing negative values
            if ($batch->inventory && $batch->quantity > 0) {
                $deductQty = min($batch->quantity, $batch->inventory->stock_qty);
                if ($deductQty > 0) {
                    \Modules\Pharmacy\Models\PharmacyInventory::where('id', $batch->inventory->id)
                        ->where('stock_qty', '>=', $deductQty)
                        ->decrement('stock_qty', $deductQty);
                }
            }

            $batchesDeactivated++;

            $existing = PharmacyExpiryAlert::where('batch_id', $batch->id)
                ->where('alert_type', PharmacyExpiryAlert::TYPE_EXPIRED)
                ->first();

            if (! $existing) {
                $alert = PharmacyExpiryAlert::create([
                    'pharmacy_id' => $batch->inventory->pharmacy_id ?? 0,
                    'batch_id' => $batch->id,
                    'alert_type' => PharmacyExpiryAlert::TYPE_EXPIRED,
                    'expiry_date' => $batch->expiry_date,
                    'days_until_expiry' => 0,
                ]);
                $notificationService->notifyExpiryAlert($alert);
                $alertsCreated++;
            }
        }
        }); // end expired chunk

        // Handle expiring-soon batches
        foreach ($thresholdDays as $days) {
            PharmacyProductBatch::where('is_active', true)
                ->where('expiry_date', '>', now()->toDateString())
                ->where('expiry_date', '<=', now()->addDays($days)->toDateString())
                ->with('inventory')
                ->chunk(200, function ($batches) use ($days, &$alertsCreated, $notificationService) {
            foreach ($batches as $batch) {
                $daysUntil = (int) now()->diffInDays($batch->expiry_date, false);

                $existing = PharmacyExpiryAlert::where('batch_id', $batch->id)
                    ->where('alert_type', PharmacyExpiryAlert::TYPE_EXPIRING_SOON)
                    ->where('days_until_expiry', '<=', $days)
                    ->first();

                if (! $existing) {
                    $alert = PharmacyExpiryAlert::create([
                        'pharmacy_id' => $batch->inventory->pharmacy_id ?? 0,
                        'batch_id' => $batch->id,
                        'alert_type' => PharmacyExpiryAlert::TYPE_EXPIRING_SOON,
                        'expiry_date' => $batch->expiry_date,
                        'days_until_expiry' => $daysUntil,
                    ]);
                    $notificationService->notifyExpiryAlert($alert);
                    $alertsCreated++;
                }
            }
            }); // end expiring-soon chunk
        }

        $this->info("Expiry check complete. Alerts: {$alertsCreated}, Batches deactivated: {$batchesDeactivated}");
    }
}
