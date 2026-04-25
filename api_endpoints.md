# KiviCare API Endpoints

Auto-generated from static route parsing. Base URL: `{{base_url}}` (default `http://localhost:8000`).

## Appointment

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| POST | `/api/save-booking` | AppointmentsController@store | auth:sanctum |
| POST | `/api/update-booking/{id}` | AppointmentsController@update | auth:sanctum |
| GET | `/api/appointment-list` | AppointmentsController@appointmentList | auth:sanctum |
| GET | `/api/appointment-detail` | AppointmentsController@appointmentDetails | auth:sanctum |
| POST | `/api/save-payment` | AppointmentsController@savePayment | auth:sanctum |
| POST | `/api/update-status/{id}` | AppointmentsController@updateStatus | auth:sanctum |
| POST | `/api/reschedule-booking` | AppointmentsController@rescheduleBooking | auth:sanctum |
| POST | `/api/cancel-appointment/{id}` | AppointmentsController@cancelAppointment | auth:sanctum |
| GET | `/api/encounter-list` | PatientEncounterController@encounterList | auth:sanctum |
| POST | `/api/save-encounter` | PatientEncounterController@store | auth:sanctum |
| POST | `/api/update-encounter/{id}` | PatientEncounterController@update | auth:sanctum |
| POST | `/api/delete-encounter/{id}` | PatientEncounterController@destroy | auth:sanctum |
| GET | `/api/encounter-details` | PatientEncounterController@encounterList | auth:sanctum |
| GET | `/api/download-encounter-invoice` | PatientEncounterController@encounterInvoice | auth:sanctum |
| GET | `/api/download-prescription` | PatientEncounterController@downloadPrescription | auth:sanctum |
| GET | `/api/encounter-dropdown-list` | EncounterDashboardController@encounterDropdownList | auth:sanctum |
| GET | `/api/get-medical-report` | EncounterDashboardController@GetMedicalReport | auth:sanctum |
| POST | `/api/save-medical-report` | PatientEncounterController@saveMedicalReport | auth:sanctum |
| POST | `/api/update-medical-report/{id}` | PatientEncounterController@updateMedicalReport | auth:sanctum |
| GET | `/api/delete-medical-report/{id}` | PatientEncounterController@deleteMedicalReport | auth:sanctum |
| GET | `/api/get-prescription` | EncounterDashboardController@GetPrescription | auth:sanctum |
| POST | `/api/save-prescription` | PatientEncounterController@saveMedicalReport | auth:sanctum |
| POST | `/api/update-prescription/{id}` | PatientEncounterController@updateMedicalReport | auth:sanctum |
| GET | `/api/delete-prescription/{id}` | PatientEncounterController@deleteMedicalReport | auth:sanctum |
| POST | `/api/save-encounter-dashboard` | EncounterDashboardController@saveEncounterDashboard | auth:sanctum |
| GET | `/api/encounter-dashboard-detail` | EncounterDashboardController@encounterDashboardDetail | auth:sanctum |
| GET | `/api/encounter-service-detail` | EncounterDashboardController@encounterServiceDetails | auth:sanctum |
| GET | `/api/download_invoice` | ClinicAppointmentController@downloadPDf | auth:sanctum |
| POST | `/api/save-bodychart` | EncounterDashboardController@saveBodychart | auth:sanctum |
| POST | `/api/update-bodychart/{id}` | EncounterDashboardController@updateBodychart | auth:sanctum |
| POST | `/api/delete-bodychart/{id}` | EncounterDashboardController@deleteBodychart | auth:sanctum |
| GET | `/api/bodychart-list` | EncounterDashboardController@bodyChartList | auth:sanctum |
| POST | `/api/save-billing-details` | BillingRecordController@saveBillingDetails | auth:sanctum |
| GET | `/api/billing-list` | EncounterDashboardController@billingList | auth:sanctum |
| GET | `/api/billing-record-detail` | EncounterDashboardController@billingList | auth:sanctum |
| GET | `/api/get-revenue-chart-data` | AppointmentsController@getRevenuechartData | auth:sanctum |
| POST | `/api/save-soap/{id}` | ClinicAppointmentController@appointment_patient | auth:sanctum |
| GET | `/api/get-soap/{id}` | ClinicAppointmentController@appointment_patient_data | auth:sanctum |
| PUT | `/api/save-appointment_patient/{id}` | ClinicAppointmentController@appointment_patient | auth:sanctum |
| GET | `/api/{id}/get_appointment_patient_data` | ClinicAppointmentController@appointment_patient_data | auth:sanctum |
| POST | `/api/save-billing-items` | BillingRecordController@saveBillingItems | auth:sanctum |
| GET | `/api/billing-item-list` | BillingRecordController@billing_item_list | auth:sanctum |
| GET | `/api/billing-item-details` | BillingRecordController@billing_item_detail | auth:sanctum |
| GET | `/api/edit-billing-item/{id}` | BillingRecordController@editBillingItem | auth:sanctum |
| POST | `/api/delete-billing-item/{id}` | BillingRecordController@deleteBillingItem | auth:sanctum |

