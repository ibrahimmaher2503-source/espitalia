<?php

namespace Modules\Pharmacy\Console;

use Illuminate\Console\Command;
use Modules\Pharmacy\Models\PharmacyCartItem;

class CleanupCartsCommand extends Command
{
    protected $signature = 'pharmacy:cleanup-carts {--days=7 : Number of days after which cart items are removed}';

    protected $description = 'Remove abandoned cart items older than the specified number of days';

    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Cleaning up cart items older than {$days} days...");

        $deleted = PharmacyCartItem::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Cleanup complete. {$deleted} cart items removed.");
    }
}
