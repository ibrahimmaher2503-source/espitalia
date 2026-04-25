<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsCategory;
use Yajra\DataTables\DataTables;
use Modules\Tax\Models\Tax;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorSession;
use Carbon\Carbon;
use Modules\Appointment\Models\Appointment;
use App\Models\Holiday;
use App\Models\User;
use App\Models\DoctorHoliday;
use Illuminate\Support\Facades\Route;
class ServiceController extends Controller
{
    public function servicesList(Request $request)
    {
        $doctor_id = $request->query('doctor_id');
        $clinic_id = $request->query('clinic_id');
        $category_id = $request->query('category_id');

        $clinics = Clinics::checkMultivendor()->with('clinicdoctor', 'specialty', 'clinicdoctor', 'receptionist')->where('status', 1)->get();
        $categories = ClinicsCategory::whereNull('parent_id')->where('status', 1)->get();
        $service = ClinicsService::with('sub_category', 'doctor_service', 'ClinicServiceMapping', 'systemservice')
            ->where('status', 1)
            ->get();
        
        // Calculate total prices including inclusive tax
        $prices = $service->map(function($item) {
            return $item->charges + ($item->inclusive_tax_price ?? 0);
        });
        
        $minPrice = 0;
        $maxPrice = $prices->max();

        $interval = 50;
        $priceRanges = [];
        if ($maxPrice <= $interval) {
            $priceRanges[] = [$minPrice, $maxPrice];
        } else {
            for ($i = $minPrice; $i <= $maxPrice; $i += $interval) {
                $priceRanges[] = [$i, min($i + $interval, $maxPrice)];
            }
        }

        return view('frontend::services', compact('clinics', 'categories', 'doctor_id', 'clinic_id', 'priceRanges', 'category_id'));

    }

    public function index_data(Request $request)
    {

        $service_list = ClinicsService::CheckMultivendor()->with('category');

        $search = $request->input('search');
        if ($search) {
            $service_list = $service_list->where('name', 'like', '%' . $search . '%');
        }
        $doctor_id = $request->query('doctor_id');
        if ($doctor_id) {
            $service_list = $service_list->whereHas('doctor_service', function ($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            });
        }

        $category_id = $request->query('category_id');
        if ($category_id && $request->has('filter.category_id') && $request->input('filter.category_id')) {
            $service_list = $service_list->where('category_id', $category_id);
        }

        $clinic_id = $request->query('clinic_id');
        if ($clinic_id) {
            $service_list = ClinicsService::with('category', 'ClinicServiceMapping')
                ->whereHas('ClinicServiceMapping', function ($query) use ($clinic_id) {
                    $query->where('clinic_id', $clinic_id);
                });
        }
        if ($request->has('filter.clinic_id') && $request->input('filter.clinic_id') ) {
            $clinicId = $request->input('filter.clinic_id');
            $service_list = $service_list->whereHas('ClinicServiceMapping', function ($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            });
        }



        if ($request->has('filter.price') && $request->input('filter.price')) {
            $priceRange = $request->input('filter.price');
            [$minPrice, $maxPrice] = explode('-', $priceRange);

            if($minPrice == $maxPrice) {
                $service_list = $service_list->where(function($query) use ($minPrice) {
                    $query->whereRaw('(charges + COALESCE(inclusive_tax_price, 0)) > ?', [(float)$minPrice]);
                });
            } else {
                
                $service_list = $service_list->where(function($query) use ($minPrice, $maxPrice) {
                    $query->whereRaw('(charges + COALESCE(inclusive_tax_price, 0)) BETWEEN ? AND ?', [
                        (float)$minPrice,
                        (float)$maxPrice
                    ]);
                });
            }
        }

        if ($request->has('filter.category_id') && $request->input('filter.category_id')) {
            $service_list = $service_list->where('category_id', $request->input('filter.category_id'));
        }
        $service_list = $service_list->where('status', 1);

        $services = $service_list->orderBy('updated_at', 'desc');

        return DataTables::of($services)
            ->addColumn('card', function ($service) {
                $discount_amount =0;
                if ($service->discount) {
                    $discount_amount = ($service->discount_type == 'percentage')
                        ? $service->charges * $service->discount_value / 100
                        : $service->discount_value;

                }

                $service->payable_amount = $service->charges-$discount_amount+$service->inclusive_tax_price ;

                return view('frontend::components.card.service_card', compact('service'))->render();
            })
            ->rawColumns(['card'])
            ->make(true);

    }

    public function serviceDetails($id)
    {
        $service = ClinicsService::CheckMultivendor()->where('id', $id)->with('category', 'sub_category', 'ClinicServiceMapping', 'doctor_service', 'systemservice')->first();

        $discount_amount =0;
        if ($service->discount) {
            $discount_amount = ($service->discount_type == 'percentage')
                ? $service->charges * $service->discount_value / 100
                : $service->discount_value;

        }

        $service->payable_amount = $service->charges - $discount_amount +  $service->inclusive_tax_price;

        return view('frontend::service_detail', compact('service'));
    }

