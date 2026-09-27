<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\FrontendController;
use Modules\Frontend\Http\Controllers\CategoryController;
use Modules\Frontend\Http\Controllers\ServiceController;
use Modules\Frontend\Http\Controllers\ClinicController;
use Modules\Frontend\Http\Controllers\DoctorController;
use Modules\Frontend\Http\Controllers\BlogController;
use Modules\Frontend\Http\Controllers\AppointmentController;
use Modules\Frontend\Http\Controllers\NurseRequestFrontController;
use Modules\Frontend\Http\Controllers\LabTestFrontController;
use Modules\Frontend\Http\Controllers\IcuFrontController;
use Modules\Frontend\Http\Controllers\IndependentBookingController;
use Modules\Frontend\Http\Controllers\CallBookingController;
use Modules\Frontend\Http\Controllers\TestOrderPaymentController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Backend\NotificationsController;
use Modules\Frontend\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [UserController::class, 'login'])->name('login-page');
Route::get('/register', [UserController::class, 'registration'])->name('register-page');
Route::get('/forgot-password', [UserController::class, 'forgotpassword'])->name('forgot-password');
Route::post('forgot-password', [UserController::class, 'store'])->name('password.emailuser');
Route::get('reset-password/{token}', [UserController::class, 'create'])->name('password.reset');
Route::post('reset-password', [UserController::class, 'storepassword'])->name('password.update');
Route::post('/forgot-password-link', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::controller(UserController::class)->group(function () {
    Route::post('user-login', 'loginstore')->name('user-login');

    // Login with Google
    Route::get('/auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('/auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');
});
Route::get('language/{language}', [LanguageController::class, 'switch'])->name('language.switch');

Route::middleware(['check.header.menu'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'categoriesList'])->name('categories');
    Route::get('/services', [ServiceController::class, 'servicesList'])->name('services');
    Route::get('/clinics', [ClinicController::class, 'clinicsList'])->name('clinics');
    Route::get('/doctors', [DoctorController::class, 'doctorsList'])->name('doctors');

    ### incidence report route front end
    Route::get('/incidence', [FrontendController::class, 'incidenceReport'])->name('incidence.index');
    Route::get('incidence_index_data', [FrontendController::class, 'index_data'])->name('incidence.index_data');
    Route::post('/incidence-save', [FrontendController::class, 'incidenceSave'])->name('incidence.store');
    Route::post('/changestatus', [FrontendController::class, 'changeStatus'])->name('changestatus');
 
    
    Route::get('/service-details/{id}', [ServiceController::class, 'serviceDetails'])->name('service-details');
    Route::get('/clinic-details/{id}', [ClinicController::class, 'clinicDetails'])->name('clinic-details');
    Route::get('/doctor-details/{id}', [DoctorController::class, 'doctorDetails'])->name('doctor-details');

    // Nurse public routes
    Route::get('/available-nurses', [NurseRequestFrontController::class, 'nursesList'])->name('available-nurses');
    Route::get('/nurse-details/{id}', [NurseRequestFrontController::class, 'nurseDetails'])->name('nurse-details');

    // Lab test public routes
    Route::get('/lab-tests', [LabTestFrontController::class, 'testCatalog'])->name('lab-tests');
    Route::get('/lab-test-details/{id}', [LabTestFrontController::class, 'testDetail'])->name('lab-test-details');

    // ICU hospital public routes
    Route::get('/icu-hospitals', [IcuFrontController::class, 'hospitalCatalog'])->name('icu-hospitals');
    Route::get('/icu-hospital-details/{id}', [IcuFrontController::class, 'hospitalDetail'])->name('icu-hospital-details');

    // Independent Doctor Booking (browsing)
    Route::get('book-doctor', [IndependentBookingController::class, 'index'])->name('book-doctor');
    Route::get('book-doctor/index-data', [IndependentBookingController::class, 'index_data'])->name('book-doctor.index-data')->middleware('throttle:60,1');
    Route::get('book-doctor/{doctor_id}/services', [IndependentBookingController::class, 'getServices'])->name('book-doctor.services');
    Route::get('book-doctor/{doctor_id}', [IndependentBookingController::class, 'bookingForm'])->name('book-doctor.form');

    // Guest-accessible booking/request forms (submit still requires auth)
    Route::get('/request-nurse', [NurseRequestFrontController::class, 'requestForm'])->name('request-nurse');
    Route::get('/order-tests', [LabTestFrontController::class, 'orderForm'])->name('order-tests');
    Route::get('/request-icu-admission', [IcuFrontController::class, 'requestForm'])->name('request-icu-admission');
    Route::post('book-doctor/slot-time-list', [IndependentBookingController::class, 'slotTimeList'])->name('book-doctor.slots');

    // Call Booking (browsing - guest accessible)
    Route::get('book-call', [CallBookingController::class, 'index'])->name('book-call');
    Route::get('book-call/index-data', [CallBookingController::class, 'index_data'])->name('book-call.index-data')->middleware('throttle:60,1');
    Route::get('book-call/{doctor_id}/services', [CallBookingController::class, 'getCallServices'])->name('book-call.services');
    Route::get('book-call/{doctor_id}', [CallBookingController::class, 'bookingForm'])->name('book-call.form');
    Route::post('book-call/slot-time-list', [CallBookingController::class, 'slotTimeList'])->name('book-call.slots');
});

