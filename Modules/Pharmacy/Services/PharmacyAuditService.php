<?php

namespace Modules\Pharmacy\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Pharmacy\Models\PharmacyAuditLog;

class PharmacyAuditService
{
    public static function log(
        int $pharmacyId,
        string $action,
        Model $auditable,
        ?array $old = null,
        ?array $new = null
    ): PharmacyAuditLog {
        return PharmacyAuditLog::create([
            'pharmacy_id' => $pharmacyId,
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
