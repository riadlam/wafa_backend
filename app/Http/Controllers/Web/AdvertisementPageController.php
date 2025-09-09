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

        // Calculate statistics
        $shopOwnerIds = Shop::pluck('user_id')->unique();
        $totalUsers = User::whereNotIn('id', $shopOwnerIds)->count();
        $totalShopOwners = $shopOwnerIds->count();
        $totalStamps = Stamp::count();
        $totalRedemptions = RedemptionStatistic::where('is_payed', 0)->count();

        return view('ads.index', [
            'ads' => $ads,
            'status' => $status,
            'totalUsers' => $totalUsers,
            'totalShopOwners' => $totalShopOwners,
            'totalStamps' => $totalStamps,
            'totalRedemptions' => $totalRedemptions,
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


