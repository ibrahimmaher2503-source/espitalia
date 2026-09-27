<?php

namespace App\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use App\Models\IdempotencyRecord;
use Illuminate\Http\Request;

class IdempotencyController extends Controller
{
    public function status(Request $request)
    {
        $validated = $request->validate([
            'operation' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_.-]+$/'],
        ]);

        $key = trim((string) ($request->header('Idempotency-Key') ?: $request->header('X-Request-ID')));
        if ($key === ''
            || strlen($key) > (int) config('idempotency.max_key_length', 128)
            || ! preg_match('/^[A-Za-z0-9._~:\-]+$/', $key)) {
            return response()->json([
                'status' => false,
                'code' => 'invalid_idempotency_key',
                'message' => __('messages.invalid_request'),
            ], 422);
        }

        $record = IdempotencyRecord::query()
            ->where('user_id', auth()->id())
            ->where('operation', $validated['operation'])
            ->where('key_hash', hash('sha256', $key))
            ->first();

        if (! $record) {
            return response()->json([
                'status' => false,
                'code' => 'operation_not_found',
                'message' => __('messages.not_found'),
            ], 404);
        }

        $status = $record->status;
        if ($record->expires_at?->isPast() && $status !== IdempotencyRecord::STATUS_PROCESSING) {
            $status = IdempotencyRecord::STATUS_EXPIRED;
        }

        $result = null;
        if ($status !== IdempotencyRecord::STATUS_PROCESSING && $status !== IdempotencyRecord::STATUS_EXPIRED) {
            $decoded = json_decode((string) ($record->response_body ?? ''), true);
            $result = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'operation' => $record->operation,
                'status' => $status,
                'request_id' => $record->request_id,
                'response_status' => $record->response_status,
                'result' => $result,
                'expires_at' => optional($record->expires_at)->toISOString(),
            ],
        ], $status === IdempotencyRecord::STATUS_PROCESSING ? 202 : 200);
    }
}
