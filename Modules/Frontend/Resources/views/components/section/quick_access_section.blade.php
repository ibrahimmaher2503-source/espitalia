@push('after-styles')
<style>
/* ── Quick Access Section ──────────────────────────────────────────────── */

:root {
    --qa-primary: var(--bs-primary, #00766b);
    --qa-radius-card: 1.25rem;
    --qa-radius-icon: 0.875rem;
    --qa-shadow-color: color-mix(in srgb, var(--qa-primary) 18%, transparent);
}

.qa-section {
    padding: clamp(4rem, 7vw, 6rem) 0 clamp(3rem, 5vw, 5rem);
    background: #f8fafb;
    position: relative;
    overflow: hidden;
    isolation: isolate;
}

[data-bs-theme="dark"] .qa-section { background: #0d1117; }

/* Layered background: soft mesh + subtle grid */
.qa-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 70% 55% at 15% 20%, color-mix(in srgb, var(--qa-primary) 7%, transparent), transparent),
        radial-gradient(ellipse 55% 45% at 85% 80%, color-mix(in srgb, #7c3aed 5%, transparent), transparent),
        radial-gradient(circle, color-mix(in srgb, var(--qa-primary) 8%, transparent) 1px, transparent 1px);
    background-size: auto, auto, 32px 32px;
    pointer-events: none;
    z-index: 0;
}

/* Decorative arc top-right */
.qa-section::after {
    content: '';
    position: absolute;
    top: -120px;
    right: -120px;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    border: 1px solid color-mix(in srgb, var(--qa-primary) 12%, transparent);
    box-shadow: inset 0 0 60px color-mix(in srgb, var(--qa-primary) 4%, transparent);
    pointer-events: none;
    z-index: 0;
}

.qa-inner {
    position: relative;
    z-index: 1;
}

/* ── Header ── */
.qa-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.25rem;
    margin-bottom: clamp(2.25rem, 4vw, 3.5rem);
    flex-wrap: wrap;
}

.qa-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Cairo', sans-serif;
    font-size: 0.675rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--qa-primary);
    background: color-mix(in srgb, var(--qa-primary) 9%, transparent);
    border: 1px solid color-mix(in srgb, var(--qa-primary) 22%, transparent);
    padding: 0.35rem 1rem;
    border-radius: 999px;
    margin-bottom: 0.8rem;
    backdrop-filter: blur(4px);
}

.qa-eyebrow i { font-size: 0.9rem; }

.qa-title {
    font-family: 'Cairo', sans-serif;
    font-size: clamp(1.75rem, 3.5vw, 2.75rem);
    font-weight: 400;
    line-height: 1.15;
    letter-spacing: -0.01em;
    color: var(--bs-body-color);
    margin: 0;
}

.qa-title em {
    font-style: italic;
    color: var(--qa-primary);
}

.qa-subtitle {
    font-family: 'Cairo', sans-serif;
    font-size: 0.9375rem;
    font-weight: 300;
    color: var(--bs-secondary-color, #6c757d);
    margin: 0.6rem 0 0;
    max-width: 420px;
    line-height: 1.65;
    letter-spacing: 0.01em;
}

/* ── Grid ── */
.qa-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 1.125rem;
}

@media (max-width: 575px) {
    .qa-grid { grid-template-columns: repeat(2, 1fr); gap: 0.875rem; }
}

/* ── Card ── */
.qa-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem 1.375rem 1.375rem;
    background: rgba(255,255,255,0.82);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.9);
    border-radius: var(--qa-radius-card);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 0 0 1px rgba(0,0,0,0.04);
    text-decoration: none;
    color: var(--bs-body-color);
    transition:
        box-shadow 0.25s ease,
        transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1),
        border-color 0.22s ease,
        background 0.22s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

[data-bs-theme="dark"] .qa-card {
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.08);
    box-shadow: 0 1px 4px rgba(0,0,0,0.25);
}

