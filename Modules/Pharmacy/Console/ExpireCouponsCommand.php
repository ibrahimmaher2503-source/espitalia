<?php

namespace Modules\Pharmacy\Console;

use Illuminate\Console\Command;
use Modules\Pharmacy\Models\PharmacyCoupon;

class ExpireCouponsCommand extends Command
{
    protected $signature = 'pharmacy:expire-coupons';

    protected $description = 'Deactivate coupons that have passed their expiration date';

    public function handle()
    {
        $this->info('Expiring outdated coupons...');

        $expired = PharmacyCoupon::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("Done. {$expired} coupons deactivated.");
    }
}
