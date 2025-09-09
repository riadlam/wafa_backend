<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoyaltyCard;
use App\Models\Stamp;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role', 'user'); // Default to regular users

        $query = User::query();

        // Filter by role - exclude shop owners if showing regular users
        if ($role === 'user') {
            $query->where('role', '!=', 'shop_owner');
        } elseif ($role === 'shop_owner') {
            $query->where('role', 'shop_owner');
        }

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with(['loyaltyCards.loyaltyCard.shop', 'addedStamps'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(20)
                       ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    public function show(User $user)
    {
        // Load user's loyalty cards with stamps and shop info
        $user->load([
            'loyaltyCards.loyaltyCard.shop',
            'loyaltyCards.stamps',
            'shops.loyaltyCards'
        ]);

        // Get user's stamp statistics
        $totalStamps = $user->addedStamps()->count();
        $completedCards = $user->loyaltyCards()->where('active_stamps', '>=', function ($query) {
            $query->selectRaw('loyalty_cards.total_stamps')
                  ->from('loyalty_cards')
                  ->whereColumn('loyalty_cards.id', 'user_loyalty_cards.loyalty_card_id');
        })->count();

        return view('users.show', [
            'user' => $user,
            'totalStamps' => $totalStamps,
            'completedCards' => $completedCards,
        ]);
    }
}
