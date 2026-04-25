<?php

namespace Modules\Offer\Console\Commands;

use Illuminate\Console\Command;
use Modules\Offer\Models\Offer;

class ExhaustOffersCommand extends Command
{
    protected $signature = 'offer:check-exhausted';

    protected $description = 'Set status=exhausted for offers that hit their usage cap';

    public function handle(): int
    {
        $count = Offer::where('status', Offer::STATUS_ACTIVE)
            ->whereNotNull('usage_limit_total')
            ->whereColumn('used_count', '>=', 'usage_limit_total')
            ->update([
                'status' => Offer::STATUS_EXHAUSTED,
                'is_active' => false,
            ]);

        $this->info("Exhausted {$count} offers.");

        return self::SUCCESS;
    }
}
