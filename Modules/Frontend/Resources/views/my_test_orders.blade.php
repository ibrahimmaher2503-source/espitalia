@extends('frontend::layouts.master')
@section('title', __('radiology_lab.my_test_orders'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">

            {{-- ── Page header ── --}}
            <div class="pp-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="pp-page-title">{{ __('radiology_lab.my_test_orders') }}</h1>
                </div>
                <a href="{{ route('order-tests') }}" class="pp-btn pp-btn-teal">
                    <i class="ph ph-plus-circle" aria-hidden="true"></i>
                    {{ __('radiology_lab.order_a_test') }}
                </a>
            </div>

            {{-- ── Status filter tabs ── --}}
            <ul class="pp-nav-pills mb-4" id="status-tabs" role="tablist">
                @php
                    $tabItems = [
                        ''                 => __('radiology_lab.lbl_all'),
                        'pending'          => __('radiology_lab.status_pending'),
                        'confirmed'        => __('radiology_lab.status_confirmed'),
                        'sample_collected' => __('radiology_lab.status_sample_collected'),
                        'processing'       => __('radiology_lab.status_processing'),
                        'completed'        => __('radiology_lab.status_completed'),
                        'delivered'        => __('radiology_lab.status_delivered'),
                        'cancelled'        => __('radiology_lab.status_cancelled'),
                    ];
                @endphp
                @foreach($tabItems as $status => $label)
                    <li role="presentation">
                        <button class="pp-nav-link {{ $status === '' ? 'active' : '' }}"
                                data-status="{{ $status }}"
                                type="button"
                                role="tab">
                            {{ $label }}
                        </button>
                    </li>
                @endforeach
            </ul>

            {{-- ── Orders list ── --}}
            <div id="orders-list">

                {{-- Shimmer --}}
                <div id="shimmer-loader">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="pp-order-shimmer">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex gap-3 flex-grow-1">
                                    <div class="pp-sk" style="width:36px;height:36px;border-radius:var(--pp-r-sm);flex-shrink:0"></div>
                                    <div style="flex:1">
                                        <div class="pp-sk pp-sk-h16 pp-sk-w40 mb-2"></div>
                                        <div class="pp-sk pp-sk-h10 pp-sk-w80 mb-2"></div>
                                        <div class="pp-sk pp-sk-h10 pp-sk-w60"></div>
                                    </div>
                                </div>
                                <div class="text-end" style="min-width:100px">
                                    <div class="pp-sk pp-sk-h16" style="width:70px;margin-left:auto;margin-bottom:8px;border-radius:var(--pp-r-xl)"></div>
                                    <div class="pp-sk pp-sk-h16" style="width:55px;margin-left:auto;border-radius:4px"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <div id="orders-content"></div>
            </div>

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

    /* ── XSS-safe text escaping ── */
    function esc(text) {
        if (!text) return '';
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    var orderDetailUrl = '{{ url('my-test-orders') }}';

    /* ── Status → pp-status-* class mapping ── */
    var statusClass = {
        pending:          'pp-status pp-status-pending',
        confirmed:        'pp-status pp-status-confirmed',
        sample_collected: 'pp-status pp-status-checkin',
        processing:       'pp-status pp-status-checkin',
        completed:        'pp-status pp-status-completed',
        delivered:        'pp-status pp-status-completed',
        cancelled:        'pp-status pp-status-cancelled'
    };

    /* ── Priority → pp-badge-* class mapping ── */
    var priorityClass = {
        routine: 'pp-badge pp-badge-navy',
        urgent:  'pp-badge pp-badge-teal',
        stat:    'pp-badge' // inline danger color
    };
    var priorityStyle = {
        stat: 'background:var(--pp-cancelled-bg);color:var(--pp-cancelled)'
    };

    /* ── Payment → pp-pay-* class mapping ── */
    var payClass = {
        unpaid:   'pp-pay-status pp-pay-pending',
        paid:     'pp-pay-status pp-pay-paid',
        refunded: 'pp-pay-status pp-pay-refunded'
    };

    function makeBadge(text, cls, style) {
        return '<span class="' + cls + '"' + (style ? ' style="' + style + '"' : '') + '>'
             + esc(text) + '</span>';
    }

    var currentPage  = 1;
    var totalPages   = 1;
    var activeStatus = '';

    var shimmer         = document.getElementById('shimmer-loader');
    var ordersContent   = document.getElementById('orders-content');
    var loadMoreCont    = document.getElementById('load-more-container');
    var loadMoreBtn     = document.getElementById('load-more-btn');
    var statusTabs      = document.querySelectorAll('#status-tabs .pp-nav-link');

    function loadOrders(page, append) {
        if (!append) {
            shimmer.style.display = '';
            ordersContent.innerHTML = '';
        }

        $.ajax({
            url:  '{{ route('my-test-orders.index_data') }}',
            data: { page: page, status: activeStatus, per_page: 10 },
            success: function (res) {
                shimmer.style.display = 'none';
                totalPages = res.last_page || 1;

                if (res.data && res.data.length > 0) {
                    res.data.forEach(function (order) {

                        var pCls   = priorityClass[order.priority]  || 'pp-badge pp-badge-navy';
                        var pStyle = priorityStyle[order.priority]  || '';
                        var sCls   = statusClass[order.status]      || 'pp-status pp-status-pending';
                        var pymtCls = payClass[order.payment_status] || 'pp-pay-status pp-pay-pending';

                        var card = document.createElement('a');
                        card.className = 'pp-order-card';
                        card.href      = orderDetailUrl + '/' + order.id;
                        card.setAttribute('aria-label', order.order_number);
                        card.innerHTML = '\
<div class="d-flex justify-content-between align-items-start gap-3">\
  <div class="d-flex gap-3 flex-grow-1 min-width-0">\
    <div class="pp-order-card-icon" aria-hidden="true"><i class="ph ph-test-tube"></i></div>\
    <div style="min-width:0;flex:1">\
      <div class="d-flex align-items-center flex-wrap gap-2 mb-1">\
        <span class="pp-order-id">' + esc(order.order_number) + '</span>\
        ' + makeBadge(order.priority_label, pCls, pStyle) + '\
      </div>\
      <div class="pp-order-meta">\
        <span class="pp-order-meta-item"><i class="ph ph-calendar" aria-hidden="true"></i>' + esc(order.order_date) + '</span>\
        <span class="pp-order-meta-item"><i class="ph ph-flask" aria-hidden="true"></i>' + esc(String(order.items_count)) + ' {{ __('radiology_lab.lbl_items_count') }}</span>\
      </div>\
      <p class="pp-order-desc">' + esc(order.test_names) + '</p>\
    </div>\
  </div>\
  <div class="text-end d-flex flex-column align-items-end gap-1" style="flex-shrink:0">\
    ' + makeBadge(order.status_label, sCls) + '\
    <span class="pp-order-amount">' + esc(order.total_amount) + '</span>\
    <span class="' + pymtCls + '">' + esc(order.payment_label) + '</span>\
    <span class="pp-order-date">' + esc(order.created_at) + '</span>\
  </div>\
</div>';
                        ordersContent.appendChild(card);
                    });

                } else if (!append) {
                    ordersContent.innerHTML = '\
<div class="pp-card text-center" style="padding:3rem 1.5rem">\
  <div class="pp-empty">\
    <i class="ph ph-test-tube" aria-hidden="true"></i>\
    <p>{{ __('radiology_lab.lbl_no_orders_found') }}</p>\
    <a href="{{ route('order-tests') }}" class="pp-btn pp-btn-teal" style="margin-top:0.75rem">\
      <i class="ph ph-plus-circle"></i>{{ __('radiology_lab.order_a_test') }}\
    </a>\
  </div>\
</div>';
                }

                loadMoreCont.style.display = (currentPage < totalPages) ? '' : 'none';
            },
            error: function () {
                shimmer.style.display = 'none';
                ordersContent.innerHTML = '\
<div class="pp-card pp-card-body">\
  <p style="color:var(--pp-cancelled);font-size:0.82rem;margin:0;font-family:var(--pp-font)">\
    <i class="ph ph-warning-circle me-2"></i>{{ __('radiology_lab.lbl_error_loading') }}\
  </p>\
</div>';
            }
        });
    }

    /* ── Tab switch ── */
    statusTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            statusTabs.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            activeStatus = tab.dataset.status;
            currentPage  = 1;
            loadOrders(1, false);
        });
    });

    /* ── Load more ── */
    loadMoreBtn.addEventListener('click', function () {
        currentPage++;
        loadOrders(currentPage, true);
    });

    /* ── Initial load ── */
    loadOrders(1, false);
}());
</script>
@endpush
