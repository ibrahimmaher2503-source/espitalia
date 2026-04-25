@extends('frontend::layouts.master')

@section('title', __('frontend.encounter'))

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@section('content')
@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">

        {{-- Page header --}}
        <div class="pp-page-header">
            <h1 class="pp-page-title">{{ __('frontend.encounter') }}</h1>
        </div>

        {{-- Encounter grid --}}
        <div id="shimmer-loader" class="row g-3 mb-3">
            @for ($i = 0; $i < 6; $i++)
                <div class="col-md-6 col-lg-4">
                    <div class="pp-enc-card">
                        <div class="pp-enc-header">
                            <div class="pp-sk" style="width:80px;height:12px;border-radius:4px"></div>
                            <div class="pp-sk" style="width:44px;height:12px;border-radius:4px"></div>
                        </div>
                        <div class="pp-enc-body">
                            <div class="pp-sk pp-sk-h16 pp-sk-w40 mb-3"></div>
                            <div class="pp-sk pp-sk-h10 pp-sk-w80 mb-2"></div>
                            <div class="pp-sk pp-sk-h10 pp-sk-w60"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <table id="datatable" class="table table-responsive custom-card-table d-none"></table>

    </div>
</div>

@endsection

@push('after-scripts')
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script type="text/javascript">
(function () {
    'use strict';

    var finalColumns = [{
        data:       'card',
        name:       'card',
        orderable:  false,
        searchable: true
    }];

    document.addEventListener('DOMContentLoaded', function () {
        var shimmer = document.getElementById('shimmer-loader');
        var tableEl = document.getElementById('datatable');

        frontInitDatatable({
            url:            '{{ route('encounter.index_data') }}',
            finalColumns:   finalColumns,
            cardColumnClass: 'row-cols-1 row-cols-sm-2 row-cols-lg-3',
            onLoadStart: function () {
                if (shimmer)  shimmer.classList.remove('d-none');
                if (tableEl)  tableEl.classList.add('d-none');
            },
            onLoadComplete: function () {
                if (shimmer)  shimmer.classList.add('d-none');
                if (tableEl)  tableEl.classList.remove('d-none');
            }
        });
    });

    /* ── Encounter accordion ── */
    window.toggleEncSection = window.toggleEncSection || function (bodyId, wrapId) {
        var body   = document.getElementById(bodyId);
        var wrap   = document.getElementById(wrapId);
        if (!body) return;
        var isOpen = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : 'block';
        if (wrap) wrap.classList.toggle('open', !isOpen);
        var toggle = wrap ? wrap.querySelector('[aria-expanded]') : null;
        if (toggle) toggle.setAttribute('aria-expanded', String(!isOpen));
    };
}());
</script>
@endpush