## Blog

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/blog` | Unknown@? | public |

## Branch

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/branches` | BranchesApiController@index | public |
| GET | `/api/v1/branches/{id}` | BranchesApiController@show | public |
| POST | `/api/v1/branches` | BranchesApiController@store | public |
| PUT | `/api/v1/branches/{id}` | BranchesApiController@update | public |
| DELETE | `/api/v1/branches/{id}` | BranchesApiController@destroy | public |
| POST | `/api/v1/branches/{id}/update-status` | BranchesApiController@updateStatus | public |

## Clinic

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/get-service-list` | ClinicesController@ServiceList | public |
| GET | `/api/get-doctor-list` | DoctorController@DoctorList | public |
| GET | `/api/get-category-list` | ClinicesController@CategoryList | public |
| GET | `/api/get-clinic-list` | ClinicesController@ClinicList | public |
| GET | `/api/get-clinic-gallery` | ClinicesController@ClinicGallery | public |
| GET | `/api/get-system-service` | ClinicesController@SystemServiceList | public |
| GET | `/api/get-service-details` | ClinicesController@ServiceList | public |
| GET | `/api/get-clinic-details` | ClinicesController@ClinicList | public |
| GET | `/api/get-doctor-details` | DoctorController@DoctorList | public |
| GET | `/api/get-rating` | DoctorController@getRating | public |
| GET | `/api/get-time-slots` | DoctorController@availableSlot | public |
| GET | `/api/independent-doctors` | IndependentBookingApiController@doctors | public |
| GET | `/api/independent-doctors/{id}/services` | IndependentBookingApiController@services | public |
| GET | `/api/call-doctors` | CallBookingApiController@doctors | public |
| GET | `/api/call-doctors/{id}/services` | CallBookingApiController@services | public |
| GET | `/api/doctors/search` | DoctorSearchApiController@search | public |
| GET | `/api/clinics/search` | ClinicSearchApiController@search | public |
| GET | `/api/get-categories` | ClinicesController@CategoryList | auth:sanctum |
| POST | `/api/save-clinic-category` | ClinicsCategoryController@store | auth:sanctum |
| POST | `/api/update-clinic-category/{id}` | ClinicsCategoryController@update | auth:sanctum |
| POST | `/api/delete-clinic-category/{id}` | ClinicsCategoryController@destroy | auth:sanctum |
| GET | `/api/get-services` | ClinicesController@ServiceList | auth:sanctum |
| POST | `/api/save-clinic-service` | ClinicsServiceController@store | auth:sanctum |
| POST | `/api/update-clinic-service/{id}` | ClinicsServiceController@update | auth:sanctum |
| POST | `/api/delete-clinic-service/{id}` | ClinicsServiceController@destroy | auth:sanctum |
| GET | `/api/service-detail` | ClinicsServiceController@ServiceDetails | auth:sanctum |
| GET | `/api/get-clinics` | ClinicesController@ClinicList | auth:sanctum |
| POST | `/api/save-clinic` | ClinicesController@store | auth:sanctum |
| POST | `/api/update-clinic/{id}` | ClinicesController@update | auth:sanctum |
| POST | `/api/delete-clinic/{id}` | ClinicesController@destroy | auth:sanctum |
| GET | `/api/clinic-session` | ClinicesController@clinicSession | auth:sanctum |
| POST | `/api/save-clinic-gallery` | ClinicesController@uploadGalleryImages | auth:sanctum |
| POST | `/api/save-clinic-session` | ClinicSessionController@store | auth:sanctum |
| GET | `/api/get-clinic-session-list` | ClinicesController@clinicSessionList | auth:sanctum |
| GET | `/api/get-doctors` | DoctorController@DoctorList | auth:sanctum |
| GET | `/api/doctor-details` | DoctorController@DoctorList | auth:sanctum |
| POST | `/api/save-doctor` | DoctorController@store | auth:sanctum |
| POST | `/api/update-doctor/{id}` | DoctorController@update | auth:sanctum |
| POST | `/api/delete-doctor/{id}` | DoctorController@destroy | auth:sanctum |
| GET | `/api/get-doctors-session_list` | DoctorController@DoctorSessionList | auth:sanctum |
| POST | `/api/save-doctor-session/{id}` | DoctorSessionController@update | auth:sanctum |
| GET | `/api/doctor-commission-list` | DoctorController@doctorCommissionList | auth:sanctum |
| GET | `/api/doctor-payout-history` | DoctorController@doctorPayoutHistory | auth:sanctum |
| GET | `/api/doctor-session` | DoctorController@doctorSession | auth:sanctum |
| POST | `/api/assign-doctor` | DoctorController@assignDoctor | auth:sanctum |
| POST | `/api/save-rating` | DoctorController@saveRating | auth:sanctum |
| POST | `/api/delete-rating` | DoctorController@deleteRating | auth:sanctum |
| GET | `/api/specialization-list` | ClinicesController@specializationList | auth:sanctum |
| GET | `/api/get-patients` | DoctorController@patientList | auth:sanctum |
| GET | `/api/get-patient-details` | DoctorController@patientList | auth:sanctum |
| POST | `/api/save-receptionist` | ReceptionistController@store | auth:sanctum |
| GET | `/api/get-receptionists` | DoctorController@receptionistList | auth:sanctum |
| GET | `/api/get-receptionist-details` | DoctorController@receptionistList | auth:sanctum |
| POST | `/api/update-receptionist/{id}` | ReceptionistController@update | auth:sanctum |
| POST | `/api/delete-receptionist/{id}` | ReceptionistController@destroy | auth:sanctum |
| POST | `/api/independent-doctors/{id}/slots` | IndependentBookingApiController@slots | auth:sanctum |
| POST | `/api/independent-booking` | IndependentBookingApiController@store | auth:sanctum |
| POST | `/api/call-doctors/{id}/slots` | CallBookingApiController@slots | auth:sanctum |
| POST | `/api/call-booking` | CallBookingApiController@store | auth:sanctum |

## Commission

_No API routes found._

## Constant

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/constants` | Closure@handle | public |

