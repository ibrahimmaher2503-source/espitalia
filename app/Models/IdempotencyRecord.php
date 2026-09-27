<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyRecord extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'operation',
        'key_hash',
        'request_hash',
        'request_id',
        'endpoint',
        'status',
        'response_status',
        'response_headers',
        'response_body',
        'expires_at',
    ];

    protected $casts = [
        'response_headers' => 'array',
        'response_body' => 'encrypted',
        'expires_at' => 'datetime',
    ];
}
