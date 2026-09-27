<header class="header-center-home header-default header-sticky header-one" id="pp-site-header">
    @php
        $headerSection = Modules\FrontendSetting\Models\FrontendSetting::where('key', 'heder-menu-setting')->first();
        $sectionData = $headerSection ? json_decode($headerSection->value, true) : null;
    @endphp

    <nav class="nav navbar navbar-expand-xl navbar-light iq-navbar header-hover-menu py-xl-0">
        <div class="container-fluid navbar-inner">
            <div class="d-flex align-items-center justify-content-between w-100 landing-header">
                <div class="d-flex gap-3 gap-xl-0 align-items-center">
                    <button type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar_main"
                        aria-controls="navbar_main" class="pp-nav-hamburger d-xl-none"
                        aria-label="{{ __('messages.open_navigation_menu') ?? 'Open navigation menu' }}">
                        <i class="ph ph-list" aria-hidden="true"></i>
                    </button>
                    <!--Logo -->
                    @include('frontend::components.partials.logo')
                </div>

                <!-- navigation -->
                @if ($sectionData['header_setting'] == 1)
                    @include('frontend::components.partials.horizontal-nav', [
                        'sectionData' => $sectionData,
                    ])
                @endif
                <div class="right-panel">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-btn">
                            <span class="navbar-toggler-icon"></span>
                        </span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <div
                            class="d-flex flex-md-row flex-column align-items-md-center align-items-end justify-content-end gap-md-3 gap-2">
                            @if ($sectionData['header_setting'] == 1 && $sectionData['enable_search'] == 1)
                                <button type="button" id="es-search-trigger" class="es-search-trigger-btn"
                                    aria-label="{{ __('messages.search') }}" aria-haspopup="dialog">
                                    <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                                    <span class="es-search-trigger-label">{{ __('messages.search') }}</span>
                                    <kbd class="es-search-trigger-kbd" aria-hidden="true">⌘K</kbd>
                                </button>
                            @endif

                            <ul class="navbar-nav align-items-center mb-0 ps-0 justify-content-end">
                                @if ($sectionData['header_setting'] == 1)
                                    @if ($sectionData['enable_darknight_mode'] == 1)
                                        <li class="nav-item theme-scheme-switch">
                                            <button type="button"
                                                class="pp-nav-icon-btn change-mode"
                                                aria-label="Toggle dark mode"
                                                aria-pressed="false">
                                                <span class="light-mode" aria-hidden="true">
                                                    <i class="ph ph-sun"></i>
                                                </span>
                                                <span class="dark-mode" aria-hidden="true">
                                                    <i class="ph ph-moon"></i>
                                                </span>
                                            </button>
                                        </li>
                                    @endif

                                    <?php
                                    $notifications_count = 0;
                                    if (auth()->user()) {
                                        $notifications_count = optional(auth()->user())->unreadNotifications->count();
                                    }
                                    ?>
                                    @if (auth()->user() && auth()->user()->getRoleNames()->contains('user'))
                                        <li class="nav-item dropdown iq-dropdown header-notification dropdown-notification-wrapper">
                                            <button type="button"
                                                class="pp-nav-icon-btn notification_list"
                                                data-bs-toggle="dropdown"
                                                aria-label="Notifications{{ $notifications_count > 0 ? ', ' . $notifications_count . ' unread' : '' }}"
                                                aria-expanded="false">
                                                <i class="ph ph-bell" aria-hidden="true"></i>
                                                @if ($notifications_count > 0)
                                                    <span class="pp-nav-badge" aria-hidden="true">{{ $notifications_count }}</span>
                                                @endif
                                            </button>
                                            <ul class="p-0 sub-drop dropdown-menu dropdown-menu-end">
                                                <div class="m-0 shadow-none card notification_data"></div>
                                            </ul>
                                        </li>


                                        {{-- <li class="nav-item dropdown iq-dropdown header-notification dropdown-notification-wrapper">
                                            <a class="nav-link btn-action"
                                                data-bs-toggle="dropdown" href="#">
                                                <div class="iq-sub-card">
                                                    <div class="d-flex align-items-center notification_list">
                                                        <span class="btn-inner">
                                                            <i class="ph ph-bell"></i>
                                                        </span>
                                                        @if ($notifications_count > 0)
                                                            <span
                                                                class="notification-alert">{{ $notifications_count }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-notification-menu shadow" aria-labelledby="navbarDropdown" data-bs-popper="static">
                                                <div class="d-flex justify-content-between align-items-center gap-3 rounded mb-3 pb-1">
                                                    <h6 class="m-0">Notifications</h6>
                                                    <button type="button" class="btn btn-close p-0"></button>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <!-- <div>
                                                        <ul class="nav nav-pills gap-2 m-0" id="pills-tab" role="tablist">
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link active" id="pills-notification-all-tab" data-bs-toggle="pill" data-bs-target="#pills-notification-all" type="button" role="tab" aria-controls="pills-notification-all" aria-selected="true">All</button>
                                                            </li>
                                                            <li class="nav-item" role="presentation">
                                                                <button class="nav-link" id="pills-unread-tab" data-bs-toggle="pill" data-bs-target="#pills-unread" type="button" role="tab" aria-controls="pills-unread" aria-selected="false">Unread</button>
                                                            </li>
                                                        </ul>

                                                    </div> -->
                                                    <a class="notification-link text-end">Mark all as read</a>
                                                </div>
                                                <!-- <div class="tab-content my-3" id="pills-tabContent">
                                                    <div class="tab-pane fade show active" id="pills-notification-all" role="tabpanel" aria-labelledby="pills-notification-all-tab">All</div>
                                                    <div class="tab-pane fade" id="pills-unread" role="tabpanel" aria-labelledby="pills-unread-tab">Unread</div>
                                                </div> -->
                                                <ul class="d-flex flex-column list-inline m-0 p-0 notifcation-data">
                                                    <li class="notifcation-fields">
                                                            <span class="d-flex align-items-center justify-content-between gap-3">
                                                                <span class="d-flex gap-3">
                                                                    <span class="flex-shrik-0">
                                                                    <img src="{{ asset('img/frontend/notification.png') }}" class="img-fluid notification-droup-down-img rounded" alt="image">
                                                                    </span>
                                                                    <div>
                                                                        <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                                                            <h6 class="m-0">Welcome to H &amp; W !</h6>
                                                                            <p class="m-0 notification-time">2 min ago</p>
                                                                        </div>
                                                                        <p class="mb-0 me-2 pe-2">Start your amazing journey to better Espitalia</p>
                                                                    </div>
                                                                </span>
                                                            </span>
                                                    </li>
                                                    <li class="notifcation-fields">
                                                            <span class="d-flex align-items-center justify-content-between gap-3">
                                                                <span class="d-flex gap-3">
                                                                    <span class="flex-shrik-0">
                                                                    <img src="{{ asset('img/frontend/notification.png') }}" class="img-fluid notification-droup-down-img rounded" alt="image">
                                                                    </span>
                                                                    <div>
                                                                        <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                                                            <h6 class="m-0">Account creation confirmation</h6>
                                                                            <p class="m-0 notification-time">6 hr ago</p>
                                                                        </div>
                                                                        <p class="mb-0 me-2 pe-2">Your account has been successfully created. Get ready to explore a
                                                                            healthier you!</p>
                                                                    </div>
                                                                </span>
                                                            </span>
                                                    </li>

                                                </ul>
                                            </div>
                                        </li> --}}
                                    @endif
                                    @if ($sectionData['enable_language'] == 1)
                                        <li class="nav-item dropdown dropdown-language-wrapper">
                                            <button class="pp-nav-lang-btn dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                aria-label="Select language">
                                                <img src="{{ asset('flags/' . App::getLocale() . '.png') }}"
                                                    alt="{{ App::getLocale() }}"
                                                    style="width:16px;height:auto;flex-shrink:0"
                                                    onerror="this.onerror=null;this.src='{{ asset('flags/globe.png') }}'">
                                                {{ strtoupper(App::getLocale()) }}
                                                <i class="ph ph-caret-down" style="font-size:0.65rem;color:var(--pp-muted)" aria-hidden="true"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-language mt-0">
                                                @foreach (config('app.available_locales') as $locale => $title)
                                                    <a class="dropdown-item"
                                                        href="{{ route('language.switch', $locale) }}">
                                                        <span class="d-flex align-items-center gap-3">
                                                            <img src="{{ asset('flags/' . $locale . '.png') }}"
                                                                alt="flag"
                                                                class="img-fluid mr-2"style="width: 20px;height: auto;min-width: 15px;">
                                                            <span> {{ $title }}</span>
                                                            <span class="active-icon"><i
                                                                    class="ph-fill ph-check-fat align-middle"></i></span>
                                                        </span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </li>
                                    @endif
                                @endif
                                @if (auth()->check() && auth()->user()->hasRole('user'))
                                    <li class="nav-item flex-shrink-0 dropdown dropdown-user-wrapper">
                                        <a class="pp-nav-user-btn dropdown-user"
                                            href="#" id="navbarDropdown"
                                            role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            aria-label="{{ __('frontend.account_menu') ?? 'Account menu' }}">
                                            <img src="{{ auth()->user()->profile_image }}"
                                                class="pp-nav-user-avatar"
                                                alt="{{ auth()->user()->first_name }}">
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end pp-nav-user-menu"
                                            aria-labelledby="navbarDropdown">

                                            {{-- Header: avatar + name + email + edit link --}}
                                            <div class="pp-nav-user-menu-header">
                                                <img src="{{ auth()->user()->profile_image }}"
                                                    class="pp-nav-user-menu-avatar" alt="">
                                                <div class="pp-nav-user-menu-info">
                                                    <p class="pp-nav-user-menu-name">{{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}</p>
                                                    <p class="pp-nav-user-menu-email">{{ auth()->user()->email }}</p>
                                                </div>
                                                <a href="{{ route('edit-profile') }}"
                                                    class="pp-nav-user-menu-edit"
                                                    aria-label="{{ __('frontend.edit_profile') ?? 'Edit profile' }}">
                                                    <i class="ph ph-pencil-simple" aria-hidden="true"></i>
                                                </a>
                                            </div>

                                            {{-- Wallet balance highlight --}}
                                            <a href="{{ route('wallet-history') }}" class="pp-nav-user-wallet">
                                                <span class="pp-nav-user-wallet-label">
                                                    <i class="ph ph-wallet" aria-hidden="true"></i>
                                                    {{ __('frontend.wallet_balance') }}
                                                </span>
                                                <span class="pp-nav-user-wallet-amount">
                                                    {{ Currency::format(\Modules\Wallet\Models\Wallet::where('user_id', auth()->user()->id)->value('amount'), 2) }}
                                                </span>
                                            </a>

                                            {{-- Menu items --}}
                                            <ul class="pp-nav-user-list">
                                                <li class="pp-nav-user-list-item">
                                                    <a href="{{ route('manage-profile') }}">
                                                        <span class="pp-nav-user-list-icon" aria-hidden="true"><i class="ph ph-users-three"></i></span>
                                                        <span class="pp-nav-user-list-text">
                                                            <p class="pp-nav-user-list-label">{{ __('frontend.other_patient') }}</p>
                                                            <p class="pp-nav-user-list-sub">{{ __('frontend.lbl_newly_added_members') ?? 'Newly added members' }}</p>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="pp-nav-user-list-item">
                                                    <a href="{{ route('appointment-list') }}">
                                                        <span class="pp-nav-user-list-icon" aria-hidden="true"><i class="ph ph-calendar-check"></i></span>
                                                        <span class="pp-nav-user-list-text">
                                                            <p class="pp-nav-user-list-label">{{ __('frontend.my_appointments') }}</p>
                                                            <p class="pp-nav-user-list-sub">{{ __('frontend.all_appointments') }}</p>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="pp-nav-user-list-item">
                                                    <a href="{{ route('encounter-list') }}">
                                                        <span class="pp-nav-user-list-icon" aria-hidden="true"><i class="ph ph-speedometer"></i></span>
                                                        <span class="pp-nav-user-list-text">
                                                            <p class="pp-nav-user-list-label">{{ __('frontend.encounter') }}</p>
                                                            <p class="pp-nav-user-list-sub">{{ __('frontend.close_active_encounter') }}</p>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="pp-nav-user-list-item">
                                                    <a href="{{ route('account-setting') }}">
                                                        <span class="pp-nav-user-list-icon" aria-hidden="true"><i class="ph ph-lock"></i></span>
                                                        <span class="pp-nav-user-list-text">
                                                            <p class="pp-nav-user-list-label">{{ __('frontend.setting') }}</p>
                                                            <p class="pp-nav-user-list-sub">{{ __('frontend.Change_password') }}</p>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="pp-nav-user-divider" role="separator" aria-hidden="true"></li>
                                                <li class="pp-nav-user-list-item pp-logout">
                                                    <a data-bs-toggle="modal" data-bs-target="#logoout-account"
                                                        role="button" tabindex="0">
                                                        <span class="pp-nav-user-list-icon" aria-hidden="true"><i class="ph ph-sign-out"></i></span>
                                                        <span class="pp-nav-user-list-text">
                                                            <p class="pp-nav-user-list-label">{{ __('frontend.logout') }}</p>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ route('login-page') }}" class="pp-nav-login-btn">
                                            <i class="ph ph-sign-in" aria-hidden="true"></i>
                                            {{ __('frontend.login') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>


@if ($sectionData && isset($sectionData['header_setting']) && $sectionData['header_setting'] == 1 && isset($sectionData['enable_search']) && $sectionData['enable_search'] == 1)
<!-- Enhanced Search Overlay -->
<div id="es-search-overlay" class="es-search-overlay" role="dialog" aria-modal="true"
     aria-label="{{ __('messages.search') }}">
    <div class="es-search-backdrop" id="es-search-backdrop"></div>
    <div class="es-search-dialog">
        <div class="es-search-input-row">
            <i class="ph ph-magnifying-glass es-search-lead-icon" aria-hidden="true"></i>
            <input type="search" id="es-search-input" class="es-search-input"
                placeholder="{{ __('messages.search') }}…"
                autocomplete="off" spellcheck="false"
                aria-label="{{ __('messages.search') }}">
            <kbd class="es-search-esc-hint" aria-hidden="true">ESC</kbd>
            <button id="es-search-close" class="es-search-close-btn"
                aria-label="{{ __('messages.close') ?? 'Close' }}">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="es-search-body">
            <div id="es-search-hint" class="es-search-hint-state">
                <p class="es-search-hint-label">Quick Browse</p>
                <div class="es-search-quick-pills">
                    <a href="{{ route('doctors') }}" class="es-search-pill">
                        <i class="ph ph-stethoscope" aria-hidden="true"></i>
                        <span>{{ __('frontend.doctors') ?? 'Doctors' }}</span>
                    </a>
                    <a href="{{ route('clinics') }}" class="es-search-pill">
                        <i class="ph ph-hospital" aria-hidden="true"></i>
                        <span>{{ __('frontend.clinics') ?? 'Clinics' }}</span>
                    </a>
                    <a href="{{ route('services') }}" class="es-search-pill">
                        <i class="ph ph-first-aid-kit" aria-hidden="true"></i>
                        <span>{{ __('frontend.services') ?? 'Services' }}</span>
                    </a>
                </div>
            </div>
            <div id="es-search-spinner" class="es-search-spinner-wrap d-none">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>
            <div id="es-search-results" class="es-search-results-wrap d-none"></div>
            <div id="es-search-empty" class="es-search-empty-state d-none">
                <i class="ph ph-magnifying-glass-minus es-search-empty-icon" aria-hidden="true"></i>
                <p class="es-search-empty-text">{{ __('messages.no_data_found') ?? 'No results found' }}</p>
                <p class="es-search-empty-sub">Try a different keyword</p>
            </div>
            <div id="es-search-viewall" class="es-search-viewall d-none">
                <a id="es-search-viewall-link" href="{{ route('search') }}" class="es-search-viewall-btn">
                    View all results <i class="ph ph-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- logout modal -->
<div class="modal fade" id="logoout-account" aria-labelledby="logout-modal-title">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content section-bg" style="position:relative">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') ?? 'Close' }}" style="position:absolute;top:0.875rem;right:0.875rem;z-index:1"></button>
            <div class="modal-body" style="padding:2rem 1.5rem 1.75rem;text-align:center">
                <div class="pp-logout-modal-icon" aria-hidden="true">
                    <i class="ph ph-sign-out"></i>
                </div>
                <h6 class="font-size-18 mb-2" id="logout-modal-title">{{ __('frontend.lbl_logout_confirm') }}</h6>
                <p class="mb-0 font-size-14" style="color:var(--pp-muted)">{{ __('frontend.lbl_logout_message') }}</p>
                <div class="d-flex align-items-center justify-content-center gap-3 mt-4">
                    <button class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('frontend.lbl_cancel') }}</button>
                    <button id="logout-btn" class="pp-btn pp-btn-teal">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span class="logout-text">{{ __('frontend.lbl_logout') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loader -->
<div class="loader simple-loader d-none">
    <div class="loader-body">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- No Data Found -->
<div class="modal fade" id="no-data-found" aria-labelledby="no-data-modal-title">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content section-bg" style="position:relative">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') ?? 'Close' }}" style="position:absolute;top:0.875rem;right:0.875rem;z-index:1"></button>
            <div class="modal-body" style="padding:2rem 1.5rem 1.75rem;text-align:center">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--pp-teal-100);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.75rem;color:var(--pp-teal)" aria-hidden="true">
                    <i class="ph ph-magnifying-glass"></i>
                </div>
                <h6 class="font-size-18 mb-2" id="no-data-modal-title">{{ __('messages.no_data_found') ?? 'No data found' }}</h6>
                <p class="mb-0 font-size-14" style="color:var(--pp-muted)">{{ __('messages.no_matching_information') ?? "We couldn't find any matching information" }}</p>
                <div class="d-flex align-items-center justify-content-center gap-3 mt-4">
                    <button class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('messages.close') ?? 'Close' }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal end -->

