<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLoyaltyCard;
use App\Models\User;
use App\Models\LoyaltyCard;
use App\Models\Shop;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserLoyaltyCardController extends Controller
{
    // GET /api/user-loyalty-cards
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }
    
    // GET /api/user/loyalty-cards/{loyalty_card_id}/active-stamps
    public function getActiveStampsCount($loyaltyCardId)
    {
        $user = auth()->user();
        
        $userLoyaltyCard = UserLoyaltyCard::where('user_id', $user->id)
            ->where('loyalty_card_id', $loyaltyCardId)
            ->firstOrFail();
            
        return response()->json([
            'success' => true,
            'data' => [
                'active_stamps' => (int)$userLoyaltyCard->active_stamps
            ]
        ]);
    }

    /**
     * Get total subscribers count for all loyalty cards owned by the authenticated shop owner
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalSubscribers()
    {
        try {
            $user = auth()->user();
            
            // Get all loyalty cards for the shop owner's shops
            $loyaltyCards = LoyaltyCard::whereHas('shop', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->pluck('id');
            
            if ($loyaltyCards->isEmpty()) {
                return response()->json([
                    'total_subscribers' => 0
                ]);
            }
            
            // Get total unique subscribers across all loyalty cards
            $totalSubscribers = UserLoyaltyCard::whereIn('loyalty_card_id', $loyaltyCards)
                ->distinct('user_id')
                ->count('user_id');
            
            return response()->json([
                'total_subscribers' => $totalSubscribers
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch subscriber count: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // GET /api/user/loyalty-cards
    public function getUserLoyaltyCards()
    {
        $user = auth()->user();
        $user->load(['loyaltyCards.loyaltyCard.shop.category']);
        
        $loyaltyCards = $user->loyaltyCards->map(function($userLoyaltyCard) {
            return [
                'id' => $userLoyaltyCard->id,
                'active_stamps' => $userLoyaltyCard->active_stamps,
                'card' => [
                    'id' => $userLoyaltyCard->loyaltyCard->id,
                    'logo' => $userLoyaltyCard->loyaltyCard->logo_url,
                    'color' => $userLoyaltyCard->loyaltyCard->color,
                    'total_stamps' => $userLoyaltyCard->loyaltyCard->total_stamps,
                    'shop' => [
                        'id' => $userLoyaltyCard->loyaltyCard->shop->id,
                        'name' => $userLoyaltyCard->loyaltyCard->shop->name,
                        'images' => $userLoyaltyCard->loyaltyCard->shop->images ?? [],
                        'category' => $userLoyaltyCard->loyaltyCard->shop->category ? [
                            'id' => $userLoyaltyCard->loyaltyCard->shop->category->id,
                            'name' => $userLoyaltyCard->loyaltyCard->shop->category->name,
                            'icon' => $userLoyaltyCard->loyaltyCard->shop->category->icon
                        ] : null
                    ]
                ]
            ];
        });
        
        return response()->json([
            'user_id' => $user->id,
            'total_cards' => $loyaltyCards->count(),
            'loyalty_cards' => $loyaltyCards
        ]);
    }
    
    public function index()
    {
        return response()->json(
            UserLoyaltyCard::with(['user', 'loyaltyCard.shop'])->get()
        );
    }

    // POST /api/user-loyalty-cards
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'loyalty_card_id' => 'required|exists:loyalty_cards,id',
        ]);

        $userLoyaltyCard = UserLoyaltyCard::create($data);
        return response()->json(
            $userLoyaltyCard->load(['user', 'loyaltyCard.shop']),
            201
        );
    }

    // GET /api/user-loyalty-cards/{userLoyaltyCard}
    public function show(UserLoyaltyCard $userLoyaltyCard)
    {
        $userLoyaltyCard->load(['user', 'loyaltyCard.shop', 'stamps']);
        
        $response = array_merge(
            $userLoyaltyCard->toArray(),
            ['active_stamps_count' => $userLoyaltyCard->active_stamps]
        );
        
        return response()->json($response);
    }

    // PUT/PATCH /api/user-loyalty-cards/{userLoyaltyCard}
    public function update(Request $request, UserLoyaltyCard $userLoyaltyCard)
    {
        $data = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'loyalty_card_id' => 'sometimes|exists:loyalty_cards,id',
        ]);

        $userLoyaltyCard->update($data);
        return response()->json(
            $userLoyaltyCard->load(['user', 'loyaltyCard.shop'])
        );
    }

    // DELETE /api/user-loyalty-cards/{userLoyaltyCard}
    public function destroy(UserLoyaltyCard $userLoyaltyCard)
    {
        $userLoyaltyCard->delete();
        return response()->json(null, 204);
    }

    /**
     * Get recent stamp activations for the authenticated user's shop
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentStamps()
    {
        $user = auth()->user();
        
        // Find the shop associated with the user
        $shop = Shop::where('user_id', $user->id)->first();
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'No shop found for this user'
            ], 404);
        }
        
        // Get all loyalty cards for this shop
        $loyaltyCards = LoyaltyCard::where('shop_id', $shop->id)->pluck('id');
        
        // Get all user loyalty cards for these loyalty cards with required relationships
        $userLoyaltyCards = UserLoyaltyCard::whereIn('loyalty_card_id', $loyaltyCards)
            ->with(['user', 'loyaltyCard'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Format the response
        $response = $userLoyaltyCards->map(function($userLoyaltyCard) {
            $now = now();
            $createdAt = $userLoyaltyCard->created_at;
            
            // Ensure we're working with Carbon instances
            $now = \Carbon\Carbon::parse($now);
            $createdAt = \Carbon\Carbon::parse($createdAt);
            
            // Get the difference in a way that's always positive and whole numbers
            $diffInSeconds = (int)abs($now->diffInSeconds($createdAt, false));
            $diffInMinutes = (int)abs($now->diffInMinutes($createdAt, false));
            $diffInHours = (int)abs($now->diffInHours($createdAt, false));
            $diffInDays = (int)abs($now->diffInDays($createdAt, false));
            $diffInWeeks = (int)abs($now->diffInWeeks($createdAt, false));
            
            if ($diffInSeconds < 60) {
                $timeAgo = $diffInSeconds . ' ' . Str::plural('second', $diffInSeconds) . ' ago';
            } elseif ($diffInMinutes < 60) {
                $timeAgo = $diffInMinutes . ' ' . Str::plural('minute', $diffInMinutes) . ' ago';
            } elseif ($diffInHours < 24) {
                $timeAgo = $diffInHours . ' ' . Str::plural('hour', $diffInHours) . ' ago';
            } elseif ($diffInDays < 7) {
                $timeAgo = $diffInDays . ' ' . Str::plural('day', $diffInDays) . ' ago';
            } else {
                $timeAgo = $diffInWeeks . ' ' . Str::plural('week', $diffInWeeks) . ' ago';
            }
            
            return [
                'username' => $userLoyaltyCard->user->name,
                'active_stamps' => (int)$userLoyaltyCard->active_stamps,
                'total_stamps' => (int)$userLoyaltyCard->loyaltyCard->total_stamps,
                'time_ago' => $timeAgo
            ];
        });
        
        return response()->json($response);
    }
}
