@extends('frontend::layouts.master')

@section('title', __('frontend.settings'))

@section('content')

@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">

        {{-- Page header --}}
        <div class="pp-page-header">
            <h1 class="pp-page-title">{{ __('frontend.settings') }}</h1>
        </div>

        <div class="row g-4">

            {{-- ── Sidebar nav ── --}}
            <div class="col-lg-3 col-md-4">
                <nav class="pp-settings-nav" id="settings-nav" aria-label="{{ __('frontend.settings') }}">
                    <button class="pp-settings-nav-item active"
                            data-bs-toggle="pill"
                            data-bs-target="#tab-password"
                            type="button"
                            role="tab"
                            aria-controls="tab-password"
                            aria-selected="true">
                        <i class="ph ph-lock-simple" aria-hidden="true"></i>
                        {{ __('frontend.change_password') }}
                    </button>
                    <button class="pp-settings-nav-item danger"
                            data-bs-toggle="pill"
                            data-bs-target="#tab-delete"
                            type="button"
                            role="tab"
                            aria-controls="tab-delete"
                            aria-selected="false">
                        <i class="ph ph-trash" aria-hidden="true"></i>
                        {{ __('frontend.delete_account') }}
                    </button>
                </nav>
            </div>

            {{-- ── Tab content ── --}}
            <div class="col-lg-9 col-md-8">
                <div class="tab-content" id="settings-content">

                    {{-- ── Change password ── --}}
                    <div class="tab-pane fade show active" id="tab-password" role="tabpanel">
                        <div class="pp-card">
                            <div class="pp-card-header">
                                <h2 class="pp-card-title">
                                    <i class="ph ph-lock-simple" aria-hidden="true"></i>
                                    {{ __('frontend.update_password') }}
                                </h2>
                            </div>
                            <div class="pp-card-body">
                                <form id="update-password-form" method="POST" novalidate>
                                    @csrf

                                    <div class="pp-form-group">
                                        <label class="pp-label" for="old_password">
                                            {{ __('frontend.old_password') }}
                                            <span class="pp-required">*</span>
                                        </label>
                                        <div style="position:relative">
                                            <input type="password"
                                                   id="old_password"
                                                   name="old_password"
                                                   class="pp-input"
                                                   placeholder="{{ __('frontend.old_password') }}"
                                                   required
                                                   autocomplete="current-password"
                                                   style="padding-right:2.5rem">
                                            <button type="button"
                                                    class="pp-pw-toggle"
                                                    data-target="old_password"
                                                    aria-label="{{ __('messages.toggle_password') ?? 'Toggle password visibility' }}"
                                                    style="position:absolute;right:0.625rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--pp-muted);cursor:pointer;display:flex;align-items:center;font-size:1rem">
                                                <i class="ph ph-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <div class="pp-field-error d-none" id="error-old-password"></div>
                                    </div>

                                    <div class="pp-form-group">
                                        <label class="pp-label" for="new_password">
                                            {{ __('frontend.new_password') }}
                                            <span class="pp-required">*</span>
                                        </label>
                                        <div style="position:relative">
                                            <input type="password"
                                                   id="new_password"
                                                   name="new_password"
                                                   class="pp-input"
                                                   placeholder="{{ __('frontend.new_password') }}"
                                                   required
                                                   autocomplete="new-password"
                                                   style="padding-right:2.5rem">
                                            <button type="button"
                                                    class="pp-pw-toggle"
                                                    data-target="new_password"
                                                    aria-label="{{ __('messages.toggle_password') ?? 'Toggle password' }}"
                                                    style="position:absolute;right:0.625rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--pp-muted);cursor:pointer;display:flex;align-items:center;font-size:1rem">
                                                <i class="ph ph-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <div class="pp-field-error d-none" id="error-new-password"></div>
                                    </div>

                                    <div class="pp-form-group">
                                        <label class="pp-label" for="new_password_confirmation">
                                            {{ __('frontend.confirm_password') }}
                                            <span class="pp-required">*</span>
                                        </label>
                                        <div style="position:relative">
                                            <input type="password"
                                                   id="new_password_confirmation"
                                                   name="new_password_confirmation"
                                                   class="pp-input"
                                                   placeholder="{{ __('frontend.confirm_password') }}"
                                                   required
                                                   autocomplete="new-password"
                                                   style="padding-right:2.5rem">
                                            <button type="button"
                                                    class="pp-pw-toggle"
                                                    data-target="new_password_confirmation"
                                                    aria-label="{{ __('messages.toggle_password') ?? 'Toggle password' }}"
                                                    style="position:absolute;right:0.625rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--pp-muted);cursor:pointer;display:flex;align-items:center;font-size:1rem">
                                                <i class="ph ph-eye-slash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="pp-btn pp-btn-teal" id="update-pw-btn">
                                            <i class="ph ph-check" aria-hidden="true"></i>
                                            {{ __('frontend.update') }}
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ── Delete account ── --}}
                    <div class="tab-pane fade" id="tab-delete" role="tabpanel">
                        <div class="pp-card text-center" style="padding:3rem 2rem">

                            {{-- Delete icon using CSS variables, not hardcoded hex --}}
                            <div style="width:72px;height:72px;background:var(--pp-cancelled-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
                                <i class="ph ph-trash" style="font-size:2rem;color:var(--pp-cancelled)" aria-hidden="true"></i>
                            </div>

                            <h3 style="font-size:1.1rem;font-weight:700;color:var(--pp-navy);margin-bottom:0.5rem;font-family:var(--pp-font)">
                                {{ __('frontend.delete_account_title') }}
                            </h3>
                            <p style="font-size:0.82rem;color:var(--pp-muted);margin-bottom:1.5rem;font-family:var(--pp-font);max-width:380px;margin-left:auto;margin-right:auto">
                                {{ __('frontend.delete_account_msg') }}
                            </p>
                            <button class="pp-btn pp-btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#delete-account-modal">
                                <i class="ph ph-trash" aria-hidden="true"></i>
                                {{ __('frontend.delete_account') }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════
     DELETE ACCOUNT MODAL
     ═══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal pp-modal-danger"
     id="delete-account-modal"
     tabindex="-1"
     aria-labelledby="deleteAccountLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountLabel">
                    <i class="ph ph-trash me-2" aria-hidden="true"></i>
                    {{ __('frontend.delete_confirm_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.close') }}"></button>
            </div>

            <div class="modal-body">
                <p style="font-size:0.82rem;color:var(--pp-muted);margin-bottom:1rem;font-family:var(--pp-font)">
                    {{ __('frontend.delete_account_msg') }}
                </p>
                <form id="deleteAccountForm" action="{{ route('account.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="pp-form-group mb-0">
                        <label class="pp-label" for="delete-password">
                            {{ __('frontend.password_lbl') }}
                            <span class="pp-required">*</span>
                        </label>
                        <input type="password"
                               id="delete-password"
                               name="password"
                               class="pp-input"
                               placeholder="{{ __('frontend.password') }}"
                               required
                               autocomplete="current-password">
                        <div class="pp-field-error d-none" id="delete-password-error"></div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">
                    {{ __('frontend.cancel') }}
                </button>
                <button type="submit" form="deleteAccountForm" class="pp-btn pp-btn-danger" id="delete-confirm-btn">
                    <i class="ph ph-trash" aria-hidden="true"></i>
                    {{ __('frontend.delete_account') }}
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function () {
    'use strict';

    /* ── Sidebar nav: sync active state with Bootstrap tab ── */
    document.querySelectorAll('.pp-settings-nav-item').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            document.querySelectorAll('.pp-settings-nav-item').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
        });
    });

    /* ── Password-toggle buttons ── */
    document.querySelectorAll('.pp-pw-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            var icon  = btn.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('ph-eye-slash', 'ph-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('ph-eye', 'ph-eye-slash');
            }
        });
    });

    /* ── Change password form ── */
    document.getElementById('update-password-form').addEventListener('submit', function (e) {
        e.preventDefault();

        var form    = this;
        var btn     = document.getElementById('update-pw-btn');
        var formData = new FormData(form);

        document.getElementById('error-old-password').classList.add('d-none');
        document.getElementById('error-new-password').classList.add('d-none');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("messages.loading") ?? "Saving…" }}';

        fetch('{{ route('account.password.update') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-check"></i>{{ __("frontend.update") }}';

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("messages.success") ?? "Done" }}',
                    text: '{{ __("frontend.password_updated") ?? "Your password has been updated." }}',
                    confirmButtonColor: 'var(--pp-teal)'
                });
                form.reset();
            } else if (data.errors) {
                if (data.errors.old_password) {
                    var el = document.getElementById('error-old-password');
                    el.textContent = Array.isArray(data.errors.old_password)
                        ? data.errors.old_password[0] : data.errors.old_password;
                    el.classList.remove('d-none');
                }
                if (data.errors.new_password) {
                    var el2 = document.getElementById('error-new-password');
                    el2.textContent = Array.isArray(data.errors.new_password)
                        ? data.errors.new_password.join(' ') : data.errors.new_password;
                    el2.classList.remove('d-none');
                }
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-check"></i>{{ __("frontend.update") }}';
        });
    });

    /* ── Delete account form ── */
    document.getElementById('deleteAccountForm').addEventListener('submit', function (e) {
        e.preventDefault();

        var form     = this;
        var btn      = document.getElementById('delete-confirm-btn');
        var errEl    = document.getElementById('delete-password-error');
        var password = form.querySelector('#delete-password').value;

        errEl.classList.add('d-none');
        errEl.textContent = '';
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ password: password, _method: 'DELETE' })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.error) {
                errEl.textContent = data.error;
                errEl.classList.remove('d-none');
                btn.disabled = false;
            } else if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('delete-account-modal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("frontend.account_deleted") ?? "Account Deleted" }}',
                    text: '{{ __("frontend.delete_account_msg") }}',
                    confirmButtonColor: 'var(--pp-cancelled)'
                }).then(function () {
                    window.location.href = '{{ route('frontend.index') }}';
                });
            }
        })
        .catch(function () { btn.disabled = false; });
    });
}());
</script>
@endpush