/* Shimmer on hover */
.qa-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        color-mix(in srgb, var(--qa-accent) 10%, transparent) 0%,
        transparent 50%
    );
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    border-radius: inherit;
}

/* Bottom accent bar */
.qa-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 10%;
    right: 10%;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--qa-accent), transparent);
    border-radius: 999px;
    opacity: 0;
    transition: opacity 0.25s ease;
}

.qa-card:hover {
    transform: translateY(-5px) scale(1.015);
    box-shadow:
        0 16px 40px color-mix(in srgb, var(--qa-accent) 18%, transparent),
        0 4px 12px rgba(0,0,0,0.06),
        0 0 0 1px color-mix(in srgb, var(--qa-accent) 30%, transparent);
    border-color: color-mix(in srgb, var(--qa-accent) 35%, transparent);
    background: rgba(255,255,255,0.95);
    color: var(--bs-body-color);
    text-decoration: none;
}

[data-bs-theme="dark"] .qa-card:hover { background: rgba(255,255,255,0.08); }

.qa-card:hover::before { opacity: 1; }
.qa-card:hover::after  { opacity: 1; }

.qa-card:hover .qa-card-icon {
    background: var(--qa-accent);
    color: #fff;
    transform: scale(1.1) rotate(-5deg);
    box-shadow: 0 6px 20px color-mix(in srgb, var(--qa-accent) 40%, transparent);
}

.qa-card:hover .qa-card-arrow {
    opacity: 1;
    transform: translate(0, 0);
}

