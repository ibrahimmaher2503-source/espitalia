@extends('backend.layouts.app')
@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title">{{ $pharmacy->name }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>{{ __('pharmacy.lbl_total_earned') }}:</strong><br>
                <h5>{{ setting('currency_symbol', '$') }}{{ number_format($totalCommission, 2) }}</h5>
            </div>
            <div class="col-md-3">
                <strong>{{ __('pharmacy.lbl_total_unpaid') }}:</strong><br>
                <h5 class="text-warning">{{ setting('currency_symbol', '$') }}{{ number_format($totalUnpaid, 2) }}</h5>
            </div>
            <div class="col-md-3">
                <strong>{{ __('pharmacy.lbl_total_paid') }}:</strong><br>
                <h5 class="text-success">{{ setting('currency_symbol', '$') }}{{ number_format($totalPaid, 2) }}</h5>
            </div>
            <div class="col-md-3">
                @if($totalUnpaid > 0)
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payoutModal">{{ __('pharmacy.lbl_process_payout') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Earnings List -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title">{{ __('messages.order') }} {{ __('pharmacy.lbl_pharmacy_earning') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="statusFilter" class="form-control">
                    <option value="">-- {{ __('messages.all') }} --</option>
                    <option value="unpaid">{{ __('pharmacy.lbl_unpaid') }}</option>
                    <option value="paid">{{ __('pharmacy.lbl_paid') }}</option>
                    <option value="cancelled">{{ __('pharmacy.lbl_cancelled') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" id="startDate" class="form-control" placeholder="Start Date">
            </div>
            <div class="col-md-3">
                <input type="date" id="endDate" class="form-control" placeholder="End Date">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>{{ __('messages.order') }} #</th><th>{{ __('messages.date') }}</th><th>{{ __('pharmacy.lbl_subtotal') }}</th><th>{{ __('pharmacy.lbl_platform_commission') }}</th><th>{{ __('pharmacy.lbl_pharmacy_receivable') }}</th><th>{{ __('messages.status') }}</th><th>{{ __('pharmacy.lbl_payout') }}</th></tr></thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payout History -->
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('pharmacy.lbl_payout') }} {{ __('messages.history') }}</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>{{ __('pharmacy.lbl_payout') }} #</th><th>{{ __('pharmacy.lbl_payment_date') }}</th><th>{{ __('messages.total') }} {{ __('pharmacy.lbl_pharmacy_receivable') }}</th><th>{{ __('messages.total') }} {{ __('pharmacy.lbl_platform_commission') }}</th><th>{{ __('pharmacy.lbl_payment_method') }}</th><th>{{ __('messages.notes') }}</th></tr></thead>
                <tbody id="payoutTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payout Modal -->
<div class="modal fade" id="payoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('pharmacy.lbl_process_payout') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payoutForm">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="payment_method" class="form-label">{{ __('pharmacy.lbl_payment_method') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">-- {{ __('messages.select') }} --</option>
                            <option value="cash">{{ __('pharmacy.lbl_cash') }}</option>
                            <option value="bank_transfer">{{ __('pharmacy.lbl_bank_transfer') }}</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const pharmacyId = {{ $pharmacy->id }};

    function loadEarnings() {
        const params = new URLSearchParams({
            status: document.getElementById('statusFilter').value,
            start_date: document.getElementById('startDate').value,
            end_date: document.getElementById('endDate').value
        });
        fetch(`{{ route('backend.pharmacy-earnings.show_data', $pharmacy->id) }}?${params}`).then(r => r.json()).then(d => {
            document.getElementById('tableBody').innerHTML = d.data.map(i => `<tr><td>#${i.order_id}</td><td>${new Date(i.created_at).toLocaleDateString()}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(i.order_subtotal).toFixed(2)}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(i.commission_amount).toFixed(2)}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(i.pharmacy_receivable).toFixed(2)}</td><td><span class="badge ${i.status === 'paid' ? 'bg-success' : i.status === 'unpaid' ? 'bg-warning' : 'bg-secondary'}">${i.status}</span></td><td>${i.payout_id || '-'}</td></tr>`).join('');
        });
    }

    function loadPayouts() {
        fetch(`{{ route('backend.pharmacy-earnings.payouts_data', $pharmacy->id) }}`).then(r => r.json()).then(d => {
            document.getElementById('payoutTableBody').innerHTML = d.map(p => `<tr><td>#${p.id}</td><td>${p.payment_date}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(p.total_amount).toFixed(2)}</td><td>{{ setting('currency_symbol', '$') }}${parseFloat(p.total_commission).toFixed(2)}</td><td>${p.payment_method}</td><td>${p.notes || '-'}</td></tr>`).join('');
        }).catch(() => {
            document.getElementById('payoutTableBody').innerHTML = '<tr><td colspan="6">{{ __("messages.no_data") }}</td></tr>';
        });
    }

    document.getElementById('statusFilter').addEventListener('change', loadEarnings);
    document.getElementById('startDate').addEventListener('change', loadEarnings);
    document.getElementById('endDate').addEventListener('change', loadEarnings);

    document.getElementById('payoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`{{ route('backend.pharmacy-earnings.payout', $pharmacy->id) }}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({
                payment_method: document.getElementById('payment_method').value,
                notes: document.getElementById('notes').value
            })
        }).then(r => r.json()).then(d => {
            if (d.status) {
                location.reload();
            } else {
                alert(d.message);
            }
        });
    });

    // Load data on page load
    loadEarnings();
    loadPayouts();
</script>
@endsection
