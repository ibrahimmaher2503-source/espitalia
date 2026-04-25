<?php

namespace Modules\Offer\Services;

use Modules\Offer\Models\Offer;
use Modules\Offer\Models\OfferAuditLog;
use Modules\Offer\Models\OfferUsage;

class OfferAuditService
{
    public static function logCreated(Offer $offer): void
    {
        self::createLog($offer, 'created', null, $offer->toArray());
    }

    public static function logUpdated(Offer $offer, array $oldValues): void
    {
        self::createLog($offer, 'updated', $oldValues, $offer->toArray());
    }

    public static function logStatusChanged(Offer $offer, string $oldStatus, string $newStatus): void
    {
        self::createLog($offer, 'status_changed', ['status' => $oldStatus], ['status' => $newStatus]);
    }

    public static function logDeleted(Offer $offer): void
    {
        self::createLog($offer, 'deleted', $offer->toArray(), null);
    }

    public static function logUsageReversed(OfferUsage $usage, string $reason): void
    {
        self::createLog(
            $usage->offer,
            'usage_reversed',
            ['usage_id' => $usage->id, 'status' => $usage->getOriginal('status')],
            ['usage_id' => $usage->id, 'status' => $usage->status, 'reason' => $reason]
        );
    }

    protected static function createLog(Offer $offer, string $action, ?array $old, ?array $new): void
    {
        OfferAuditLog::create([
            'offer_id' => $offer->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);
    }
}
