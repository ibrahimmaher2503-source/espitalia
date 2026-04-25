@extends('backend.layouts.app')
@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('pharmacy.lbl_platform_commission') }}</h5>
                <h3 class="text-primary">{{ setting('currency_symbol', '$') }}{{ number_format($totalPlatformCommission, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('pharmacy.lbl_total_unpaid') }}</h5>
                <h3 class="text-warning">{{ setting('currency_symbol', '$') }}{{ number_format($totalUnpaidReceivable, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('pharmacy.lbl_total_paid') }}</h5>
                <h3 class="text-success">{{ setting('currency_symbol', '$') }}{{ number_format($totalPaidReceivable, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('pharmacy.lbl_pharmacy_earning') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6"><input type="text" id="search" class="form-control" placeholder="{{ __('messages.search') }}..."></div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>{{ __('pharmacy.lbl_pharmacy') }}</th><th>{{ __('messages.orders') }}</th><th>{{ __('pharmacy.lbl_platform_commission') }}</th><th>{{ __('pharmacy.lbl_total_unpaid') }}</th><th>{{ __('pharmacy.lbl_total_paid') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function load() {
            fetch(`{{ route('backend.pharmacy-earnings.index_data') }}?search=${document.getElementById('search').value}`).then(r => r.json()).then(d => {
                document.getElementById('tableBody').innerHTML = d.data.map(i => `<tr><td>${i.name}</td><td>${i.total_orders}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(i.total_platform_commission).toFixed(2)}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(i.total_unpaid_receivable).toFixed(2)}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(i.total_paid_receivable).toFixed(2)}</td><td><a href="{{ route('backend.pharmacy-earnings.show', '') }}/${i.id}" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i></a></td></tr>`).join('');
            });
        }
        load();
        document.getElementById('search').addEventListener('keyup', load);
    });
</script>
@endsection
