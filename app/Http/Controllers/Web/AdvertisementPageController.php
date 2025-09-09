<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchAdvertisementJob;
use App\Models\Advertisement;
use App\Models\AdvertisementAudit;
use App\Models\User;
use App\Models\Shop;
use App\Models\RedemptionStatistic;
use App\Models\Stamp;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvertisementPageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Advertisement::query()->orderByDesc('created_at');
        if ($status) {
            $query->where('status', $status);
        }

        $ads = $query->paginate(20)->withQueryString();

        // Calculate comprehensive statistics
        $shopOwnerIds = Shop::pluck('user_id')->unique();

        // Helper function to get stats for date range
        $getStatsForPeriod = function ($startDate, $endDate = null) use ($shopOwnerIds) {
            $query = User::whereNotIn('id', $shopOwnerIds);
            if ($startDate) $query->whereDate('created_at', '>=', $startDate);
            if ($endDate) $query->whereDate('created_at', '<=', $endDate);

            $users = $query->count();

            $shopsQuery = Shop::query();
            if ($startDate) $shopsQuery->whereDate('created_at', '>=', $startDate);
            if ($endDate) $shopsQuery->whereDate('created_at', '<=', $endDate);
            $shops = $shopsQuery->count();

            $stampsQuery = Stamp::query();
            if ($startDate) $stampsQuery->whereDate('created_at', '>=', $startDate);
            if ($endDate) $stampsQuery->whereDate('created_at', '<=', $endDate);
            $stamps = $stampsQuery->count();

            $redemptionsQuery = RedemptionStatistic::query();
            if ($startDate) $redemptionsQuery->whereDate('created_at', '>=', $startDate);
            if ($endDate) $redemptionsQuery->whereDate('created_at', '<=', $endDate);
            $redemptions = $redemptionsQuery->where('is_payed', 0)->count();

            return [
                'users' => $users,
                'shops' => $shops,
                'stamps' => $stamps,
                'redemptions' => $redemptions,
            ];
        };

        // Today's statistics
        $today = now()->toDateString();
        $stats['today'] = $getStatsForPeriod($today);

        // Yesterday's statistics for comparison
        $yesterday = now()->subDay()->toDateString();
        $yesterdayStats = $getStatsForPeriod($yesterday);

        // Calculate differences
        $stats['yesterday_comparison'] = [
            'users' => $stats['today']['users'] - $yesterdayStats['users'],
            'shops' => $stats['today']['shops'] - $yesterdayStats['shops'],
            'stamps' => $stats['today']['stamps'] - $yesterdayStats['stamps'],
            'redemptions' => $stats['today']['redemptions'] - $yesterdayStats['redemptions'],
        ];

        // Last 15 days statistics
        $fifteenDaysAgo = now()->subDays(15)->toDateString();
        $stats['last_15_days'] = $getStatsForPeriod($fifteenDaysAgo);

        // Last month statistics
        $oneMonthAgo = now()->subMonth()->toDateString();
        $stats['last_month'] = $getStatsForPeriod($oneMonthAgo);

        // Payment due insights
        $pendingRedemptions = RedemptionStatistic::where('is_payed', 0)->get();
        $totalAmountDue = $pendingRedemptions->count() * 100; // 100 DA per redemption

        // Get affected shops with detailed information
        $affectedShopIds = $pendingRedemptions->pluck('loyalty_card_id')->unique()->map(function ($cardId) {
            return \App\Models\LoyaltyCard::find($cardId)?->shop_id;
        })->filter()->unique();

        $shopsDetailed = [];
        foreach ($affectedShopIds as $shopId) {
            $shop = \App\Models\Shop::find($shopId);
            if ($shop) {
                $shopPendingRedemptions = $pendingRedemptions->filter(function ($redemption) use ($shopId) {
                    $loyaltyCard = \App\Models\LoyaltyCard::find($redemption->loyalty_card_id);
                    return $loyaltyCard && $loyaltyCard->shop_id == $shopId;
                });

                $shopAmountDue = $shopPendingRedemptions->count() * 100;
                $oldestDue = $shopPendingRedemptions->min('created_at');

                $shopsDetailed[] = [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'logo' => $shop->images ? asset('storage/' . (str_contains($shop->images[0], 'storage/') ? str_replace('storage/', '', $shop->images[0]) : $shop->images[0])) : null,
                    'pending_count' => $shopPendingRedemptions->count(),
                    'amount_due' => $shopAmountDue,
                    'oldest_due' => $oldestDue ? \Carbon\Carbon::parse($oldestDue) : now(),
                ];
            }
        }

        // Sort by amount due (highest first)
        usort($shopsDetailed, function($a, $b) {
            return $b['amount_due'] <=> $a['amount_due'];
        });

        $oldestDue = $pendingRedemptions->min('created_at');
        $oldestDueDays = $oldestDue ? now()->diffInDays($oldestDue) : 0;

        $stats['payment_due'] = [
            'total_amount' => $totalAmountDue,
            'pending_redemptions' => $pendingRedemptions->count(),
            'affected_shops' => $affectedShopIds->count(),
            'oldest_due_days' => $oldestDueDays,
            'shops_detailed' => $shopsDetailed,
        ];

        return view('ads.index', [
            'ads' => $ads,
            'status' => $status,
            'stats' => $stats,
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $ad = Advertisement::findOrFail($id);
        if ($ad->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending ads can be approved');
        }

        DB::transaction(function () use ($ad) {
            $ad->status = 'approved';
            $ad->approved_by = null; // set to current admin user id if you have auth on web
            $ad->approved_at = now();
            $ad->save();

            AdvertisementAudit::create([
                'advertisement_id' => $ad->id,
                'action' => 'approved',
                'performed_by' => null,
                'meta' => null,
            ]);
        });

        DispatchAdvertisementJob::dispatch($ad->id);

        return redirect()->back()->with('success', 'Advertisement approved and dispatch queued');
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $ad = Advertisement::findOrFail($id);
        if ($ad->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending ads can be rejected');
        }

        DB::transaction(function () use ($ad, $request) {
            $ad->status = 'rejected';
            $ad->rejection_reason = $request->input('reason');
            $ad->approved_by = null;
            $ad->approved_at = now();
            $ad->save();

            AdvertisementAudit::create([
                'advertisement_id' => $ad->id,
                'action' => 'rejected',
                'performed_by' => null,
                'meta' => ['reason' => $ad->rejection_reason],
            ]);
        });

        return redirect()->back()->with('success', 'Advertisement rejected');
    }

    public function notifyOwners(Request $request, FcmService $fcm)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:500',
        ]);

        $ownerIds = Shop::query()->pluck('user_id')->unique()->values();
        if ($ownerIds->isEmpty()) {
            return redirect()->back();
        }

        $tokens = User::whereIn('id', $ownerIds)
            ->pluck('fcm_tokens')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        $projectId = env('FCM_PROJECT_ID');
        if (!$projectId || empty($tokens)) {
            return redirect()->back();
        }

        $accessToken = $fcm->getAccessToken();
        $sent = 0; $failed = 0;
        foreach ($tokens as $t) {
            $ok = $fcm->sendToToken($projectId, $accessToken, $t, $data['title'], $data['description'], '/admin/dashboard');
            if ($ok) $sent++; else $failed++;
        }

        return redirect()->back();
    }

    public function notifyUsers(Request $request, FcmService $fcm)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:500',
        ]);

        $ownerIds = Shop::query()->pluck('user_id')->unique()->values();
        $query = User::query();
        if ($ownerIds->isNotEmpty()) {
            $query->whereNotIn('id', $ownerIds);
        }

        $tokens = $query->pluck('fcm_tokens')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        $projectId = env('FCM_PROJECT_ID');
        if (!$projectId || empty($tokens)) {
            return redirect()->back();
        }

        $accessToken = $fcm->getAccessToken();
        $sent = 0; $failed = 0;
        foreach ($tokens as $t) {
            $ok = $fcm->sendToToken($projectId, $accessToken, $t, $data['title'], $data['description']);
            if ($ok) $sent++; else $failed++;
        }

        return redirect()->back();
    }
}


