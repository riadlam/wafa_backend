<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrScan;
use App\Models\Shop;
use App\Models\User;
use App\Models\LoyaltyCard;
use App\Models\UserLoyaltyCard;
use App\Models\Stamp;
use App\Models\RedemptionStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;
use App\Events\MessageSent;


class QrScanController extends Controller
{
    /**
     * Display a listing of the QR scans for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $scans = QrScan::with('shop')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $scans
        ]);
    }

    /**
     * Store a newly created QR scan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Check if the shop exists
        $shop = Shop::find($request->shop_id);
        if (!$shop) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shop not found'
            ], 404);
        }

        $scan = QrScan::create([
            'user_id' => $user->id,
            'shop_id' => $request->shop_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'QR scan recorded successfully',
            'data' => $scan->load('shop')
        ], 201);
    }

    /**
     * Display the specified QR scan.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $scan = QrScan::with('shop')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$scan) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR scan not found or unauthorized'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $scan
        ]);
    }

    /**
     * Update the specified QR scan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $scan = QrScan::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$scan) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR scan not found or unauthorized'
            ], 404);
        }


        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $scan->update($request->only(['status']));

        return response()->json([
            'status' => 'success',
            'message' => 'QR scan updated successfully',
            'data' => $scan->refresh()->load('shop')
        ]);
    }

    /**
     * Remove the specified QR scan from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $scan = QrScan::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$scan) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR scan not found or unauthorized'
            ], 404);
        }

        $scan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'QR scan deleted successfully'
        ]);
    }

    /**
     * Process QR code scan and add stamp to user's loyalty card
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processQrScan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $shopOwner = Auth::user();
        
        // Check if the authenticated user is a shop owner
        if ($shopOwner->role !== 'shop_owner') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only shop owners can process QR scans'
            ], 403);
        }

        // Get the shop owned by this user
        $shop = Shop::where('user_id', $shopOwner->id)->first();
        
        if (!$shop) {
            return response()->json([
                'status' => 'error',
                'message' => 'No shop found for this user'
            ], 404);
        }

        // Get the loyalty card for this shop
        $loyaltyCard = LoyaltyCard::where('shop_id', $shop->id)->first();
        
        if (!$loyaltyCard) {
            return response()->json([
                'status' => 'error',
                'message' => 'No loyalty card found for this shop'
            ], 404);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();
        
        // Get the shop owned by this user
        $shop = Shop::where('user_id', $shopOwner->id)->first();
        
        if (!$shop) {
            return response()->json([
                'status' => 'error',
                'message' => 'No shop found for this user'
            ], 404);
        }

        // Start database transaction
        return DB::transaction(function () use ($user, $loyaltyCard, $shopOwner, $shop) {
            // Find or create user loyalty card
            $userLoyaltyCard = UserLoyaltyCard::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'loyalty_card_id' => $loyaltyCard->id,
                ],
                ['active_stamps' => 0]
            );

            // Calculate new stamp count
            $newStampCount = $userLoyaltyCard->active_stamps + 1;
            $stampReset = false;

            // Check if we've reached the total stamps needed
            if ($newStampCount >= $loyaltyCard->total_stamps) {
                $newStampCount = 0;
                $stampReset = true;
            }

            // Update the user's stamp count
            $userLoyaltyCard->update(['active_stamps' => $newStampCount]);

            // Create a new stamp record
            $stamp = Stamp::create([
                'user_loyalty_card_id' => $userLoyaltyCard->id,
                'added_by' => $shopOwner->id,
            ]);

            // Create a QR scan record
            $qrScan = QrScan::create([
                'user_id' => $user->id,
                'shop_id' => $loyaltyCard->shop_id,
                'status' => 'scanned',
            ]);

            // Create a redemption record if the user completed their loyalty card
            if ($stampReset) {
                RedemptionStatistic::create([
                    'user_id' => $user->id,
                    'loyalty_card_id' => $loyaltyCard->id,
                ]);
            }

            // Prepare the message to broadcast
            $message = $stampReset
                ? "🎉 {$user->name} has completed a loyalty card at {$shop->name}! 🎊"
                : "✨ {$user->name} just earned a stamp at {$shop->name}! ({$newStampCount}/{$loyaltyCard->total_stamps})";

            // Broadcast the message
            broadcast(new MessageSent([
                'message' => $message,
                'time' => now()->format('H:i:s'),
                'stamp_count' => $newStampCount,
                'total_stamps' => $loyaltyCard->total_stamps,
                'user_name' => $user->name,
                'shop_name' => $shop->name,
                'stamp_reset' => $stampReset
            ]));

            return response()->json([
                'status' => 'success',
                'message' => $stampReset 
                    ? 'Stamp added successfully! Loyalty card has been reset.' 
                    : 'Stamp added successfully!',
                'data' => [
                    'user_loyalty_card' => $userLoyaltyCard->load('loyaltyCard'),
                    'current_stamps' => $newStampCount,
                    'total_stamps_needed' => $loyaltyCard->total_stamps,
                    'stamp_reset' => $stampReset,
                    'broadcasted_message' => $message
                ]
            ]);
        });
    }
}
