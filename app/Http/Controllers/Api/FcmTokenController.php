<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Services\FcmService;
use App\Models\UserLoyaltyCard;
use App\Models\LoyaltyCard;
use App\Models\Shop;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid token payload',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $token = $request->input('token');
        $tokens = $user->fcm_tokens ?? [];

        // Ensure array and uniqueness
        if (!is_array($tokens)) {
            $tokens = [];
        }
        if (!in_array($token, $tokens, true)) {
            $tokens[] = $token;
        }

        $user->fcm_tokens = $tokens;
        $user->save();

        return response()->json([
            'message' => 'FCM token saved',
            'tokens' => $user->fcm_tokens,
        ]);
    }

    public function sendTestNotification(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $tokens = $user->fcm_tokens ?? [];
        if (empty($tokens)) {
            return response()->json(['message' => 'No FCM tokens found for user'], 400);
        }

        // Get FCM project ID from environment
        $projectId = env('FCM_PROJECT_ID');
        if (!$projectId) {
            return response()->json(['message' => 'FCM project ID not configured'], 500);
        }

        // Send to first token (for testing)
        $token = $tokens[0];
        
        // Use FCM HTTP v1 API
        $accessToken = app(FcmService::class)->getAccessToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => 'Test Notification! 🎉',
                    'body' => 'This is a test push notification from your loyalty app!',
                ],
                'data' => [
                    'type' => 'test',
                    'route' => '/cards',
                ],
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Test notification sent successfully',
                'token' => substr($token, 0, 20) . '...',
                'fcm_response' => $response->json(),
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $response->body(),
            ], 500);
        }
    }


    public function sendShopAdvertise(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:500',
        ]);

        // Find shops owned by user, then their loyalty cards
        $shopIds = Shop::where('user_id', $user->id)->pluck('id');
        if ($shopIds->isEmpty()) {
            return response()->json(['message' => 'No shops found for this owner'], 404);
        }

        $loyaltyCardIds = LoyaltyCard::whereIn('shop_id', $shopIds)->pluck('id');
        if ($loyaltyCardIds->isEmpty()) {
            return response()->json(['message' => 'No loyalty cards found for shops'], 404);
        }

        // Get subscribers who have stamps for these loyalty cards
        $userIds = UserLoyaltyCard::whereIn('loyalty_card_id', $loyaltyCardIds)
            ->where('active_stamps', '>', 0)
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return response()->json(['message' => 'No eligible subscribers found'], 200);
        }

        // Collect FCM tokens for these users
        $tokens = \App\Models\User::whereIn('id', $userIds)
            ->pluck('fcm_tokens')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            return response()->json(['message' => 'No device tokens found for subscribers'], 200);
        }

        $projectId = env('FCM_PROJECT_ID');
        if (!$projectId) {
            return response()->json(['message' => 'FCM project ID not configured'], 500);
        }

        $accessToken = app(FcmService::class)->getAccessToken();

        $sent = 0; $failed = 0;
        foreach ($tokens as $token) {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $validated['title'],
                        'body' => $validated['description'],
                    ],
                    'data' => [
                        'type' => 'shop_advertise',
                        'route' => '/cards',
                    ],
                    'android' => [
                        'notification' => [ 'sound' => 'default' ],
                    ],
                ],
            ]);

            if ($resp->successful()) $sent++; else $failed++;
        }

        return response()->json([
            'message' => 'Advertise notification dispatched',
            'sent' => $sent,
            'failed' => $failed,
            'targets' => count($tokens),
        ]);
    }
}


