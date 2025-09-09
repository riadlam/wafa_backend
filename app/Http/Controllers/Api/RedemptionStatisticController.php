<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RedemptionStatistic;
use App\Models\User;
use App\Models\LoyaltyCard;
use App\Models\Shop;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RedemptionStatisticController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Track a shop redemption
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shopRedemptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'loyalty_card_id' => 'required|exists:loyalty_cards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create a new redemption record
            $redemption = RedemptionStatistic::create([
                'user_id' => $request->user_id,
                'loyalty_card_id' => $request->loyalty_card_id,
            ]);

            // Get the count of redemptions for this user and loyalty card
            $redemptionCount = RedemptionStatistic::where('user_id', $request->user_id)
                ->where('loyalty_card_id', $request->loyalty_card_id)
                ->count();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Redemption recorded successfully',
                'data' => [
                    'redemption' => $redemption,
                    'total_redemptions' => $redemptionCount
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record redemption',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get redemption statistics for a user and loyalty card
     * 
     * @param int $userId
     * @param int $loyaltyCardId
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Get redemption statistics for the authenticated user's shop
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopRedemptionStats(Request $request)
    {
        try {
            // Get the authenticated user
            $user = auth()->user();
            
            // Get the user's shop
            $shop = $user->shops()->first();
            
            if (!$shop) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No shop found for this user'
                ], 404);
            }
            
            // Get loyalty cards for the shop
            $loyaltyCards = $shop->loyaltyCards()->pluck('id');
            
            if ($loyaltyCards->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No loyalty cards found for this shop'
                ], 404);
            }
            
            // Get total redemptions count for all loyalty cards
            $totalRedemptions = RedemptionStatistic::whereIn('loyalty_card_id', $loyaltyCards)->count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_redemptions' => $totalRedemptions
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch redemption statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get redemption statistics for a specific user and loyalty card
     * 
     * @param int $userId
     * @param int $loyaltyCardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRedemptionStats($userId, $loyaltyCardId)
    {
        $redemptions = RedemptionStatistic::with(['user', 'loyaltyCard'])
            ->where('user_id', $userId)
            ->where('loyalty_card_id', $loyaltyCardId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_redemptions' => $redemptions->count(),
                'redemptions' => $redemptions,
                'last_redemption' => $redemptions->first()
            ]
        ]);
    }

    /**
     * Calculate the total amount due for unredeemed loyalty cards
     * Using flat fee of 100 DA per redemption
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateAmountDue()
    {
        try {
            // Get the authenticated user
            $user = auth()->user();

            // Get the user's shop
            $shop = $user->shops()->first();

            if (!$shop) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No shop found for this user'
                ], 404);
            }

            // Get all loyalty card IDs for the shop
            $loyaltyCardIds = $shop->loyaltyCards()->pluck('id')->toArray();

            if (empty($loyaltyCardIds)) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'total_amount_due' => 0,
                        'unredeemed_count' => 0,
                        'flat_fee_per_redemption' => 100,
                        'details' => []
                    ]
                ]);
            }

            // Count total unredeemed redemptions for all shop's loyalty cards
            $totalUnredeemed = RedemptionStatistic::whereIn('loyalty_card_id', $loyaltyCardIds)
                ->where('is_payed', 0)
                ->count();

            // Calculate total amount due (flat fee of 100 DA per redemption)
            $flatFeePerRedemption = 100;
            $totalAmountDue = $totalUnredeemed * $flatFeePerRedemption;

            // Debug logging
            \Log::info("Shop ID {$shop->id}: Total unredeemed = {$totalUnredeemed}, Total amount due = {$totalAmountDue}");

            // Get breakdown by loyalty card for details
            $details = [];
            foreach ($loyaltyCardIds as $cardId) {
                $card = \App\Models\LoyaltyCard::find($cardId);
                if ($card) {
                    $unredeemedCount = RedemptionStatistic::where('loyalty_card_id', $cardId)
                        ->where('is_payed', 0)
                        ->count();

                    if ($unredeemedCount > 0) {
                        $cardAmount = $unredeemedCount * $flatFeePerRedemption;
                        $details[] = [
                            'loyalty_card_id' => $cardId,
                            'card_name' => 'Loyalty Card #' . $cardId,
                            'total_stamps' => $card->total_stamps,
                            'flat_fee_per_redemption' => $flatFeePerRedemption,
                            'unredeemed_count' => $unredeemedCount,
                            'amount_for_card' => $cardAmount
                        ];
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_amount_due' => $totalAmountDue,
                    'unredeemed_count' => $totalUnredeemed,
                    'flat_fee_per_redemption' => $flatFeePerRedemption,
                    'details' => $details
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to calculate amount due',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
