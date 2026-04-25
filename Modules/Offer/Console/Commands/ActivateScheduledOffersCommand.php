<?php

namespace Modules\Offer\Console\Commands;

use Illuminate\Console\Command;
use Modules\Offer\Models\Offer;

class ActivateScheduledOffersCommand extends Command
{
    protected $signature = 'offer:activate-scheduled';

    protected $description = 'Activate scheduled offers whose starts_at has arrived';

    public function handle(): int
    {
        $count = Offer::where('status', Offer::STATUS_SCHEDULED)
            ->where('is_active', true)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->update([
                'status' => Offer::STATUS_ACTIVE,
            ]);

        $this->info("Activated {$count} scheduled offers.");

        return self::SUCCESS;
    }
}
