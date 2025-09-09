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

            <!-- Shop Owners Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop Owner</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Cards</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribers</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($shopOwners as $shopOwner)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location.href='/shop-owners/{{ $shopOwner->id }}'">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($shopOwner->shops->first() && $shopOwner->shops->first()->images)
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover"
                                                     src="{{ getImageUrl($shopOwner->shops->first()->images[0] ?? '') }}"
                                                     alt="{{ $shopOwner->name }}"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center" style="display: none;">
                                                    <i class="fas fa-store text-green-600"></i>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-store text-green-600"></i>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $shopOwner->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $shopOwner->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($shopOwner->shops->first())
                                        <div class="text-sm text-gray-900">{{ $shopOwner->shops->first()->name }}</div>
                                    @else
                                        <span class="text-gray-400">No shop</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($shopOwner->shops->first() && $shopOwner->shops->first()->category)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $shopOwner->shops->first()->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $shopOwner->shops->sum(function($shop) { return $shop->loyaltyCards->count(); }) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $shopOwner->shops->sum(function($shop) { return $shop->loyaltyCards->sum(function($card) { return $card->userCards->count(); }); }) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $shopOwner->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="event.stopPropagation(); window.location.href='/shop-owners/{{ $shopOwner->id }}'"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs transition-colors">
                                        <i class="fas fa-eye mr-1"></i>Show
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <i class="fas fa-store text-gray-300 text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No shop owners found</h3>
                                    <p class="text-gray-500">Try adjusting your search or filters</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($shopOwners->hasPages())
            <div class="mt-8">
                {{ $shopOwners->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Shop Owner Details Modal -->
    <div id="shopOwnerDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="shopOwnerModalTitle">Shop Owner Details</h3>
                <button onclick="closeShopOwnerModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Shop Owner Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Basic Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Name:</span> <span id="shopOwnerModalName"></span></div>
                            <div><span class="font-medium">Email:</span> <span id="shopOwnerModalEmail"></span></div>
                            <div><span class="font-medium">Joined:</span> <span id="shopOwnerModalJoined"></span></div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-medium text-green-900 mb-2">Shop Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Shop Name:</span> <span id="shopOwnerModalShopName"></span></div>
                            <div><span class="font-medium">Category:</span> <span id="shopOwnerModalCategory" class="px-2 py-1 rounded-full text-xs"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-900" id="shopOwnerModalActiveCards"></div>
                        <div class="text-sm text-blue-700">Active Cards</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-900" id="shopOwnerModalSubscribers"></div>
                        <div class="text-sm text-green-700">Subscribers</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-purple-900" id="shopOwnerModalRedemptions">0</div>
                        <div class="text-sm text-purple-700">Total Redemptions</div>
                    </div>
                </div>

                <!-- Loyalty Cards List -->
                <div id="shopLoyaltyCardsSection" class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-3">Loyalty Cards & Subscribers</h4>
                    <div id="shopLoyaltyCardsList" class="space-y-2">
                        <!-- Cards will be loaded here via AJAX -->
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button onclick="closeShopOwnerModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function showShopOwnerDetails(shopOwnerId, name, email, shopName, category, activeCards, subscribers, joined) {
            // Set basic info
            document.getElementById('shopOwnerModalName').textContent = name;
            document.getElementById('shopOwnerModalEmail').textContent = email;
            document.getElementById('shopOwnerModalShopName').textContent = shopName;
            document.getElementById('shopOwnerModalJoined').textContent = joined;
            document.getElementById('shopOwnerModalActiveCards').textContent = activeCards;
            document.getElementById('shopOwnerModalSubscribers').textContent = subscribers;

            // Set category with styling
            const categoryElement = document.getElementById('shopOwnerModalCategory');
            categoryElement.textContent = category;
            categoryElement.className = `px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800`;

            // Show modal
            document.getElementById('shopOwnerDetailsModal').classList.remove('hidden');

            // Load detailed shop information via AJAX
            loadShopOwnerDetails(shopOwnerId);
        }

        function loadShopOwnerDetails(shopOwnerId) {
            fetch(`/shop-owners/${shopOwnerId}`)
                .then(response => response.text())
                .then(html => {
                    // Extract shop information from the detailed page
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Update total redemptions and amount due from the detailed page
                    const redemptionElement = doc.querySelector('[data-total-redemptions]');
                    const amountDueElement = doc.querySelector('[data-amount-due]');

                    if (redemptionElement) {
                        const totalRedemptions = redemptionElement.getAttribute('data-total-redemptions');
                        document.getElementById('shopOwnerModalRedemptions').textContent = totalRedemptions;
                    }

                    if (amountDueElement) {
                        const amountDue = amountDueElement.getAttribute('data-amount-due');
                        // Add amount due display
                        const amountDueDiv = document.createElement('div');
                        amountDueDiv.className = 'bg-red-50 p-4 rounded-lg text-center mt-4';
                        amountDueDiv.innerHTML = `
                            <div class="text-2xl font-bold text-red-900">${amountDue} DA</div>
                            <div class="text-sm text-red-700">Amount Due</div>
                        `;

                        const statsGrid = document.querySelector('#shopOwnerDetailsModal .grid.grid-cols-1.md\\:grid-cols-3');
                        if (statsGrid) {
                            // Replace the existing grid with a 4-column grid
                            statsGrid.className = 'grid grid-cols-2 md:grid-cols-4 gap-4';
                            statsGrid.appendChild(amountDueDiv);
                        }
                    }

                    // Try to extract loyalty cards and subscriber information
                    const cardsList = document.getElementById('shopLoyaltyCardsList');
                    cardsList.innerHTML = '<div class="text-center text-gray-500 py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading detailed information...</div>';

                    // Extract detailed shop data from the page
                    setTimeout(() => {
                        // Look for shop data in the HTML
                        const shopData = extractShopDataFromHTML(html);
                        displayShopDetails(shopData);
                    }, 500);
                })
                .catch(error => {
                    console.error('Error loading shop owner details:', error);
                    document.getElementById('shopLoyaltyCardsList').innerHTML =
                        '<div class="text-center text-red-500 py-4">Error loading shop details</div>';
                });
        }

        function extractShopDataFromHTML(html) {
            // Parse the actual data from the HTML response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Extract loyalty cards data from the page
            const loyaltyCards = [];
            const cardElements = doc.querySelectorAll('[data-loyalty-card]');

            cardElements.forEach(cardElement => {
                const cardId = cardElement.getAttribute('data-loyalty-card-id');
                const cardName = cardElement.getAttribute('data-loyalty-card-name') || 'Loyalty Card #' + cardId;
                const totalStamps = parseInt(cardElement.getAttribute('data-total-stamps') || '10');

                // Extract subscribers for this card
                const subscribers = [];
                const subscriberElements = cardElement.querySelectorAll('[data-subscriber]');

                subscriberElements.forEach(subElement => {
                    subscribers.push({
                        userId: subElement.getAttribute('data-user-id'),
                        userName: subElement.getAttribute('data-user-name'),
                        stamps: parseInt(subElement.getAttribute('data-stamps') || '0')
                    });
                });

                loyaltyCards.push({
                    id: cardId,
                    name: cardName,
                    totalStamps: totalStamps,
                    subscribers: subscribers
                });
            });

            return {
                loyaltyCards: loyaltyCards,
                totalSubscribers: loyaltyCards.reduce((total, card) => total + card.subscribers.length, 0),
                totalRedemptions: parseInt(doc.querySelector('[data-total-redemptions]')?.getAttribute('data-total-redemptions') || '0'),
                amountDue: parseInt(doc.querySelector('[data-amount-due]')?.getAttribute('data-amount-due') || '0')
            };
        }

        function displayShopDetails(shopData) {
            const cardsList = document.getElementById('shopLoyaltyCardsList');
            let html = '';

            if (shopData.loyaltyCards && shopData.loyaltyCards.length > 0) {
                shopData.loyaltyCards.forEach(card => {
                    html += `
                        <div class="border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-medium text-gray-900">${card.name}</h5>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    ${card.totalStamps} stamps
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-3">
                                ${card.subscribers.length} subscribers
                            </div>
                            <div class="space-y-2">
                    `;

                    card.subscribers.forEach(subscriber => {
                        html += `
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-user text-gray-400"></i>
                                    <span>${subscriber.userName}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-blue-600 font-medium">${subscriber.stamps}/${card.totalStamps}</span>
                                    <i class="fas fa-ticket-alt text-blue-500"></i>
                                </div>
                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="text-center text-gray-500 py-4">No loyalty cards found for this shop.</div>';
            }

            cardsList.innerHTML = html;
        }

        function closeShopOwnerModal() {
            document.getElementById('shopOwnerDetailsModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('shopOwnerDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeShopOwnerModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeShopOwnerModal();
            }
        });
    </script>
</body>
</html>