## Core

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/user-detail` | AuthController@userDetails | public |
| GET | `/api/user` | Closure@handle | public |
| POST | `/api/register` | Unknown@? | public |
| POST | `/api/login` | Unknown@? | public |
| POST | `/api/generate-qr` | Unknown@? | public |
| POST | `/api/verify` | Unknown@? | public |
| POST | `/api/otp-resend` | Unknown@? | public |
| POST | `/api/otp-verify` | Unknown@? | public |
| POST | `/api/social-login` | Unknown@? | public |
| POST | `/api/forgot-password` | Unknown@? | public |
| GET | `/api/logout` | Unknown@? | public |
| POST | `/api/check-contact` | AuthController@checkContact | public |
| POST | `/api/check-email` | AuthController@checkEmail | public |
| POST | `/api/store-access-token` | SettingController@storeToken | public |
| POST | `/api/token-revoke` | SettingController@revokeToken | public |
| GET | `/api/dashboard-detail` | DashboardController@DashboardDetail | public |
| GET | `/api/setting` | SettingController@index | public |
| POST | `/api/setting` | SettingController@store | public |
| GET | `/api/setting/{id}` | SettingController@show | public |
| PUT | `/api/setting/{id}` | SettingController@update | public |
| DELETE | `/api/setting/{id}` | SettingController@destroy | public |
| GET | `/api/app-configuration` | SettingController@appConfiguraton | public |
| GET | `/api/translations` | SettingController@translations | public |
| GET | `/api/download-json` | SettingController@downloadJson | public |

## Currency

_No API routes found._

## CustomField

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/customfield` | Closure@handle | public |

