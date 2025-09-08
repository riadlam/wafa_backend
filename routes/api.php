<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\LoyaltyCardController;
use App\Http\Controllers\Api\UserLoyaltyCardController;
use App\Http\Controllers\Api\StampController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QrScanController;
use App\Http\Controllers\Api\RedemptionStatisticController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Events\MessageSent;
use Illuminate\Http\Request;

Route::post('/send', function (Request $request) {
    broadcast(new MessageSent('Hello from Laravel!')); // 👈 remove ->toOthers()
    return response()->json(['status' => 'Message sent']);
});



// Public Routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'store']); // Moved outside protected routes
Route::get('/categories/{category}/shops', [ShopController::class, 'getByCategory']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/login/google', [AuthController::class, 'googleLogin']);
Route::post('/login/firebase', [AuthController::class, 'firebaseLogin']);

// Location routes
Route::get('/locations/filter', [\App\Http\Controllers\Api\LocationController::class, 'filterByWilaya']);


// Protected Routes
Route::middleware(['jwt.verify'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::put('/user/update-name', [AuthController::class, 'updateName']);
    
    // Get loyalty cards for shops owned by the authenticated user
    Route::get('/my-shops/loyalty-cards', [ShopController::class, 'getMyShopsLoyaltyCards']);
    Route::patch('/my-loyalty-card', [LoyaltyCardController::class, 'updateMyLoyaltyCard']);
    
    // Update shop name and loyalty card
    Route::patch('/update-shop-name', [LoyaltyCardController::class, 'updateShopName']);
    Route::post('/update-loyalty-card', [LoyaltyCardController::class, 'updateLoyaltyCard']);
    Route::post('/save-shop-location', [LoyaltyCardController::class, 'saveShopLocation']);
    Route::post('/upsert-loyalty-card', [LoyaltyCardController::class, 'upsertLoyaltyCard']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    Route::post('/mark-as-existed', [AuthController::class, 'markAsExisted']);
    Route::post('/setup-shop-owner', [AuthController::class, 'setupShopOwner']);

    // FCM tokens
    Route::post('/user/fcm-tokens', [FcmTokenController::class, 'store']);
    Route::post('/user/send-test-notification', [FcmTokenController::class, 'sendTestNotification']);

    // Redemption Statistics
    Route::post('/shop-redemptions', [RedemptionStatisticController::class, 'shopRedemptions']);
    Route::get('/shop/redemption-stats', [RedemptionStatisticController::class, 'getShopRedemptionStats']);
    Route::get('/user/{userId}/loyalty-card/{loyaltyCardId}/redemptions', [RedemptionStatisticController::class, 'getRedemptionStats']);
    Route::get('/shop/calculate-amount-due', [RedemptionStatisticController::class, 'calculateAmountDue']);

    // User Plan Info
    Route::get('/user/plan-info', [StampController::class, 'getPlanInfo']);

    // User Loyalty Cards
    Route::get('/user/loyalty-cards', [UserLoyaltyCardController::class, 'getUserLoyaltyCards']);
    Route::get('/user/loyalty-cards/{loyaltyCardId}/active-stamps', [UserLoyaltyCardController::class, 'getActiveStampsCount']);
    Route::get('/user/recent-stamps', [UserLoyaltyCardController::class, 'recentStamps']);
    Route::get('/shop/total-subscribers', [UserLoyaltyCardController::class, 'getTotalSubscribers']);

    // Protected API resources
    Route::apiResource('users', UserController::class);
    
    // Shop search route
    Route::get('/shops/search', [ShopController::class, 'search']);
    
    // Shop resource routes
    Route::apiResource('shops', ShopController::class);
    Route::apiResource('loyalty-cards', LoyaltyCardController::class);
    Route::apiResource('user-loyalty-cards', UserLoyaltyCardController::class);
    Route::get('/user-loyalty-cards/{cardId}/active-stamps', [StampController::class, 'getActiveStamps']);
    Route::get('/user/loyalty-cards', [UserLoyaltyCardController::class, 'getUserLoyaltyCards']);
    Route::apiResource('stamps', StampController::class);
    
    // QR Scan routes
    Route::apiResource('qr-scans', QrScanController::class);
    Route::post('/process-qr-scan', [QrScanController::class, 'processQrScan']);
    
    // Protected category routes
    Route::middleware('can:manage-categories')->group(function () {
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });
});
