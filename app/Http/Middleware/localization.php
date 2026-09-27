<?php

namespace App\Http\Middleware;

use Closure;

class localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $supported = array_map('strtolower', array_keys(config('app.available_locales', ['en' => 'English'])));
        $requested = $this->normalizeSupportedLocale($request->header('global-localization'), $supported);

        if ($requested === null) {
            foreach (preg_split('/\s*,\s*/', (string) $request->header('Accept-Language', '')) as $preference) {
                [$language, $quality] = array_pad(preg_split('/\s*;\s*q\s*=\s*/i', $preference, 2), 2, '1');
                if ((float) $quality <= 0) {
                    continue;
                }

                $requested = $this->normalizeSupportedLocale($language, $supported);
                if ($requested !== null) {
                    break;
                }
            }
        }

        // Fall back to session locale, then default to 'en'.
        $sessionLocale = strtolower((string) session()->get('locale', 'en'));
        $locale = $requested !== null
            ? $requested
            : (in_array($sessionLocale, $supported, true) ? $sessionLocale : 'en');

        // set laravel localization
        app()->setLocale($locale);

        // continue request
        return $next($request);
    }

    private function normalizeSupportedLocale($value, array $supported): ?string
    {
        $tag = strtolower(trim((string) $value));
        if ($tag === '') {
            return null;
        }

        $tag = str_replace('_', '-', $tag);
        $base = strtolower((string) strtok($tag, '-'));

        return in_array($tag, $supported, true) ? $tag : (in_array($base, $supported, true) ? $base : null);
    }
}
