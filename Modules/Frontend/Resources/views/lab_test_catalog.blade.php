@extends('frontend::layouts.master')
@section('title', __('radiology_lab.test_catalog'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">

            {{-- ── Page header ── --}}
            <div class="pp-page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="pp-page-title">{{ __('radiology_lab.test_catalog') }}</h1>
                    <p style="color:var(--pp-muted);font-size:0.82rem;margin:0;font-family:var(--pp-font)">
                        {{ __('radiology_lab.lbl_browse_available_tests') ?? 'Browse and order laboratory & radiology tests' }}
                    </p>
                </div>
                @auth
                    <a href="{{ route('my-test-orders') }}" class="pp-btn pp-btn-outline">
                        <i class="ph ph-list-bullets" aria-hidden="true"></i>
                        {{ __('radiology_lab.my_test_orders') }}
                    </a>
                @endauth
            </div>

            {{-- ── Filter bar ── --}}
            <div class="pp-filter-bar mb-3">
                <div class="d-flex flex-lg-row flex-column gap-3 align-items-lg-center flex-wrap">
                    <div class="d-flex flex-wrap gap-2 flex-grow-1">
                        <div style="min-width:150px;flex:1">
                            <select id="category-filter" class="pp-select"
                                    aria-label="{{ __('radiology_lab.lbl_category') }}">
                                <option value="">{{ __('service.all') }} {{ __('radiology_lab.lbl_category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="min-width:130px;flex:1">
                            <select id="department-filter" class="pp-select"
                                    aria-label="{{ __('radiology_lab.lbl_department') }}">
                                <option value="">{{ __('service.all') }} {{ __('radiology_lab.lbl_department') }}</option>
                                <option value="laboratory">{{ __('radiology_lab.dept_laboratory') }}</option>
                                <option value="radiology">{{ __('radiology_lab.dept_radiology') }}</option>
                            </select>
                        </div>
                        <div style="min-width:130px;flex:1">
                            <select id="sample-type-filter" class="pp-select"
                                    aria-label="{{ __('radiology_lab.lbl_sample_type') }}">
                                <option value="">{{ __('service.all') }} {{ __('radiology_lab.lbl_sample_type') }}</option>
                                <option value="blood">{{ __('radiology_lab.sample_blood') }}</option>
                                <option value="urine">{{ __('radiology_lab.sample_urine') }}</option>
                                <option value="stool">{{ __('radiology_lab.sample_stool') }}</option>
                                <option value="swab">{{ __('radiology_lab.sample_swab') }}</option>
                                <option value="tissue">{{ __('radiology_lab.sample_tissue') }}</option>
                                <option value="imaging">{{ __('radiology_lab.sample_imaging') }}</option>
                                <option value="other">{{ __('radiology_lab.sample_other') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="pp-input-group" style="min-width:200px;max-width:260px;flex-shrink:0">
                        <input type="search" id="datatable-search"
                               class="pp-input"
                               placeholder="{{ __('messages.search') }}…"
                               aria-label="{{ __('messages.search') }}">
                        <span class="pp-input-icon" aria-hidden="true">
                            <i class="ph ph-magnifying-glass"></i>
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── Active filter pills ── --}}
            <div id="selected-filters" class="d-flex gap-2 flex-wrap mb-3"
                 aria-live="polite" aria-label="Active filters"></div>

            {{-- ── Shimmer grid ── --}}
            <div id="shimmer-loader" class="pp-lab-grid">
                @for ($i = 0; $i < 8; $i++)
                    <div class="pp-lab-shimmer">
                        <div class="pp-lab-shimmer-icon-area">
                            <div class="pp-sk" style="width:52px;height:52px;border-radius:var(--pp-r)"></div>
                        </div>
                        <div class="pp-lab-shimmer-body">
                            <div class="pp-sk pp-sk-h16 pp-sk-w80 mb-2"></div>
                            <div class="pp-sk pp-sk-h10 pp-sk-w60 mb-1"></div>
                            <div class="pp-sk pp-sk-h10 pp-sk-w40 mb-3"></div>
                            <div class="pp-sk" style="height:30px;border-radius:var(--pp-r-sm)"></div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- ── Test card grid ── --}}
            <div id="content-grid" class="pp-lab-grid d-none" role="list"></div>

            {{-- ── Load more ── --}}
            <div class="text-center py-4 d-none" id="load-more-container">
                <button class="pp-btn pp-btn-outline" id="load-more-btn" style="padding-left:2rem;padding-right:2rem">
                    <i class="ph ph-arrow-down" aria-hidden="true"></i>
                    {{ __('radiology_lab.lbl_load_more') }}
                </button>
            </div>

        </div>
    </div>
@endsection

@push('after-scripts')
<script>
(function () {
    'use strict';

    function esc(text) {
        if (!text && text !== 0) return '';
        var d = document.createElement('div');
        d.textContent = String(text);
        return d.innerHTML;
    }

    var labTestDetailUrl   = '{{ url('lab-test-details') }}';
    var shimmerLoader      = document.getElementById('shimmer-loader');
    var contentGrid        = document.getElementById('content-grid');
    var loadMoreCont       = document.getElementById('load-more-container');
    var loadMoreBtn        = document.getElementById('load-more-btn');
    var searchInput        = document.getElementById('datatable-search');
    var selectedFiltersDiv = document.getElementById('selected-filters');
    var categoryFilter     = document.getElementById('category-filter');
    var departmentFilter   = document.getElementById('department-filter');
    var sampleTypeFilter   = document.getElementById('sample-type-filter');
    var currentPage        = 1;
    var totalPages         = 1;
    var searchTimer        = null;

    /* ── Filter pills ───────────────────────────────────────────── */
    function addFilterPill(key, text) {
        var pill = document.createElement('span');
        pill.className = 'pp-filter-pill';
        pill.dataset.filterKey = key;
        var textNode = document.createTextNode(text);
        pill.appendChild(textNode);
        var btn = document.createElement('button');
        btn.className = 'pp-filter-pill-remove';
        btn.setAttribute('type', 'button');
        btn.setAttribute('aria-label', 'Remove filter');
        btn.innerHTML = '<i class="ph ph-x" aria-hidden="true"></i>';
        btn.onclick = function () { clearFilter(key); };
        pill.appendChild(btn);
        selectedFiltersDiv.appendChild(pill);
    }

    function updateFilterPills() {
        selectedFiltersDiv.innerHTML = '';
        if (categoryFilter.value)   addFilterPill('category',    categoryFilter.options[categoryFilter.selectedIndex].text);
        if (departmentFilter.value) addFilterPill('department',  departmentFilter.options[departmentFilter.selectedIndex].text);
        if (sampleTypeFilter.value) addFilterPill('sample_type', sampleTypeFilter.options[sampleTypeFilter.selectedIndex].text);
    }

    function clearFilter(key) {
        if (key === 'category')    categoryFilter.value   = '';
        if (key === 'department')  departmentFilter.value = '';
        if (key === 'sample_type') sampleTypeFilter.value = '';
        updateFilterPills();
        currentPage = 1;
        loadTests(1, false);
    }

    /* ── Build lab card HTML ────────────────────────────────────── */
    function buildLabCard(test) {
        var iconMap = { laboratory: 'ph-flask', radiology: 'ph-x-ray' };
        var icon    = iconMap[test.department] || 'ph-test-tube';
        var deptCls = test.department === 'laboratory' ? 'pp-badge-teal' : 'pp-badge-navy';

        var metaHtml =
            '<span class="pp-lab-card-meta-item"><i class="ph ph-tag" aria-hidden="true"></i>' + esc(test.category_name) + '</span>' +
            '<span class="pp-lab-card-meta-item"><i class="ph ph-test-tube" aria-hidden="true"></i>' + esc(test.sample_type_label) + '</span>' +
            (test.turnaround_time ? '<span class="pp-lab-card-meta-item"><i class="ph ph-clock" aria-hidden="true"></i>' + esc(test.turnaround_time) + '</span>' : '');

        return '<a href="' + labTestDetailUrl + '/' + esc(String(test.id)) + '" class="pp-lab-card" role="listitem">' +
            '<div class="pp-lab-card-icon-area">' +
                '<div class="pp-lab-card-icon" aria-hidden="true"><i class="ph ' + esc(icon) + '"></i></div>' +
                '<div class="d-flex gap-1 flex-wrap justify-content-center">' +
                    '<span class="pp-badge">' + esc(test.code) + '</span>' +
                    '<span class="pp-badge ' + esc(deptCls) + '">' + esc(test.department_label) + '</span>' +
                '</div>' +
            '</div>' +
            '<div class="pp-lab-card-body">' +
                '<p class="pp-lab-card-name">' + esc(test.name) + '</p>' +
                '<div class="pp-lab-card-meta">' + metaHtml + '</div>' +
            '</div>' +
            '<div class="pp-lab-card-footer">' +
                '<span class="pp-lab-card-price">' + esc(test.default_price) + '</span>' +
                '<span class="pp-badge pp-badge-teal" style="font-size:0.68rem">{{ __('radiology_lab.lbl_view_details') }}</span>' +
            '</div>' +
        '</a>';
    }

    /* ── AJAX load ──────────────────────────────────────────────── */
    function loadTests(page, append) {
        if (!append) {
            shimmerLoader.classList.remove('d-none');
            contentGrid.classList.add('d-none');
            contentGrid.innerHTML = '';
        }

        $.ajax({
            url: '{{ route('lab-test.index_data') }}',
            data: {
                page:        page,
                search:      searchInput.value,
                category_id: categoryFilter.value,
                department:  departmentFilter.value,
                sample_type: sampleTypeFilter.value,
                per_page:    12
            },
            success: function (res) {
                shimmerLoader.classList.add('d-none');
                contentGrid.classList.remove('d-none');
                totalPages = res.last_page || 1;

                if (res.data && res.data.length > 0) {
                    res.data.forEach(function (test) {
                        contentGrid.insertAdjacentHTML('beforeend', buildLabCard(test));
                    });
                } else if (!append) {
                    contentGrid.innerHTML =
                        '<div class="pp-card text-center" style="padding:3rem 1.5rem;grid-column:1/-1">' +
                            '<div class="pp-empty">' +
                                '<i class="ph ph-test-tube" aria-hidden="true"></i>' +
                                '<p>{{ __('radiology_lab.lbl_no_tests_found') }}</p>' +
                            '</div>' +
                        '</div>';
                }

                loadMoreCont.classList.toggle('d-none', currentPage >= totalPages);
            },
            error: function () {
                shimmerLoader.classList.add('d-none');
                contentGrid.classList.remove('d-none');
                contentGrid.innerHTML =
                    '<div class="pp-card" style="grid-column:1/-1;padding:1rem 1.25rem">' +
                        '<p style="color:var(--pp-cancelled);font-size:0.82rem;margin:0;font-family:var(--pp-font)">' +
                            '<i class="ph ph-warning-circle me-2" aria-hidden="true"></i>' +
                            '{{ __('radiology_lab.lbl_error_loading') }}' +
                        '</p>' +
                    '</div>';
            }
        });
    }

    /* ── Wire events ────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        [categoryFilter, departmentFilter, sampleTypeFilter].forEach(function (sel) {
            sel.addEventListener('change', function () {
                updateFilterPills();
                currentPage = 1;
                loadTests(1, false);
            });
        });

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                currentPage = 1;
                loadTests(1, false);
            }, 400);
        });

        loadMoreBtn.addEventListener('click', function () {
            currentPage++;
            loadTests(currentPage, true);
        });

        loadTests(1, false);
    });
}());
</script>
@endpush
