<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchAdvertisementJob;
use App\Models\Advertisement;
use App\Models\AdvertisementAudit;
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

        return view('ads.index', [
            'ads' => $ads,
            'status' => $status,
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
}