Route::get('/getClinicsByService', [ClinicController::class, 'getClinicsByService'])->name('getClinicsByService');
Route::get('/blogs', [BlogController::class, 'blogsList'])->name('blogs');
Route::get('/reviews', [DoctorController::class, 'reviewsList'])->name('reviews');
Route::post('/cancel-appointment/{id}', [AppointmentController::class, 'cancelAppointment'])->name('cancel-appointment');
Route::get('/get-services-by-clinic', [ClinicController::class, 'getServicesByClinic'])->name('getServicesByClinic');


Route::get('/search', [FrontendController::class, 'searchList'])->name('search');
Route::get('/get-search', [FrontendController::class, 'getSearch'])->name('getSearchData');
Route::get('/faq', [FrontendController::class, 'faqList'])->name('faq');
Route::get('/about-us', [FrontendController::class, 'aboutUs'])->name('about-us');
Route::get('/contact-us', [FrontendController::class, 'contactUs'])->name('contact-us');


Route::get('/blog-details/{id}', [BlogController::class, 'blogDetails'])->name('blog-details');

Route::group(['middleware' => ['auth','user_check']], function () {
    Route::get('/account-setting', [UserController::class, 'accountSetting'])->name('account-setting');
    Route::get('/edit-profile', [UserController::class, 'editProfile'])->name('edit-profile');
    Route::post('/update-profile', [UserController::class, 'updateProfile'])->name('update-profile');
    Route::post('/update-profile-image', [UserController::class, 'updateProfileImage'])->name('update-profile-image');
    Route::get('/appointment-list', [AppointmentController::class, 'appointmentList'])->name('appointment-list')->middleware('check.header.menu');
    Route::get('/wallet-history', [UserController::class, 'walletHistory'])->name('wallet-history');
    Route::get('/wallet-history-index-data', [UserController::class, 'walletHistoryIndexData'])->name('wallet-history.index_data');

    Route::post('/account/password/update', [UserController::class, 'updatePassword'])->name('account.password.update');
    Route::delete('/account/delete', [UserController::class, 'deleteAccount'])->name('account.delete');
    Route::post('user-logout', [UserController::class, 'destroy'])->name('user-logout');
   
    Route::get('/encounter-list', [AppointmentController::class, 'encounterList'])->name('encounter-list');
    Route::post('/save-appointment', [AppointmentController::class, 'saveAppointment'])->name('saveAppointment');
    Route::get('/user-notifications', [UserController::class, 'userNotifications'])->name('user-notifications');
    Route::get('/user-notifications-index-data', [UserController::class, 'userNotifications_indexData'])->name('user-notifications.index_data');
    Route::get('booking/{id}', [ServiceController::class, 'booking'])->name('booking');
    Route::post('/pay-now', [AppointmentController::class, 'payNow'])->name('pay-now');
    Route::get('/appointment-details/{id}', [AppointmentController::class, 'appointmentDetails'])->name('appointment-details');
    Route::get('/other-patients-list', [AppointmentController::class, 'otherpatientlist'])->name('other-patients.list');
    Route::post('other-patients', [AppointmentController::class, 'otherpatient'])->name('other-patients.store');
    Route::get('/manage-profile', [AppointmentController::class, 'manageProfile'])->name('manage-profile');
    Route::get('/manage-profile-data', [AppointmentController::class, 'manageProfile_index_data'])->name('manage-profile-data');
    Route::get('/other-patients/{id}/edit', [AppointmentController::class, 'editOtherPatient'])->name('other-patients.edit');
    Route::post('/other-patients/{id}', [AppointmentController::class, 'updateOtherPatient'])->name('other-patients.update');
    Route::delete('/other-patients/{id}', [AppointmentController::class, 'destroyOtherPatient'])->name('other-patients.destroy');

    // Nurse request auth routes (submit & manage)
    Route::post('/request-nurse/store', [NurseRequestFrontController::class, 'store'])->name('request-nurse.store');
    Route::get('/my-nurse-requests', [NurseRequestFrontController::class, 'myRequests'])->name('my-nurse-requests');
    Route::get('/my-nurse-requests/{id}', [NurseRequestFrontController::class, 'requestDetail'])->name('my-nurse-requests.detail');
    Route::get('/my-nurse-requests-index-data', [NurseRequestFrontController::class, 'myRequests_index_data'])->name('my-nurse-requests.index_data');
    Route::post('/cancel-nurse-request/{id}', [NurseRequestFrontController::class, 'cancel'])->name('cancel-nurse-request');

    // Lab test order auth routes (submit & manage)
    Route::post('/order-tests/store', [LabTestFrontController::class, 'store'])->name('order-tests.store');
    Route::post('/order-tests/validate-coupon', [LabTestFrontController::class, 'validateCoupon'])->name('order-tests.validate-coupon');
    Route::get('/my-test-orders', [LabTestFrontController::class, 'myOrders'])->name('my-test-orders');
    Route::get('/my-test-orders/index-data', [LabTestFrontController::class, 'myOrders_index_data'])->name('my-test-orders.index_data');
    Route::get('/my-test-orders/{id}', [LabTestFrontController::class, 'orderDetail'])->name('my-test-orders.detail');
    Route::post('/cancel-test-order/{id}', [LabTestFrontController::class, 'cancel'])->name('cancel-test-order');
    Route::get('/my-test-orders/{id}/download-report', [LabTestFrontController::class, 'downloadReport'])->name('my-test-orders.download-report');

    // Test order payment routes
    Route::get('/order-tests/{id}/payment', [TestOrderPaymentController::class, 'showPayment'])->name('test-order-payment.show');
    Route::post('/order-tests/{id}/process-payment', [TestOrderPaymentController::class, 'processPayment'])->name('test-order-payment.process');
    Route::get('/payment/test-order/stripe-success', [TestOrderPaymentController::class, 'stripeSuccess'])->name('test-order-payment.stripe-success');
    Route::get('/payment/test-order/stripe-cancel/{id}', [TestOrderPaymentController::class, 'stripeCancel'])->name('test-order-payment.stripe-cancel');
    Route::get('/payment/test-order/razorpay/{id}', [TestOrderPaymentController::class, 'razorpayPage'])->name('test-order-payment.razorpay-page');
    Route::post('/payment/test-order/razorpay-verify', [TestOrderPaymentController::class, 'razorpayVerify'])->name('test-order-payment.razorpay-verify');

    // ICU admission auth routes (submit & manage)
    Route::post('/request-icu-admission/store', [IcuFrontController::class, 'store'])->name('request-icu-admission.store');
    Route::get('/my-icu-requests', [IcuFrontController::class, 'myRequests'])->name('my-icu-requests');
    Route::get('/my-icu-requests/index-data', [IcuFrontController::class, 'myRequests_index_data'])->name('my-icu-requests.index_data');
    Route::get('/my-icu-requests/{id}', [IcuFrontController::class, 'requestDetail'])->name('my-icu-requests.detail');
    Route::post('/cancel-icu-request/{id}', [IcuFrontController::class, 'cancel'])->name('cancel-icu-request');
    Route::post('/respond-icu-request/{id}', [IcuFrontController::class, 'respond'])->name('respond-icu-request');

    // Independent Booking (save requires auth)
    Route::post('book-doctor/save-appointment', [IndependentBookingController::class, 'saveAppointment'])->name('book-doctor.save');

    // Call Booking (save requires auth)
    Route::post('book-call/save-appointment', [CallBookingController::class, 'saveAppointment'])->name('book-call.save');
});


