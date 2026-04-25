<!-- Horizontal Menu Start -->
<nav id="navbar_main" class="offcanvas mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav py-xl-0">
    <div class="container-fluid p-lg-0">
        <div class="offcanvas-header">
            <div class="navbar-brand p-0">
                <!--Logo -->
                @include('frontend::components.partials.logo')

            </div>
            <button type="button" class="btn-close p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <ul class="navbar-nav iq-nav-menu  list-unstyled" id="header-menu">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('frontend.index') ? 'active' : '' }}" href="{{ route('frontend.index') }}">
                    <span class="item-name">{{ __('frontend.home') }}</span>
                </a>
            </li>
            @auth
                @if($sectionData['appointments'] == 1 && auth()->user()->hasRole('user'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('appointment-list') ? 'active' : '' }}"
                            href="{{ route('appointment-list') }}">
                            <span class="item-name">{{ __('frontend.my_appointments') }}</span>
                        </a>
                    </li>
                @endif
            @endauth

            @if($sectionData['categories'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories') ? 'active' : '' }}"
                        href="{{ route('categories') }}">
                        <span class="item-name">{{ __('frontend.categories') }}</span>
                    </a>
                </li>
            @endif

            @if($sectionData['services'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">
                        <span class="item-name">{{ __('frontend.services') }}</span>
                    </a>
                </li>
            @endif

            @if($sectionData['clinics'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clinics') ? 'active' : '' }}" href="{{ route('clinics') }}">
                        <span class="item-name">{{ __('frontend.clinics') }}</span>
                    </a>
                </li>
            @endif

            @if($sectionData['doctors'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('doctors') ? 'active' : '' }}" href="{{ route('doctors') }}">
                        <span class="item-name">{{ __('frontend.doctors') }}</span>
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('book-doctor') ? 'active' : '' }}"
                    href="{{ route('book-doctor') }}">
                    <span class="item-name">{{ __('frontend.book_a_doctor') }}</span>
                </a>
            </li>

            @if(!empty($sectionData['available_nurses']) && $sectionData['available_nurses'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('available-nurses') ? 'active' : '' }}" href="{{ route('available-nurses') }}">
                        <span class="item-name">{{ __('frontend.available_nurses') }}</span>
                    </a>
                </li>
            @endif

            @if(!empty($sectionData['lab_tests']) && $sectionData['lab_tests'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('lab-tests') ? 'active' : '' }}" href="{{ route('lab-tests') }}">
                        <span class="item-name">{{ __('frontend.lab_tests') }}</span>
                    </a>
                </li>
            @endif

            @if(!empty($sectionData['icu_hospitals']) && $sectionData['icu_hospitals'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('icu-hospitals') ? 'active' : '' }}" href="{{ route('icu-hospitals') }}">
                        <span class="item-name">{{ __('intensive_care.hospital_catalog') }}</span>
                    </a>
                </li>
            @endif

            @if(!empty($sectionData['book_call']) && $sectionData['book_call'] == 1)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('book-call*') ? 'active' : '' }}" href="{{ route('book-call') }}">
                        <span class="item-name">{{ __('call_booking.book_a_call') }}</span>
                    </a>
                </li>
            @endif

            @auth
                @if(auth()->user()->hasRole('user'))
                    @if(!empty($sectionData['available_nurses']) && $sectionData['available_nurses'] == 1)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('my-nurse-requests') ? 'active' : '' }}" href="{{ route('my-nurse-requests') }}">
                                <span class="item-name">{{ __('frontend.my_nurse_requests') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(!empty($sectionData['lab_tests']) && $sectionData['lab_tests'] == 1)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('my-test-orders') ? 'active' : '' }}" href="{{ route('my-test-orders') }}">
                                <span class="item-name">{{ __('frontend.my_test_orders') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(!empty($sectionData['icu_hospitals']) && $sectionData['icu_hospitals'] == 1)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('my-icu-requests') ? 'active' : '' }}" href="{{ route('my-icu-requests') }}">
                                <span class="item-name">{{ __('intensive_care.my_icu_requests') }}</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('incidence.index') ? 'active' : '' }}" href="{{ route('incidence.index') }}">
                            <span class="item-name">{{ __('frontend.incidence') }}</span>
                        </a>
                    </li>
                @endif
            @endauth

            {{-- <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('contact-us') ? 'active' : '' }}" href="{{ route('contact-us') }}">
                    <span class="item-name">Contact</span>
                </a>
            </li> --}}

        </ul>
    </div>
    <!-- container-fluid.// -->
</nav>
<!-- Horizontal Menu End -->
