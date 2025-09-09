<!DOCTYPE html>
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
                    @if($shop && $shop->images)
                        <img src="/storage/{{ $shop->images[0] }}" alt="{{ $shop->name }}"
                             class="w-20 h-20 rounded-full object-cover border-4 border-gray-200">
                    @else
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center border-4 border-gray-200">
                            <i class="fas fa-store text-green-600 text-2xl"></i>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $shopOwner->name }}</h2>
                        <p class="text-gray-600">{{ $shopOwner->email }}</p>
                        <p class="text-sm text-gray-500">Joined: {{ $shopOwner->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                @if($shop)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">Shop Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Shop Name:</span> {{ $shop->name }}</div>
                            <div><span class="font-medium">Category:</span> {{ $shop->category->name ?? 'N/A' }}</div>
                            @if($shop->contact_info)
                            <div><span class="font-medium">Phone:</span> {{ $shop->contact_info['phone'] ?? 'N/A' }}</div>
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
    </div>
</body>
</html>
