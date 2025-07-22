<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stamp;
use App\Models\UserLoyaltyCard;
use Illuminate\Support\Facades\Auth;

class StampController extends Controller
{
    // GET /api/stamps
    public function index()
    {
        return response()->json(Stamp::with('userLoyaltyCard')->get());
    }

    // POST /api/stamps
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_loyalty_card_id' => 'required|exists:user_loyalty_cards,id',
            'is_active' => 'required|boolean',
        ]);

        $stamp = Stamp::create($data);
        return response()->json($stamp->load('userLoyaltyCard'), 201);
    }

    // GET /api/stamps/{stamp}
    public function show(Stamp $stamp)
    {
        return response()->json($stamp->load('userLoyaltyCard'));
    }

    // PUT/PATCH /api/stamps/{stamp}
    public function update(Request $request, Stamp $stamp)
    {
        $data = $request->validate([
            'is_active' => 'sometimes|boolean',
        ]);

        $stamp->update($data);
        return response()->json($stamp->load('userLoyaltyCard'));
    }

    // GET /api/stamps/plan
    public function getPlanInfo()
    {
        $user = Auth::user();
        $expirationDate = $user->plan === 'free' 
            ? $user->trial_ends_at 
            : $user->pro_ends_at;
            
        $now = now();
        $diffInMinutes = $expirationDate ? $now->diffInMinutes($expirationDate, false) : null;
        
        $readableExpiration = null;
        if ($diffInMinutes !== null) {
            if ($diffInMinutes < 0) {
                $readableExpiration = 'Expired';
            } elseif ($diffInMinutes < 1) {
                $readableExpiration = 'Less than a minute';
            } elseif ($diffInMinutes < 60) {
                $readableExpiration = $diffInMinutes . ' minute' . ($diffInMinutes > 1 ? 's' : '');
            } elseif ($diffInMinutes < 1440) { // Less than 24 hours
                $hours = floor($diffInMinutes / 60);
                $readableExpiration = $hours . ' hour' . ($hours > 1 ? 's' : '');
            } elseif ($diffInMinutes < 10080) { // Less than 7 days
                $days = floor($diffInMinutes / 1440);
                $readableExpiration = $days . ' day' . ($days > 1 ? 's' : '');
            } elseif ($diffInMinutes < 43800) { // Less than ~1 month (30.42 days)
                $weeks = floor($diffInMinutes / 10080);
                $readableExpiration = $weeks . ' week' . ($weeks > 1 ? 's' : '');
            } elseif ($diffInMinutes < 525600) { // Less than 1 year
                $months = floor($diffInMinutes / 43800);
                $readableExpiration = $months . ' month' . ($months > 1 ? 's' : '');
            } else {
                $years = floor($diffInMinutes / 525600);
                $readableExpiration = $years . ' year' . ($years > 1 ? 's' : '');
            }
        }
        
        return response()->json([
            'plan' => $user->plan,
            'expiration_date' => $expirationDate,
            'expires_in' => $readableExpiration,
        ]);
    }

    // DELETE /api/stamps/{stamp}
    public function destroy(Stamp $stamp)
    {
        $stamp->delete();
        return response()->json(null, 204);
    }

    // GET /api/user-loyalty-cards/{cardId}/active-stamps
    public function getActiveStamps($cardId)
    {
        $card = UserLoyaltyCard::with(['stamps' => function($query) {
            $query->where('is_active', true);
        }])->findOrFail($cardId);

        return response()->json([
            'card_id' => $card->id,
            'active_stamps_count' => $card->stamps->count(),
            'stamps' => $card->stamps
        ]);
    }
}
