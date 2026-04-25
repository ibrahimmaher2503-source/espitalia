<?php

namespace Modules\Pharmacy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Pharmacy\Models\PharmacyInventory;

class SyncBatchStockCommand extends Command
{
    protected $signature = 'pharmacy:sync-batch-stock';

    protected $description = 'Reconcile inventory stock_qty with sum of active batch quantities';

    public function handle()
    {
        $this->info('Syncing batch stock with inventory...');

        $synced = 0;

        $inventories = PharmacyInventory::whereHas('batches')->get();

        foreach ($inventories as $inventory) {
            $batchSum = $inventory->batches()
                ->where('is_active', true)
                ->sum('quantity');

            if ((int) $inventory->stock_qty !== (int) $batchSum) {
                $inventory->update(['stock_qty' => $batchSum]);
                $synced++;
            }
        }

        $this->info("Batch stock sync complete. {$synced} inventory records updated.");
    }
}