/* ── Icon ── */
.qa-card-icon {
    width: 3rem;
    height: 3rem;
    border-radius: var(--qa-radius-icon);
    background: color-mix(in srgb, var(--qa-accent) 11%, transparent);
    color: var(--qa-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
    transition:
        background 0.25s ease,
        color 0.25s ease,
        transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.25s ease;
    position: relative;
}

/* Icon inner glow ring */
.qa-card-icon::after {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: inherit;
    border: 1px solid color-mix(in srgb, var(--qa-accent) 25%, transparent);
    transition: opacity 0.25s;
}

/* ── Text ── */
.qa-card-body { flex: 1; min-width: 0; }

.qa-card-label {
    font-family: 'Cairo', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1.3;
    margin: 0 0 0.25rem;
    color: var(--bs-body-color);
    letter-spacing: -0.01em;
}

.qa-card-desc {
    font-family: 'Cairo', sans-serif;
    font-size: 0.775rem;
    font-weight: 300;
    color: var(--bs-secondary-color, #6c757d);
    margin: 0;
    line-height: 1.5;
}

/* Arrow chip */
.qa-card-arrow {
    position: absolute;
    top: 1.1rem;
    right: 1.1rem;
    width: 1.6rem;
    height: 1.6rem;
    border-radius: 50%;
    background: color-mix(in srgb, var(--qa-accent) 12%, transparent);
    color: var(--qa-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    opacity: 0;
    transform: translate(-4px, 4px);
    transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* ── Dashboard strip ── */
.qa-dashboard {
    margin-top: 2.25rem;
    padding: 1.375rem 1.75rem;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(10px);
    border: 1px solid color-mix(in srgb, var(--qa-primary) 15%, transparent);
    border-radius: calc(var(--qa-radius-card) + 0.125rem);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

[data-bs-theme="dark"] .qa-dashboard {
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.08);
}

.qa-dashboard-label {
    font-family: 'Cairo', sans-serif;
    font-size: 0.725rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--qa-primary);
    white-space: nowrap;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.qa-dashboard-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    flex: 1;
}

.qa-dashboard-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.95rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.85);
    border: 1px solid color-mix(in srgb, var(--qa-primary) 18%, transparent);
    color: var(--bs-body-color);
    font-family: 'Cairo', sans-serif;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.18s, border-color 0.18s, color 0.18s, transform 0.18s, box-shadow 0.18s;
    white-space: nowrap;
}

.qa-dashboard-link i { font-size: 0.95rem; color: var(--qa-primary); transition: color 0.18s; }

.qa-dashboard-link:hover {
    background: var(--qa-primary);
    border-color: var(--qa-primary);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px color-mix(in srgb, var(--qa-primary) 30%, transparent);
    text-decoration: none;
}

.qa-dashboard-link:hover i { color: #fff; }

/* ── Staggered entrance ── */
@keyframes qa-rise {
    from { opacity: 0; transform: translateY(28px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.qa-card {
    animation: qa-rise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
    will-change: transform, opacity;
}

.qa-card:nth-child(1) { animation-delay: 0.05s; }
.qa-card:nth-child(2) { animation-delay: 0.10s; }
.qa-card:nth-child(3) { animation-delay: 0.15s; }
.qa-card:nth-child(4) { animation-delay: 0.20s; }
.qa-card:nth-child(5) { animation-delay: 0.25s; }
.qa-card:nth-child(6) { animation-delay: 0.30s; }
.qa-card:nth-child(7) { animation-delay: 0.35s; }
.qa-card:nth-child(8) { animation-delay: 0.40s; }

@keyframes qa-header-in {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.qa-header { animation: qa-header-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
</style>
@endpush

@php
    $qaMenuSection = \Modules\FrontendSetting\Models\FrontendSetting::where('key', 'heder-menu-setting')->first();
    $qaMenu = $qaMenuSection ? json_decode($qaMenuSection->value, true) : [];
@endphp

<section class="qa-section">
    <div class="container qa-inner">

        {{-- Header --}}
        <div class="qa-header">
            <div>
                <div class="qa-eyebrow">
                    <i class="ph ph-squares-four" aria-hidden="true"></i>
                    {{ __('frontend.our_services') ?? 'Our Services' }}
                </div>
                <h2 class="qa-title">
                    {{ __('frontend.everything_you_need') ?? 'Everything you' }}
                    <em>{{ __('frontend.need') ?? 'need' }}</em>
                </h2>
                <p class="qa-subtitle">{{ __('frontend.quick_access_desc') ?? 'All our healthcare services, one tap away.' }}</p>
            </div>
        </div>

        {{-- Service cards grid --}}
        <div class="qa-grid" role="list">

            @if(!empty($qaMenu['categories']) && $qaMenu['categories'] == 1)
            <a href="{{ route('categories') }}" class="qa-card" style="--qa-accent:#00766b" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-grid-four" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('frontend.categories') }}</p>
                    <p class="qa-card-desc">{{ __('frontend.browse_by_specialty') ?? 'Browse by specialty' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

            @if(!empty($qaMenu['services']) && $qaMenu['services'] == 1)
            <a href="{{ route('services') }}" class="qa-card" style="--qa-accent:#0e7490" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-first-aid-kit" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('frontend.services') }}</p>
                    <p class="qa-card-desc">{{ __('frontend.all_medical_services') ?? 'All medical services' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

            @if(!empty($qaMenu['clinics']) && $qaMenu['clinics'] == 1)
            <a href="{{ route('clinics') }}" class="qa-card" style="--qa-accent:#0891b2" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-hospital" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('frontend.clinics') }}</p>
                    <p class="qa-card-desc">{{ __('frontend.find_nearby_clinics') ?? 'Find nearby clinics' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

            @if(!empty($qaMenu['doctors']) && $qaMenu['doctors'] == 1)
            <a href="{{ route('doctors') }}" class="qa-card" style="--qa-accent:#0369a1" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-stethoscope" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('frontend.doctors') }}</p>
                    <p class="qa-card-desc">{{ __('frontend.meet_our_specialists') ?? 'Meet our specialists' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

            @if(!empty($qaMenu['available_nurses']) && $qaMenu['available_nurses'] == 1)
            <a href="{{ route('available-nurses') }}" class="qa-card" style="--qa-accent:#7c3aed" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-user-circle-plus" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('frontend.available_nurses') }}</p>
                    <p class="qa-card-desc">{{ __('frontend.home_nursing_care') ?? 'Home nursing care' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

            @if(!empty($qaMenu['lab_tests']) && $qaMenu['lab_tests'] == 1)
            <a href="{{ route('lab-tests') }}" class="qa-card" style="--qa-accent:#b45309" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-flask" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('frontend.lab_tests') }}</p>
                    <p class="qa-card-desc">{{ __('frontend.order_lab_tests') ?? 'Order lab tests online' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

            @if(!empty($qaMenu['icu']) && $qaMenu['icu'] == 1)
            <a href="{{ route('icu-hospitals') }}" class="qa-card" style="--qa-accent:#dc2626" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-heartbeat" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('intensive_care.hospital_catalog') ?? 'ICU Hospitals' }}</p>
                    <p class="qa-card-desc">{{ __('frontend.intensive_care_units') ?? 'Intensive care units' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

            @if(!empty($qaMenu['book_call']) && $qaMenu['book_call'] == 1)
            <a href="{{ route('book-call') }}" class="qa-card" style="--qa-accent:#059669" role="listitem">
                <div class="qa-card-icon"><i class="ph ph-phone-call" aria-hidden="true"></i></div>
                <div class="qa-card-body">
                    <p class="qa-card-label">{{ __('call_booking.book_a_call') ?? 'Book a Call' }}</p>
                    <p class="qa-card-desc">{{ __('frontend.virtual_consultation') ?? 'Virtual consultation' }}</p>
                </div>
                <i class="ph ph-arrow-right qa-card-arrow" aria-hidden="true"></i>
            </a>
            @endif

        </div>

        {{-- Authenticated user dashboard strip --}}
        @auth
            @if(auth()->user()->hasRole('user'))
            <div class="qa-dashboard" role="navigation" aria-label="{{ __('frontend.my_account') ?? 'My account' }}">
                <span class="qa-dashboard-label">
                    <i class="ph ph-user-circle" aria-hidden="true"></i>
                    {{ __('frontend.my_account') ?? 'My account' }}
                </span>
                <div class="qa-dashboard-links">

                    @if(!empty($qaMenu['appointments']) && $qaMenu['appointments'] == 1)
                    <a href="{{ route('appointment-list') }}" class="qa-dashboard-link">
                        <i class="ph ph-calendar-check" aria-hidden="true"></i>
                        {{ __('frontend.my_appointments') }}
                    </a>
                    @endif

                    @if(!empty($qaMenu['available_nurses']) && $qaMenu['available_nurses'] == 1)
                    <a href="{{ route('my-nurse-requests') }}" class="qa-dashboard-link">
                        <i class="ph ph-bandaids" aria-hidden="true"></i>
                        {{ __('frontend.my_nurse_requests') }}
                    </a>
                    @endif

                    @if(!empty($qaMenu['lab_tests']) && $qaMenu['lab_tests'] == 1)
                    <a href="{{ route('my-test-orders') }}" class="qa-dashboard-link">
                        <i class="ph ph-test-tube" aria-hidden="true"></i>
                        {{ __('frontend.my_test_orders') }}
                    </a>
                    @endif

                    @if(!empty($qaMenu['icu']) && $qaMenu['icu'] == 1)
                    <a href="{{ route('my-icu-requests') }}" class="qa-dashboard-link">
                        <i class="ph ph-heartbeat" aria-hidden="true"></i>
                        {{ __('intensive_care.my_icu_requests') }}
                    </a>
                    @endif

                    <a href="{{ route('incidence.index') }}" class="qa-dashboard-link">
                        <i class="ph ph-warning-circle" aria-hidden="true"></i>
                        {{ __('frontend.incidence') }}
                    </a>

                    <a href="{{ route('encounter-list') }}" class="qa-dashboard-link">
                        <i class="ph ph-speedometer" aria-hidden="true"></i>
                        {{ __('frontend.encounter') }}
                    </a>

                </div>
            </div>
            @endif
        @endauth

    </div>
</section>
