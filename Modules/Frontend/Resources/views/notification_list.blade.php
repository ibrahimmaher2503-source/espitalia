@extends('frontend::layouts.master')

@section('title', __('frontend.notifications'))

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@section('content')
@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">

        {{-- Page header --}}
        <div class="pp-page-header">
            <h1 class="pp-page-title">{{ __('frontend.notifications') }}</h1>
        </div>

        {{-- Notifications list --}}
        <div class="pp-card" style="overflow:hidden">
            <div id="shimmer-loader" class="shimmer-loader" style="padding:1rem">
                @for ($i = 0; $i < 6; $i++)
                    <div style="display:flex;gap:0.75rem;padding:0.875rem 0;border-bottom:1px solid var(--pp-border)">
                        <div class="pp-sk" style="width:38px;height:38px;border-radius:var(--pp-r-sm);flex-shrink:0"></div>
                        <div style="flex:1">
                            <div class="pp-sk pp-sk-h16 pp-sk-w60 mb-2"></div>
                            <div class="pp-sk pp-sk-h10 pp-sk-w80"></div>
                        </div>
                        <div class="pp-sk" style="width:60px;height:12px;border-radius:4px;flex-shrink:0"></div>
                    </div>
                @endfor
            </div>
            <ul class="list-unstyled m-0">
                <table id="datatable" class="table table-responsive custom-card-table d-none"></table>
            </ul>
        </div>

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
        var shimmer  = document.getElementById('shimmer-loader');
        var tableEl  = document.getElementById('datatable');

        frontInitDatatable({
            url:           '{{ route('user-notifications.index_data') }}',
            finalColumns:  finalColumns,
            cardColumnClass: 'row-cols-1',
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
}());
</script>
@endpush
