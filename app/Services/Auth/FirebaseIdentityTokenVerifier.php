<?php

namespace App\Services\Auth;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseIdentityTokenVerifier
{
    /**
     * Verify a Firebase Auth ID token without trusting client-supplied email
     * or profile fields. Firebase rotates these public keys, so they are
     * cached briefly and refreshed by the normal HTTP cache path.
     *
     * @return array<string, mixed>
     */
    public function verify(string $token): array
    {
        $projectId = trim((string) config('services.firebase.project_id'));
        if ($projectId === '') {
            throw new RuntimeException('Firebase identity verification is not configured.');
        }

        $keys = Cache::remember(
            'firebase.secure-token-public-keys',
            now()->addHour(),
            function (): array {
                $response = Http::timeout(5)->acceptJson()->get(
                    (string) config(
                        'services.firebase.identity_token_keys_url',
                        'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'
                    )
                );

                if (! $response->successful() || ! is_array($response->json())) {
                    throw new RuntimeException('Firebase identity keys could not be loaded.');
                }

                return $response->json();
            }
        );

        try {
            $claims = (array) JWT::decode($token, JWK::parseKeySet($keys));
        } catch (\Throwable $exception) {
            throw new RuntimeException('Firebase identity token is invalid.', 0, $exception);
        }

        $expectedIssuer = 'https://securetoken.google.com/'.$projectId;
        if (($claims['iss'] ?? null) !== $expectedIssuer
            || ($claims['aud'] ?? null) !== $projectId
            || trim((string) ($claims['sub'] ?? '')) === '') {
            throw new RuntimeException('Firebase identity token claims are invalid.');
        }

        if (($claims['email_verified'] ?? false) !== true
            || filter_var($claims['email'] ?? null, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Firebase identity email is not verified.');
        }

        return $claims;
    }
}
