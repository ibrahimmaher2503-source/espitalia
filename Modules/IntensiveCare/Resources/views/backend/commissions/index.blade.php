@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4 class="card-title">{{ __('intensive_care.lbl_commission') }}</h4></div>
            <div class="col-md-6 text-end">
                <a href="{{ route('backend.icu-commissions.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> {{ __('messages.add_new') }}</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6"><input type="text" id="search" class="form-control" placeholder="{{ __('messages.search') }}..."></div>
            <div class="col-md-6 text-end"><button id="bulk-delete" class="btn btn-danger btn-sm" style="display:none;"><i class="fa fa-trash"></i></button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th><input type="checkbox" id="selectAll"></th><th>{{ __('intensive_care.lbl_commission_template') }}</th><th>{{ __('intensive_care.lbl_commission_type') }}</th><th>{{ __('intensive_care.lbl_commission_value') }}</th><th>{{ __('messages.assigned_to') }}</th><th>{{ __('messages.status') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function load() {
            fetch(`{{ route('backend.icu-commissions.index_data') }}?search=${document.getElementById('search').value}`).then(r => r.json()).then(d => {
                document.getElementById('tableBody').innerHTML = d.data.map(i => `<tr><td><input type="checkbox" name="ids" value="${i.id}"></td><td>${i.title}</td><td>${i.commission_type === 'percentage' ? '{{ __("intensive_care.lbl_percentage") }}' : '{{ __("intensive_care.lbl_fixed") }}'}</td><td>${i.commission_type === 'percentage' ? i.commission_value + '%' : '{{ setting('currency_symbol', '$') }}' + i.commission_value}</td><td>${i.hospitals_count || 0}</td><td><button class="btn btn-xs ${i.status ? 'btn-success' : 'btn-secondary'}" onclick="status(${i.id})">${i.status ? '{{ __("messages.active") }}' : '{{ __("messages.inactive") }}'}</button></td><td><a href="{{ route('backend.icu-commissions.edit', '') }}/${i.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a><button onclick="del(${i.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button></td></tr>`).join('');
                document.querySelectorAll('input[name="ids"]').forEach(cb => cb.addEventListener('change', () => {
                    document.getElementById('bulk-delete').style.display = document.querySelectorAll('input[name="ids"]:checked').length > 0 ? 'inline-block' : 'none';
                }));
            });
        }
        load();
        document.getElementById('search').addEventListener('keyup', load);
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('input[name="ids"]').forEach(cb => cb.checked = this.checked);
        });
        document.getElementById('bulk-delete').addEventListener('click', function() {
            if (confirm('{{ __("messages.are_you_sure") }}')) {
                const ids = Array.from(document.querySelectorAll('input[name="ids"]:checked')).map(cb => cb.value);
                fetch('{{ route('backend.icu-commissions.bulk_action') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify({ids: ids, action_type: 'delete'})}).then(() => load());
            }
        });
        window.status = function(id) {
            fetch(`{{ route('backend.icu-commissions.update_status', '') }}/${id}`, {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => load());
        };
        window.del = function(id) {
            if (confirm('{{ __("messages.are_you_sure") }}')) {
                fetch(`{{ route('backend.icu-commissions.destroy', '') }}/${id}`, {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => load());
            }
        };
    });
</script>
@endsection
