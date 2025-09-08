<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementAudit;
use App\Models\LoyaltyCard;
use App\Models\Shop;
use App\Models\UserLoyaltyCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller
{
    // Owner: create a pending advertisement
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid payload', 'errors' => $validator->errors()], 422);
        }

        // Find first shop of owner (extend to support multiple if needed)
        $shop = Shop::where('user_id', $user->id)->first();
        if (!$shop) {
            return response()->json(['message' => 'No shop found for owner'], 404);
        }

        $ad = null;
        DB::transaction(function () use ($request, $user, $shop, &$ad) {
            $ad = Advertisement::create([
                'shop_id' => $shop->id,
                'owner_user_id' => $user->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => 'pending',
            ]);

            AdvertisementAudit::create([
                'advertisement_id' => $ad->id,
                'action' => 'created',
                'performed_by' => $user->id,
                'meta' => null,
            ]);
        });

        return response()->json(['message' => 'Advertisement submitted for approval', 'data' => $ad], 201);
    }

    // Owner: list my advertisements
    public function myIndex(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $ads = Advertisement::where('owner_user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
        return response()->json(['data' => $ads]);
    }

    // Owner: daily usage
    public function myDailyUsage(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $today = now()->startOfDay();
        $sentToday = Advertisement::where('owner_user_id', $user->id)
            ->whereIn('status', ['approved', 'sent'])
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', $today)
            ->count();

        // TODO: compute from plan. For now, hardcode 3/day
        $dailyLimit = 3;

        return response()->json([
            'data' => [
                'sent_today' => $sentToday,
                'daily_limit' => $dailyLimit,
                'remaining' => max(0, $dailyLimit - $sentToday),
            ],
        ]);
    }
}


