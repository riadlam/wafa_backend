<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopOwnerManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');

        $query = User::where('role', 'shop_owner');

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by category if provided
        if ($category) {
            $query->whereHas('shops', function ($q) use ($category) {
                $q->where('category_id', $category);
            });
        }

        $shopOwners = $query->with([
            'shops.category',
            'shops.loyaltyCards.userCards.user',
            'shops.loyaltyCards.userCards.stamps'
        ])
                            ->orderBy('created_at', 'desc')
                            ->paginate(20)
                            ->withQueryString();

        // Get all categories for filter dropdown
        $categories = Category::all();

        return view('shop-owners.index', [
            'shopOwners' => $shopOwners,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $category,
        ]);
    }

    public function show(User $shopOwner)
    {
        // Load shop owner's shop with all related data
        $shopOwner->load([
            'shops.category',
            'shops.loyaltyCards.userCards.user',
            'shops.loyaltyCards.userCards.stamps',
            'shops.shopLocations' // Load shop locations
        ]);

        // Get shop first
        $shop = $shopOwner->shops->first();

        // Calculate additional statistics
        $totalRedemptions = 0;
        $totalAmountDue = 0;

        if ($shop) {
            // Calculate total redemptions
            $totalRedemptions = \App\Models\RedemptionStatistic::whereIn('loyalty_card_id',
                $shop->loyaltyCards->pluck('id')->toArray()
            )->where('is_payed', 0)->count();

            // Calculate amount due (flat fee of 100 DA per redemption)
            $totalAmountDue = $totalRedemptions * 100;
        }

        if ($shop) {
            $totalSubscribers = $shop->loyaltyCards->sum(function ($card) {
                return $card->userCards->count();
            });

            // Calculate total redemptions from RedemptionStatistic model
            $loyaltyCardIds = $shop->loyaltyCards->pluck('id')->toArray();
            $totalRedemptions = \App\Models\RedemptionStatistic::whereIn('loyalty_card_id', $loyaltyCardIds)
                ->where('is_payed', 0)
                ->count();

            $activeCards = $shop->loyaltyCards->count();

            return view('shop-owners.show', [
                'shopOwner' => $shopOwner,
                'shop' => $shop,
                'totalSubscribers' => $totalSubscribers,
                'totalRedemptions' => $totalRedemptions,
                'totalAmountDue' => $totalAmountDue,
                'activeCards' => $activeCards,
            ]);
        }

        return view('shop-owners.show', [
            'shopOwner' => $shopOwner,
            'shop' => null,
            'totalSubscribers' => 0,
            'totalRedemptions' => 0,
            'activeCards' => 0,
        ]);
    }
}
