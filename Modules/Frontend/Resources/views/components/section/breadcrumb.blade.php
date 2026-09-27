@php
    $crumbs    = generateBreadcrumb();
    $lastCrumb = !empty($crumbs) ? $crumbs[array_key_last($crumbs)] : null;
    $pageTitle = $lastCrumb['name'] ?? '';
@endphp

@push('after-styles')
<style>
/* ── Page Hero ──────────────────────────────────────────────────────────── */
.es-page-hero {
    position: relative;
    padding: 3.25rem 0 2.25rem;
    overflow: hidden;
    isolation: isolate;
    background:
        linear-gradient(135deg,
            #063a36 0%,
            #0a5c53 35%,
            #0c4f5e 65%,
            #082233 100%);
}

/* Dot-grid overlay */
.es-ph-grid {
    position: absolute; inset: 0; z-index: 0; pointer-events: none;
    background-image: radial-gradient(circle, rgba(255,255,255,0.065) 1px, transparent 1px);
    background-size: 26px 26px;
}

/* Noise texture for depth */
.es-ph-noise {
    position: absolute; inset: 0; z-index: 0; pointer-events: none; opacity: 0.03;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    background-repeat: repeat;
    background-size: 180px;
}

/* Ambient glow orbs */
.es-ph-glow {
    position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none; z-index: 0;
}
.es-ph-glow-1 {
    width: 520px; height: 520px;
    background: radial-gradient(circle, rgba(0,196,172,0.28) 0%, transparent 70%);
    top: -180px; right: -80px;
    animation: es-ph-pulse 8s ease-in-out infinite;
}
.es-ph-glow-2 {
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(14,120,210,0.22) 0%, transparent 70%);
    bottom: -120px; left: 8%;
    animation: es-ph-pulse 10s ease-in-out 2s infinite;
}
.es-ph-glow-3 {
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    top: 20%; left: 40%;
}

@keyframes es-ph-pulse {
    0%, 100% { transform: scale(1); opacity: 0.9; }
    50%       { transform: scale(1.12); opacity: 1; }
}

/* Decorative cross/plus marks */
.es-ph-mark {
    position: absolute; z-index: 0; pointer-events: none;
    color: rgba(255,255,255,0.10);
    font-weight: 200;
    line-height: 1;
    animation: es-ph-float 7s ease-in-out infinite;
}
.es-ph-mark-a { font-size: 2.25rem; top: 18%; right: 18%; animation-delay: 0s; }
.es-ph-mark-b { font-size: 1.25rem; bottom: 22%; right: 7%; animation-delay: 2.5s; }
.es-ph-mark-c { font-size: 3.5rem;  bottom: 8%;  left: 3%;  animation-delay: 5s; }
.es-ph-mark-d { font-size: 1rem;    top: 12%;    left: 18%; animation-delay: 1.5s; }

@keyframes es-ph-float {
    0%, 100% { transform: translateY(0)   rotate(0deg);   opacity: 0.10; }
    50%       { transform: translateY(-9px) rotate(20deg); opacity: 0.18; }
}

/* Diagonal light streak */
.es-ph-streak {
    position: absolute; z-index: 0; pointer-events: none;
    width: 2px; height: 60%;
    top: -10%; right: 30%;
    background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.07), transparent);
    transform: rotate(25deg);
}

/* Content layer */
.es-ph-body {
    position: relative; z-index: 1;
}

/* Eyebrow chip above title */
.es-ph-eyebrow {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.16);
    border-radius: 999px;
    padding: 0.25rem 0.85rem;
    margin-bottom: 0.85rem;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.7);
    backdrop-filter: blur(8px);
    animation: es-ph-slide-in 0.55s cubic-bezier(0.22,1,0.36,1) both;
}
.es-ph-eyebrow i { font-size: 0.8rem; opacity: 0.8; }

/* Page title */
.es-ph-title {
    font-size: clamp(1.65rem, 4vw, 2.6rem);
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 1.1rem;
    letter-spacing: -0.025em;
    line-height: 1.15;
    font-family: 'Cairo', sans-serif;
    animation: es-ph-slide-in 0.6s cubic-bezier(0.22,1,0.36,1) 0.07s both;
}

