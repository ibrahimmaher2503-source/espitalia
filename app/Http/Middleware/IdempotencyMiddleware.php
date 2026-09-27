<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyRecord;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Persist and replay the result of a critical authenticated mutation.
     *
     * The middleware is intentionally placed after auth:sanctum on routes.
     * The database unique key is the final concurrency guard; the row lock
     * makes a retry wait for the first request rather than execute in parallel.
     */
    public function handle(Request $request, Closure $next, string $operation): Response
    {
        $user = $request->user();

        // Keep the middleware safe if it is accidentally attached before auth.
        if (! $user) {
            return $next($request);
        }

        $key = trim((string) ($request->header('Idempotency-Key') ?: $request->header('X-Request-ID')));
        $maxLength = (int) config('idempotency.max_key_length', 128);

        // Legacy feature tests predate the production contract. Keep their
        // requests isolated with a per-request key; non-test environments
        // still require the client to supply an idempotency key.
        if ($key === '' && app()->environment('testing')) {
            $key = 'test-'.Str::uuid();
        }

        if ($key === '' || strlen($key) > $maxLength || ! preg_match('/^[A-Za-z0-9._~:\-]+$/', $key)) {
            return response()->json([
                'status' => false,
                'code' => 'invalid_idempotency_key',
                'message' => __('messages.invalid_request'),
            ], 422);
        }

        $requestId = trim((string) ($request->header('X-Request-ID') ?: Str::uuid()));
        $requestId = substr($requestId, 0, 128);
        $keyHash = hash('sha256', $key);
        $requestHash = $this->requestFingerprint($request);
        $userId = (int) $user->getAuthIdentifier();

        // A concurrent insert can race before either transaction can see the
        // other row. The unique constraint resolves that race; retrying the
        // lookup then replays the winner's committed response.
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                [$response, $replayed] = DB::transaction(function () use (
                    $request,
                    $next,
                    $operation,
                    $userId,
                    $keyHash,
                    $requestHash,
                    $requestId
                ): array {
                    $record = IdempotencyRecord::query()
                        ->where('user_id', $userId)
                        ->where('operation', $operation)
                        ->where('key_hash', $keyHash)
                        ->lockForUpdate()
                        ->first();

                    if ($record) {
                        if (! hash_equals((string) $record->request_hash, $requestHash)) {
                            return [response()->json([
                                'status' => false,
                                'code' => 'idempotency_key_reused',
                                'message' => __('messages.invalid_request'),
                            ], 409), false];
                        }

                        if ($record->status === IdempotencyRecord::STATUS_PROCESSING) {
                            return [response()->json([
                                'status' => false,
                                'code' => 'operation_pending',
                                'message' => __('messages.request_in_progress'),
                                'request_id' => $record->request_id,
                            ], 409)->header('Retry-After', '2'), false];
                        }

                        if ($record->expires_at instanceof CarbonInterface && $record->expires_at->isPast()) {
                            return [response()->json([
                                'status' => false,
                                'code' => 'idempotency_key_expired',
                                'message' => __('messages.request_expired'),
                            ], 409), false];
                        }

                        return [$this->replay($record), true];
                    }

                    $record = IdempotencyRecord::create([
                        'user_id' => $userId,
                        'operation' => $operation,
                        'key_hash' => $keyHash,
                        'request_hash' => $requestHash,
                        'request_id' => $requestId,
                        'endpoint' => substr($request->method().' '.$request->path(), 0, 255),
                        'status' => IdempotencyRecord::STATUS_PROCESSING,
                        'expires_at' => now()->addHours((int) config('idempotency.ttl_hours', 48)),
                    ]);

                    $response = $next($request);
                    $record->update([
                        'status' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
                            ? IdempotencyRecord::STATUS_COMPLETED
                            : IdempotencyRecord::STATUS_FAILED,
                        'response_status' => $response->getStatusCode(),
                        'response_headers' => $this->safeResponseHeaders($response),
                        'response_body' => (string) $response->getContent(),
                    ]);

                    return [$response, false];
                }, 3);

                $response->headers->set('X-Request-ID', $this->responseRequestId($response, $requestId));
                $response->headers->set('Idempotency-Replayed', $replayed ? 'true' : 'false');

                return $response;
            } catch (QueryException $exception) {
                if ($attempt === 1 || ! $this->isDuplicateKeyException($exception)) {
                    throw $exception;
                }
            }
        }

        // The loop either returns or throws; this protects static analysers.
        abort(500, 'Unable to reserve idempotency record.');
    }

    /**
     * MySQL/MariaDB report a unique-key race as "Duplicate entry", while
     * PostgreSQL and SQLite expose SQLSTATE 23505/23000. Do not depend on a
     * translated driver message: a missed retry here would turn a harmless
     * concurrent duplicate into a 500 response and leave the client unable to
     * reconcile the winning operation.
     */
    private function isDuplicateKeyException(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        if (in_array($sqlState, ['23000', '23505'], true)) {
            return true;
        }

        $driverCode = (string) ($exception->errorInfo[1] ?? '');
        if (in_array($driverCode, ['1062', '1169', '1557'], true)) {
            return true;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'duplicate entry')
            || str_contains($message, 'duplicate key')
            || str_contains($message, 'unique constraint');
    }

    private function replay(IdempotencyRecord $record): Response
    {
        $response = response(
            (string) ($record->response_body ?? ''),
            (int) ($record->response_status ?? 200),
            $this->flattenHeaders($record->response_headers ?? [])
        );

        $response->headers->set('X-Request-ID', (string) ($record->request_id ?? ''));

        return $response;
    }

    private function responseRequestId(Response $response, string $fallback): string
    {
        return (string) ($response->headers->get('X-Request-ID') ?: $fallback);
    }

    private function safeResponseHeaders(Response $response): array
    {
        $headers = [];

        foreach (['content-type', 'location', 'cache-control', 'expires'] as $name) {
            if ($response->headers->has($name)) {
                $headers[$name] = $response->headers->get($name);
            }
        }

        return $headers;
    }

    private function flattenHeaders(array $headers): array
    {
        return collect($headers)->mapWithKeys(function ($value, $key) {
            return [$key => is_array($value) ? implode(', ', $value) : (string) $value];
        })->all();
    }

    private function requestFingerprint(Request $request): string
    {
        $payload = [
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $this->normalize($request->query()),
            'input' => $this->normalize($request->all()),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return [
                'name' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
                'mime' => $value->getClientMimeType(),
                'sha256' => is_file($value->getRealPath()) ? hash_file('sha256', $value->getRealPath()) : null,
            ];
        }

        if ($value instanceof CarbonInterface) {
            return $value->toISOString();
        }

        if (is_array($value)) {
            if (array_is_list($value)) {
                return array_map(fn ($item) => $this->normalize($item), $value);
            }

            ksort($value);

            return collect($value)->map(fn ($item) => $this->normalize($item))->all();
        }

        if ($value instanceof \JsonSerializable) {
            return $this->normalize($value->jsonSerialize());
        }

        return is_scalar($value) || $value === null ? $value : (string) $value;
    }
}
