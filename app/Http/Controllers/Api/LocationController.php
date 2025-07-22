<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Filter shops by wilaya
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Filter shops by wilaya and optionally by category
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Filter shops by wilaya and optionally by category
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByWilaya(Request $request)
    {
        $request->validate([
            'wilaya' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $wilaya = $request->input('wilaya');
        $categoryId = $request->input('category_id');

        // Start building the query to get shop locations
        $query = ShopLocation::where('name', 'LIKE', '%"wilaya":"' . $wilaya . '"%')
            ->with([
                'shop' => function($query) use ($categoryId) {
                    $query->with([
                        'owner',
                        'category',
                        'loyaltyCards' => function($query) {
                            $query->select([
                                'id', 
                                'shop_id', 
                                'logo_url as logo', 
                                'color as backgroundColor', 
                                'total_stamps', 
                                'description'
                            ]);
                        },
                        'shopLocations' => function($query) {
                            $query->select(['id', 'shop_id', 'lat', 'lng', 'name']);
                        }
                    ]);
                    
                    if ($categoryId) {
                        $query->where('category_id', $categoryId);
                    }
                }
            ])
            ->whereHas('shop', function($query) use ($categoryId) {
                $query->whereHas('loyaltyCards');
                
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }
            });

        // Get the results
        $shopLocations = $query->get();

        // Process the results to match the desired structure
        $shops = $shopLocations->map(function ($location) {
            if (!$location->relationLoaded('shop') || !$location->shop) {
                return null;
            }

            $shop = $location->shop;
            
            // Add category_name to the shop
            $shop->category_name = $shop->category->name ?? null;
            
            // Ensure shop_locations is a collection
            if (!isset($shop->shop_locations)) {
                $shop->shop_locations = collect([]);
            } else if (is_array($shop->shop_locations)) {
                $shop->shop_locations = collect($shop->shop_locations);
            }
            
            // Add the current location if not already in the collection
            $locationExists = $shop->shop_locations->contains('id', $location->id);
            if (!$locationExists) {
                $shop->shop_locations->push($location);
            }
            
            return $shop;
        })
        ->filter()
        ->unique('id')
        ->values();

        return response()->json($shops);
    }
}
