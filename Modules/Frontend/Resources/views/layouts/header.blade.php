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
                                <div class="pp-header-search">
                                    <button type="button" id="search-button" class="pp-header-search-btn" aria-label="{{ __('messages.search') }}">
                                        <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                                    </button>
                                    <input type="search" id="search-query"
                                        class="pp-header-search-input"
                                        placeholder="{{ __('messages.search') }}…"
                                        aria-label="{{ __('messages.search') }}">
                                </div>
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
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        const urlParams = new URLSearchParams(window.location.search);
        const query = urlParams.get('query');
        const searchInput = document.getElementById('search-query');
        const searchButton = document.getElementById('search-button');

        if (searchInput) {
            searchInput.value = query || '';
        }

        function handleSearch() {
            const q = searchInput ? searchInput.value.trim() : '';
            const searchRoute = "{{ route('search') }}";
            if (q) {
                window.location.href = searchRoute + '?query=' + encodeURIComponent(q);
            }
        }

        if (searchButton) {
            searchButton.addEventListener('click', function (e) {
                e.preventDefault();
                handleSearch();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleSearch();
                }
            });
        }

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
