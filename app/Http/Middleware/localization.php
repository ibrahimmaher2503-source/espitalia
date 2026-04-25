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
        // Check Accept-Language header for Arabic
        $acceptLanguage = $request->header('Accept-Language', '');
        if (preg_match('/\bar\b/i', $acceptLanguage)) {
            $locale = 'ar';
        } else {
            // Fall back to session locale, then default to 'en'
            $locale = session()->get('locale', 'en');
        }

        // set laravel localization
        app()->setLocale($locale);

        // continue request
        return $next($request);
    }
}
