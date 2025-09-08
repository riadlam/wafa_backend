<?php

namespace App\Jobs;

use App\Models\Advertisement;
use App\Models\LoyaltyCard;
use App\Models\Shop;
use App\Models\User;
use App\Models\UserLoyaltyCard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Services\FcmService;

class DispatchAdvertisementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $advertisementId;

    public function __construct(int $advertisementId)
    {
        $this->advertisementId = $advertisementId;
    }

    public function handle(): void
    {
        $ad = Advertisement::find($this->advertisementId);
        if (!$ad) return;

        // Resolve subscribers of this shop
        $loyaltyCardIds = LoyaltyCard::where('shop_id', $ad->shop_id)->pluck('id');
        if ($loyaltyCardIds->isEmpty()) {
            $ad->status = 'failed';
            $ad->save();
            return;
        }

        $userIds = UserLoyaltyCard::whereIn('loyalty_card_id', $loyaltyCardIds)
            ->where('active_stamps', '>', 0)
            ->pluck('user_id')
            ->unique()
            ->values();

        $tokens = User::whereIn('id', $userIds)
            ->pluck('fcm_tokens')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        $ad->target_count = count($tokens);
        $ad->save();

        if (empty($tokens)) {
            $ad->status = 'sent';
            $ad->sent_at = now();
            $ad->save();
            return;
        }

        $projectId = env('FCM_PROJECT_ID');
        if (!$projectId) {
            $ad->status = 'failed';
            $ad->save();
            return;
        }

        $accessToken = app(FcmService::class)->getAccessToken();

        $sent = 0; $failed = 0;
        foreach ($tokens as $token) {
            $ok = app(FcmService::class)->sendToToken($projectId, $accessToken, $token, $ad->title, $ad->description);
            if ($ok) $sent++; else $failed++;
        }

        $ad->delivered_count = $sent;
        $ad->status = 'sent';
        $ad->sent_at = now();
        $ad->save();
    }
}


