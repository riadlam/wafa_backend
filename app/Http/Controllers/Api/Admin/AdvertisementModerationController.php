<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchAdvertisementJob;
use App\Models\Advertisement;
use App\Models\AdvertisementAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvertisementModerationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $ads = Advertisement::where('status', $status)
            ->orderBy('created_at')
            ->limit(200)
            ->get();
        return response()->json(['data' => $ads]);
    }

    public function approve(Request $request, int $id)
    {
        $admin = $request->user();
        $ad = Advertisement::findOrFail($id);
        if ($ad->status !== 'pending') {
            return response()->json(['message' => 'Only pending ads can be approved'], 422);
        }

        DB::transaction(function () use ($admin, $ad) {
            $ad->status = 'approved';
            $ad->approved_by = $admin->id;
            $ad->approved_at = now();
            $ad->save();

            AdvertisementAudit::create([
                'advertisement_id' => $ad->id,
                'action' => 'approved',
                'performed_by' => $admin->id,
                'meta' => null,
            ]);
        });

        // Dispatch async notification job
        DispatchAdvertisementJob::dispatch($ad->id);

        return response()->json(['message' => 'Advertisement approved and dispatch queued']);
    }

    public function reject(Request $request, int $id)
    {
        $admin = $request->user();
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $ad = Advertisement::findOrFail($id);
        if ($ad->status !== 'pending') {
            return response()->json(['message' => 'Only pending ads can be rejected'], 422);
        }

        DB::transaction(function () use ($admin, $ad, $validated) {
            $ad->status = 'rejected';
            $ad->rejection_reason = $validated['reason'] ?? null;
            $ad->approved_by = $admin->id;
            $ad->approved_at = now();
            $ad->save();

            AdvertisementAudit::create([
                'advertisement_id' => $ad->id,
                'action' => 'rejected',
                'performed_by' => $admin->id,
                'meta' => ['reason' => $ad->rejection_reason],
            ]);
        });

        return response()->json(['message' => 'Advertisement rejected']);
    }
}


