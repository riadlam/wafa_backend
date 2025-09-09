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

// Debug route to check image paths
Route::get('/debug-images', function () {
    $shops = \App\Models\Shop::whereNotNull('images')->with('owner')->get();

    $debug = [];
    foreach ($shops as $shop) {
        $debug[] = [
            'shop_name' => $shop->name,
            'owner_name' => $shop->owner->name ?? 'N/A',
            'images_count' => count($shop->images),
            'images' => $shop->images,
            'first_image_url' => $shop->images ? asset('storage/' . (str_contains($shop->images[0], 'storage/') ? str_replace('storage/', '', $shop->images[0]) : $shop->images[0])) : null,
        ];
    }

    return response()->json($debug);
});

// Debug route to check location data
Route::get('/debug-locations', function () {
    $shops = \App\Models\Shop::whereNotNull('location')->with('owner')->get();

    $debug = [];
    foreach ($shops as $shop) {
        $debug[] = [
            'shop_name' => $shop->name,
            'owner_name' => $shop->owner->name ?? 'N/A',
            'location_raw' => $shop->location,
            'location_type' => gettype($shop->location),
            'has_address' => isset($shop->location['address']),
            'has_wilaya' => isset($shop->location['wilaya']),
            'has_coordinates' => isset($shop->location['coordinates']),
            'coordinates_type' => isset($shop->location['coordinates']) ? gettype($shop->location['coordinates']) : null,
        ];
    }

    return response()->json($debug);
});
