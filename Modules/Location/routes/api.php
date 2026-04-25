<?php

use Illuminate\Support\Facades\Route;
use Modules\Location\Http\Controllers\API\LocationApiController;

Route::get('governorates', [LocationApiController::class, 'governorates']);
Route::get('cities', [LocationApiController::class, 'cities']);
