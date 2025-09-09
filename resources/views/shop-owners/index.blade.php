<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Owners Management - Wafa Loyalty</title>
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
                        <h1 class="text-2xl font-bold text-gray-900">Shop Owners Management</h1>
                        <p class="text-gray-600">Manage and view all shop owners</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Ads
                        </a>
                        <a href="/users" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-users mr-2"></i>Users
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Search -->
                    <div class="flex-1 max-w-md">
                        <form method="GET" class="relative">
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="Search shop owners by name or email..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <button type="submit" class="hidden">Search</button>
                        </form>
                    </div>

                    <!-- Category Filter -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Category:</span>
                        <select name="category" onchange="this.form.submit()"
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $selectedCategory == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Shop Owners Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($shopOwners as $shopOwner)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <!-- Shop Owner Header -->
                        <div class="flex items-center space-x-3 mb-4">
                            @if($shopOwner->shops->first() && $shopOwner->shops->first()->images)
                                <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200">
                                    <img src="/storage/{{ $shopOwner->shops->first()->images[0] ?? '' }}"
                                         alt="{{ $shopOwner->name }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                    <div class="w-full h-full bg-green-100 flex items-center justify-center" style="display: none;">
                                        <i class="fas fa-store text-green-600 text-lg"></i>
                                    </div>
                                </div>
                            @else
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-store text-green-600 text-lg"></i>
                                </div>
                            @endif
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $shopOwner->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $shopOwner->email }}</p>
                            </div>
                        </div>

                        <!-- Shop Info -->
                        @if($shopOwner->shops->first())
                        <div class="mb-4">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="text-sm font-medium text-gray-700">Shop:</span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                    {{ $shopOwner->shops->first()->name }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="text-sm font-medium text-gray-700">Category:</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    {{ $shopOwner->shops->first()->category->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- Statistics -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-900">{{ $shopOwner->shops->sum(function($shop) { return $shop->loyaltyCards->count(); }) }}</div>
                                <div class="text-xs text-gray-600">Active Cards</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-900">{{ $shopOwner->shops->sum(function($shop) { return $shop->loyaltyCards->sum(function($card) { return $card->userCards->count(); }); }) }}</div>
                                <div class="text-xs text-gray-600">Subscribers</div>
                            </div>
                        </div>

                        <!-- Shop Info -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Joined:</span>
                                <span class="text-gray-900">{{ $shopOwner->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($shopOwner->shops->first() && $shopOwner->shops->first()->contact_info)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Phone:</span>
                                <span class="text-gray-900">{{ $shopOwner->shops->first()->contact_info['phone'] ?? 'N/A' }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <a href="/shop-owners/{{ $shopOwner->id }}"
                           class="w-full bg-green-500 hover:bg-green-600 text-white text-center py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-store text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No shop owners found</h3>
                    <p class="text-gray-500">Try adjusting your search or filters</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($shopOwners->hasPages())
            <div class="mt-8">
                {{ $shopOwners->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
