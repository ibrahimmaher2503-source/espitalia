<?php

namespace App\Console\Commands;

use App\Models\IdempotencyRecord;
use Illuminate\Console\Command;

class PruneIdempotencyRecords extends Command
{
    protected $signature = 'idempotency:prune';

    protected $description = 'Expire stale idempotency reservations and remove records beyond retention.';

    public function handle(): int
    {
        $now = now();

        $staleProcessing = IdempotencyRecord::query()
            ->where('status', IdempotencyRecord::STATUS_PROCESSING)
            ->where('created_at', '<', $now->copy()->subHours((int) config('idempotency.processing_ttl_hours', 168)))
            ->update([
                'status' => IdempotencyRecord::STATUS_EXPIRED,
                'response_body' => null,
                'response_headers' => null,
                'response_status' => null,
                'expires_at' => $now,
                'updated_at' => $now,
            ]);

        // Keep a compact tombstone after the replay window. Deleting the row
        // would allow a delayed retry to reuse the same key and execute a
        // second clinical/financial mutation.
        $expired = IdempotencyRecord::query()
            ->whereIn('status', [
                IdempotencyRecord::STATUS_COMPLETED,
                IdempotencyRecord::STATUS_FAILED,
            ])
            ->where('updated_at', '<', $now->copy()->subDays((int) config('idempotency.retention_days', 90)))
            ->update([
                'status' => IdempotencyRecord::STATUS_EXPIRED,
                'response_body' => null,
                'response_headers' => null,
                'response_status' => null,
                'expires_at' => $now,
                'updated_at' => $now,
            ]);

        $this->info("Expired {$staleProcessing} stale reservations and compacted {$expired} old records into key tombstones.");

        return self::SUCCESS;
    }
}
