<?php

namespace Modules\Offer\Console\Commands;

use Illuminate\Console\Command;
use Modules\Offer\Models\Offer;

class ExpireOffersCommand extends Command
{
    protected $signature = 'offer:expire';

    protected $description = 'Set status=expired for offers past their ends_at date';

    public function handle(): int
    {
        $count = Offer::where('status', Offer::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update([
                'status' => Offer::STATUS_EXPIRED,
                'is_active' => false,
            ]);

        $this->info("Expired {$count} offers.");

        return self::SUCCESS;
    }
}