## CustomForm

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/get-customform-list` | CustomFormsController@getCustomFormList | public |
| GET | `/api/get-customform-data-list` | CustomFormsController@getCustomFormDataList | public |

## Customer

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/profile-details` | CustomersController@profileDetails | auth:sanctum |
| POST | `/api/add-patient-member` | CustomersController@addOtherMember | auth:sanctum |
| GET | `/api/other-members-list` | CustomersController@getOtherMembersList | auth:sanctum |
| POST | `/api/delete-other_member/{id}` | CustomersController@deleteOtherMember | auth:sanctum |

## DoctorVisit

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| POST | `/api/admin/doctor-visit/requests/v1/doctor-visit/requests` | DoctorVisitApiController@store | public |
| GET | `/api/admin/doctor-visit/requests/v1/doctor-visit/requests` | DoctorVisitApiController@index | public |
| GET | `/api/admin/doctor-visit/requests/v1/doctor-visit/requests/{reference}` | DoctorVisitApiController@show | public |
| GET | `/api/admin/doctor-visit/requests/v1/admin/doctor-visit/requests` | AdminDoctorVisitApiController@index | public |
| PUT | `/api/admin/doctor-visit/requests/v1/admin/doctor-visit/requests/{reference}/status` | AdminDoctorVisitApiController@updateStatus | public |
| PUT | `/api/admin/doctor-visit/requests/v1/admin/doctor-visit/requests/{reference}/assign-doctor` | AdminDoctorVisitApiController@assignDoctor | public |
| POST | `/api/admin/doctor-visit/requests/v1/admin/doctor-visit/requests/{reference}/notes` | AdminDoctorVisitApiController@addNote | public |

## Earning

_No API routes found._

## FAQ

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/faq` | Unknown@? | public |

## Frontend

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/frontend` | Unknown@? | public |

## FrontendSetting

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/frontendsetting` | Unknown@? | public |

## HomeHealthcare

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/home-healthcare/search` | HomeHealthcareSearchApiController@search | public |

## Installer

_No API routes found._

## IntensiveCare

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/hospitals` | HospitalsApiController@index | public |
| GET | `/api/v1/hospitals/{id}` | HospitalsApiController@show | public |
| GET | `/api/v1/icu-departments` | HospitalsApiController@departments | public |
| GET | `/api/v1/icu-admission-requests` | IcuAdmissionRequestsApiController@index | public |
| POST | `/api/v1/icu-admission-requests` | IcuAdmissionRequestsApiController@store | public |
| GET | `/api/v1/icu-admission-requests/{id}` | IcuAdmissionRequestsApiController@show | public |
| POST | `/api/v1/icu-admission-requests/{id}/cancel` | IcuAdmissionRequestsApiController@cancel | public |
| GET | `/api/v1/icu-commissions` | IcuCommissionsApiController@index | public |
| GET | `/api/v1/icu-earnings` | IcuEarningsApiController@index | public |

## Language

_No API routes found._

## Location

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/governorates` | LocationApiController@governorates | public |
| GET | `/api/cities` | LocationApiController@cities | public |

