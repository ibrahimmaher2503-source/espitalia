@extends('frontend::layouts.master')
@section('title', __('intensive_care.my_icu_requests'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">

            {{-- ── Page header ── --}}
            <div class="pp-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="pp-page-title">{{ __('intensive_care.my_icu_requests') }}</h1>
                </div>
                <a href="{{ route('request-icu-admission') }}" class="pp-btn pp-btn-teal">
                    <i class="ph ph-plus-circle" aria-hidden="true"></i>
                    {{ __('intensive_care.lbl_request_admission') }}
                </a>
            </div>

            {{-- ── Status filter tabs ── --}}
            <ul class="pp-nav-pills mb-4" id="status-tabs" role="tablist">
                @php
                    $tabs = [
                        ''               => ['label' => __('intensive_care.lbl_all'),               'icon' => 'ph-squares-four'],
                        'pending'        => ['label' => __('intensive_care.status_pending'),         'icon' => 'ph-clock'],
                        'accepted'       => ['label' => __('intensive_care.status_accepted'),        'icon' => 'ph-check-circle'],
                        'rejected'       => ['label' => __('intensive_care.status_rejected'),        'icon' => 'ph-x-circle'],
                        'info_requested' => ['label' => __('intensive_care.status_info_requested'),  'icon' => 'ph-info'],
                        'cancelled'      => ['label' => __('intensive_care.status_cancelled'),       'icon' => 'ph-prohibit'],
                    ];
                @endphp
                @foreach($tabs as $status => $tab)
                    <li role="presentation">
                        <button class="pp-nav-link {{ $status === '' ? 'active' : '' }}"
                                data-status="{{ $status }}"
                                type="button"
                                role="tab">
                            <i class="ph {{ $tab['icon'] }}" aria-hidden="true"></i>
                            {{ $tab['label'] }}
                        </button>
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
                                        <div class="pp-sk pp-sk-h10" style="width:100px;border-radius:4px"></div>
                                        <div class="pp-sk pp-sk-h10" style="width:75px;border-radius:4px"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end" style="min-width:80px">
                                <div class="pp-sk pp-sk-h16" style="width:70px;margin-left:auto;margin-bottom:8px;border-radius:var(--pp-r-xl)"></div>
                                <div class="pp-sk pp-sk-h10" style="width:55px;margin-left:auto;border-radius:4px"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- ── Requests list ── --}}
            <div id="requests-content"></div>

            {{-- ── Load more ── --}}
            <div class="text-center py-4 d-none" id="load-more-container">
                <button class="pp-btn pp-btn-outline" id="load-more-btn" style="padding-left:2rem;padding-right:2rem">
                    <i class="ph ph-arrow-down" aria-hidden="true"></i>
                    {{ __('intensive_care.lbl_load_more') }}
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
        if (!text) return '';
        var d = document.createElement('div');
        d.textContent = String(text);
        return d.innerHTML;
    }

    var requestDetailUrl = '{{ url('my-icu-requests') }}';

    /* ── Status → pp-status-* class mapping ── */
    var statusClass = {
        pending:        'pp-status pp-status-pending',
        accepted:       'pp-status pp-status-confirmed',
        rejected:       'pp-status pp-status-cancelled',
        info_requested: 'pp-status pp-status-checkin',
        cancelled:      'pp-status pp-status-cancelled'
    };

    /* ── Urgency → pp-badge-* class mapping ── */
    var urgencyClass = {
        critical: 'pp-badge',
        urgent:   'pp-badge pp-badge-teal',
        standard: 'pp-badge pp-badge-navy'
    };
    var urgencyStyle = {
        critical: 'background:var(--pp-cancelled-bg);color:var(--pp-cancelled)'
    };

    var currentPage     = 1;
    var totalPages      = 1;
    var activeStatus    = '';

    var shimmer         = document.getElementById('shimmer-loader');
    var requestsContent = document.getElementById('requests-content');
    var loadMoreCont    = document.getElementById('load-more-container');
    var loadMoreBtn     = document.getElementById('load-more-btn');
    var statusTabs      = document.querySelectorAll('#status-tabs .pp-nav-link');

    function loadRequests(page, append) {
        if (!append) {
            shimmer.style.display  = '';
            requestsContent.innerHTML = '';
        }

        $.ajax({
            url:  '{{ route('my-icu-requests.index_data') }}',
            data: { page: page, status: activeStatus, per_page: 10 },
            success: function (res) {
                shimmer.style.display = 'none';
                totalPages = res.last_page || 1;

                if (res.data && res.data.length > 0) {
                    res.data.forEach(function (req) {
                        var uCls   = urgencyClass[req.urgency]  || 'pp-badge pp-badge-navy';
                        var uStyle = urgencyStyle[req.urgency]  || '';
                        var sCls   = statusClass[req.status]    || 'pp-status pp-status-pending';

                        var card = document.createElement('a');
                        card.className = 'pp-order-card';
                        card.href      = requestDetailUrl + '/' + req.id;
                        card.setAttribute('aria-label', req.request_number);
                        card.innerHTML = '\
<div class="d-flex justify-content-between align-items-start gap-3">\
  <div class="d-flex gap-3 flex-grow-1 min-width-0">\
    <div class="pp-order-card-icon" aria-hidden="true"><i class="ph ph-bed"></i></div>\
    <div style="min-width:0;flex:1">\
      <div class="d-flex align-items-center flex-wrap gap-2 mb-1">\
        <span class="pp-order-id">' + esc(req.request_number) + '</span>\
        <span class="' + uCls + '"' + (uStyle ? ' style="' + uStyle + '"' : '') + '>' + esc(req.urgency_label) + '</span>\
      </div>\
      <div class="pp-order-meta">\
        <span class="pp-order-meta-item"><i class="ph ph-hospital" aria-hidden="true"></i>' + esc(req.hospital_name) + '</span>\
        <span class="pp-order-meta-item"><i class="ph ph-first-aid-kit" aria-hidden="true"></i>' + esc(req.case_type_label) + '</span>\
      </div>\
    </div>\
  </div>\
  <div class="text-end d-flex flex-column align-items-end gap-1" style="flex-shrink:0">\
    <span class="' + sCls + '">' + esc(req.status_label) + '</span>\
    <span class="pp-order-date">' + esc(req.created_at) + '</span>\
  </div>\
</div>';
                        requestsContent.appendChild(card);
                    });
                } else if (!append) {
                    requestsContent.innerHTML = '\
<div class="pp-card text-center" style="padding:3rem 1.5rem">\
  <div class="pp-empty">\
    <i class="ph ph-bed" aria-hidden="true"></i>\
    <p>{{ __('intensive_care.lbl_no_results') }}</p>\
    <a href="{{ route('request-icu-admission') }}" class="pp-btn pp-btn-teal" style="margin-top:0.75rem">\
      <i class="ph ph-plus-circle"></i>{{ __('intensive_care.lbl_request_admission') }}\
    </a>\
  </div>\
</div>';
                }

                loadMoreCont.classList.toggle('d-none', currentPage >= totalPages);
            },
            error: function () {
                shimmer.style.display = 'none';
                requestsContent.innerHTML = '\
<div class="pp-card pp-card-body">\
  <p style="color:var(--pp-cancelled);font-size:0.82rem;margin:0;font-family:var(--pp-font)">\
    <i class="ph ph-warning-circle me-2"></i>{{ __('intensive_care.lbl_error_loading') }}\
  </p>\
</div>';
            }
        });
    }

    /* ── Tab switching ── */
    statusTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            statusTabs.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            activeStatus = tab.dataset.status;
            currentPage  = 1;
            loadRequests(1, false);
        });
    });

    /* ── Load more ── */
    loadMoreBtn.addEventListener('click', function () {
        currentPage++;
        loadRequests(currentPage, true);
    });

    /* ── Initial load ── */
    loadRequests(1, false);
}());
</script>
@endpush
