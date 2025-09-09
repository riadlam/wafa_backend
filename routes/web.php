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
    $shops = \App\Models\Shop::with(['owner', 'shopLocations'])->get();

    $debug = [];
    foreach ($shops as $shop) {
        $shopLocation = $shop->shopLocations->first();

        $debug[] = [
            'shop_name' => $shop->name,
            'owner_name' => $shop->owner->name ?? 'N/A',
            'shop_location_field' => $shop->location, // JSON field in shops table
            'shop_locations_table' => $shopLocation ? [
                'name' => $shopLocation->name,
                'lat' => $shopLocation->lat,
                'lng' => $shopLocation->lng,
                'user_id' => $shopLocation->user_id,
                'shop_id' => $shopLocation->shop_id,
            ] : null,
            'has_shop_location' => $shopLocation ? true : false,
            'shop_locations_count' => $shop->shopLocations->count(),
        ];
    }

    return response()->json($debug);
});
