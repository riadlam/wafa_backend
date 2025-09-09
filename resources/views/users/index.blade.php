<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Wafa Loyalty</title>
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
                        <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
                        <p class="text-gray-600">Manage and view all registered users</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Ads
                        </a>
                        <a href="/shop-owners" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-store mr-2"></i>Shop Owners
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
                                   placeholder="Search users by name or email..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <button type="submit" class="hidden">Search</button>
                        </form>
                    </div>

                    <!-- Role Filter -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Role:</span>
                        <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                            <a href="?{{ request()->getQueryString() }}&role=user"
                               class="px-4 py-2 text-sm {{ $role === 'user' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} transition-colors">
                                Regular Users
                            </a>
                            <a href="?{{ request()->getQueryString() }}&role=shop_owner"
                               class="px-4 py-2 text-sm border-l border-gray-300 {{ $role === 'shop_owner' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} transition-colors">
                                Shop Owners
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($users as $user)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <!-- User Header -->
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600 text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>

                        <!-- User Info -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Role:</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Joined:</span>
                                <span class="text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Total Stamps:</span>
                                <span class="text-gray-900">{{ $user->addedStamps->count() }}</span>
                            </div>
                        </div>

                        <!-- Loyalty Cards -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Loyalty Cards ({{ $user->loyaltyCards->count() }})</h4>
                            @if($user->loyaltyCards->count() > 0)
                                <div class="space-y-2">
                                    @foreach($user->loyaltyCards->take(2) as $userCard)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded text-xs">
                                        <div>
                                            <span class="font-medium">{{ $userCard->loyaltyCard->shop->name ?? 'Unknown Shop' }}</span>
                                            <div class="text-gray-500">{{ $userCard->active_stamps }}/{{ $userCard->loyaltyCard->total_stamps }} stamps</div>
                                        </div>
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-ticket-alt text-blue-600 text-xs"></i>
                                        </div>
                                    </div>
                                    @endforeach
                                    @if($user->loyaltyCards->count() > 2)
                                    <p class="text-xs text-gray-500 text-center">+{{ $user->loyaltyCards->count() - 2 }} more cards</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-500 text-center py-2">No loyalty cards yet</p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <a href="/users/{{ $user->id }}"
                           class="w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-4 rounded-lg transition-colors text-sm font-medium">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                    <p class="text-gray-500">Try adjusting your search or filters</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="mt-8">
                {{ $users->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