Route::get('nurse_index_data', [NurseRequestFrontController::class, 'nurse_index_data'])->name('nurse.index_data');
Route::get('lab_test_index_data', [LabTestFrontController::class, 'test_index_data'])->name('lab-test.index_data')->middleware('throttle:60,1');
Route::get('icu_hospital_index_data', [IcuFrontController::class, 'hospital_index_data'])->name('icu-hospital.index_data')->middleware('throttle:60,1');
Route::get('category_index_data', [CategoryController::class, 'index_data'])->name('category.index_data');
Route::get('service_index_data', [ServiceController::class, 'index_data'])->name('service.index_data');
Route::get('clinic_index_data', [ClinicController::class, 'index_data'])->name('clinic.index_data');
Route::get('doctor_index_data', [DoctorController::class, 'index_data'])->name('doctor.index_data');
Route::get('blog_index_data', [BlogController::class, 'index_data'])->name('blog.index_data');
Route::get('appointment_index_data', [AppointmentController::class, 'index_data'])->name('appointment.index_data');
Route::get('encounter_index_data', [AppointmentController::class, 'encounter_index_data'])->name('encounter.index_data');

Route::post('/get-payment-data', [AppointmentController::class, 'getPaymentData'])->name('payment.data');
Route::post('/slot-time-list', [AppointmentController::class, 'slot_time_list'])->name('slot_time_list');
// Route::post('/save-appointment', [AppointmentController::class, 'saveAppointment'])->name('saveAppointment');
Route::get('/payment/success', [AppointmentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('notification-list', [NotificationsController::class, 'notificationList'])->name('notification.list');
Route::get('notification-counts', [NotificationsController::class, 'notificationCounts'])->name('notification.counts');
Route::post('/check-wallet-balance', [AppointmentController::class, 'checkWalletBalance'])->name('check.wallet.balance');
Route::post('/random-slot', [AppointmentController::class, 'randomSlot'])->name('random_slot');
Route::get('/check-booking-status/{serviceId}/{doctorId}/{clinicId}', [DoctorController::class, 'checkBookingStatus'])->name('check.booking.status');
Route::get('download_invoice', [AppointmentController::class, 'downloadPDf'])->name('download_invoice');