    public function booking($id, Request $request)
    {
        // dd('Booking page');

        if (!auth()->check()) {
            return view('frontend::auth.login', ['redirect_to' => route('booking', ['id' => $id])]);
        }
        $previousUrl = url()->previous();

        $clinicId = null;
        $doctorId = null;
        $selectedClinic = null;
        $selectedDoctor = null;
        $currentStep = 0;
            if ($request->has('clinic_id')) {
            $clinicId = $request->query('clinic_id');
            $currentStep = 1;
            $selectedClinic = Clinics::CheckMultivendor()->findOrFail($clinicId);

            // Tabs: Clinics first with dynamic indexes
            $tabs = [
                ['index' => 0, 'label' => __('frontend.choose_clinics') ,'value'=>'Choose Clinics'],
                ['index' => 1, 'label' => __('frontend.choose_doctors'),'value'=>'Choose Doctors'],
                ['index' => 2, 'label' => __('frontend.choose_date_time_payment'),'value'=>'Choose Date, Time, Payment'],
            ];
        } else if (preg_match('/clinic-details\/(\d+)/', $previousUrl, $matches)) {
            $clinicId = $matches[1];
            $currentStep = 1;
            $selectedClinic = Clinics::CheckMultivendor()->findOrFail($clinicId);
        }
            if (preg_match('/doctor-details\/(\d+)/', $previousUrl, $matches)) {
            $doctorId = $matches[1];
            $currentStep = 2;
            $selectedDoctor = Doctor::CheckMultivendor()->with('user')->findOrFail($doctorId);
        }

        // Logic for Service ID
        $selectedService = ClinicsService::CheckMultivendor()->findOrFail($id);
        $serviceId = $selectedService->id;

        if ($currentStep === 1 && $clinicId && !$doctorId) {
            $tabs = [
                ['index' => 0, 'label' => __('frontend.choose_clinics') ,'value'=>'Choose Clinics'],
                ['index' => 1, 'label' => __('frontend.choose_doctors'),'value'=>'Choose Doctors'],
                ['index' => 2, 'label' => __('frontend.choose_date_time_payment'),'value'=>'Choose Date, Time, Payment'],
            ];
        } else if (preg_match('/doctor-details\/(\d+)/', $previousUrl, $matches)) {
            $doctorId = $matches[1];
            $currentStep = 2;
            $selectedDoctor = Doctor::CheckMultivendor()->with('user')->findOrFail($doctorId);
            $tabs = [
                ['index' => 0, 'label' => __('frontend.choose_clinics') ,'value'=>'Choose Doctors'],
                ['index' => 1, 'label' => __('frontend.choose_doctors'),'value'=>'Choose Clinics'],
                ['index' => 2, 'label' => __('frontend.choose_date_time_payment'),'value'=>'Choose Date, Time, Payment'],
            ];
        } else {
            $currentStep = session('currentStep', 0); // Default to 0 if not set
            $tabs = [
                ['index' => 0, 'label' => __('frontend.choose_clinics') ,'value'=>'Choose Clinics'],
                ['index' => 1, 'label' => __('frontend.choose_doctors'),'value'=>'Choose Doctors'],
                ['index' => 2, 'label' => __('frontend.choose_date_time_payment'),'value'=>'Choose Date, Time, Payment'],
            ];
        }
        $selectedService = ClinicsService::CheckMultivendor()->findOrFail($id);
        $serviceId = $selectedService->id;
        $paymentMethods = [];

        // List of available payment methods
        $paymentMethodsList = [
            'cash' => 'cash_payment_method',  // Always available
            'Wallet' => 'wallet_payment_method', // Always available
            'Stripe' => 'str_payment_method',
            'Paystack' => 'paystack_payment_method',
            'PayPal' => 'paypal_payment_method',
            'Flutterwave' => 'flutterwave_payment_method',
            'Airtel' => 'airtel_payment_method',
            'PhonePay' => 'phonepay_payment_method',
            'Midtrans' => 'midtrans_payment_method',
            'Cinet' => 'cinet_payment_method',
            'Sadad' => 'sadad_payment_method',
            'Razor Pay' => 'razor_payment_method',
        ];

        $enabledPaymentMethods = ['cash', 'Wallet']; // Add Cash and Wallet by default

        if ($selectedService->is_enable_advance_payment == 1) {
            $enabledPaymentMethods = array_filter($enabledPaymentMethods, function($method) {
                return $method !== 'cash';
            });
        }
        // Iterate through all payment methods and check if they are enabled
        foreach ($paymentMethodsList as $displayName => $settingKey) {
            if (setting($settingKey, 0) == 1) { // Assuming 1 means enabled
                $enabledPaymentMethods[] = $displayName; // Add enabled methods to the list
            }

        }
        return view('frontend::booking', compact('tabs', 'currentStep', 'selectedService', 'serviceId', 'selectedClinic', 'clinicId', 'selectedDoctor', 'doctorId', 'previousUrl', 'tabs', 'enabledPaymentMethods'));
    }




}
