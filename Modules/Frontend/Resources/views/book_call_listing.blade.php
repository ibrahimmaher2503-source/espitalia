@extends('frontend::layouts.master')
@section('title', __('call_booking.book_a_call'))

@section('content')
    @include('frontend::components.section.breadcrumb')
    <div class="list-page section-spacing px-0">
        <div class="movie-lists">
            <div class="container-fluid">
                <div class="row gy-2">
                    <div class="col-xl-9 col-lg-9"></div>
                    <div class="col-lg-3">
                        <div class="d-flex align-items-center p-2 rounded section-bg">
                            <div class="icon ps-2">
                                <i class="ph ph-magnifying-glass align-middle"></i>
                            </div>
                            <input type="text" id="datatable-search"
                                   style="background:none;border:0;outline:0;flex:1;padding:0.4rem 0.5rem;font-family:var(--pp-font);font-size:0.875rem;color:var(--pp-text)"
                                   placeholder="{{ __('call_booking.search_doctors') }}...">
                        </div>
                    </div>
                </div>

                <div class="card-style-slider">
                    <div id="shimmer-loader" class="d-flex gap-3 flex-wrap p-4">
                        @for ($i = 0; $i < 8; $i++)
                            @include('frontend::components.card.shimmer_doctor_card')
                        @endfor
                    </div>
                    <table id="datatable" class="table table-responsive custom-card-table">
                    </table>
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

    <script type="text/javascript" defer>
        let finalColumns = [{
            data: 'card',
            name: 'card',
            orderable: false,
            searchable: true
        }];

        document.addEventListener('DOMContentLoaded', () => {
            const shimmerLoader = document.getElementById('shimmer-loader');
            const dataTableElement = document.getElementById('datatable');

            frontInitDatatable({
                url: '{{ route("book-call.index-data") }}',
                finalColumns,
                onLoadStart: () => {
                    shimmerLoader.classList.remove('d-none');
                    dataTableElement.classList.add('d-none');
                },
                onLoadComplete: () => {
                    shimmerLoader.classList.add('d-none');
                    dataTableElement.classList.remove('d-none');
                },
                language: {
                    processing: '',
                }
            });
        });
    </script>
@endpush
