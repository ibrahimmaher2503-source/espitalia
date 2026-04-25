@extends('frontend::layouts.master')
@section('title', __('request_nurse.my_nurse_requests'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">

            {{-- ── Page header ── --}}
            <div class="pp-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="pp-page-title">{{ __('request_nurse.my_nurse_requests') }}</h1>
                </div>
                <a href="{{ route('request-nurse') }}" class="pp-btn pp-btn-teal">
                    <i class="ph ph-plus-circle" aria-hidden="true"></i>
                    {{ __('request_nurse.request_a_nurse') }}
                </a>
            </div>

            {{-- ── Status filter tabs ── --}}
            <ul class="pp-nav-pills mb-4" id="status-tabs" role="tablist">
                @php
                    $tabs = [
                        ''            => ['label' => __('service.all'),                         'icon' => 'ph-squares-four'],
                        'pending'     => ['label' => __('request_nurse.status_pending'),        'icon' => 'ph-clock'],
                        'confirmed'   => ['label' => __('request_nurse.status_confirmed'),      'icon' => 'ph-check-circle'],
                        'in_progress' => ['label' => __('request_nurse.status_in_progress'),   'icon' => 'ph-spinner'],
                        'completed'   => ['label' => __('request_nurse.status_completed'),     'icon' => 'ph-seal-check'],
                        'cancelled'   => ['label' => __('request_nurse.status_cancelled'),     'icon' => 'ph-x-circle'],
                    ];
                @endphp
                @foreach($tabs as $status => $tab)
                    <li role="presentation">
                        <a href="#"
                           class="pp-nav-link {{ $status === '' ? 'active' : '' }}"
                           data-status="{{ $status }}"
                           role="tab">
                            <i class="ph {{ $tab['icon'] }}" aria-hidden="true"></i>
                            {{ $tab['label'] }}
                            <span class="pp-nav-count" id="badge-{{ $status === '' ? 'all' : $status }}">0</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- ── Shimmer ── --}}
            <div id="shimmer-loader">
                @for ($i = 0; $i < 3; $i++)
                    <div class="pp-order-shimmer">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="d-flex gap-3 flex-grow-1">
                                <div class="pp-sk" style="width:36px;height:36px;border-radius:var(--pp-r-sm);flex-shrink:0"></div>
                                <div style="flex:1">
                                    <div class="pp-sk pp-sk-h16 pp-sk-w60 mb-2"></div>
                                    <div class="pp-order-meta">
                                        <div class="pp-sk pp-sk-h10" style="width:90px;border-radius:4px"></div>
                                        <div class="pp-sk pp-sk-h10" style="width:70px;border-radius:4px"></div>
                                        <div class="pp-sk pp-sk-h10" style="width:55px;border-radius:4px"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end" style="min-width:80px">
                                <div class="pp-sk pp-sk-h16" style="width:70px;margin-left:auto;margin-bottom:8px;border-radius:var(--pp-r-xl)"></div>
                                <div class="pp-sk pp-sk-h16" style="width:55px;margin-left:auto;border-radius:4px"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- ── Requests list ── --}}
            <div id="requests-list" class="d-none"></div>

            {{-- ── Empty state ── --}}
            <div id="no-results" class="d-none">
                <div class="pp-card text-center" style="padding:3rem 1.5rem">
                    <div class="pp-empty">
                        <i class="ph ph-first-aid-kit" aria-hidden="true"></i>
                        <p>{{ __('messages.no_data_found') }}</p>
                        <a href="{{ route('request-nurse') }}" class="pp-btn pp-btn-teal" style="margin-top:0.75rem">
                            <i class="ph ph-plus-circle"></i>{{ __('request_nurse.request_a_nurse') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── Load more ── --}}
            <div class="text-center mt-3 d-none" id="load-more-container">
                <button class="pp-btn pp-btn-outline" id="load-more-btn" style="padding-left:2rem;padding-right:2rem">
                    <i class="ph ph-arrow-down" aria-hidden="true"></i>
                    {{ __('messages.load_more') }}
                </button>
            </div>

        </div>
    </div>
@endsection