/* Underline accent bar */
.es-ph-accent {
    display: block;
    width: 3rem; height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, #00e5c8, #00aaff);
    margin-bottom: 1.25rem;
    animation: es-ph-slide-in 0.6s cubic-bezier(0.22,1,0.36,1) 0.14s both;
}

/* Breadcrumb */
.es-ph-nav { animation: es-ph-slide-in 0.6s cubic-bezier(0.22,1,0.36,1) 0.2s both; }

.es-ph-crumbs {
    list-style: none; margin: 0; padding: 0;
    display: flex; flex-wrap: wrap; align-items: center; gap: 0.2rem;
}
.es-ph-crumb { display: flex; align-items: center; gap: 0.2rem; }
.es-ph-crumb-link {
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    transition: color 0.15s;
}
.es-ph-crumb-link:hover { color: rgba(255,255,255,0.95); }
.es-ph-crumb-sep {
    color: rgba(255,255,255,0.25);
    font-size: 0.7rem;
    font-weight: 300;
}
.es-ph-crumb--active span {
    color: rgba(255,255,255,0.85);
    font-size: 0.8rem;
    font-weight: 500;
}

@keyframes es-ph-slide-in {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Bottom edge fade */
.es-ph-bottom-edge {
    position: absolute; bottom: 0; left: 0; right: 0; height: 1px; z-index: 1;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(0,229,200,0.4) 30%,
        rgba(0,170,255,0.4) 70%,
        transparent 100%);
}

/* Responsive */
@media (max-width: 576px) {
    .es-page-hero { padding: 2.25rem 0 1.75rem; }
    .es-ph-title  { margin-bottom: 0.85rem; }
}
</style>
@endpush

<div class="es-page-hero">
    {{-- Decorative background layers --}}
    <div class="es-ph-grid"   aria-hidden="true"></div>
    <div class="es-ph-noise"  aria-hidden="true"></div>
    <div class="es-ph-glow es-ph-glow-1" aria-hidden="true"></div>
    <div class="es-ph-glow es-ph-glow-2" aria-hidden="true"></div>
    <div class="es-ph-glow es-ph-glow-3" aria-hidden="true"></div>
    <div class="es-ph-streak" aria-hidden="true"></div>
    <span class="es-ph-mark es-ph-mark-a" aria-hidden="true">+</span>
    <span class="es-ph-mark es-ph-mark-b" aria-hidden="true">✕</span>
    <span class="es-ph-mark es-ph-mark-c" aria-hidden="true">+</span>
    <span class="es-ph-mark es-ph-mark-d" aria-hidden="true">✕</span>
    <div class="es-ph-bottom-edge" aria-hidden="true"></div>

    <div class="container">
        <div class="es-ph-body">

            {{-- Eyebrow chip --}}
            <div class="es-ph-eyebrow">
                <i class="ph ph-map-pin" aria-hidden="true"></i>
                {{ __('frontend.home') ?? 'Home' }}
            </div>

            {{-- Page title --}}
            @if($pageTitle)
                <h1 class="es-ph-title">{{ $pageTitle }}</h1>
                <span class="es-ph-accent" aria-hidden="true"></span>
            @endif

            {{-- Breadcrumb --}}
            <nav class="es-ph-nav" aria-label="{{ __('messages.breadcrumb') ?? 'Breadcrumb' }}">
                <ol class="es-ph-crumbs">
                    @foreach($crumbs as $item)
                        @if($loop->last)
                            <li class="es-ph-crumb es-ph-crumb--active" aria-current="page">
                                <span>{{ $item['name'] }}</span>
                            </li>
                        @else
                            <li class="es-ph-crumb">
                                <a href="{{ $item['url'] }}" class="es-ph-crumb-link">{{ $item['name'] }}</a>
                                <span class="es-ph-crumb-sep" aria-hidden="true">/</span>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>

        </div>
    </div>
</div>
