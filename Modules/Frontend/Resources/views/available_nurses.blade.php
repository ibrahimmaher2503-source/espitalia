@extends('frontend::layouts.master')
@section('title', __('request_nurse.available_nurses'))

@section('content')
    @include('frontend::components.section.breadcrumb')
    <div class="list-page section-spacing px-0">
        <div class="movie-lists">
            <div class="container-fluid">
                <div class="row gy-2">
                    <div class="col-xl-7 col-lg-9 filter">
                        <div class="d-flex flex-lg-row flex-column gap-3 align-items-lg-center">
                            <h6 class="m-0 flex-shrink-0">{{ __('frontend.filter_by') }}</h6>
                            <div class="row gx-3 gy-2 flex-grow-1">
                                <div class="col-md-6">
                                    <div class="form-group datatable-filter">
                                        <select name="specialization" id="specialization"
                                                class="form-select select2"
                                                data-filter="select"
                                                multiple="multiple">
                                            @foreach ($specializations as $spec)
                                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group datatable-filter">
                                        <select name="availability_status" id="availability_status" class="form-select select2" data-filter="select">
                                            <option value="">{{ __('service.all') }} {{ __('request_nurse.lbl_availability_status') }}</option>
                                            <option value="available">{{ __('request_nurse.status_available') }}</option>
                                            <option value="busy">{{ __('request_nurse.status_busy') }}</option>
                                            <option value="off_duty">{{ __('request_nurse.status_off_duty') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 d-xl-block d-none"></div>
                    <div class="col-lg-3">
                        <div class="d-flex align-items-center p-2 rounded section-bg">
                            <div class="icon ps-2">
                                <i class="ph ph-magnifying-glass align-middle"></i>
                            </div>
                            <input type="text" id="datatable-search"
                                   style="background:none;border:0;outline:0;flex:1;padding:0.4rem 0.5rem;font-family:var(--pp-font);font-size:0.875rem;color:var(--pp-text)"
                                   placeholder="{{ __('messages.search') }}...">
                        </div>
                    </div>
                </div>

                <div id="selected-filters" class="d-flex gap-2 flex-wrap mt-3"></div>

                <div class="card-style-slider">
                    <div id="shimmer-loader" class="d-flex gap-3 flex-wrap p-4">
                        @for ($i = 0; $i < 8; $i++)
                            <div class="doctor-card rounded text-center placeholder-glow doctor-section-card" style="width: 280px;">
                                <div class="placeholder-glow text-center pt-4">
                                    <div class="placeholder rounded-circle mx-auto" style="height:100px;width:100px;"></div>
                                </div>
                                <div class="p-3">
                                    <div class="placeholder rounded mb-2" style="height:18px;width:70%;"></div>
                                    <div class="placeholder rounded mb-3" style="height:14px;width:50%;"></div>
                                    <div class="placeholder rounded" style="height:32px;width:90%;"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div id="content-grid" class="row gx-3 gy-4 d-none p-4">
                        {{-- Cards rendered by JS --}}
                    </div>

                    <div id="no-results" class="d-none">
                        <div class="pp-card text-center" style="padding:3rem 1.5rem">
                            <div class="pp-empty">
                                <i class="ph ph-user-circle-minus" aria-hidden="true"></i>
                                <p>{{ __('request_nurse.no_nurses_available') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center pb-4 d-none" id="load-more-container">
                    <button class="pp-btn pp-btn-outline" id="load-more-btn" style="padding-left:2rem;padding-right:2rem">
                        <i class="ph ph-arrow-down" aria-hidden="true"></i>
                        {{ __('messages.load_more') }}
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
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('datatable-search');
            const selectedFiltersDiv = document.getElementById('selected-filters');
            const specializationFilter = $('#specialization');
            const availabilityFilter = $('#availability_status');
            const shimmerLoader = document.getElementById('shimmer-loader');
            const contentGrid = document.getElementById('content-grid');
            const noResults = document.getElementById('no-results');
            const loadMoreContainer = document.getElementById('load-more-container');
            const loadMoreBtn = document.getElementById('load-more-btn');

            let currentPage = 1;
            let totalPages = 1;
            let searchDebounce = null;

            specializationFilter.select2({
                placeholder: '{{ __('specialization.lbl_specializations') }}',
                allowClear: true,
                width: '100%'
            });

            availabilityFilter.select2({
                placeholder: '{{ __('request_nurse.lbl_availability_status') }}',
                allowClear: true,
                width: '100%'
            });

            function updateSelectedFilters() {
                selectedFiltersDiv.innerHTML = '';

                const activeFilters = [];

                specializationFilter.find('option:selected').each(function () {
                    if ($(this).val()) {
                        activeFilters.push({ key: 'specialization', val: $(this).val(), text: $(this).text() });
                    }
                });

                const selectedAvail = availabilityFilter.find('option:selected');
                if (selectedAvail.val() && !selectedAvail.text().toLowerCase().includes('all')) {
                    activeFilters.push({ key: 'availability_status', val: selectedAvail.val(), text: selectedAvail.text() });
                }

                activeFilters.forEach(function (filter) {
                    const filterBadge = document.createElement('span');
                    filterBadge.classList.add('filters-bagde', 'px-3', 'py-2');
                    filterBadge.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
                    filterBadge.style.border = '1px solid var(--bs-primary)';
                    filterBadge.style.color = 'var(--bs-heading-color)';
                    filterBadge.style.borderRadius = 'var(--bs-border-radius-pill)';
                    filterBadge.textContent = filter.text;

                    const closeIcon = document.createElement('span');
                    closeIcon.classList.add('ms-3', 'fw-bold', 'text-primary', 'font-size-14');
                    closeIcon.innerHTML = '&times;';
                    closeIcon.style.cursor = 'pointer';
                    closeIcon.onclick = function () { clearFilter(filter.key, filter.val); };

                    filterBadge.appendChild(closeIcon);
                    selectedFiltersDiv.appendChild(filterBadge);
                });
            }

            function clearFilter(key, val) {
                if (key === 'specialization' && val) {
                    var current = specializationFilter.val() || [];
                    current = current.filter(function (v) { return v !== val; });
                    specializationFilter.val(current).trigger('change');
                } else if (key === 'availability_status') {
                    availabilityFilter.val('').trigger('change');
                }
            }

            function buildNurseCard(nurse) {
                let availClass = 'text-success bg-success-subtle';
                if (nurse.availability_status === 'busy') {
                    availClass = 'text-warning bg-warning-subtle';
                } else if (nurse.availability_status === 'off_duty') {
                    availClass = 'text-secondary bg-secondary-subtle';
                }

                const statusLabel = nurse.availability_status === 'available'
                    ? '{{ __('request_nurse.status_available') }}'
                    : (nurse.availability_status === 'busy'
                        ? '{{ __('request_nurse.status_busy') }}'
                        : '{{ __('request_nurse.status_off_duty') }}');

                return `<div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="doctor-card rounded text-center doctor-section-card">
                        <div class="doctor-inner-card d-block">
                            <img src="${nurse.profile_image}" class="w-100 object-cover rounded-top" style="height:200px;" alt="${nurse.name}">
                            <div class="doctor-content p-3">
                                <div class="d-flex align-items-center justify-content-center gap-2 mt-2 mb-1">
                                    <h6 class="m-0">${nurse.name}</h6>
                                    <i class="ph-fill ph-seal-check text-success"></i>
                                </div>
                                <p class="text-muted font-size-14 mb-2">${nurse.specialization || '{{ __('request_nurse.nurse_singular') }}'}</p>
                                <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                                    <span class="font-size-14"><i class="ph ph-clock"></i> ${nurse.experience}</span>
                                    <span class="font-size-14 fw-bold"><i class="ph ph-currency-dollar"></i> ${nurse.hourly_rate}{{ __('request_nurse.lbl_per_hour') }}</span>
                                </div>
                                <div class="${availClass} d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1 mb-3 rate-meta-box">
                                    <i class="ph ph-circle-fill" style="font-size:8px;"></i>
                                    <small>${statusLabel}</small>
                                </div>
                                <a href="/nurse-details/${nurse.id}" class="pp-btn pp-btn-teal w-100" style="justify-content:center">{{ __('request_nurse.lbl_view_details') }}</a>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            function loadNurses(page, append) {
                if (!append) {
                    shimmerLoader.classList.remove('d-none');
                    contentGrid.classList.add('d-none');
                    noResults.classList.add('d-none');
                    loadMoreContainer.classList.add('d-none');
                }

                $.ajax({
                    url: '{{ route('nurse.index_data') }}',
                    method: 'GET',
                    data: {
                        page: page,
                        search: searchInput.value,
                        specialization_ids: specializationFilter.val(),
                        availability_status: availabilityFilter.val(),
                        per_page: 12
                    },
                    success: function (response) {
                        shimmerLoader.classList.add('d-none');
                        totalPages = response.last_page || 1;

                        const cards = (response.data || []).map(buildNurseCard).join('');

                        if (!append) {
                            contentGrid.innerHTML = cards;
                        } else {
                            contentGrid.insertAdjacentHTML('beforeend', cards);
                        }

                        if ((response.data || []).length === 0 && !append) {
                            noResults.classList.remove('d-none');
                        } else {
                            contentGrid.classList.remove('d-none');
                        }

                        if (page < totalPages) {
                            loadMoreContainer.classList.remove('d-none');
                        } else {
                            loadMoreContainer.classList.add('d-none');
                        }
                    },
                    error: function () {
                        shimmerLoader.classList.add('d-none');
                        contentGrid.classList.remove('d-none');
                    }
                });
            }

            function resetAndLoad() {
                currentPage = 1;
                updateSelectedFilters();
                loadNurses(1, false);
            }

            specializationFilter.on('change', resetAndLoad);
            availabilityFilter.on('change', resetAndLoad);

            searchInput.addEventListener('keyup', function (e) {
                if (e.key === 'Enter') {
                    clearTimeout(searchDebounce);
                    resetAndLoad();
                    return;
                }
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(resetAndLoad, 400);
            });

            loadMoreBtn.addEventListener('click', function () {
                currentPage++;
                loadNurses(currentPage, true);
            });

            loadNurses(1, false);
        });
    </script>
@endpush
