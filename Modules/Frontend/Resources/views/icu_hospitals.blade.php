@extends('frontend::layouts.master')
@section('title', __('intensive_care.hospital_catalog'))

@section('content')
    @include('frontend::components.section.breadcrumb')
    <div class="list-page section-spacing px-0">
        <div class="movie-lists">
            <div class="container-fluid">
                <div class="row gy-2">
                    <div class="col-xl-9 col-lg-9 filter">
                        <div class="d-flex flex-lg-row flex-column gap-3 align-items-lg-center">
                            <h6 class="m-0 flex-shrink-0">{{ __('frontend.filter_by') }}</h6>
                            <div class="row gx-3 gy-2 flex-grow-1">
                                <div class="col-md-3">
                                    <div class="form-group datatable-filter">
                                        <input type="text" id="city-filter" class="form-control"
                                            placeholder="{{ __('intensive_care.lbl_city') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group datatable-filter">
                                        <select id="specialty-filter" class="form-select select2">
                                            <option value="">{{ __('service.all') }} {{ __('intensive_care.lbl_specialty_type') }}</option>
                                            @foreach ($specialtyTypes as $type)
                                                <option value="{{ $type }}">{{ __('intensive_care.case_' . $type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group datatable-filter">
                                        <select id="equipment-level-filter" class="form-select select2">
                                            <option value="">{{ __('service.all') }} {{ __('intensive_care.lbl_equipment_level') }}</option>
                                            @foreach ($equipmentLevels as $level)
                                                <option value="{{ $level }}">{{ __('intensive_care.equipment_' . $level) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group datatable-filter">
                                        <input type="text" id="insurance-filter" class="form-control"
                                            placeholder="{{ __('intensive_care.lbl_accepted_insurance') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-lg-row flex-column gap-3 align-items-lg-center mt-2">
                            <h6 class="m-0 flex-shrink-0" style="visibility:hidden;">{{ __('frontend.filter_by') }}</h6>
                            <div class="row gx-3 gy-2 flex-grow-1">
                                <div class="col-md-2">
                                    <div class="form-group datatable-filter">
                                        <input type="number" id="price-min-filter" class="form-control"
                                            placeholder="{{ __('intensive_care.lbl_price_min') }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group datatable-filter">
                                        <input type="number" id="price-max-filter" class="form-control"
                                            placeholder="{{ __('intensive_care.lbl_price_max') }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="ventilator-filter">
                                        <label class="form-check-label" for="ventilator-filter">
                                            <i class="ph ph-wind me-1"></i>{{ __('intensive_care.lbl_needs_ventilator') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="oxygen-filter">
                                        <label class="form-check-label" for="oxygen-filter">
                                            <i class="ph ph-drop me-1"></i>{{ __('intensive_care.lbl_needs_oxygen') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="d-flex align-items-center p-2 rounded section-bg">
                            <div class="icon ps-2">
                                <i class="ph ph-magnifying-glass align-middle"></i>
                            </div>
                            <input type="text" id="datatable-search"
                                class="form-control px-2 py-2 h-auto border-0 focus:ring-0"
                                placeholder="{{ __('messages.search') }}...">
                        </div>
                    </div>
                </div>

                <div id="selected-filters" class="d-flex gap-2 flex-wrap mt-3"></div>

                <div class="card-style-slider">
                    <div id="shimmer-loader" class="d-flex gap-3 flex-wrap p-4">
                        @for ($i = 0; $i < 8; $i++)
                            <div class="doctor-card rounded placeholder-glow doctor-section-card" style="width:280px;">
                                <div class="placeholder rounded-top section-bg" style="height:140px;width:100%;"></div>
                                <div class="p-3">
                                    <div class="placeholder rounded mb-2" style="height:18px;width:80%;"></div>
                                    <div class="placeholder rounded mb-1" style="height:14px;width:60%;"></div>
                                    <div class="placeholder rounded mb-2" style="height:14px;width:50%;"></div>
                                    <div class="placeholder rounded" style="height:32px;width:100%;"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div id="content-grid" class="row g-3 p-4 d-none"></div>
                </div>

                <div class="text-center py-4" id="load-more-container" style="display:none;">
                    <button class="btn btn-outline-primary px-5 py-2 rounded-pill" id="load-more-btn">
                        {{ __('intensive_care.lbl_load_more') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript">
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        const hospitalDetailUrl = "{{ url('icu-hospital-details') }}";

        document.addEventListener('DOMContentLoaded', function () {
            const shimmerLoader  = document.getElementById('shimmer-loader');
            const contentGrid    = document.getElementById('content-grid');
            const searchInput    = document.getElementById('datatable-search');
            const loadMoreBtn    = document.getElementById('load-more-btn');
            const loadMoreContainer = document.getElementById('load-more-container');
            const selectedFiltersDiv = document.getElementById('selected-filters');

            const cityFilter           = document.getElementById('city-filter');
            const specialtyFilter      = $('#specialty-filter');
            const equipmentLevelFilter = $('#equipment-level-filter');
            const insuranceFilter      = document.getElementById('insurance-filter');
            const priceMinFilter       = document.getElementById('price-min-filter');
            const priceMaxFilter       = document.getElementById('price-max-filter');
            const ventilatorFilter     = document.getElementById('ventilator-filter');
            const oxygenFilter         = document.getElementById('oxygen-filter');

            let currentPage = 1;
            let totalPages  = 1;
            let searchTimer = null;

            // Initialise Select2
            specialtyFilter.select2({
                placeholder: '{{ __('intensive_care.lbl_specialty_type') }}',
                allowClear: true,
                width: '100%'
            });
            equipmentLevelFilter.select2({
                placeholder: '{{ __('intensive_care.lbl_equipment_level') }}',
                allowClear: true,
                width: '100%'
            });

            // ── Filter badge helpers ────────────────────────────────────────────
            function addFilterBadge(key, text) {
                const badge = document.createElement('span');
                badge.classList.add('filters-bagde', 'px-3', 'py-2');
                badge.dataset.key = key;
                badge.style.backgroundColor   = 'var(--bs-primary-bg-subtle)';
                badge.style.border            = '1px solid var(--bs-primary)';
                badge.style.color             = 'var(--bs-heading-color)';
                badge.style.borderRadius      = 'var(--bs-border-radius-pill)';
                badge.textContent = text;
                const x = document.createElement('span');
                x.classList.add('ms-3', 'fw-bold', 'text-primary', 'font-size-14');
                x.innerHTML = '&times;';
                x.style.cursor = 'pointer';
                x.onclick = () => clearFilter(key);
                badge.appendChild(x);
                selectedFiltersDiv.appendChild(badge);
            }

            function updateSelectedFilters() {
                selectedFiltersDiv.innerHTML = '';
                const selSpec  = specialtyFilter.find('option:selected');
                const selEquip = equipmentLevelFilter.find('option:selected');
                if (selSpec.val()  && !selSpec.text().startsWith('{{ __('service.all') }}'))  addFilterBadge('specialty',       selSpec.text());
                if (selEquip.val() && !selEquip.text().startsWith('{{ __('service.all') }}')) addFilterBadge('equipment_level', selEquip.text());
                if (cityFilter.value)      addFilterBadge('city',      cityFilter.value);
                if (insuranceFilter.value)  addFilterBadge('insurance', insuranceFilter.value);
                if (ventilatorFilter.checked) addFilterBadge('ventilator', '{{ __('intensive_care.lbl_needs_ventilator') }}');
                if (oxygenFilter.checked)    addFilterBadge('oxygen',    '{{ __('intensive_care.lbl_needs_oxygen') }}');
            }

            function clearFilter(key) {
                if (key === 'specialty')       specialtyFilter.val('').trigger('change');
                if (key === 'equipment_level') equipmentLevelFilter.val('').trigger('change');
                if (key === 'city')            { cityFilter.value = ''; triggerReload(); }
                if (key === 'insurance')       { insuranceFilter.value = ''; triggerReload(); }
                if (key === 'ventilator')      { ventilatorFilter.checked = false; triggerReload(); }
                if (key === 'oxygen')          { oxygenFilter.checked = false; triggerReload(); }
            }

            function triggerReload() {
                updateSelectedFilters();
                currentPage = 1;
                loadHospitals(1, false);
            }

            function renderStars(rating) {
                let stars = '';
                const r = parseFloat(rating) || 0;
                for (let i = 1; i <= 5; i++) {
                    if (i <= Math.floor(r)) {
                        stars += '<i class="ph-fill ph-star text-warning"></i>';
                    } else if (i - r < 1 && i - r > 0) {
                        stars += '<i class="ph-fill ph-star-half text-warning"></i>';
                    } else {
                        stars += '<i class="ph ph-star text-muted"></i>';
                    }
                }
                return stars;
            }

            // ── Load hospitals via AJAX ─────────────────────────────────────────
            function loadHospitals(page, append) {
                if (!append) {
                    shimmerLoader.classList.remove('d-none');
                    contentGrid.classList.add('d-none');
                    contentGrid.innerHTML = '';
                }

                $.ajax({
                    url: '{{ route('icu-hospital.index_data') }}',
                    data: {
                        page:            page,
                        search:          searchInput.value,
                        city:            cityFilter.value,
                        specialty:       specialtyFilter.val(),
                        equipment_level: equipmentLevelFilter.val(),
                        insurance:       insuranceFilter.value,
                        price_min:       priceMinFilter.value,
                        price_max:       priceMaxFilter.value,
                        ventilator:      ventilatorFilter.checked ? 1 : '',
                        oxygen:          oxygenFilter.checked ? 1 : '',
                        per_page:        12
                    },
                    success: function (response) {
                        shimmerLoader.classList.add('d-none');
                        contentGrid.classList.remove('d-none');
                        totalPages = response.last_page || 1;

                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function (hospital) {
                                const specialtiesBadges = (hospital.specialties || []).map(function(s) {
                                    return '<span class="badge bg-info me-1 mb-1">' + escapeHtml(s) + '</span>';
                                }).join('');

                                const col = document.createElement('div');
                                col.className = 'col-xl-3 col-lg-4 col-md-6';
                                col.innerHTML = `
                                    <div class="doctor-card rounded doctor-section-card">
                                        <div class="doctor-inner-card d-block">
                                            <div class="p-3 text-center section-bg rounded-top" style="min-height:140px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                                <i class="ph ph-hospital text-primary" style="font-size:48px;"></i>
                                                <div class="mt-2">
                                                    ${renderStars(hospital.rating)}
                                                </div>
                                            </div>
                                            <div class="doctor-content p-3">
                                                <h6 class="mb-1">${escapeHtml(hospital.name)}</h6>
                                                <p class="text-muted font-size-14 mb-1"><i class="ph ph-map-pin me-1"></i>${escapeHtml(hospital.city)}</p>
                                                <p class="text-muted font-size-14 mb-1"><i class="ph ph-bed me-1"></i>${hospital.total_available_beds} {{ __('intensive_care.lbl_available_beds') }}</p>
                                                <div class="mb-2">${specialtiesBadges || '<span class="text-muted font-size-14">-</span>'}</div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold font-size-14">${escapeHtml(hospital.price_range)}</span>
                                                </div>
                                                <a href="${hospitalDetailUrl}/${hospital.id}" class="btn btn-primary btn-sm w-100">{{ __('intensive_care.lbl_view_details') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                contentGrid.appendChild(col);
                            });
                        } else if (!append) {
                            contentGrid.innerHTML = `
                                <div class="col-12 text-center py-5 section-bg rounded">
                                    <i class="ph ph-hospital" style="font-size:64px;opacity:.3;"></i>
                                    <p class="text-muted mt-3">{{ __('intensive_care.lbl_no_results') }}</p>
                                </div>
                            `;
                        }

                        loadMoreContainer.style.display = (currentPage < totalPages) ? '' : 'none';
                    },
                    error: function () {
                        shimmerLoader.classList.add('d-none');
                        contentGrid.classList.remove('d-none');
                        contentGrid.innerHTML = '<div class="col-12"><div class="alert alert-danger">{{ __("intensive_care.lbl_error_loading") }}</div></div>';
                    }
                });
            }

            // ── Wire up events ──────────────────────────────────────────────────
            specialtyFilter.on('change', function () {
                updateSelectedFilters();
                currentPage = 1;
                loadHospitals(1, false);
            });
            equipmentLevelFilter.on('change', function () {
                updateSelectedFilters();
                currentPage = 1;
                loadHospitals(1, false);
            });

            ventilatorFilter.addEventListener('change', triggerReload);
            oxygenFilter.addEventListener('change', triggerReload);

            cityFilter.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(triggerReload, 400);
            });
            insuranceFilter.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(triggerReload, 400);
            });
            priceMinFilter.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(triggerReload, 400);
            });
            priceMaxFilter.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(triggerReload, 400);
            });

            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    currentPage = 1;
                    loadHospitals(1, false);
                }, 400);
            });

            loadMoreBtn.addEventListener('click', function () {
                currentPage++;
                loadHospitals(currentPage, true);
            });

            // Initial load
            loadHospitals(1, false);
            updateSelectedFilters();
        });
    </script>
@endpush
