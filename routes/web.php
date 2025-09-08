<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdvertisementPageController;

// Main page: list received advertisements
Route::get('/', [AdvertisementPageController::class, 'index']);
Route::post('/notify/owners', [AdvertisementPageController::class, 'notifyOwners']);
Route::post('/notify/users', [AdvertisementPageController::class, 'notifyUsers']);
Route::post('/ads/{id}/approve', [AdvertisementPageController::class, 'approve']);
Route::post('/ads/{id}/reject', [AdvertisementPageController::class, 'reject']);
