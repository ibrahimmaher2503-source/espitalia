<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Critical operation retention
    |--------------------------------------------------------------------------
    |
    | Completed and failed responses remain replayable for this period. A key
    | is never reused after expiry; the record is retained until the longer
    | retention window so a delayed retry cannot execute a second mutation.
    |
    */
    'ttl_hours' => max(1, (int) env('IDEMPOTENCY_TTL_HOURS', 48)),
    'processing_ttl_hours' => max(24, (int) env('IDEMPOTENCY_PROCESSING_TTL_HOURS', 168)),
    'retention_days' => max(30, (int) env('IDEMPOTENCY_RETENTION_DAYS', 90)),
    'max_key_length' => 128,
];
