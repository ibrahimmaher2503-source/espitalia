<?php

namespace App\Http\Middleware;

use App\Trait\Menu;

class GenerateMenus
{
    use Menu;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle()
    {
        return \Menu::make('menu', function ($menu) {
            if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
                $this->staticMenu($menu, [
                    'title' =>  __('menu.main'),
                    'order' => 0
                ]);
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-squares-four',
                    'title' => __('sidebar.dashboard'),
                    'route' => 'backend.home',
                    'active' => ['app', 'app/dashboard'],
                    'order' => 0,
                ]);
            } else if (auth()->user()->hasRole('doctor')) {
                $this->staticMenu($menu, ['title' => __('menu.main'), 'order' => 0]);

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-squares-four',
                    'title' => __('sidebar.dashboard'),
                    'route' => 'backend.doctor-dashboard',
                    'active' => ['app', 'app/doctor-dashboard'],
                    'order' => 0,
                ]);
            } else if (auth()->user()->hasRole('receptionist')) {
                $this->staticMenu($menu, ['title' => __('menu.main'), 'order' => 0]);

                // main
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-squares-four',
                    'title' => __('sidebar.dashboard'),
                    'route' => 'backend.receptionist-dashboard',
                    'active' => ['app', 'app/receptionist-dashboard'],
                    'order' => 0,
                ]);
            } else if (auth()->user()->hasRole('vendor')) {

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-squares-four',
                    'title' => __('sidebar.dashboard'),
                    'route' => 'backend.vendor-dashboard',
                    'active' => ['app', 'app/receptionist-dashboard'],
                    'order' => 0,
                ]);
            }

            $this->mainRoute($menu, [
                'icon' => 'ph ph-sliders-horizontal',
                'title' => __('sidebar.appointment'),
                'route' => 'backend.appointments.index',
                'permission' => ['view_clinic_appointment_list'],
                'active' => ['app/appointments'],
                'order' => 0,
            ]);
            if (auth()->user()->hasRole('receptionist')) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-list-bullets',
                    'title' => __('sidebar.encounter'),
                    'route' => 'backend.encounter.index',
                    'active' => 'app/encounter',
                    'permission' => ['view_encounter'],
                    'order' => 0,
                ]);
            }

            if (auth()->user()->hasRole(['admin', 'demo_admin',])) {
                $encounter = $this->parentMenu($menu, [
                    'icon' => 'ph ph-clock-counter-clockwise',
                    'title' =>  __('sidebar.encounter'),
                    'route' => 'backend.encounter.index',
                    'permission' => ['view_encounter'],
                    'nickname' => 'encounter',
                    'order' => 0,
                ]);
                $this->childMain($encounter, [
                    'icon' => 'ph ph-list-bullets',
                    'title' => __('sidebar.encounter'),
                    'route' => 'backend.encounter.index',
                    'active' => 'app/encounter',
                    'permission' => ['view_encounter'],
                    'order' => 0,
                ]);

                $this->childMain($encounter, [
                    'icon' => 'ph ph-layout',
                    'title' => __('sidebar.encounter_template'),
                    'route' => 'backend.encounter-template.index',
                    'active' => 'app/encounter-template',
                    'permission' => ['view_encounter_template'],
                    'order' => 0,
                ]);

                $this->childMain($encounter, [
                    'icon' => 'ph ph-warning-diamond',
                    'title' => __('sidebar.problems'),
                    'route' => 'backend.problems.index',
                    'active' => 'app/problems',
                    'permission' => ['view_encounter'],
                    'order' => 0,
                ]);
                $this->childMain($encounter, [
                    'icon' => 'ph ph-eye',
                    'title' => __('appointment.observation'),
                    'route' => 'backend.observation.index',
                    'active' => 'app/observation',
                    'permission' => ['view_encounter'],
                    'order' => 0,
                ]);
            }

            if (!auth()->user()->hasRole(['doctor'])) {
                $doctor = $this->parentMenu($menu, [
                    'icon' => 'ph ph-stethoscope',
                    'title' => __('sidebar.doctor'),
                    'route' => 'backend.doctor.index',
                    'permission' => ['view_doctors_session'],
                    'nickname' => 'doctor',
                    'order' => 0,
                ]);
                $this->childMain($doctor, [
                    'icon' => 'ph ph-stethoscope',
                    'title' => __('sidebar.doctor'),
                    'route' => 'backend.doctor.index',
                    'active' => 'app/doctor',
                    'permission' => ['view_doctors'],
                    'order' => 0,
                ]);
                $this->childMain($doctor, [
                    'icon' => 'ph ph-clock',
                    'title' => __('clinic.doctor_session'),
                    'route' => 'backend.doctor-session.index',
                    'active' => 'app/doctor-session',
                    'permission' => ['view_doctors_session'],
                    'order' => 0,
                ]);
                $this->childMain($doctor, [
                    'icon' => 'ph ph-first-aid-kit',
                    'title' => __('sidebar.independent_services'),
                    'route' => 'backend.doctor-independent-service.index',
                    'active' => 'app/doctor-independent-service',
                    'permission' => ['view_doctors'],
                    'order' => 0,
                ]);

                $this->childMain($doctor, [
                    'icon' => 'ph ph-clock',
                    'title' => __('sidebar.independent_sessions'),
                    'route' => 'backend.doctor-independent-session.index',
                    'active' => 'app/doctor-independent-session',
                    'permission' => ['view_doctors_session'],
                    'order' => 0,
                ]);

                $this->childMain($doctor, [
                    'icon' => 'ph ph-phone-call',
                    'title' => __('sidebar.call_services'),
                    'route' => 'backend.doctor-call-service.index',
                    'active' => 'app/doctor-call-service',
                    'permission' => ['view_doctors'],
                    'order' => 0,
                ]);

                $this->childMain($doctor, [
                    'icon' => 'ph ph-calendar-check',
                    'title' => __('sidebar.call_sessions'),
                    'route' => 'backend.doctor-call-session.index',
                    'active' => 'app/doctor-call-session',
                    'permission' => ['view_doctors_session'],
                    'order' => 0,
                ]);
            }

            $this->mainRoute($menu, [
                'icon' => 'ph ph-calendar-heart',
                'title' => __('clinic.specialization'),
                'route' => 'backend.specializations.index',
                'active' => ['app/specializations'],
                'permission' => ['view_specialization'],
                'order' => 0,
            ]);


            $permissionsToCheck = ['view_clinics_center', 'view_clinics_category', 'view_clinics_service', 'view_doctors', 'view_doctors_session', 'view_clinic_patient_list', 'view_patient_soap', 'view_clinic_appointment_list', 'view_encounter_template', 'view_encounter'];

            if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission))) {

                if (multiVendor() == "1" || auth()->user()->hasRole(['admin', 'demo_admin', 'vendor', 'doctor', 'receptionist'])) {

                    $this->staticMenu($menu, ['title' => __('sidebar.clinic_center'), 'order' => 0]);
                }
            }
            if (!auth()->user()->hasRole('receptionist')) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-hospital',
                    'title' => __('sidebar.clinic'),
                    'route' => 'backend.clinics.index',
                    'permission' => ['view_clinics_center'],
                    'active' => ['app/clinics'],
                    'order' => 0,
                ]);
            }
            $this->mainRoute($menu, [
                'icon' => 'ph ph-list-bullets',
                'title' => __('sidebar.categories'),
                'route' => 'backend.category.index',
                'permission' => ['view_clinics_category'],
                'active' => ['app/category'],
                'order' => 0,
            ]);



            $this->mainRoute($menu, [
                'icon' => 'ph ph-first-aid-kit',
                'title' => __('sidebar.services'),
                'route' => 'backend.services.index',
                'active' => ['app/services'],
                'permission' => ['view_clinics_service'],
                'order' => 0,
            ]);



            if (auth()->user()->hasRole(['doctor'])) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-clock',
                    'title' => __('clinic.doctor_session'),
                    'route' => 'backend.doctor-session.index',
                    'active' => 'app/doctor-session',
                    'permission' => ['view_doctors_session'],
                    'order' => 0,
                ]);

                // Independent Practice section for doctors
                $independentPractice = $this->parentMenu($menu, [
                    'icon' => 'ph ph-user-focus',
                    'title' => __('clinic.independent_practice'),
                    'nickname' => 'independent-practice',
                    'permission' => ['manage_own_independent_services'],
                    'order' => 0,
                ]);

                $this->childMain($independentPractice, [
                    'icon' => 'ph ph-first-aid-kit',
                    'title' => __('sidebar.my_services'),
                    'route' => 'backend.doctor-independent-service.index',
                    'active' => 'app/doctor-independent-service',
                    'permission' => ['manage_own_independent_services'],
                    'order' => 0,
                ]);

                $this->childMain($independentPractice, [
                    'icon' => 'ph ph-clock',
                    'title' => __('sidebar.my_availability'),
                    'route' => 'backend.doctor-independent-session.index',
                    'active' => 'app/doctor-independent-session',
                    'permission' => ['manage_own_independent_sessions'],
                    'order' => 0,
                ]);

                $this->childMain($independentPractice, [
                    'icon' => 'ph ph-phone-call',
                    'title' => __('sidebar.my_call_services'),
                    'route' => 'backend.doctor-call-service.index',
                    'active' => 'app/doctor-call-service',
                    'permission' => ['manage_own_call_services'],
                    'order' => 0,
                ]);

                $this->childMain($independentPractice, [
                    'icon' => 'ph ph-calendar-check',
                    'title' => __('sidebar.my_call_availability'),
                    'route' => 'backend.doctor-call-session.index',
                    'active' => 'app/doctor-call-session',
                    'permission' => ['manage_own_call_sessions'],
                    'order' => 0,
                ]);
            }


            if (auth()->user()->hasRole(['doctor'])) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-list-bullets',
                    'title' => __('sidebar.encounter'),
                    'route' => 'backend.encounter.index',
                    'active' => 'app/encounter',
                    'permission' => ['view_encounter'],
                    'order' => 0,
                ]);
            }
            $this->mainRoute($menu, [
                'icon' => ' ph ph-star',
                'title' => __('sidebar.reviews'),
                'route' => ['backend.doctors.review'],
                'active' => ['app/doctors-review'],
                'permission' => ['view_reviews'],
                'order' => 0,
            ]);


            //shop

            // $permissionsToCheck = ['view_product', 'view_brand', 'view_product_category', 'view_product_subcategory','view_unit','view_tag','view_product_variation',
            // 'view_order','view_supply','view_logistics','view_shipping_zones'];


            // if (collect($permissionsToCheck)->contains(fn ($permission) => auth()->user()->can($permission))) {
            //     $this->staticMenu($menu, ['title' => __('sidebar.shop'), 'order' => 0]);
            // }
            // $product = $this->parentMenu($menu, [
            //     'icon' => 'fa-solid fa-store',
            //     'title' => __('sidebar.product'),
            //     'route' => 'backend.products.index',
            //     'permission' => ['view_product'],
            //     'nickname' => 'PR',
            //     'order' => 0,
            // ]);
            // $this->childMain($product, [
            //     'title' => __('sidebar.all_product'),
            //     'route' => 'backend.products.index',
            //     'active' => 'app/products',
            //     'shortTitle' => 'AP',
            //     'permission' => ['view_product'],
            //     'order' => 0,
            // ]);
            // $this->childMain($product, [
            //     'title' => __('sidebar.brand'),
            //     'route' => 'backend.brands.index',
            //     'shortTitle' => 'BR',
            //     'permission' => ['view_brand'],
            //     'active' => ['app/brands'],
            //     'order' => 0,
            // ]);
            // $this->childMain($product, [
            //     'title' => __('sidebar.categories'),
            //     'route' => 'backend.products-categories.index',
            //     'shortTitle' => 'C',
            //     'permission' => ['view_product_category'],
            //     'active' => ['app/products-categories'],
            //     'order' => 0,
            // ]);
            // $this->childMain($product, [
            //     'title' => __('sidebar.sub_categories'),
            //     'route' => 'backend.products-categories.index_nested',
            //     'shortTitle' => 'SC',
            //     'permission' => ['view_product_subcategory'],
            //     'active' => ['app/products-sub-categories'],
            //     'order' => 0,
            // ]);

            // $this->childMain($product, [
            //     'title' => __('sidebar.units'),
            //     'route' => 'backend.units.index',
            //     'shortTitle' => 'U',
            //     'permission' => ['view_unit'],
            //     'active' => ['app/units'],
            //     'order' => 0,
            // ]);

            // $this->childMain($product, [
            //     'title' => __('sidebar.tag'),
            //     'route' => 'backend.tags.index',
            //     'shortTitle' => 'T',
            //     'permission' => ['view_tag'],
            //     'active' => ['app/tags'],
            //     'order' => 0,
            // ]);

            // $this->mainRoute($menu, [
            //     'icon' => 'fa-solid fa-swatchbook',
            //     'title' => __('sidebar.variations'),
            //     'route' => ['backend.variations.index'],
            //     'active' => ['app/variations'],
            //     'permission' => ['view_product_variation'],
            //     'order' => 0,
            // ]);

            // $this->mainRoute($menu, [
            //     'icon' => 'fa-solid fa-bag-shopping',
            //     'title' => __('sidebar.orders'),
            //     'permission' => 'view_tag',
            //     'route' => ['backend.orders.index'],
            //     'permission' => ['view_order'],
            //     'active' => ['app/orders'],
            //     'order' => 0,
            // ]);

            // $supply = $this->parentMenu($menu, [
            //     'icon' => 'fa-solid fa-truck-field',
            //     'title' => __('sidebar.supply'),
            //     'nickname' => 'supply',
            //     'permission' => ['view_supply'],
            //     'order' => 0,
            // ]);

            // $this->childMain($supply, [
            //     'title' => __('sidebar.logistics'),
            //     'route' => 'backend.logistics.index',
            //     'shortTitle' => 'AP',
            //     'active' => ['app/logistics'],
            //     'permission' => ['view_logistics'],
            //     'order' => 0,
            // ]);

            // $this->childMain($supply, [
            //     'title' => __('sidebar.logistic_zone'),
            //     'route' => 'backend.logistic-zones.index',
            //     'permission' => ['view_shipping_zones'],
            //     'shortTitle' => 'AP',
            //     'active' => ['app/logistic-zones'],
            //     'order' => 0,
            // ]);

            // FINANCE Static

            $permissionsToCheck = ['view_customer', 'view_clinic_receptionist_list', 'view_vendor_list'];

            if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission))) {
                $this->staticMenu($menu, ['title' => __('sidebar.user'), 'order' => 0]);
            }


            $this->mainRoute($menu, [
                'icon' => 'ph ph-users',
                'title' =>  __('sidebar.patient'),
                'route' => 'backend.customers.index',
                'active' => ['app/customers'],
                'permission' => 'view_customer',
                'order' => 0,
            ]);

            $this->mainRoute($menu, [
                'icon' => 'ph ph-user-circle-gear',
                'title' => __('sidebar.receptionist'),
                'route' => 'backend.receptionist.index',
                'active' => ['app/receptionist'],
                'permission' => ['view_clinic_receptionist_list'],
                'order' => 0,
            ]);
            if (multiVendor() == "1" && auth()->user()->hasRole(['admin', 'demo_admin'])) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-user-square',
                    'title' => __('sidebar.vendors'),
                    'route' => 'backend.multivendors.index',
                    'active' => ['app/multivendors'],
                    'permission' => ['view_vendor_list'],
                    'order' => 0,
                ]);
            }
            $permissionsToCheck = ['view_tax', 'view_earning', 'view_billing_record'];

            if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission))) {
                $this->staticMenu($menu, ['title' => __('sidebar.finance'), 'order' => 0]);
            }


            $this->mainRoute($menu, [
                'icon' => 'ph ph-percent',
                'title' => __('sidebar.tax'),
                'route' => 'backend.tax.index',
                'active' => ['app/tax'],
                'permission' => 'view_tax',
                'order' => 0,
            ]);
            $this->mainRoute($menu, [
                'icon' => 'ph ph-invoice',
                'title' => __('sidebar.billing_record'),
                'route' => 'backend.billing-record.index',
                'active' => ['app/billing-record'],
                'permission' => 'view_billing_record',
                'order' => 0,
            ]);
            $this->mainRoute($menu, [
                'icon' => 'ph ph-currency-dollar',
                'title' => __('sidebar.doctor_earning'),
                'route' => 'backend.earnings.index',
                'active' => ['app/earnings'],
                'permission' => ['view_doctor_earning'],
                'order' => 0,
            ]);
            if (multiVendor() == "1" && auth()->user()->hasRole(['admin', 'demo_admin'])) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-user-circle-check',
                    'title' => __('sidebar.vendor_earning'),
                    'route' => 'backend.vendor-earnings.index',
                    'active' => ['app/vendor-earnings'],
                    'permission' => ['view_vendor_earning'],
                    'order' => 0,
                ]);
            }



            //Report

            $permissionsToCheck = ['view_daily_bookings', 'view_overall_bookings', 'view_staff_payouts', 'view_staff_service', 'view_order_reports', 'view_commission_reports', 'view_appointment_overview', 'view_clinic_overview'];

            if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission))) {
                $this->staticMenu($menu, ['title' => __('sidebar.reports'), 'order' => 0]);
            }



            if (auth()->user()->hasRole('vendor')) {

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-percent',
                    'title' =>  __('appointment.revenue_breakdown'),
                    'route' => 'backend.reports.commission-revenue',
                    'active' => ['app/commission-revenue'],
                    'order' => 0,
                ]);
            }


            if (auth()->user()->hasRole('vendor') || auth()->user()->hasRole('demo_admin') ||  auth()->user()->hasRole('admin')) {

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-file-magnifying-glass',
                    'title' =>  __('dashboard.lbl_title_appointment_overview'),
                    'route' => 'backend.reports.appointment-overview',
                    'active' => ['app/appointment-overview'],
                    'order' => 0,
                ]);
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-map-pin-plus',
                    'title' =>  __('sidebar.clinic_overview'),
                    'route' => 'backend.reports.clinic-overview',
                    'active' => ['app/clinic-overview'],
                    'order' => 0,
                ]);
            }


            if (multiVendor() == "1") {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-share',
                    'title' =>  __('sidebar.request_service'),
                    'route' => 'backend.requestservices.index',
                    'active' => ['app/requestservices'],
                    'permission' => ['view_request_service'],
                    'order' => 0,
                ]);
            }

            // --- NURSING ---
            $nursingPermissions = ['view_request_nurse', 'view_nurses'];
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'nurse']) && collect($nursingPermissions)->contains(fn($permission) => auth()->user()->can($permission))) {
                $this->staticMenu($menu, ['title' => __('sidebar.nursing'), 'order' => 0]);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'nurse'])) {
                $nursing = $this->parentMenu($menu, [
                    'icon' => 'ph ph-first-aid',
                    'title' => __('sidebar.request_nurse'),
                    'route' => 'backend.nurse-requests.index',
                    'permission' => ['view_request_nurse'],
                    'nickname' => 'request_nurse',
                    'order' => 0,
                ]);

                $this->childMain($nursing, [
                    'icon' => 'ph ph-clipboard-text',
                    'title' => __('sidebar.nurse_requests'),
                    'route' => 'backend.nurse-requests.index',
                    'active' => 'app/nurse-requests',
                    'permission' => ['view_request_nurse'],
                    'order' => 0,
                ]);

                if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                    $this->childMain($nursing, [
                        'icon' => 'ph ph-users',
                        'title' => __('sidebar.nurses'),
                        'route' => 'backend.nurses.index',
                        'active' => 'app/nurses',
                        'permission' => ['view_nurses'],
                        'order' => 0,
                    ]);
                }
            }

            // --- DOCTOR VISITS ---
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'receptionist', 'doctor']) && auth()->user()->can('view_doctor_visit')) {
                $doctorVisits = $this->parentMenu($menu, [
                    'icon'       => 'ph ph-house-line',
                    'title'      => __('sidebar.doctor_visit'),
                    'route'      => 'backend.doctor_visit.index',
                    'permission' => ['view_doctor_visit'],
                    'nickname'   => 'doctor_visit',
                    'order'      => 0,
                ]);

                $this->childMain($doctorVisits, [
                    'icon'       => 'ph ph-house-line',
                    'title'      => __('sidebar.doctor_visit'),
                    'route'      => 'backend.doctor_visit.index',
                    'active'     => 'app/doctor-visit',
                    'permission' => ['view_doctor_visit'],
                    'order'      => 0,
                ]);
            }

            // --- HOME HEALTHCARE ---
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $homeHealthcare = $this->parentMenu($menu, [
                    'icon'       => 'ph ph-house-line',
                    'title'      => __('sidebar.home_healthcare'),
                    'route'      => 'backend.home-healthcares.index',
                    'nickname'   => 'home_healthcare',
                    'order'      => 0,
                ]);

                $this->childMain($homeHealthcare, [
                    'icon'       => 'ph ph-first-aid-kit',
                    'title'      => __('sidebar.home_healthcare_services'),
                    'route'      => 'backend.home-healthcares.index',
                    'active'     => 'app/home-healthcares',
                    'order'      => 0,
                ]);
            }

            // --- LOCATION MANAGEMENT ---
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $locationMgmt = $this->parentMenu($menu, [
                    'icon'       => 'ph ph-map-pin',
                    'title'      => __('location.lbl_governorates'),
                    'route'      => 'backend.governorates.index',
                    'nickname'   => 'location_mgmt',
                    'order'      => 0,
                ]);

                $this->childMain($locationMgmt, [
                    'icon'       => 'ph ph-map-pin',
                    'title'      => __('sidebar.governorates'),
                    'route'      => 'backend.governorates.index',
                    'active'     => 'app/governorates',
                    'order'      => 0,
                ]);

                $this->childMain($locationMgmt, [
                    'icon'       => 'ph ph-buildings',
                    'title'      => __('sidebar.governorate_cities'),
                    'route'      => 'backend.governorate-cities.index',
                    'active'     => 'app/governorate-cities',
                    'order'      => 1,
                ]);
            }

            // --- BRANCHES ---
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'vendor'])) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-buildings',
                    'title' => __('branch.title'),
                    'route' => 'backend.branches.index',
                    'active' => ['app/branches'],
                    'permission' => ['view_branch'],
                    'order' => 0,
                ]);
            }

            // --- DIAGNOSTICS ---
            $diagnosticsPermissions = ['view_test_orders', 'view_lab_tests', 'view_lab_test_categories', 'view_labs', 'view_radiology_centers', 'view_facility_sessions', 'view_facility_bookings'];
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'lab_technician', 'doctor']) && collect($diagnosticsPermissions)->contains(fn($permission) => auth()->user()->can($permission))) {
                $this->staticMenu($menu, ['title' => __('sidebar.diagnostics'), 'order' => 0]);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'lab_technician', 'doctor'])) {
                $diagnostics = $this->parentMenu($menu, [
                    'icon' => 'ph ph-flask',
                    'title' => __('sidebar.diagnostics'),
                    'route' => 'backend.test-orders.index',
                    'permission' => ['view_test_orders'],
                    'nickname' => 'diagnostics',
                    'order' => 0,
                ]);

                $this->childMain($diagnostics, [
                    'icon' => 'ph ph-clipboard-text',
                    'title' => __('sidebar.test_orders'),
                    'route' => 'backend.test-orders.index',
                    'active' => 'app/test-orders',
                    'permission' => ['view_test_orders'],
                    'order' => 0,
                ]);

                if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                    $this->childMain($diagnostics, [
                        'icon' => 'ph ph-test-tube',
                        'title' => __('sidebar.test_catalog'),
                        'route' => 'backend.lab-tests.index',
                        'active' => 'app/lab-tests',
                        'permission' => ['view_lab_tests'],
                        'order' => 0,
                    ]);

                    $this->childMain($diagnostics, [
                        'icon' => 'ph ph-folder-simple',
                        'title' => __('sidebar.test_categories'),
                        'route' => 'backend.lab-test-categories.index',
                        'active' => 'app/lab-test-categories',
                        'permission' => ['view_lab_test_categories'],
                        'order' => 0,
                    ]);

                    $this->childMain($diagnostics, [
                        'icon' => 'ph ph-flask',
                        'title' => __('sidebar.labs'),
                        'route' => 'backend.labs.index',
                        'active' => 'app/labs',
                        'permission' => ['view_labs'],
                        'order' => 0,
                    ]);

                    $this->childMain($diagnostics, [
                        'icon' => 'ph ph-scan',
                        'title' => __('sidebar.radiology_centers'),
                        'route' => 'backend.radiology-centers.index',
                        'active' => 'app/radiology-centers',
                        'permission' => ['view_radiology_centers'],
                        'order' => 0,
                    ]);

                    $this->childMain($diagnostics, [
                        'icon' => 'ph ph-calendar-blank',
                        'title' => __('sidebar.facility_sessions'),
                        'route' => 'backend.facility-sessions.index',
                        'active' => 'app/facility-sessions',
                        'permission' => ['view_facility_sessions'],
                        'order' => 0,
                    ]);

                    $this->childMain($diagnostics, [
                        'icon' => 'ph ph-calendar-check',
                        'title' => __('sidebar.facility_bookings'),
                        'route' => 'backend.facility-bookings.index',
                        'active' => 'app/facility-bookings',
                        'permission' => ['view_facility_bookings'],
                        'order' => 0,
                    ]);
                }
            }

            // --- INTENSIVE CARE ---
            $icuPermissions = ['view_icu_admission_requests', 'view_hospitals', 'view_icu_departments', 'view_icu_commission', 'view_icu_earning'];
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'hospital_coordinator']) && collect($icuPermissions)->contains(fn($permission) => auth()->user()->can($permission))) {
                $this->staticMenu($menu, ['title' => __('sidebar.intensive_care'), 'order' => 0]);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'hospital_coordinator'])) {
                $intensiveCare = $this->parentMenu($menu, [
                    'icon' => 'ph ph-heartbeat',
                    'title' => __('sidebar.intensive_care'),
                    'route' => 'backend.icu-admission-requests.index',
                    'permission' => ['view_icu_admission_requests'],
                    'nickname' => 'intensive_care',
                    'order' => 0,
                ]);

                $this->childMain($intensiveCare, [
                    'icon' => 'ph ph-clipboard-text',
                    'title' => __('sidebar.icu_admission_requests'),
                    'route' => 'backend.icu-admission-requests.index',
                    'active' => 'app/icu-admission-requests',
                    'permission' => ['view_icu_admission_requests'],
                    'order' => 0,
                ]);

                if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                    $this->childMain($intensiveCare, [
                        'icon' => 'ph ph-buildings',
                        'title' => __('sidebar.hospitals'),
                        'route' => 'backend.hospitals.index',
                        'active' => 'app/hospitals',
                        'permission' => ['view_hospitals'],
                        'order' => 0,
                    ]);
                }

                $this->childMain($intensiveCare, [
                    'icon' => 'ph ph-bed',
                    'title' => __('sidebar.icu_departments'),
                    'route' => 'backend.icu-departments.index',
                    'active' => 'app/icu-departments',
                    'permission' => ['view_icu_departments'],
                    'order' => 0,
                ]);

                if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                    $this->childMain($intensiveCare, [
                        'icon' => 'ph ph-receipt',
                        'title' => __('sidebar.icu_commissions'),
                        'route' => 'backend.icu-commissions.index',
                        'active' => 'app/icu-commissions',
                        'permission' => ['view_icu_commission'],
                        'order' => 0,
                    ]);
                }

                $this->childMain($intensiveCare, [
                    'icon' => 'ph ph-chart-line-up',
                    'title' => __('sidebar.icu_earnings'),
                    'route' => 'backend.icu-earnings.index',
                    'active' => 'app/icu-earnings',
                    'permission' => ['view_icu_earning'],
                    'order' => 0,
                ]);
            }

            // --- PHARMACY PANEL (for pharmacy staff) ---
            if (auth()->user()->hasRole('pharmacy')) {
                $this->staticMenu($menu, ['title' => __('sidebar.my_pharmacy'), 'order' => 0]);

                $pharmacy = $this->parentMenu($menu, [
                    'icon' => 'ph ph-storefront',
                    'title' => __('sidebar.my_pharmacy'),
                    'route' => 'backend.pharmacy-panel-dashboard.index',
                    'permission' => ['view_pharmacy_inventory'],
                    'nickname' => 'my_pharmacy',
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-gauge',
                    'title' => __('sidebar.dashboard'),
                    'route' => 'backend.pharmacy-panel-dashboard.index',
                    'active' => 'app/pharmacy-panel/dashboard',
                    'permission' => ['view_pharmacy_inventory'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-package',
                    'title' => __('sidebar.pharmacy_inventory'),
                    'route' => 'backend.pharmacy-panel-inventory.index',
                    'active' => 'app/pharmacy-panel/inventory',
                    'permission' => ['view_pharmacy_inventory'],
                    'order' => 1,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-receipt',
                    'title' => __('sidebar.pharmacy_orders'),
                    'route' => 'backend.pharmacy-panel-orders.index',
                    'active' => 'app/pharmacy-panel/orders',
                    'permission' => ['view_pharmacy_orders'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-stack',
                    'title' => __('sidebar.pharmacy_batches'),
                    'route' => 'backend.pharmacy-panel-batches.index',
                    'active' => 'app/pharmacy-panel/batches',
                    'permission' => ['view_pharmacy_batches'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-prescription',
                    'title' => __('sidebar.pharmacy_prescriptions'),
                    'route' => 'backend.pharmacy-panel-prescriptions.index',
                    'active' => 'app/pharmacy-panel/prescriptions',
                    'permission' => ['view_pharmacy_prescriptions'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-users-three',
                    'title' => __('sidebar.pharmacy_staff'),
                    'route' => 'backend.pharmacy-panel-staff.index',
                    'active' => 'app/pharmacy-panel/staff',
                    'permission' => ['view_pharmacy_staff'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-map-pin-area',
                    'title' => __('sidebar.pharmacy_delivery_zones'),
                    'route' => 'backend.pharmacy-panel-delivery-zones.index',
                    'active' => 'app/pharmacy-panel/delivery-zones',
                    'permission' => ['view_pharmacy_delivery_zones'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-ticket',
                    'title' => __('sidebar.pharmacy_coupons'),
                    'route' => 'backend.pharmacy-panel-coupons.index',
                    'active' => 'app/pharmacy-panel/coupons',
                    'permission' => ['view_pharmacy_coupons'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-arrow-counter-clockwise',
                    'title' => __('sidebar.pharmacy_refunds'),
                    'route' => 'backend.pharmacy-panel-refunds.index',
                    'active' => 'app/pharmacy-panel/refunds',
                    'permission' => ['view_pharmacy_refunds'],
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-bell-ringing',
                    'title' => __('sidebar.pharmacy_alerts'),
                    'route' => 'backend.pharmacy-panel-alerts.index',
                    'active' => 'app/pharmacy-panel/alerts',
                    'permission' => ['view_pharmacy_notifications'],
                    'order' => 0,
                ]);
            }

            $this->mainRoute($menu, [
                'icon' => 'ph ph-currency-dollar',
                'title' => __('sidebar.doctor_payout'),
                'route' => 'backend.reports.doctor-payout-report',
                'active' => ['app/doctor-payout-report'],
                'permission' => ['view_doctor_payouts'],
                'order' => 0,
            ]);
            if (multiVendor() == "1" && auth()->user()->hasRole(['admin', 'demo_admin'])) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-currency-dollar-simple',
                    'title' => __('sidebar.vendor_payout'),
                    'route' => 'backend.reports.vendor-payout-report',
                    'active' => ['app/vendor-payout-report'],
                    'permission' => ['view_vendor_payouts'],
                    'order' => 0,
                ]);
            }

            // --- OFFERS ---
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'vendor'])) {
                $this->staticMenu($menu, ['title' => __('sidebar.offers'), 'order' => 0]);

                $offers = $this->parentMenu($menu, [
                    'icon' => 'ph ph-tag',
                    'title' => __('sidebar.offers'),
                    'route' => 'backend.offers.index',
                    'permission' => ['view_offers'],
                    'nickname' => 'offers',
                    'order' => 0,
                ]);

                $this->childMain($offers, [
                    'icon' => 'ph ph-list-bullets',
                    'title' => __('sidebar.offers'),
                    'route' => 'backend.offers.index',
                    'permission' => ['view_offers'],
                    'nickname' => 'offers_list',
                    'order' => 1,
                ]);

                $this->childMain($offers, [
                    'icon' => 'ph ph-plus-circle',
                    'title' => __('offer.lbl_create_offer'),
                    'route' => 'backend.offers.create',
                    'permission' => ['add_offers'],
                    'nickname' => 'offers_create',
                    'order' => 2,
                ]);

                $this->childMain($offers, [
                    'icon' => 'ph ph-chart-line-up',
                    'title' => __('sidebar.offer_analytics'),
                    'route' => 'backend.offer-analytics.index',
                    'permission' => ['view_offer_analytics'],
                    'nickname' => 'offer_analytics',
                    'order' => 3,
                ]);

                $this->childMain($offers, [
                    'icon' => 'ph ph-clipboard-text',
                    'title' => __('sidebar.offer_logs'),
                    'route' => 'backend.offer-usage-logs.index',
                    'permission' => ['view_offer_analytics'],
                    'nickname' => 'offer_logs',
                    'order' => 4,
                ]);
            }

            // --- PHARMACY MARKETPLACE ---
            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $this->staticMenu($menu, ['title' => __('sidebar.pharmacy_marketplace'), 'order' => 0]);

                $pharmacy = $this->parentMenu($menu, [
                    'icon' => 'ph ph-prescription',
                    'title' => __('sidebar.pharmacy_marketplace'),
                    'route' => 'backend.pharmacy-categories.index',
                    'permission' => ['view_pharmacy_categories'],
                    'nickname' => 'pharmacy',
                    'order' => 0,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-list-bullets',
                    'title' => __('sidebar.pharmacy_categories'),
                    'route' => 'backend.pharmacy-categories.index',
                    'active' => 'app/pharmacy-categories',
                    'permission' => ['view_pharmacy_categories'],
                    'order' => 1,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-tag',
                    'title' => __('sidebar.pharmacy_brands'),
                    'route' => 'backend.pharmacy-brands.index',
                    'active' => 'app/pharmacy-brands',
                    'permission' => ['view_pharmacy_brands'],
                    'order' => 2,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-article',
                    'title' => __('sidebar.pharmacy_product_types'),
                    'route' => 'backend.pharmacy-product-types.index',
                    'active' => 'app/pharmacy-product-types',
                    'permission' => ['view_pharmacy_product_types'],
                    'order' => 3,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-package',
                    'title' => __('sidebar.pharmacy_products'),
                    'route' => 'backend.pharmacy-products.index',
                    'active' => 'app/pharmacy-products',
                    'permission' => ['view_pharmacy_products'],
                    'order' => 4,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-storefront',
                    'title' => __('sidebar.pharmacies'),
                    'route' => 'backend.pharmacies.index',
                    'active' => 'app/pharmacies',
                    'permission' => ['view_pharmacies'],
                    'order' => 5,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-package',
                    'title' => __('sidebar.pharmacy_inventory'),
                    'route' => 'backend.pharmacy-panel-inventory.index',
                    'active' => 'app/pharmacy-panel/inventory',
                    'permission' => ['view_pharmacy_inventory'],
                    'order' => 6,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-receipt',
                    'title' => __('sidebar.pharmacy_orders'),
                    'route' => 'backend.pharmacy-orders.index',
                    'active' => 'app/pharmacy-orders',
                    'permission' => ['view_pharmacy_orders'],
                    'order' => 7,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-stack',
                    'title' => __('sidebar.pharmacy_batches'),
                    'route' => 'backend.pharmacy-panel-batches.index',
                    'active' => 'app/pharmacy-panel/batches',
                    'permission' => ['view_pharmacy_batches'],
                    'order' => 8,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-users-three',
                    'title' => __('sidebar.pharmacy_staff'),
                    'route' => 'backend.pharmacy-panel-staff.index',
                    'active' => 'app/pharmacy-panel/staff',
                    'permission' => ['view_pharmacy_staff'],
                    'order' => 9,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-map-pin-area',
                    'title' => __('sidebar.pharmacy_delivery_zones'),
                    'route' => 'backend.pharmacy-panel-delivery-zones.index',
                    'active' => 'app/pharmacy-panel/delivery-zones',
                    'permission' => ['view_pharmacy_delivery_zones'],
                    'order' => 10,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-percent',
                    'title' => __('sidebar.pharmacy_commissions'),
                    'route' => 'backend.pharmacy-commissions.index',
                    'active' => 'app/pharmacy-commissions',
                    'permission' => ['view_pharmacy_commission'],
                    'order' => 11,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-currency-circle-dollar',
                    'title' => __('sidebar.pharmacy_earnings'),
                    'route' => 'backend.pharmacy-earnings.index',
                    'active' => 'app/pharmacy-earnings',
                    'permission' => ['view_pharmacy_earning'],
                    'order' => 12,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-prescription',
                    'title' => __('sidebar.pharmacy_prescriptions'),
                    'route' => 'backend.pharmacy-prescriptions.index',
                    'active' => 'app/pharmacy-prescriptions',
                    'permission' => ['view_pharmacy_prescriptions'],
                    'order' => 13,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-ticket',
                    'title' => __('sidebar.pharmacy_coupons'),
                    'route' => 'backend.pharmacy-coupons.index',
                    'active' => 'app/pharmacy-coupons',
                    'permission' => ['view_pharmacy_coupons'],
                    'order' => 14,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-arrow-counter-clockwise',
                    'title' => __('sidebar.pharmacy_refunds'),
                    'route' => 'backend.pharmacy-refunds.index',
                    'active' => 'app/pharmacy-refunds',
                    'permission' => ['view_pharmacy_refunds'],
                    'order' => 15,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-bell-ringing',
                    'title' => __('sidebar.pharmacy_alerts'),
                    'route' => 'backend.pharmacy-panel-alerts.index',
                    'active' => 'app/pharmacy-panel/alerts',
                    'permission' => ['view_pharmacy_notifications'],
                    'order' => 16,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-chart-bar',
                    'title' => __('sidebar.pharmacy_reports'),
                    'route' => 'backend.pharmacy-reports.index',
                    'active' => 'app/pharmacy-reports',
                    'permission' => ['view_pharmacy_reports'],
                    'order' => 17,
                ]);

                $this->childMain($pharmacy, [
                    'icon' => 'ph ph-clipboard-text',
                    'title' => __('sidebar.pharmacy_audit'),
                    'route' => 'backend.pharmacy-audit.index',
                    'active' => 'app/pharmacy-audit',
                    'permission' => ['view_pharmacy_audit'],
                    'order' => 18,
                ]);
            }

            // System Static
            $permissionsToCheck = [
                'view_setting',
                'add_setting',
                'edit_setting',
                'delete_setting',
                'view_location',
                'view_city',
                'view_state',
                'view_country',
                'view_pages',
                'view_notification',
                'view_notification_template',
                'view_app_banner',
                'view_constant',
                'view_permission',
                'view_promotions',
                'view_vital',
                'view_subscription',
                'view_my_account',
                'view_subscription_list',
                'view_plan_list',
                'view_plan_limitation',
                'view_backup'
            ];

            if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission))) {
                $this->staticMenu($menu, ['title' => __('sidebar.system'), 'order' => 0]);
            }
            if (multiVendor() == "1" && auth()->user()->hasRole(['admin', 'demo_admin'])) {
                $this->mainRoute($menu, [
                    'icon' => 'ph ph-airplay',
                    'title' => __('sidebar.system_service'),
                    'route' => 'backend.system-service.index',
                    'active' => ['app/system-service'],
                    'permission' => ['view_system_service'],
                    'order' => 0,
                ]);
            }

            // --- INCIDENCE REPORT ---
            $this->mainRoute($menu, [
                'icon' => 'ph ph-map-pin-plus',
                'title' => __('messages.incidence'),
                'route' => 'backend.incidence.index',
                'active' => 'app/incidence',
                'permission' => ['view_incidence_report'],
                'order' => 0,
            ]);

            // --- BLOG ---
            $this->mainRoute($menu, [
                'icon' => 'ph ph-pencil-simple',
                'title' => __('sidebar.blog'),
                'route' => 'backend.blog.index',
                'active' => ['app/blog'],
                'permission' => ['view_blog'],
                'order' => 0,
            ]);

            $location = $this->parentMenu($menu, [
                'icon' => 'ph ph-map-pin-line',
                'title' => __('sidebar.location'),
                'nickname' => 'location',
                'permission' => ['view_location'],
                'order' => 0,
            ]);

            $this->childMain($location, [
                'title' => __('sidebar.city'),
                'route' => 'backend.city.index',
                'active' => 'app/city',
                'shortTitle' => 'CT',
                'permission' => ['view_city'],
                'order' => 0,
                'icon' => 'ph ph-city',
            ]);
            $this->childMain($location, [
                'title' => __('sidebar.state'),
                'route' => 'backend.state.index',
                'shortTitle' => 'CT',
                'permission' => ['view_state'],
                'active' => ['app/state'],
                'order' => 0,
                'icon' => 'ph ph-map-trifold',
            ]);
            $this->childMain($location, [
                'title' => __('sidebar.country'),
                'route' => 'backend.country.index',
                'shortTitle' => 'CT',
                'active' => ['app/country'],
                'permission' => ['view_country'],
                'order' => 0,
                'icon' => 'ph ph-globe-hemisphere-east',
            ]);

            $this->mainRoute($menu, [
                'icon' => ' ph ph-note',
                'title' => __('page.title'),
                'route' => ['backend.pages.index'],
                'active' => ['app/pages'],
                'permission' => ['view_pages'],
                'order' => 0,
            ]);

            // --- APP BANNER ---
            $this->mainRoute($menu, [
                'icon' => 'ph ph-exam',
                'title' => __('sidebar.app_banner'),
                'route' => 'backend.app-banners.index',
                'active' => 'app/app-banners',
                'permission' => ['view_app_banner'],
                'order' => 0,
            ]);

            // --- FAQ ---
            $this->mainRoute($menu, [
                'icon' => 'ph ph-question',
                'title' => __('messages.faq_title'),
                'route' => 'backend.faqs.index',
                'active' => ['app/faqs'],
                'permission' => ['view_faq'],
                'order' => 0,
            ]);

            // --- SPECIALIZATIONS ---
            $this->mainRoute($menu, [
                'icon' => 'ph ph-stethoscope',
                'title' => __('sidebar.lbl_specializations'),
                'route' => 'backend.specializations.index',
                'active' => ['app/specializations'],
                'permission' => ['view_specialization'],
                'order' => 0,
            ]);

            // --- CUSTOM FORMS ---
            if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
                $custom_form = $this->parentMenu($menu, [
                    'icon' => 'ph ph-table',
                    'title' => __('messages.customforms'),
                    'nickname' => 'custom_form',
                    'permission' => ['view_notification'],
                    'order' => 0,
                ]);
                $this->childMain($custom_form, [
                    'icon' => 'ph ph-list-bullets',
                    'title' => __('messages.customforms_list'),
                    'route' => 'backend.custom-form.index',
                    'shortTitle' => 'Li',
                    'active' => 'app/settings#/customform',
                    'permission' => ['view_notification'],
                    'order' => 0,
                ]);
            }

            // --- NOTIFICATION ---
            $notification = $this->parentMenu($menu, [
                'icon' => 'ph ph-bell',
                'title' => __('notification.title'),
                'nickname' => 'notifications',
                'permission' => ['view_notification'],
                'order' => 0,
            ]);

            $this->childMain($notification, [
                'icon' => 'ph ph-list-bullets',
                'title' => __('notification.list'),
                'route' => 'backend.notifications.index',
                'shortTitle' => 'Li',
                'active' => 'app/notifications',
                'permission' => ['view_notification'],
                'order' => 0,
            ]);
            if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
                $this->childMain($notification, [
                    'icon' => 'ph ph-layout',
                    'title' => __('notification.template'),
                    'route' => 'backend.notification-templates.index',
                    'shortTitle' => 'TE',
                    'active' => 'app/notification-templates*',
                    'permission' => ['view_notification_template'],
                    'order' => 0,
                ]);
            }

            // --- SETTING ---
            $this->mainRoute($menu, [
                'icon' => 'ph ph-gear-six',
                'title' => __('menu.settings'),
                'route' => 'backend.settings',
                'active' => 'app/settings',
                'permission' => ['view_setting'],
                'order' => 0,
            ]);

            // --- FRONTEND SETTING ---
            $this->mainRoute($menu, [
                'icon' => 'ph ph-layout',
                'title' =>  __('sidebar.frontend_setting'),
                'route' => 'frontend_setting.index',
                'active' => ['app/frontend_setting'],
                'permission' => 'view_frontend_setting',
                'order' => 0,
            ]);

            // --- LOG/BACKUPS ---
            $location11 = $this->parentMenu($menu, [
                'icon' => 'ph ph-note',
                'title' => __('sidebar.log'),
                'nickname' => 'log',
                'permission' => ['view_backup'],
                'order' => 0,
            ]);
            $this->childMain($location11, [
                'title' => __('sidebar.backups'),
                'route' => 'backend.backups.index',
                'active' => 'app/backups',
                'shortTitle' => '',
                'permission' => ['view_backup'],
                'order' => 0,
                'icon' => 'ph ph-note',
            ]);
            $this->childMain($location11, [
                'title' => __('sidebar.activity_logs'),
                'route' => 'backend.backups.logs',
                'active' => 'app/backups/logs',
                'shortTitle' => '',
                'permission' => ['view_backup'],
                'order' => 0,
                'icon' => 'ph ph-note',
            ]);

            // --- ACCESS CONTROL ---
            if (auth()->user()->hasRole('admin')) {

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-devices',
                    'title' => __('sidebar.access_control'),
                    'route' => 'backend.permission-role.list',
                    'active' => ['app/permission-role'],
                    'order' => 10,
                ]);
            }

            // Access Permission Check
            $menu->filter(function ($item) {
                if ($item->data('permission')) {
                    if (auth()->check()) {
                        if (\Auth::getDefaultDriver() == 'admin') {
                            return true;
                        }
                        if (auth()->user()->hasRole('admin')) {
                            return true;
                        }
                        if (auth()->user()->hasAnyPermission($item->data('permission'), \Auth::getDefaultDriver())) {
                            return true;
                        }
                    }

                    return false;
                } else {
                    return true;
                }
            });
            // Set Active Menu
            $menu->filter(function ($item) {
                if ($item->activematches) {
                    $activematches = (is_string($item->activematches)) ? [$item->activematches] : $item->activematches;
                    foreach ($activematches as $pattern) {
                        if (request()->is($pattern)) {
                            $item->active();
                            $item->link->active();
                            if ($item->hasParent()) {
                                $item->parent()->active();
                            }
                        }
                    }
                }

                return true;
            });
        })->sortBy('order');
    }
}
