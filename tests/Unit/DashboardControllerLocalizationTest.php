<?php

namespace Tests\Unit;

use App\Http\Controllers\Backend\API\DashboardController;
use Tests\TestCase;
use ReflectionMethod;
use App\Http\Middleware\localization;
use Illuminate\Http\Request;

class DashboardControllerLocalizationTest extends TestCase
{
    public function test_dashboard_section_titles_use_the_current_locale_and_fallback(): void
    {
        $section = ['title_ar' => 'عربي', 'title_en' => 'English', 'subtitle_ar' => 'وصف عربي', 'subtitle_en' => 'English subtitle'];
        $method = new ReflectionMethod(DashboardController::class, 'localizeSection');
        $method->setAccessible(true);

        $this->assertSame('عربي', $method->invoke(new DashboardController(), $section, 'ar')['title']);
        $this->assertSame('وصف عربي', $method->invoke(new DashboardController(), $section, 'ar')['subtitle']);

        $this->assertSame('English', $method->invoke(new DashboardController(), $section, 'en')['title']);
        $this->assertSame('English subtitle', $method->invoke(new DashboardController(), $section, 'en')['subtitle']);

        $legacy = ['title' => 'Legacy', 'subtitle' => 'Legacy subtitle'];
        $resolved = $method->invoke(new DashboardController(), $legacy, 'en');
        $this->assertSame('Legacy', $resolved['title']);
        $this->assertSame('Legacy subtitle', $resolved['subtitle']);
    }

    public function test_api_localization_prefers_global_localization_header(): void
    {
        $request = Request::create('/api/dashboard-detail', 'GET', [], [], [], [
            'HTTP_GLOBAL_LOCALIZATION' => 'ar',
            'HTTP_ACCEPT_LANGUAGE' => 'en',
        ]);

        (new localization())->handle($request, function () {
            $this->assertSame('ar', app()->getLocale());
            return response()->noContent();
        });
    }

    public function test_api_localization_normalizes_regional_global_localization_header(): void
    {
        $request = Request::create('/api/dashboard-detail', 'GET', [], [], [], [
            'HTTP_GLOBAL_LOCALIZATION' => 'ar-EG',
            'HTTP_ACCEPT_LANGUAGE' => 'en',
        ]);

        (new localization())->handle($request, function () {
            $this->assertSame('ar', app()->getLocale());
            return response()->noContent();
        });
    }

    public function test_api_localization_scans_accept_language_until_supported_preference(): void
    {
        $request = Request::create('/api/dashboard-detail', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'zz-ZZ, ar_SA;q=0.9, en;q=0.8',
        ]);

        (new localization())->handle($request, function () {
            $this->assertSame('ar', app()->getLocale());
            return response()->noContent();
        });
    }
}
