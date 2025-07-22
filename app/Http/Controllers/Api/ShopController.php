<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }
    
    // GET /api/categories/{category}/shops
    public function getByCategory($categoryId)
    {
        $shops = Shop::with([
                    'owner', 
                    'category',
                    'loyaltyCards' => function($query) {
                        $query->select(['id', 'shop_id', 'logo_url as logo', 'color as backgroundColor', 'total_stamps', 'description']);
                    },
                    'shopLocations' => function($query) {
                        $query->select(['id', 'shop_id', 'lat', 'lng', 'name']);
                    }
                ])
                ->where('category_id', $categoryId)
                ->get(['id', 'user_id', 'category_id', 'name', 'contact_info', 'location', 'images', 'created_at', 'updated_at'])
                ->map(function($shop) {
                    $shop->category_name = $shop->category->name;
                    return $shop;
                });
        
        return response()->json($shops);
    }
    // GET /api/my-shops/loyalty-cards
    public function getMyShopsLoyaltyCards()
    {
        $user = auth()->user();
        
        $shops = $user->shops()->with(['loyaltyCards' => function($query) {
            return $query->select(['id', 'shop_id', 'logo_url', 'color', 'total_stamps', 'description', 'created_at']);
        }])->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $shops
        ]);
    }
    
    // GET /api/shops
    public function index()
    {
        return response()->json(Shop::with('owner', 'category')->get());
    }

    // POST /api/shops
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string',
            'contact_info' => 'nullable|array',
            'location' => 'nullable|string',
        ]);

        $shop = Shop::create($data);
        return response()->json($shop->load('owner', 'category'), 201);
    }

    // GET /api/shops/{shop}
    public function show(Shop $shop)
    {
        return response()->json($shop->load('owner', 'category'));
    }

    // PUT/PATCH /api/shops/{shop}
    public function update(Request $request, Shop $shop)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'sometimes|string',
            'contact_info' => 'nullable|array',
            'location' => 'nullable|string',
        ]);

        $shop->update($data);
        return response()->json($shop->load('owner', 'category'));
    }

    // DELETE /api/shops/{shop}
    public function destroy(Shop $shop)
    {
        $shop->delete();
        return response()->json(null, 204);
    }

    // GET /api/shops/search?q={search_term}
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $searchTerm = '%' . $request->input('q') . '%';

        $shops = Shop::with([
                    'owner', 
                    'category',
                    'loyaltyCards' => function($query) {
                        $query->select(['id', 'shop_id', 'logo_url as logo', 'color as backgroundColor', 'total_stamps']);
                    },
                    'shopLocations' => function($query) {
                        $query->select(['id', 'shop_id', 'lat', 'lng', 'name']);
                    }
                ])
                ->where('name', 'LIKE', $searchTerm)
                ->get(['id', 'user_id', 'category_id', 'name', 'contact_info', 'location', 'images', 'created_at', 'updated_at'])
                ->map(function($shop) {
                    $shop->category_name = $shop->category->name ?? null;
                    return $shop;
                });

        return response()->json([
            'success' => true,
            'data' => $shops,
            'message' => 'Shops retrieved successfully'
        ]);
    }
}