@push('after-scripts')
<script type="text/javascript">
(function () {
    'use strict';

    function esc(text) {
        if (!text && text !== 0) return '';
        var d = document.createElement('div');
        d.textContent = String(text);
        return d.innerHTML;
    }

    var requestsList    = document.getElementById('requests-list');
    var shimmerLoader   = document.getElementById('shimmer-loader');
    var noResults       = document.getElementById('no-results');
    var loadMoreCont    = document.getElementById('load-more-container');
    var loadMoreBtn     = document.getElementById('load-more-btn');

    var currentPage  = 1;
    var totalPages   = 1;
    var activeStatus = '';

    function buildCard(req) {
        var nurseName = req.nurse_name
            ? esc(req.nurse_name)
            : '<em style="color:var(--pp-xmuted)">{{ __('request_nurse.lbl_unassigned') }}</em>';

        var desc = req.service_description || '';
        var shortDesc = desc.length > 80 ? desc.substring(0, 80) + '…' : desc;

        /* Server already sends a status_badge HTML — wrap it in pp-* container */
        var statusHtml = req.status_badge || '';

        return '<a href="/my-nurse-requests/' + esc(String(req.id)) + '" class="pp-order-card">\
<div class="d-flex justify-content-between align-items-start gap-3">\
  <div class="d-flex gap-3 flex-grow-1" style="min-width:0">\
    <div class="pp-order-card-icon" aria-hidden="true"><i class="ph ph-first-aid"></i></div>\
    <div style="min-width:0;flex:1">\
      <p class="pp-order-id mb-1">' + esc(shortDesc) + '</p>\
      <div class="pp-order-meta">\
        <span class="pp-order-meta-item"><i class="ph ph-calendar"></i>' + esc(req.preferred_date) + '</span>\
        <span class="pp-order-meta-item"><i class="ph ph-map-pin"></i>' + esc(req.city || '—') + '</span>\
        <span class="pp-order-meta-item"><i class="ph ph-clock"></i>' + esc(String(req.duration_hours)) + ' {{ __('request_nurse.lbl_hours_short') }}</span>\
        <span class="pp-order-meta-item"><i class="ph ph-user-nurse"></i>' + nurseName + '</span>\
      </div>\
    </div>\
  </div>\
  <div class="text-end d-flex flex-column align-items-end gap-1" style="flex-shrink:0">\
    ' + statusHtml + '\
    <span class="pp-order-amount">' + esc(req.total_amount) + '</span>\
    <span class="pp-order-date">' + esc(req.created_at) + '</span>\
  </div>\
</div>\
</a>';
    }

    function loadRequests(page, append) {
        if (!append) {
            shimmerLoader.classList.remove('d-none');
            requestsList.classList.add('d-none');
            noResults.classList.add('d-none');
            loadMoreCont.classList.add('d-none');
        }

        $.ajax({
            url:    '{{ route('my-nurse-requests.index_data') }}',
            method: 'GET',
            data:   { page: page, status: activeStatus, per_page: 10 },
            success: function (res) {
                shimmerLoader.classList.add('d-none');
                totalPages = res.last_page || 1;

                /* Update count badges from first page response */
                if (page === 1 && res.counts) {
                    Object.keys(res.counts).forEach(function (key) {
                        var el = document.getElementById('badge-' + key);
                        if (el) el.textContent = res.counts[key];
                    });
                }

                var cards = (res.data || []).map(buildCard).join('');

                if (!append) {
                    requestsList.innerHTML = cards;
                } else {
                    requestsList.insertAdjacentHTML('beforeend', cards);
                }

                if ((res.data || []).length === 0 && !append) {
                    noResults.classList.remove('d-none');
                } else {
                    requestsList.classList.remove('d-none');
                }

                if (page < totalPages) {
                    loadMoreCont.classList.remove('d-none');
                } else {
                    loadMoreCont.classList.add('d-none');
                }
            },
            error: function () {
                shimmerLoader.classList.add('d-none');
                requestsList.classList.remove('d-none');
            }
        });
    }

    /* ── Tab switching ── */
    document.querySelectorAll('#status-tabs .pp-nav-link').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('#status-tabs .pp-nav-link').forEach(function (t) {
                t.classList.remove('active');
            });
            tab.classList.add('active');
            activeStatus = tab.getAttribute('data-status');
            currentPage  = 1;
            loadRequests(1, false);
        });
    });

    loadMoreBtn.addEventListener('click', function () {
        currentPage++;
        loadRequests(currentPage, true);
    });

    loadRequests(1, false);
}());
</script>
@endpush
