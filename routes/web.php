<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdvertisementPageController;
use App\Http\Controllers\Web\UserManagementController;
use App\Http\Controllers\Web\ShopOwnerManagementController;

// Main page: list received advertisements
Route::get('/', [AdvertisementPageController::class, 'index']);
Route::post('/notify/owners', [AdvertisementPageController::class, 'notifyOwners']);
Route::post('/notify/users', [AdvertisementPageController::class, 'notifyUsers']);
Route::post('/ads/{id}/approve', [AdvertisementPageController::class, 'approve']);
Route::post('/ads/{id}/reject', [AdvertisementPageController::class, 'reject']);

// User management routes
Route::get('/users', [UserManagementController::class, 'index']);
Route::get('/users/{user}', [UserManagementController::class, 'show']);

// Shop owner management routes
Route::get('/shop-owners', [ShopOwnerManagementController::class, 'index']);
Route::get('/shop-owners/{shopOwner}', [ShopOwnerManagementController::class, 'show']);