<style>
/* ===== Espitalia Enhanced Search ===== */

/* --- Trigger button pill --- */
.es-search-trigger-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.875rem 0.45rem 0.75rem;
    border-radius: 999px;
    border: 1.5px solid var(--bs-border-color, #dee2e6);
    background: var(--bs-body-bg, #fff);
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s, box-shadow 0.15s;
    white-space: nowrap;
}
.es-search-trigger-btn:hover {
    border-color: var(--bs-primary);
    color: var(--bs-primary);
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb, 0,118,107), 0.1);
}
.es-search-trigger-btn .ph-magnifying-glass { font-size: 1rem; }
.es-search-trigger-label { display: none; }
.es-search-trigger-kbd {
    display: none;
    font-size: 0.65rem;
    padding: 0.15em 0.5em;
    border: 1px solid currentColor;
    border-radius: 0.3rem;
    opacity: 0.55;
    font-family: ui-monospace, monospace;
    letter-spacing: 0;
}
@media (min-width: 992px) {
    .es-search-trigger-label { display: inline; }
    .es-search-trigger-kbd { display: inline; }
}

/* --- Overlay --- */
.es-search-overlay {
    position: fixed;
    inset: 0;
    z-index: 10500;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: clamp(3rem, 6vh, 6rem) 1rem 1rem;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.18s ease;
}
.es-search-overlay.es-open {
    pointer-events: all;
    opacity: 1;
}
.es-search-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(8, 18, 36, 0.6);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.es-search-dialog {
    position: relative;
    width: 100%;
    max-width: 660px;
    background: var(--bs-body-bg, #fff);
    border: 1px solid var(--bs-border-color, #dee2e6);
    border-radius: 1.125rem;
    box-shadow: 0 24px 64px rgba(0,0,0,0.22), 0 0 0 1px rgba(255,255,255,0.06);
    overflow: hidden;
    transform: translateY(-20px) scale(0.97);
    transition: transform 0.26s cubic-bezier(0.34,1.4,0.64,1);
}
.es-search-overlay.es-open .es-search-dialog {
    transform: translateY(0) scale(1);
}

/* --- Input row --- */
.es-search-input-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1.125rem;
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
}
.es-search-lead-icon {
    font-size: 1.25rem;
    color: var(--bs-primary, #00766b);
    flex-shrink: 0;
}
.es-search-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 1.0625rem;
    color: var(--bs-body-color);
    font-weight: 500;
    min-width: 0;
}
.es-search-input::placeholder { color: var(--bs-secondary-color, #6c757d); font-weight: 400; }
.es-search-esc-hint {
    display: none;
    font-size: 0.6875rem;
    padding: 0.2em 0.5em;
    border: 1px solid var(--bs-border-color, #dee2e6);
    border-radius: 0.3rem;
    color: var(--bs-secondary-color);
    font-family: ui-monospace, monospace;
    opacity: 0.65;
    flex-shrink: 0;
}
@media (min-width: 576px) { .es-search-esc-hint { display: inline; } }
.es-search-close-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.875rem;
    height: 1.875rem;
    border: none;
    background: var(--bs-gray-200, #e9ecef);
    border-radius: 50%;
    font-size: 0.9rem;
    cursor: pointer;
    color: var(--bs-body-color);
    flex-shrink: 0;
    transition: background 0.15s;
}
.es-search-close-btn:hover { background: var(--bs-gray-300, #dee2e6); }

/* --- Body --- */
.es-search-body {
    padding: 1rem 1.125rem 0.875rem;
    max-height: min(58vh, 460px);
    overflow-y: auto;
    scrollbar-width: thin;
}

/* Quick hint pills */
.es-search-hint-label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--bs-secondary-color, #6c757d);
    margin: 0 0 0.625rem;
}
.es-search-quick-pills { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.es-search-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.4rem 0.9375rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--bs-primary, #00766b) 10%, transparent);
    color: var(--bs-primary, #00766b);
    font-size: 0.8125rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid color-mix(in srgb, var(--bs-primary, #00766b) 18%, transparent);
    transition: background 0.15s, color 0.15s, transform 0.12s;
}
.es-search-pill:hover {
    background: var(--bs-primary, #00766b);
    color: #fff;
    transform: translateY(-1px);
    border-color: transparent;
}

/* Spinner */
.es-search-spinner-wrap { display: flex; justify-content: center; padding: 2rem 0; }

/* Results wrap — inherits card grid from server HTML */
.es-search-results-wrap { }
.es-search-results-wrap .row { margin: 0; gap: 0.625rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
.es-search-results-wrap .col { padding: 0; width: auto !important; max-width: none !important; flex: none !important; }
.es-search-results-wrap .serach-card { font-size: 0.8125rem; }
.es-search-results-wrap .serach-card img { height: 100px !important; width: 100%; object-fit: cover; }
.es-search-results-wrap .serach-card h6 { font-size: 0.8125rem; margin-bottom: 0.2rem; }
.es-search-results-wrap .serach-card p { font-size: 0.75rem; }

/* Empty */
.es-search-empty-state { text-align: center; padding: 2rem 1rem; }
.es-search-empty-icon { font-size: 2.25rem; display: block; margin-bottom: 0.625rem; color: var(--bs-secondary-color, #6c757d); opacity: 0.45; }
.es-search-empty-text { font-weight: 600; margin-bottom: 0.25rem; color: var(--bs-body-color); }
.es-search-empty-sub { font-size: 0.8125rem; color: var(--bs-secondary-color, #6c757d); margin: 0; }

/* View all */
.es-search-viewall {
    padding-top: 0.75rem;
    border-top: 1px solid var(--bs-border-color, #dee2e6);
    margin-top: 0.75rem;
    text-align: center;
}
.es-search-viewall-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--bs-primary, #00766b);
    text-decoration: none;
    transition: gap 0.15s;
}
.es-search-viewall-btn:hover { gap: 0.625rem; color: var(--bs-primary, #00766b); }

/* --- Banner enhanced search (applied on pages with banner) --- */
.es-banner-search-wrap { }
.es-banner-search-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: 999px;
    padding: 0.4375rem 0.4375rem 0.4375rem 1.25rem;
    box-shadow: 0 12px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(255,255,255,0.35);
}
[data-bs-theme="dark"] .es-banner-search-form {
    background: rgba(22, 34, 54, 0.88);
    box-shadow: 0 12px 40px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.07);
}
.es-banner-search-icon { font-size: 1.25rem; color: var(--bs-primary, #00766b); flex-shrink: 0; }
.es-banner-search-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 1rem;
    padding: 0.5rem 0.25rem;
    outline: none;
    color: var(--bs-body-color);
    min-width: 0;
}
.es-banner-search-input::placeholder { color: var(--bs-secondary-color, #6c757d); }
.es-banner-search-btn {
    border: none;
    border-radius: 999px;
    padding: 0.625rem 1.5rem;
    background: var(--bs-primary, #00766b);
    color: #fff;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: filter 0.2s, transform 0.12s, box-shadow 0.2s;
}
.es-banner-search-btn:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(var(--bs-primary-rgb, 0,118,107), 0.45);
}
.es-banner-search-categories {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.875rem;
}
.es-banner-cat-label {
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.7);
    font-weight: 500;
}
.es-banner-cat-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3125rem 0.875rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.14);
    color: #fff;
    font-size: 0.8125rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid rgba(255,255,255,0.22);
    backdrop-filter: blur(4px);
    transition: background 0.15s;
}
.es-banner-cat-pill:hover { background: rgba(255,255,255,0.26); color: #fff; }
</style>

<script>
    // Sticky header scroll shadow
    (function () {
        var header = document.getElementById('pp-site-header');
        if (header) {
            var onScroll = function () {
                if (window.scrollY > 4) {
                    header.classList.add('pp-scrolled');
                } else {
                    header.classList.remove('pp-scrolled');
                }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }
    }());

    window.onload = function() {
        // ── Search overlay ──────────────────────────────────────────────────
        (function () {
            var trigger   = document.getElementById('es-search-trigger');
            var overlay   = document.getElementById('es-search-overlay');
            var backdrop  = document.getElementById('es-search-backdrop');
            var closeBtn  = document.getElementById('es-search-close');
            var input     = document.getElementById('es-search-input');
            var hint      = document.getElementById('es-search-hint');
            var spinner   = document.getElementById('es-search-spinner');
            var results   = document.getElementById('es-search-results');
            var empty     = document.getElementById('es-search-empty');
            var viewall   = document.getElementById('es-search-viewall');
            var viewallLink = document.getElementById('es-search-viewall-link');
            var searchRoute     = "{{ route('search') }}";
            var searchDataRoute = "{{ route('getSearchData') }}";

            if (!overlay) return;

            var debounceTimer;

            function openOverlay() {
                overlay.classList.add('es-open');
                document.body.style.overflow = 'hidden';
                setTimeout(function () { if (input) input.focus(); }, 60);
            }

            function closeOverlay() {
                overlay.classList.remove('es-open');
                document.body.style.overflow = '';
            }

            function showState(state) {
                hint.classList.toggle('d-none', state !== 'hint');
                spinner.classList.toggle('d-none', state !== 'spinner');
                results.classList.toggle('d-none', state !== 'results');
                empty.classList.toggle('d-none', state !== 'empty');
                viewall.classList.toggle('d-none', state !== 'results');
            }

            function performSearch(query) {
                showState('spinner');
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    $.ajax({
                        url: searchDataRoute + '?search=' + encodeURIComponent(query),
                        method: 'GET',
                        success: function (response) {
                            if (response.status && response.html) {
                                results.innerHTML = response.html;
                                if (viewallLink) {
                                    viewallLink.href = searchRoute + '?query=' + encodeURIComponent(query);
                                }
                                showState('results');
                            } else {
                                showState('empty');
                            }
                        },
                        error: function () { showState('empty'); }
                    });
                }, 280);
            }

            if (trigger) trigger.addEventListener('click', openOverlay);
            if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
            if (backdrop) backdrop.addEventListener('click', closeOverlay);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('es-open')) {
                    closeOverlay();
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    overlay.classList.contains('es-open') ? closeOverlay() : openOverlay();
                }
            });

            if (input) {
                input.addEventListener('input', function () {
                    var q = this.value.trim();
                    if (!q) { showState('hint'); return; }
                    performSearch(q);
                });
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        var q = this.value.trim();
                        if (q) window.location.href = searchRoute + '?query=' + encodeURIComponent(q);
                    }
                });
            }

            // Pre-fill from URL on search-results page
            var urlQuery = new URLSearchParams(window.location.search).get('query');
            if (urlQuery && input) input.value = urlQuery;
        }());

        $('.notification_list').on('click', function() {
            notificationList();
            notification_count()
        });
    };

    document.getElementById('logout-btn').addEventListener('click', function() {
        const button = this;
        const spinner = button.querySelector('.spinner-border');
        const text = button.querySelector('.logout-text');
        const loader = document.querySelector('.loader.simple-loader');

        // Show spinner in button
        spinner.classList.remove('d-none');
        text.textContent = '{{ __('frontend.lbl_logging_out') }}';
        button.disabled = true;

        // Show full page loader
        loader.classList.remove('d-none');

        // Make the logout request
        fetch('{{ route("user-logout") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '{{ route("frontend.index") }}';
            } else {
                throw new Error('Logout failed');
            }
        })
        .catch(error => {
            console.error('Logout error:', error);
            // Reset button state
            spinner.classList.add('d-none');
            text.textContent = '{{ __('frontend.lbl_logout') }}';
            button.disabled = false;
            // Hide loader
            loader.classList.add('d-none');
            // Show error message
            alert('Failed to logout. Please try again.');
        });
    });


    function notification_count() {

        var url = "{{ route('notification.counts') }}";
        $.ajax({
            type: 'get',
            url: url,
            success: function(res) {

                console.log(res);


            }
        });


    }

    function notificationList(type = '') {
        var url = "{{ route('notification.list') }}";

        $.ajax({
            type: 'get',
            url: url,
            data: {
                'type': type
            },
            success: function(res) {

                $('.notification_data').html(res.data);
                getNotificationCounts();
                if (res.type == "markas_read") {
                    notificationList();
                }
                $('.notify_count').removeClass('notification_tag').text('');
            }
        });
    }
</script>
