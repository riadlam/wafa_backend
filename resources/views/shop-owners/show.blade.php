<!DOCTYPE html>
@php
function getImageUrl($imagePath) {
    if (!$imagePath) return '';

    // If the path already contains 'storage/', remove it to avoid duplication
    $cleanPath = str_contains($imagePath, 'storage/') ? str_replace('storage/', '', $imagePath) : $imagePath;

    // Return the asset URL
    return asset('storage/' . $cleanPath);
}
@endphp
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $shopOwner->name }} - Shop Owner Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $shopOwner->name }}</h1>
                        <p class="text-gray-600">Shop Owner Details & Analytics</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/shop-owners" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Shop Owners
                        </a>
                        <a href="/" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Shop Owner Info -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="flex items-center space-x-4 mb-6">
                    @if($shop && $shop->images && count($shop->images) > 0)
                        <div class="relative">
                            <img src="{{ getImageUrl($shop->images[0]) }}"
                                 alt="{{ $shop->name }} Logo"
                                 class="w-20 h-20 rounded-full object-cover border-4 border-orange-200 shadow-lg cursor-pointer hover:border-orange-300 transition-colors"
                                 onclick="openImageModal('{{ getImageUrl($shop->images[0]) }}', '{{ $shop->name }} Logo')"
                            <div class="absolute -bottom-1 -right-1 bg-orange-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-md">
                                <i class="fas fa-crown"></i>
                            </div>
                        </div>
                    @else
                        <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center border-4 border-green-300 shadow-lg">
                            <i class="fas fa-store text-green-600 text-2xl"></i>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $shopOwner->name }}</h2>
                        <p class="text-gray-600">{{ $shopOwner->email }}</p>
                        <div class="flex items-center space-x-4 mt-1">
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Joined: {{ $shopOwner->created_at->format('M d, Y') }}
                            </p>
                            @if($shop)
                            <p class="text-sm text-blue-600 font-medium">
                                <i class="fas fa-store mr-1"></i>
                                {{ $shop->name }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($shop)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Shop Basic Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">Shop Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Shop Name:</span> {{ $shop->name }}</div>
                            <div><span class="font-medium">Category:</span> {{ $shop->category->name ?? 'N/A' }}</div>
                            @if($shop->contact_info)
                            <div><span class="font-medium">Phone:</span> {{ $shop->contact_info['phone'] ?? 'N/A' }}</div>
                            @if(isset($shop->contact_info['email']))
                            <div><span class="font-medium">Email:</span> {{ $shop->contact_info['email'] }}</div>
                            @endif
                            @if(isset($shop->contact_info['website']))
                            <div><span class="font-medium">Website:</span> <a href="{{ $shop->contact_info['website'] }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $shop->contact_info['website'] }}</a></div>
                            @endif
                            @if(isset($shop->contact_info['description']))
                            <div class="mt-3">
                                <span class="font-medium">Description:</span>
                                <p class="text-gray-700 mt-1">{{ $shop->contact_info['description'] }}</p>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>

                    <!-- Shop Location -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-medium text-green-900 mb-2 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>Location
                        </h3>
                        <div class="space-y-2 text-sm">
                            @php
                                $shopLocation = $shop->shopLocations->first(); // Get primary location
                            @endphp

                            @if($shopLocation)
                                @if($shopLocation->name)
                                <div><span class="font-medium">Address:</span> {{ $shopLocation->name }}</div>
                                @endif

                                <div><span class="font-medium">Coordinates:</span>
                                    <span class="text-xs text-gray-600">
                                        {{ $shopLocation->lat }}, {{ $shopLocation->lng }}
                                    </span>
                                </div>

                                {{-- Show wilaya if available in shop's location field --}}
                                @if($shop->location && isset($shop->location['wilaya']))
                                <div><span class="font-medium">Wilaya:</span> {{ $shop->location['wilaya'] }}</div>
                                @endif
                            @elseif($shop->location)
                                {{-- Fallback to shop's location field if shop_locations is empty --}}
                                @if(isset($shop->location['address']))
                                <div><span class="font-medium">Address:</span> {{ $shop->location['address'] }}</div>
                                @endif

                                @if(isset($shop->location['wilaya']))
                                <div><span class="font-medium">Wilaya:</span> {{ $shop->location['wilaya'] }}</div>
                                @endif

                                @if(isset($shop->location['coordinates']))
                                <div><span class="font-medium">Coordinates:</span>
                                    <span class="text-xs text-gray-600">
                                        {{ $shop->location['coordinates']['latitude'] ?? 'N/A' }},
                                        {{ $shop->location['coordinates']['longitude'] ?? 'N/A' }}
                                    </span>
                                </div>
                                @endif
                            @else
                                <div class="text-gray-500 italic">Location not set</div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-medium text-blue-900 mb-2">Statistics</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Active Cards:</span> {{ $activeCards }}</div>
                            <div><span class="font-medium">Total Subscribers:</span> {{ $totalSubscribers }}</div>
                            <div><span class="font-medium">Total Redemptions:</span>
                                <span data-total-redemptions="{{ $totalRedemptions }}">{{ $totalRedemptions }}</span>
                            </div>
                            <div><span class="font-medium">Amount Due:</span>
                                <span data-amount-due="{{ $totalAmountDue }}">{{ $totalAmountDue }} DA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shop Images Gallery -->
                @if($shop->images && count($shop->images) > 0)
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-images mr-2 text-gray-600"></i>Shop Images
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($shop->images as $index => $image)
                        <div class="relative group">
                            <img src="{{ getImageUrl($image) }}"
                                 alt="Shop Image {{ $index + 1 }}"
                                 class="w-full h-32 object-cover rounded-lg border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer"
                                 onclick="openImageModal('{{ getImageUrl($image) }}', 'Shop Image {{ $index + 1 }}')"
                            @if($index === 0)
                            <div class="absolute top-2 left-2 bg-orange-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                                <i class="fas fa-star mr-1"></i>Logo
                            </div>
                            @endif
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity rounded-lg flex items-center justify-center">
                                <i class="fas fa-eye text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="mt-8 bg-gray-50 rounded-lg p-8 text-center">
                    <i class="fas fa-image text-gray-300 text-3xl mb-3"></i>
                    <p class="text-gray-500">No images uploaded for this shop</p>
                </div>
                @endif

                @endif
            </div>

            <!-- Loyalty Cards & Subscribers -->
            @if($shop && $shop->loyaltyCards->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Loyalty Cards & Subscribers</h3>

                <div class="space-y-6">
                    @foreach($shop->loyaltyCards as $card)
                    <div class="border border-gray-200 rounded-lg p-6"
                         data-loyalty-card
                         data-loyalty-card-id="{{ $card->id }}"
                         data-loyalty-card-name="{{ $card->description ?? 'Loyalty Card #' . $card->id }}"
                         data-total-stamps="{{ $card->total_stamps }}">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-medium text-gray-900">{{ $card->description ?? 'Loyalty Card #' . $card->id }}</h4>
                            <div class="flex items-center space-x-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                    {{ $card->total_stamps }} stamps required
                                </span>
                                <span class="text-sm text-gray-600">
                                    {{ $card->userCards->count() }} subscribers
                                </span>
                            </div>
                        </div>

                        @if($card->userCards->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stamps</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($card->userCards as $userCard)
                                    <tr data-subscriber
                                        data-user-id="{{ $userCard->user->id }}"
                                        data-user-name="{{ $userCard->user->name }}"
                                        data-stamps="{{ $userCard->active_stamps }}">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-user text-blue-600 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $userCard->user->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $userCard->user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $userCard->active_stamps }}/{{ $card->total_stamps }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                     style="width: {{ ($userCard->active_stamps / $card->total_stamps) * 100 }}%"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ round(($userCard->active_stamps / $card->total_stamps) * 100) }}% complete
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($userCard->active_stamps >= $card->total_stamps)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Ready for Redemption
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    In Progress
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-users text-3xl mb-2"></i>
                            <p>No subscribers yet for this loyalty card.</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center py-12">
                    <i class="fas fa-store text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Shop Found</h3>
                    <p class="text-gray-500">This shop owner doesn't have an active shop yet.</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Image Modal -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
            <div class="relative max-w-4xl max-h-full">
                <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
                <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full p-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        function openImageModal(imageSrc, altText) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalImage').alt = altText;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('imageModal').classList.contains('hidden')) {
                closeImageModal();
            }
        });
    </script>
</body>
</html>