## Logistic

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/get-logisticzone-list` | LogisticZoneController@logisticzoneList | public |

## MultiVendor

_No API routes found._

## NotificationTemplate

_No API routes found._

## Offer

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/offers/public` | PublicOfferApiController@publicIndex | public |
| GET | `/api/v1/offers/public/{slug}` | PublicOfferApiController@publicShow | public |
| GET | `/api/v1/services/{serviceId}/offers` | PublicOfferApiController@serviceOffers | public |
| POST | `/api/v1/offers/validate-coupon` | PublicOfferApiController@validateCoupon | auth:sanctum |
| POST | `/api/v1/offers/calculate-discount` | PublicOfferApiController@calculateDiscount | auth:sanctum |
| POST | `/api/v1/offers/apply` | PublicOfferApiController@apply | auth:sanctum |
| POST | `/api/v1/offers/remove` | PublicOfferApiController@remove | auth:sanctum |
| GET | `/api/v1/offers` | OfferApiController@index | auth:sanctum |
| POST | `/api/v1/offers` | OfferApiController@store | auth:sanctum |
| POST | `/api/v1/offers/bulk-action` | OfferApiController@bulkAction | auth:sanctum |
| GET | `/api/v1/offers/usage-logs/all` | OfferApiController@allUsageLogs | auth:sanctum |
| GET | `/api/v1/offers/{id}` | OfferApiController@show | auth:sanctum |
| PUT | `/api/v1/offers/{id}` | OfferApiController@update | auth:sanctum |
| DELETE | `/api/v1/offers/{id}` | OfferApiController@destroy | auth:sanctum |
| POST | `/api/v1/offers/{id}/update-status` | OfferApiController@updateStatus | auth:sanctum |
| GET | `/api/v1/offers/{id}/usage-logs` | OfferApiController@usageLogs | auth:sanctum |
| POST | `/api/v1/offers/{id}/duplicate` | OfferApiController@duplicate | auth:sanctum |

## Page

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/page-list` | PageController@pageList | public |

## Pharmacy

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/v1/pharmacy/categories` | CategoryApiController@index | auth:sanctum |
| GET | `/api/v1/pharmacy/categories/{id}/children` | CategoryApiController@children | auth:sanctum |
| GET | `/api/v1/pharmacy/products` | ProductApiController@index | auth:sanctum |
| GET | `/api/v1/pharmacy/products/{id}` | ProductApiController@show | auth:sanctum |
| GET | `/api/v1/pharmacy/filters/brands` | FilterApiController@brands | auth:sanctum |
| GET | `/api/v1/pharmacy/filters/product-types` | FilterApiController@productTypes | auth:sanctum |
| GET | `/api/v1/pharmacy/cart` | CartApiController@index | auth:sanctum |
| POST | `/api/v1/pharmacy/cart/items` | CartApiController@store | auth:sanctum |
| PATCH | `/api/v1/pharmacy/cart/items/{id}` | CartApiController@update | auth:sanctum |
| DELETE | `/api/v1/pharmacy/cart/items/{id}` | CartApiController@destroy | auth:sanctum |
| GET | `/api/v1/pharmacy/cart/available-pharmacies` | PharmacyMatchingApiController@index | auth:sanctum |
| POST | `/api/v1/pharmacy/orders` | OrderApiController@store | auth:sanctum |
| GET | `/api/v1/pharmacy/orders` | OrderApiController@index | auth:sanctum |
| GET | `/api/v1/pharmacy/orders/{id}` | OrderApiController@show | auth:sanctum |
| POST | `/api/v1/pharmacy/orders/{id}/cancel` | OrderApiController@cancel | auth:sanctum |
| POST | `/api/v1/pharmacy/prescriptions` | PrescriptionApiController@store | auth:sanctum |
| GET | `/api/v1/pharmacy/prescriptions` | PrescriptionApiController@index | auth:sanctum |
| GET | `/api/v1/pharmacy/prescriptions/{id}` | PrescriptionApiController@show | auth:sanctum |
| POST | `/api/v1/pharmacy/coupons/validate` | CouponApiController@validate | auth:sanctum |
| POST | `/api/v1/pharmacy/orders/{id}/refund` | RefundApiController@store | auth:sanctum |
| GET | `/api/v1/pharmacy/refunds` | RefundApiController@index | auth:sanctum |
| GET | `/api/v1/pharmacy/refunds/{id}` | RefundApiController@show | auth:sanctum |
| GET | `/api/v1/pharmacy/notifications` | NotificationApiController@index | auth:sanctum |
| POST | `/api/v1/pharmacy/notifications/{id}/read` | NotificationApiController@markAsRead | auth:sanctum |
| POST | `/api/v1/pharmacy/notifications/read-all` | NotificationApiController@markAllAsRead | auth:sanctum |
| GET | `/api/v1/pharmacy/notifications/unread-count` | NotificationApiController@unreadCount | auth:sanctum |

