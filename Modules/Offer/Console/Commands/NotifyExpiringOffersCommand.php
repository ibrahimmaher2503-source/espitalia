<?php

namespace Modules\Offer\Console\Commands;

use Illuminate\Console\Command;
use Modules\Offer\Models\Offer;

class NotifyExpiringOffersCommand extends Command
{
    protected $signature = 'offer:notify-expiring';

    protected $description = 'Send notifications for offers expiring within 7 days and 1 day';

    public function handle(): int
    {
        $thresholds = [7, 1];

        foreach ($thresholds as $days) {
            $offers = Offer::active()
                ->whereNotNull('ends_at')
                ->whereDate('ends_at', '=', now()->addDays($days)->toDateString())
                ->get();

            foreach ($offers as $offer) {
                $this->info("Offer #{$offer->id} '{$offer->title_en}' expires in {$days} day(s).");
                // Notification dispatch can be added here when notification system is integrated
            }
        }

        return self::SUCCESS;
    }
}
