@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('pharmacy.lbl_reports') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.from') }}</label>
                <input type="date" id="from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.to') }}</label>
                <input type="date" id="to" class="form-control" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.group_by') }}</label>
                <select id="groupBy" class="form-control">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary" onclick="loadReport()">{{ __('messages.filter') }}</button>
            </div>
        </div>

        <div id="reportContent">
            <div class="table-responsive">
                <table class="table table-striped" id="reportTable">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Total Orders</th>
                            <th>Revenue</th>
                            <th>Commission</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadReport();
});

function loadReport() {
    const from = document.getElementById('from').value;
    const to = document.getElementById('to').value;
    const groupBy = document.getElementById('groupBy').value;
    fetch(`{{ route('backend.pharmacy-reports.revenue') }}?from=${from}&to=${to}&group_by=${groupBy}`)
        .then(r => r.json()).then(data => {
            if (data.status && data.data) {
                document.getElementById('reportBody').innerHTML = data.data.map(row => `
                    <tr>
                        <td>${row.period}</td>
                        <td>${row.total_orders}</td>
                        <td>${row.revenue}</td>
                        <td>${row.commission || '-'}</td>
                    </tr>
                `).join('');
            }
        });
}
</script>
@endsection