## Product

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/get-product-category` | CategoryController@categoryList | public |
| GET | `/api/get-product-list` | ProductsController@ProductList | public |
| GET | `/api/product_detail` | ProductsController@product_detail | public |
| GET | `/api/product-brand` | BrandsController@product_brand | public |
| GET | `/api/product-unit` | UnitsController@product_unit | public |
| GET | `/api/product-dashboard` | DashboardController@productDashboard | public |
| GET | `/api/get-review-list` | ReviewController@getReviewList | public |
| GET | `/api/get-order-status-list` | OrdersController@statusList | public |
| POST | `/api/add-to-cart` | CartController@store | auth:sanctum |
| GET | `/api/get-cart-list` | CartController@getCartList | auth:sanctum |
| GET | `/api/remove-cart` | CartController@removeCart | auth:sanctum |
| POST | `/api/update-cart` | CartController@UpdateCart | auth:sanctum |
| POST | `/api/add-to-wishlist` | WishListController@store | auth:sanctum |
| GET | `/api/get-wishlist` | WishListController@getWishList | auth:sanctum |
| GET | `/api/remove-wishlist` | WishListController@removeWishList | auth:sanctum |
| POST | `/api/add-review` | ReviewController@store | auth:sanctum |
| GET | `/api/remove-review` | ReviewController@removeReview | auth:sanctum |
| POST | `/api/update-review` | ReviewController@UpdateReview | auth:sanctum |
| POST | `/api/place-order` | OrdersController@store | auth:sanctum |
| GET | `/api/get-order-list` | OrdersController@orderList | auth:sanctum |
| GET | `/api/cancle-order` | OrdersController@cancleOrder | auth:sanctum |
| GET | `/api/get-order-details` | OrdersController@orderDetails | auth:sanctum |

## Promotion

_No API routes found._

## QuickBooking

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/quick-booking/service-provider-list` | QuickBookingsController@service_provider_list | public |
| GET | `/api/quick-booking/slot-time-list` | QuickBookingsController@slot_time_list | public |
| GET | `/api/quick-booking/services-list` | QuickBookingsController@services_list | public |
| POST | `/api/quick-booking/store` | QuickBookingsController@create_booking | public |
| GET | `/api/quick-booking/system-service-list` | QuickBookingsController@system_service_list | public |
| GET | `/api/quick-booking/clinic-list` | QuickBookingsController@clinic_list | public |
| GET | `/api/quick-booking/doctor-list` | QuickBookingsController@doctor_list | public |
| POST | `/api/quick-booking/verify_customer` | QuickBookingsController@verify_customer | public |

