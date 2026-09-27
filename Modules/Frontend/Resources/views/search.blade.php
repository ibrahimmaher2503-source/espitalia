@extends('frontend::layouts.master')

@push('after-styles')
<style>
/* ===== Search Hero Header ===== */
.es-search-hero {
    position: relative;
    background: linear-gradient(135deg, #063a36 0%, #0a5c53 35%, #0c4f5e 65%, #082233 100%);
    padding: 3rem 0 2.25rem;
    overflow: hidden;
    isolation: isolate;
}
.es-search-hero-grid {
    position: absolute; inset: 0; z-index: 0; pointer-events: none;
    background-image: radial-gradient(circle, rgba(255,255,255,0.065) 1px, transparent 1px);
    background-size: 26px 26px;
}
.es-search-hero-glow-1 {
    position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none; z-index: 0;
    width: 480px; height: 480px;
    background: radial-gradient(circle, rgba(0,196,172,0.28) 0%, transparent 70%);
    top: -160px; right: -60px;
}
.es-search-hero-glow-2 {
    position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none; z-index: 0;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(14,120,210,0.2) 0%, transparent 70%);
    bottom: -100px; left: 10%;
}
.es-search-hero-body { position: relative; z-index: 1; text-align: center; }
.es-search-hero-eyebrow {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.16);
    border-radius: 999px;
    padding: 0.25rem 0.9rem;
    margin-bottom: 0.9rem;
    font-size: 0.7rem; font-weight: 600;
    letter-spacing: 0.12em; text-transform: uppercase;
    color: rgba(255,255,255,0.7);
    animation: es-sh-in 0.5s cubic-bezier(0.22,1,0.36,1) both;
}
.es-search-hero-title {
    font-size: clamp(1.75rem, 4.5vw, 2.8rem);
    font-weight: 700; color: #fff;
    margin: 0 0 0.5rem;
    letter-spacing: -0.025em; line-height: 1.15;
    font-family: 'Inter Tight', sans-serif;
    animation: es-sh-in 0.55s cubic-bezier(0.22,1,0.36,1) 0.07s both;
}
.es-search-hero-title span { color: #00e5c8; }
.es-search-hero-sub {
    font-size: 0.925rem; color: rgba(255,255,255,0.6);
    margin: 0;
    animation: es-sh-in 0.6s cubic-bezier(0.22,1,0.36,1) 0.14s both;
}
.es-search-hero-accent {
    display: block; width: 3rem; height: 3px; border-radius: 999px;
    background: linear-gradient(90deg, #00e5c8, #00aaff);
    margin: 1rem auto 0;
    animation: es-sh-in 0.6s cubic-bezier(0.22,1,0.36,1) 0.2s both;
}
.es-sh-mark {
    position: absolute; z-index: 0; pointer-events: none;
    color: rgba(255,255,255,0.09); font-weight: 200; line-height: 1;
}
.es-sh-mark-a { font-size: 2.5rem; top: 20%; right: 15%; }
.es-sh-mark-b { font-size: 1.5rem; bottom: 15%; right: 6%; }
.es-sh-mark-c { font-size: 3rem;   top: 10%;  left: 4%; }
.es-search-hero-edge {
    position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(0,229,200,0.4) 30%, rgba(0,170,255,0.4) 70%, transparent 100%);
}
@keyframes es-sh-in {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ===== Search Results Page Enhancements ===== */
.es-sp-search-wrap {
    max-width: 680px;
    margin: 0 auto 1.5rem;
}
.es-sp-input-group {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 1rem;
    border-radius: 999px;
    border: 1.5px solid var(--bs-border-color, #dee2e6);
    background: var(--bs-body-bg);
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    transition: border-color 0.15s, box-shadow 0.15s;
}
.es-sp-input-group:focus-within {
    border-color: var(--bs-primary, #00766b);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--bs-primary, #00766b) 15%, transparent);
}
.es-sp-icon { font-size: 1.125rem; color: var(--bs-primary, #00766b); flex-shrink: 0; }
.es-sp-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 1rem;
    color: var(--bs-body-color);
    min-width: 0;
}
.es-sp-input::placeholder { color: var(--bs-secondary-color, #6c757d); }
.es-sp-clear {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border: none;
    background: var(--bs-gray-200, #e9ecef);
    border-radius: 50%;
    font-size: 0.875rem;
    cursor: pointer;
    color: var(--bs-secondary-color);
    flex-shrink: 0;
    transition: background 0.15s;
}
.es-sp-clear:hover { background: var(--bs-gray-300, #dee2e6); }

/* Filter tabs */
.es-filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4375rem;
    margin-bottom: 1.5rem;
}
.es-filter-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.9375rem;
    border-radius: 999px;
    border: 1.5px solid var(--bs-border-color, #dee2e6);
    background: transparent;
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s;
    white-space: nowrap;
}
.es-filter-tab.active,
.es-filter-tab:hover {
    border-color: var(--bs-primary, #00766b);
    background: var(--bs-primary, #00766b);
    color: #fff;
}
.es-filter-count {
    font-size: 0.6875rem;
    background: rgba(255,255,255,0.22);
    border-radius: 999px;
    padding: 0.05em 0.45em;
    min-width: 1.1em;
    text-align: center;
}
.es-filter-tab:not(.active) .es-filter-count {
    background: var(--bs-gray-200, #e9ecef);
    color: var(--bs-secondary-color, #6c757d);
}

/* Results summary */
.es-results-summary {
    font-size: 0.8125rem;
    color: var(--bs-secondary-color, #6c757d);
    margin-bottom: 1rem;
}
.es-results-summary strong { color: var(--bs-body-color); }

/* No result enhanced */
.es-no-result {
    text-align: center;
    padding: 3rem 1rem;
}
.es-no-result img { max-width: 180px; margin-bottom: 1.25rem; opacity: 0.85; }
.es-no-result h5 { font-weight: 700; margin-bottom: 0.5rem; }
.es-no-result p { color: var(--bs-secondary-color, #6c757d); margin-bottom: 1.25rem; }
.es-no-result-pills { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; }
.es-no-result-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.9375rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--bs-primary, #00766b) 10%, transparent);
    color: var(--bs-primary, #00766b);
    font-size: 0.8125rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid color-mix(in srgb, var(--bs-primary, #00766b) 20%, transparent);
    transition: background 0.15s;
}
.es-no-result-pill:hover { background: var(--bs-primary, #00766b); color: #fff; }
</style>
@endpush

@section('content')
    {{-- Search Hero Header --}}
    <div class="es-search-hero">
        <div class="es-search-hero-grid"   aria-hidden="true"></div>
        <div class="es-search-hero-glow-1" aria-hidden="true"></div>
        <div class="es-search-hero-glow-2" aria-hidden="true"></div>
        <span class="es-sh-mark es-sh-mark-a" aria-hidden="true">+</span>
        <span class="es-sh-mark es-sh-mark-b" aria-hidden="true">✕</span>
        <span class="es-sh-mark es-sh-mark-c" aria-hidden="true">+</span>
        <div class="es-search-hero-edge"   aria-hidden="true"></div>

        <div class="container">
            <div class="es-search-hero-body">
                <div class="es-search-hero-eyebrow">
                    <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                    {{ __('messages.search') }}
                </div>
                <h1 class="es-search-hero-title">
                    {{ __('frontend.find_your_care') ?? 'Find Your' }} <span>{{ __('frontend.care') ?? 'Care' }}</span>
                </h1>
                <p class="es-search-hero-sub">
                    {{ __('frontend.search_subtitle') ?? 'Search doctors, clinics, services and more' }}
                </p>
                <span class="es-search-hero-accent" aria-hidden="true"></span>
            </div>
        </div>
    </div>

    <div class="list-page section-spacing px-0">
        <div class="page-title" id="page_title">
            <div class="container">

                {{-- Enhanced search input --}}
                <div class="es-sp-search-wrap">
                    <div class="es-sp-input-group">
                        <i class="ph ph-magnifying-glass es-sp-icon" aria-hidden="true"></i>
                        <input type="text" id="search-query" class="es-sp-input search-input"
                            placeholder="{{ __('messages.search') }}…"
                            autocomplete="off" aria-label="{{ __('messages.search') }}">
                        <button type="button" class="es-sp-clear d-none" id="remove-search"
                            aria-label="{{ __('messages.clear') ?? 'Clear search' }}">
                            <i class="ph ph-x"></i>
                        </button>
                    </div>
                </div>

                {{-- Category filter tabs (hidden until results load) --}}
                <div class="es-filter-bar d-none" id="es-filter-bar">
                    <button class="es-filter-tab active" data-filter="all">
                        <i class="ph ph-squares-four" aria-hidden="true"></i>
                        {{ __('messages.all') ?? 'All' }}
                        <span class="es-filter-count" id="count-all">0</span>
                    </button>
                    <button class="es-filter-tab" data-filter="doctor">
                        <i class="ph ph-stethoscope" aria-hidden="true"></i>
                        {{ __('frontend.doctors') ?? 'Doctors' }}
                        <span class="es-filter-count" id="count-doctor">0</span>
                    </button>
                    <button class="es-filter-tab" data-filter="clinic">
                        <i class="ph ph-hospital" aria-hidden="true"></i>
                        {{ __('frontend.clinics') ?? 'Clinics' }}
                        <span class="es-filter-count" id="count-clinic">0</span>
                    </button>
                    <button class="es-filter-tab" data-filter="service">
                        <i class="ph ph-first-aid-kit" aria-hidden="true"></i>
                        {{ __('frontend.services') ?? 'Services' }}
                        <span class="es-filter-count" id="count-service">0</span>
                    </button>
                    <button class="es-filter-tab" data-filter="category">
                        <i class="ph ph-tag" aria-hidden="true"></i>
                        {{ __('frontend.categories') ?? 'Categories' }}
                        <span class="es-filter-count" id="count-category">0</span>
                    </button>
                </div>

                <p class="es-results-summary d-none" id="es-results-summary" aria-live="polite"></p>

                <!-- Search Results -->
                <div class="my-2">
                    <div class="row gy-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4" id="results"></div>
                </div>

                <!-- Shimmer Loader -->
                <div class="row gy-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 d-none" id="shimmer-loader">
                    @for ($i = 0; $i < 8; $i++)
                        <div class="serach-card p-3 text-center rounded placeholder-glow search-shimmer-card"
                            style="width: 300px;">
                            <div class="position-relative">
                                <div class="d-block">
                                    <div class="placeholder rounded object-cover" style="width: 100%; height: 150px;"></div>
                                </div>
                                <h6 class="line-count-1 mt-3 mb-2 pb-1 placeholder rounded"
                                    style="height: 20px; width: 70%; margin: 10px auto;"></h6>
                                <p class="mb-0 text-capitalize line-count-2 placeholder rounded"
                                    style="height: 14px; width: 90%; margin: 5px auto;"></p>
                                <p class="mb-0 text-capitalize line-count-2 placeholder rounded"
                                    style="height: 14px; width: 80%; margin: 5px auto;"></p>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- No Results -->
                <div id="no_result" class="es-no-result d-none">
                    <img src="{{ asset('img/frontend/search-not-found.png') }}" alt="">
                    <h5>{{ __('messages.no_data_found') ?? "Sorry, we couldn't find your search!" }}</h5>
                    <p>{{ __('messages.try_different_keyword') ?? 'Try a different keyword or browse a category' }}</p>
                    <div class="es-no-result-pills">
                        <a href="{{ route('doctors') }}" class="es-no-result-pill">
                            <i class="ph ph-stethoscope" aria-hidden="true"></i>
                            {{ __('frontend.doctors') ?? 'Doctors' }}
                        </a>
                        <a href="{{ route('clinics') }}" class="es-no-result-pill">
                            <i class="ph ph-hospital" aria-hidden="true"></i>
                            {{ __('frontend.clinics') ?? 'Clinics' }}
                        </a>
                        <a href="{{ route('services') }}" class="es-no-result-pill">
                            <i class="ph ph-first-aid-kit" aria-hidden="true"></i>
                            {{ __('frontend.services') ?? 'Services' }}
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var debounceTimer;
    var searchInput      = document.getElementById('search-query');
    var clearBtn         = document.getElementById('remove-search');
    var resultsContainer = document.getElementById('results');
    var noResult         = document.getElementById('no_result');
    var shimmerLoader    = document.getElementById('shimmer-loader');
    var filterBar        = document.getElementById('es-filter-bar');
    var resultsSummary   = document.getElementById('es-results-summary');
    var filterTabs       = document.querySelectorAll('.es-filter-tab');
    var activeFilter     = 'all';

    // ── Detect result type from its link href ──────────────────────────────
    var typePatterns = {
        doctor:   /doctor-details/i,
        clinic:   /clinic-details/i,
        service:  /service-details/i,
        category: /category-details/i
    };

    function detectType(colEl) {
        var link = colEl.querySelector('a[href]');
        if (!link) return 'other';
        var href = link.getAttribute('href') || '';
        for (var type in typePatterns) {
            if (typePatterns[type].test(href)) return type;
        }
        return 'other';
    }

    // ── Tag result cols and count types ───────────────────────────────────
    function tagAndCountResults() {
        var cols = resultsContainer.querySelectorAll('.col');
        var counts = { all: cols.length, doctor: 0, clinic: 0, service: 0, category: 0 };

        cols.forEach(function (col) {
            var type = detectType(col);
            col.setAttribute('data-result-type', type);
            if (counts[type] !== undefined) counts[type]++;
        });

        // Update count badges
        for (var key in counts) {
            var el = document.getElementById('count-' + key);
            if (el) el.textContent = counts[key];
        }

        // Show/hide tabs that have results
        filterTabs.forEach(function (tab) {
            var filter = tab.getAttribute('data-filter');
            var count  = filter === 'all' ? counts.all : (counts[filter] || 0);
            tab.style.display = (filter === 'all' || count > 0) ? '' : 'none';
        });

        return counts;
    }

    // ── Apply active filter ────────────────────────────────────────────────
    function applyFilter(filter) {
        activeFilter = filter;
        var cols = resultsContainer.querySelectorAll('.col');
        var visible = 0;
        cols.forEach(function (col) {
            var type = col.getAttribute('data-result-type') || 'other';
            var show = (filter === 'all' || type === filter);
            col.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        filterTabs.forEach(function (tab) {
            tab.classList.toggle('active', tab.getAttribute('data-filter') === filter);
        });

        if (resultsSummary) {
            var q = searchInput ? searchInput.value.trim() : '';
            resultsSummary.innerHTML = 'Showing <strong>' + visible + '</strong> result' + (visible !== 1 ? 's' : '') +
                (q ? ' for <strong>"' + q.replace(/</g, '&lt;') + '"</strong>' : '');
        }
    }

    // ── Filter tab clicks ─────────────────────────────────────────────────
    filterTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            applyFilter(this.getAttribute('data-filter'));
        });
    });

    // ── Toggle clear button ───────────────────────────────────────────────
    function toggleClear() {
        if (clearBtn) {
            clearBtn.classList.toggle('d-none', !(searchInput && searchInput.value.trim()));
        }
    }

    // ── Core search ───────────────────────────────────────────────────────
    function search(query) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            if (!query) {
                resultsContainer.innerHTML = '';
                noResult.classList.remove('d-none');
                shimmerLoader.classList.add('d-none');
                filterBar.classList.add('d-none');
                if (resultsSummary) resultsSummary.classList.add('d-none');
                return;
            }
            performSearch(query);
        }, 300);
    }

    function performSearch(query) {
        shimmerLoader.classList.remove('d-none');
        noResult.classList.add('d-none');
        filterBar.classList.add('d-none');
        if (resultsSummary) resultsSummary.classList.add('d-none');

        $.ajax({
            url: "{{ route('getSearchData') }}" + '?search=' + encodeURIComponent(query),
            method: 'GET',
            success: function (response) {
                shimmerLoader.classList.add('d-none');
                if (response.status && response.html) {
                    resultsContainer.innerHTML = response.html;
                    noResult.classList.add('d-none');

                    // Tag, count, then apply filter
                    tagAndCountResults();
                    applyFilter(activeFilter);

                    filterBar.classList.remove('d-none');
                    if (resultsSummary) resultsSummary.classList.remove('d-none');

                    // Update URL without reload
                    window.history.replaceState({}, '',
                        window.location.pathname + '?query=' + encodeURIComponent(query));
                } else {
                    resultsContainer.innerHTML = '';
                    noResult.classList.remove('d-none');
                }
            },
            error: function () {
                shimmerLoader.classList.add('d-none');
                noResult.classList.remove('d-none');
            }
        });
    }

    // ── Events ────────────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            toggleClear();
            search(this.value.trim());
        });
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); search(this.value.trim()); }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            toggleClear();
            search('');
            window.history.replaceState({}, '', window.location.pathname);
        });
    }

    // ── Pre-fill from URL ─────────────────────────────────────────────────
    var urlQuery = new URLSearchParams(window.location.search).get('query');
    if (urlQuery) {
        if (searchInput) searchInput.value = urlQuery;
        toggleClear();
        search(urlQuery);

        // Also pre-fill the overlay input if present (header sync)
        var overlayInput = document.getElementById('es-search-input');
        if (overlayInput) overlayInput.value = urlQuery;
    } else {
        noResult.classList.remove('d-none');
    }
});
</script>
@endpush
