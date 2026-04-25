<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeHealthcare\Http\Controllers\API\HomeHealthcareSearchApiController;

Route::get('home-healthcare/search', [HomeHealthcareSearchApiController::class, 'search']);