## RadiologyLab

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/labs/search` | LabSearchApiController@search | public |
| GET | `/api/radiology/search` | RadiologySearchApiController@search | public |
| GET | `/api/v1/lab-tests` | LabTestsApiController@index | public |
| GET | `/api/v1/lab-tests/{id}` | LabTestsApiController@show | public |
| GET | `/api/v1/lab-test-categories` | LabTestsApiController@categories | public |
| GET | `/api/v1/test-orders` | TestOrdersApiController@index | public |
| POST | `/api/v1/test-orders` | TestOrdersApiController@store | public |
| GET | `/api/v1/test-orders/{id}` | TestOrdersApiController@show | public |
| POST | `/api/v1/test-orders/{id}/cancel` | TestOrdersApiController@cancel | public |
| GET | `/api/v1/test-orders/{id}/report/download` | TestOrdersApiController@downloadReport | public |
| GET | `/api/v1/facility-bookings/labs/{labId}/slots` | FacilityBookingApiController@labSlots | public |
| GET | `/api/v1/facility-bookings/radiology-centers/{centerId}/slots` | FacilityBookingApiController@radiologyCenterSlots | public |
| POST | `/api/v1/facility-bookings` | FacilityBookingApiController@store | public |
| GET | `/api/v1/facility-bookings` | FacilityBookingApiController@index | public |
| GET | `/api/v1/facility-bookings/{id}` | FacilityBookingApiController@show | public |
| POST | `/api/v1/facility-bookings/{id}/cancel` | FacilityBookingApiController@cancel | public |
| GET | `/api/v1/admin/facility-bookings` | AdminFacilityBookingApiController@index | public |
| POST | `/api/v1/admin/facility-bookings/{id}/confirm` | AdminFacilityBookingApiController@confirm | public |
| POST | `/api/v1/admin/facility-bookings/{id}/cancel` | AdminFacilityBookingApiController@cancel | public |
| POST | `/api/v1/admin/facility-bookings/{id}/status` | AdminFacilityBookingApiController@updateStatus | public |

## RequestNurse

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/nurses/search` | NurseSearchApiController@search | public |
| GET | `/api/nurse-requests` | NurseRequestApiController@index | public |
| POST | `/api/nurse-requests` | NurseRequestApiController@store | public |
| GET | `/api/nurse-requests/{id}` | NurseRequestApiController@show | public |
| PUT | `/api/nurse-requests/{id}` | NurseRequestApiController@update | public |
| DELETE | `/api/nurse-requests/{id}` | NurseRequestApiController@destroy | public |

## RequestService

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/get-request-service` | RequestServicesController@getRequestList | auth:sanctum |
| POST | `/api/save-request-service` | RequestServicesController@store | auth:sanctum |

## Service

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/service-list` | ServiceController@serviceList | public |
| GET | `/api/service` | ServiceController@index | public |
| POST | `/api/service` | ServiceController@store | public |
| GET | `/api/service/{id}` | ServiceController@show | public |
| PUT | `/api/service/{id}` | ServiceController@update | public |
| DELETE | `/api/service/{id}` | ServiceController@destroy | public |

## Slider

_No API routes found._

## Subscriptions

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/planlimitation` | PlanLimitationController@index | public |
| POST | `/api/planlimitation` | PlanLimitationController@store | public |
| GET | `/api/planlimitation/{id}` | PlanLimitationController@show | public |
| PUT | `/api/planlimitation/{id}` | PlanLimitationController@update | public |
| DELETE | `/api/planlimitation/{id}` | PlanLimitationController@destroy | public |

## Tag

_No API routes found._

## Tax

_No API routes found._

## Tip

_No API routes found._

## Vital

_No API routes found._

## Wallet

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/get-patient-wallet` | walletcontroller@getPatientWallet | auth:sanctum |
| GET | `/api/get-wallet-history` | walletcontroller@getWalletHistory | auth:sanctum |

## World

| Method | Endpoint | Controller@method | Auth |
|--------|----------|-------------------|------|
| GET | `/api/country-list` | CountryController@index_list | auth:sanctum |
| GET | `/api/state-list` | StateController@index_list | auth:sanctum |
| GET | `/api/city-list` | CityController@index_list | auth:sanctum |

