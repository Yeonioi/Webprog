<?php
// Get user's referral info for header
$currentUserId = 1; // Demo user
$stmt = $conn->prepare("SELECT referral_code, referral_points FROM users WHERE id = ?");
$stmt->execute([$currentUserId]);
$headerUser = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="home.php" class="flex items-center">
                    <span class="text-2xl font-bold text-blue-600">Friend</span>
                    <span class="text-2xl font-bold text-purple-600">Link</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="home.php" class="text-gray-700 hover:text-blue-600 transition-colors">Home</a>
                <a href="search.php" class="text-gray-700 hover:text-blue-600 transition-colors">Browse Services</a>
                <a href="add-service.php" class="text-gray-700 hover:text-blue-600 transition-colors">Add Service</a>
                <a href="referrals.php" class="flex items-center text-blue-600 hover:text-blue-700 transition-colors font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                    Refer & Earn
                    <?php if ($headerUser['referral_points'] > 0): ?>
                    <span class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full"><?= number_format($headerUser['referral_points']) ?></span>
                    <?php endif; ?>
                </a>
                <a href="bookmarks.php" class="text-gray-700 hover:text-blue-600 transition-colors">Bookmarks</a>
            </div>

            <!-- Right Side Icons -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <button class="relative text-gray-600 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                </button>

                <!-- Messages -->
                <a href="messages.php" class="relative text-gray-600 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-blue-500"></span>
                </a>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button class="flex items-center space-x-2" onclick="toggleDropdown()">
                        <div class="h-8 w-8 rounded-full overflow-hidden bg-gradient-to-br from-blue-400 to-purple-400">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100" alt="Profile" class="h-full w-full object-cover">
                        </div>
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-1 z-10 border border-gray-100">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                        <a href="add-service.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add Service</a>
                        <a href="bookmarks.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Bookmarks</a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="referrals.php" class="block px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 flex items-center justify-between">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                </svg>
                                Refer & Earn
                            </span>
                            <?php if ($headerUser['referral_points'] > 0): ?>
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full"><?= number_format($headerUser['referral_points']) ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-600" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="hidden md:hidden pb-4">
            <div class="space-y-2">
                <a href="home.php" class="block py-2 text-gray-700 hover:text-blue-600">Home</a>
                <a href="search.php" class="block py-2 text-gray-700 hover:text-blue-600">Browse Services</a>
                <a href="add-service.php" class="block py-2 text-gray-700 hover:text-blue-600">Add Service</a>
                <a href="referrals.php" class="block py-2 text-blue-600 hover:text-blue-700 font-medium">Refer & Earn</a>
                <a href="bookmarks.php" class="block py-2 text-gray-700 hover:text-blue-600">Bookmarks</a>
                <a href="profile.php" class="block py-2 text-gray-700 hover:text-blue-600">Profile</a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('hidden');
}

function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// Close dropdown when clicking outside
window.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.getElementById('profileDropdown').classList.add('hidden');
    }
});
</script>