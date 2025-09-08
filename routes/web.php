<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdvertisementPageController;

// Main page: list received advertisements
Route::get('/', [AdvertisementPageController::class, 'index']);
