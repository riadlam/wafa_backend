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

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loyalty Cards</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Stamps</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $user->role === 'shop_owner' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->loyaltyCards->count() }} cards
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->addedStamps->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="showUserDetails({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', '{{ $user->created_at->format('M d, Y') }}', {{ $user->loyaltyCards->count() }}, {{ $user->addedStamps->count() }})"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition-colors">
                                        <i class="fas fa-eye mr-1"></i>Show
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                                    <p class="text-gray-500">Try adjusting your search or filters</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="mt-8">
                {{ $users->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- User Details Modal -->
    <div id="userDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">User Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- User Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Basic Information</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Name:</span> <span id="modalName"></span></div>
                            <div><span class="font-medium">Email:</span> <span id="modalEmail"></span></div>
                            <div><span class="font-medium">Role:</span> <span id="modalRole" class="px-2 py-1 rounded-full text-xs"></span></div>
                            <div><span class="font-medium">Joined:</span> <span id="modalJoined"></span></div>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">Activity Summary</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Loyalty Cards:</span> <span id="modalCards"></span></div>
                            <div><span class="font-medium">Total Stamps:</span> <span id="modalStamps"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Cards List -->
                <div id="loyaltyCardsSection" class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-3">Loyalty Cards</h4>
                    <div id="loyaltyCardsList" class="space-y-2">
                        <!-- Cards will be loaded here via AJAX -->
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function showUserDetails(userId, name, email, role, joined, cards, stamps) {
            // Set basic info
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalJoined').textContent = joined;
            document.getElementById('modalCards').textContent = cards;
            document.getElementById('modalStamps').textContent = stamps;

            // Set role with styling
            const roleElement = document.getElementById('modalRole');
            roleElement.textContent = role === 'shop_owner' ? 'Shop Owner' : 'Regular User';
            roleElement.className = `px-2 py-1 rounded-full text-xs font-medium ${
                role === 'shop_owner'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-blue-100 text-blue-800'
            }`;

            // Show modal
            document.getElementById('userDetailsModal').classList.remove('hidden');

            // Load detailed loyalty cards info via AJAX
            loadUserLoyaltyCards(userId);
        }

        function loadUserLoyaltyCards(userId) {
            fetch(`/users/${userId}`)
                .then(response => response.text())
                .then(html => {
                    // Extract loyalty cards info from the detailed page
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Try to extract loyalty cards information
                    const cardsList = document.getElementById('loyaltyCardsList');
                    cardsList.innerHTML = '<div class="text-center text-gray-500 py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading loyalty cards...</div>';

                    // For now, show a simple message
                    setTimeout(() => {
                        cardsList.innerHTML = '<div class="text-center text-gray-500 py-4">Loyalty cards details would be loaded here from the detailed view.</div>';
                    }, 500);
                })
                .catch(error => {
                    console.error('Error loading user details:', error);
                    document.getElementById('loyaltyCardsList').innerHTML =
                        '<div class="text-center text-red-500 py-4">Error loading loyalty cards</div>';
                });
        }

        function closeModal() {
            document.getElementById('userDetailsModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('userDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
