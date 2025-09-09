<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\LoyaltyCard;
use App\Models\Shop;
use App\Models\ShopLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class LoyaltyCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }
    
    
    // GET /api/loyalty-cards
    public function index()
    {
        return response()->json(LoyaltyCard::with('shop')->get());
    }

    // POST /api/loyalty-cards
    public function store(Request $request)
    {
        $data = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'logo_url' => 'nullable|string',
            'color' => 'nullable|string',
            'total_stamps' => 'required|integer|min:1',
        ]);

        $loyaltyCard = LoyaltyCard::create($data);
        return response()->json($loyaltyCard, 201);
    }

    // GET /api/loyalty-cards/{loyaltyCard}
    public function show(LoyaltyCard $loyaltyCard)
    {
        $loyaltyCard->load('shop');
        return response()->json($loyaltyCard);
    }


    // DELETE /api/loyalty-cards/{loyaltyCard}
    public function destroy(LoyaltyCard $loyaltyCard)
    {
        $loyaltyCard->delete();
        return response()->json(null, 204);
    }

    /**
     * Update the specified loyalty card
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Save or update shop location
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveShopLocation(Request $request)
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'name' => 'required|string|max:255',
            ]);
            
            // Get the user's shop
            $shop = Shop::where('user_id', $user->id)->firstOrFail();
            
            // Update or create the shop location
            $location = ShopLocation::updateOrCreate(
                ['shop_id' => $shop->id],
                [
                    'user_id' => $user->id,
                    'lat' => $validated['lat'],
                    'lng' => $validated['lng'],
                    'name' => $validated['name']
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Shop location saved successfully',
                'data' => $location
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving shop location:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save shop location',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create or update loyalty card with shop details and location
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upsertLoyaltyCard(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $user = auth()->user();
            
            $validated = $request->validate([
                'shop_name' => 'required|string|max:255',
                'color' => 'required|string|max:50',
                'total_stamps' => 'required|integer|min:1',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'location_name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            // Debug: Log all request files
            \Log::info('Uploaded files:', $request->allFiles());
            
            // Create directories if they don't exist
            $shopLogosDir = public_path('storage/shop_logos');
            $storeImagesDir = public_path('storage/store_images');
            
            if (!File::isDirectory($shopLogosDir)) {
                File::makeDirectory($shopLogosDir, 0755, true, true);
            }
            if (!File::isDirectory($storeImagesDir)) {
                File::makeDirectory($storeImagesDir, 0755, true, true);
            }

            // Handle logo upload if present
            $logoPath = null;
            if ($request->hasFile('logo')) {
                try {
                    $logo = $request->file('logo');
                    
                    // Verify file was uploaded successfully
                    if (!$logo->isValid()) {
                        throw new \Exception('Invalid file upload: ' . $logo->getErrorMessage());
                    }
                    
                    // Verify file exists and is readable
                    if (!file_exists($logo->getPathname()) || !is_readable($logo->getPathname())) {
                        throw new \Exception('Temporary file is not accessible: ' . $logo->getPathname());
                    }
                    
                    $logoName = 'logo_' . time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                    $destinationPath = $shopLogosDir . '/' . $logoName;
                    
                    // Use copy instead of move to avoid permission issues
                    if (!copy($logo->getPathname(), $destinationPath)) {
                        throw new \Exception('Failed to copy file to destination');
                    }
                    
                    // Set proper permissions
                    chmod($destinationPath, 0644);
                    
                    $logoPath = 'storage/shop_logos/' . $logoName;
                    \Log::info('Logo uploaded successfully: ' . $logoPath);
                    
                } catch (\Exception $e) {
                    \Log::error('Logo upload error:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload logo',
                        'error' => $e->getMessage(),
                        'details' => 'Check server logs for more information'
                    ], 500);
                }
            }

            // Handle images upload if present
            $uploadedImages = [];
            if ($request->hasFile('images')) {
                try {
                    foreach ($request->file('images') as $key => $image) {
                        if ($image->isValid()) {
                            // Verify file exists and is readable
                            if (!file_exists($image->getPathname()) || !is_readable($image->getPathname())) {
                                \Log::warning('Skipping invalid image upload:', [
                                    'name' => $image->getClientOriginalName(),
                                    'error' => 'Temporary file is not accessible'
                                ]);
                                continue;
                            }
                            
                            $imageName = 'img_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $destinationPath = $storeImagesDir . '/' . $imageName;
                            
                            if (copy($image->getPathname(), $destinationPath)) {
                                chmod($destinationPath, 0644);
                                $uploadedImages[] = 'storage/store_images/' . $imageName;
                                \Log::info('Image uploaded successfully: ' . $imageName);
                            } else {
                                \Log::warning('Failed to copy image: ' . $image->getClientOriginalName());
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Images upload error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload images',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
            
            // Get existing shop if it exists
            $shop = Shop::where('user_id', $user->id)->first();
            
            // Prepare shop data with location
            $shopData = [
                'name' => $validated['shop_name'],
                'category_id' => $validated['category_id'],
                'location' => [
                    'address' => $validated['location_name'],
                    'coordinates' => [
                        'latitude' => $validated['lat'],
                        'longitude' => $validated['lng']
                    ],
                    'wilaya' => $request->input('wilaya', null) // Optional wilaya field
                ]
            ];
            
            // If shop exists, merge existing images with new ones
            if ($shop) {
                $existingImages = $shop->images ?? [];
                $shopData['images'] = array_merge($existingImages, $uploadedImages);
                $shop->update($shopData);
            } else {
                // Create new shop with uploaded images
                $shopData['images'] = $uploadedImages;
                $shop = Shop::create(array_merge($shopData, ['user_id' => $user->id]));
            }
            
            // Handle logo upload if present
            $logoUrl = $shop->logo_url ?? null;
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($shop->logo_url) {
                    $oldLogoPath = public_path(str_replace(url('/'), '', $shop->logo_url));
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }
                
                // Store new logo
                $logoName = 'logo_' . time() . '_' . uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();
                $logoPath = 'storage/shop_logos/' . $logoName;
                $destinationPath = public_path($logoPath);
                
                // Ensure directory exists
                if (!file_exists(dirname($destinationPath))) {
                    mkdir(dirname($destinationPath), 0755, true);
                }
                
                // Move the file
                $request->file('logo')->move(dirname($destinationPath), $logoName);
                $logoUrl = url($logoPath);
            }
            
            // Create or update loyalty card
            $loyaltyCard = LoyaltyCard::updateOrCreate(
                ['shop_id' => $shop->id],
                [
                    'color' => $validated['color'],
                    'total_stamps' => $validated['total_stamps'],
                    'logo_url' => $logoUrl ?? $shop->logo_url,
                    'description' => $request->input('description', $shop->loyaltyCard->description ?? null)
                ]
            );
            
            // Create or update shop location
            $location = ShopLocation::updateOrCreate(
                ['shop_id' => $shop->id],
                [
                    'user_id' => $user->id,
                    'lat' => $validated['lat'],
                    'lng' => $validated['lng'],
                    'name' => $validated['location_name']
                ]
            );
            
            DB::commit();
            
            // Get fresh data with proper URLs
            $freshShop = $shop->fresh();
            $freshLoyaltyCard = $loyaltyCard->fresh();
            
            // Ensure logo URL is absolute
            if ($freshShop->logo_url && !filter_var($freshShop->logo_url, FILTER_VALIDATE_URL)) {
                $freshShop->logo_url = url($freshShop->logo_url);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Loyalty card updated successfully',
                'data' => [
                    'shop' => $freshShop,
                    'loyalty_card' => $freshLoyaltyCard,
                    'location' => $location,
                    'logo_url' => $freshShop->logo_url  // Explicitly include logo URL
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating loyalty card:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update loyalty card',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function updateLoyaltyCard(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Get the shop that belongs to the authenticated user
            $shop = Shop::where('user_id', $user->id)->first();
            
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'No shop found for this user.'
                ], 404);
            }
            
            // Get the loyalty card for this shop
            $loyaltyCard = LoyaltyCard::where('shop_id', $shop->id)->first();
            
            if (!$loyaltyCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'No loyalty card found for this shop.'
                ], 404);
            }
            
            // Validate the request
            $validated = $request->validate([
                'shop_name' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:50',
                'total_stamps' => 'nullable|integer|min:1',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'description' => 'nullable|string',
            ]);
            
            // Update shop name if provided
            if ($request->has('shop_name') && $request->shop_name !== $shop->name) {
                $shop->update(['name' => $request->shop_name]);
                $shop->refresh(); // Refresh to get updated shop data
            }
            
            // Handle logo upload if present
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($loyaltyCard->logo_url) {
                    $oldLogoPath = str_replace('/storage', 'public', $loyaltyCard->logo_url);
                    if (Storage::exists($oldLogoPath)) {
                        Storage::delete($oldLogoPath);
                    }
                }
                
                // Store new logo in the correct path
                $logoPath = $request->file('logo')->store('shop_logos', 'public');
                // Generate the full public URL
                $validated['logo_url'] = asset(Storage::url($logoPath));
            }
            
            // Update the loyalty card
            $loyaltyCard->update($validated);
            
            // Get updated data
            $updatedCard = $loyaltyCard->fresh();
            
            // Prepare response data
            $response = [
                'success' => true,
                'message' => 'Loyalty card updated successfully',
                'shop_name' => $shop->name, // This will now show the updated name
                'shop_id' => $shop->id,
                'description' => $validated['description'] ?? $loyaltyCard->description,
                'loyalty_card_id' => $updatedCard->id,
                'logo_url' => $updatedCard->logo_url ? url($updatedCard->logo_url) : null,
                'color' => $updatedCard->color,
                'total_stamps' => $updatedCard->total_stamps,
                'updated_at' => $updatedCard->updated_at->toDateTimeString()
            ];
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error updating loyalty card:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update loyalty card',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
