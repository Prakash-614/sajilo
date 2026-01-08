<?php

require_once "config/config.php";

// Require user to be logged in
require_login();

$user_id = get_user_id();
$errors = [];
$success = false;

// Get user data
$user = get_user();

// ==================== NEW CODE BLOCK 1: HANDLE PROFILE PICTURE UPLOAD ====================
// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $upload_result = upload_image($_FILES['profile_image'], 'profiles');
        
        if ($upload_result['success']) {
            // Delete old profile image if not default
            if ($user['profile_image'] !== 'default-avatar.png' && !empty($user['profile_image'])) {
                delete_image($user['profile_image']);
            }
            
            // Update database with new image
            $sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
            $stmt = db_execute($sql, "si", [$upload_result['filename'], $user_id]);
            
            if ($stmt) {
                $stmt->close();
                set_flash('success', 'Profile picture updated successfully!');
                $user = get_user(); // Refresh user data
            } else {
                $errors[] = 'Failed to update profile picture';
            }
        } else {
            $errors[] = $upload_result['message'];
        }
    }
}
// ==================== END NEW CODE BLOCK 1 ====================

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    }
    
    if ($_POST['action'] === 'update_profile' && empty($errors)) {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        
        // Validation
        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        
        if (!empty($phone) && !validate_phone($phone)) {
            $errors[] = 'Invalid phone number format';
        }
        
        if (empty($errors)) {
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, city = ? WHERE user_id = ?";
            $stmt = db_execute($sql, "ssssi", [$full_name, $phone, $address, $city, $user_id]);
            
            if ($stmt) {
                $stmt->close();
                $success = true;
                set_flash('success', 'Profile updated successfully!');
                $user = get_user(); // Refresh user data
            } else {
                $errors[] = 'Failed to update profile';
            }
        }
    }
    
    if ($_POST['action'] === 'change_password' && empty($errors)) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password)) {
            $errors[] = 'Current password is required';
        } elseif (!verify_password($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        if (empty($new_password)) {
            $errors[] = 'New password is required';
        } elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            $hashed_password = hash_password($new_password);
            $sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt = db_execute($sql, "si", [$hashed_password, $user_id]);
            
            if ($stmt) {
                $stmt->close();
                set_flash('success', 'Password changed successfully!');
                $success = true;
            } else {
                $errors[] = 'Failed to change password';
            }
        }
    }
    
    // ==================== NEW CODE BLOCK 2: HANDLE REMOVE PROFILE PICTURE ====================
    // Handle remove profile picture
    if ($_POST['action'] === 'remove_profile_picture' && empty($errors)) {
        // Delete old profile image if not default
        if ($user['profile_image'] !== 'default-avatar.png' && !empty($user['profile_image'])) {
            delete_image($user['profile_image']);
        }
        
        // Update database to default
        $sql = "UPDATE users SET profile_image = 'default-avatar.png' WHERE user_id = ?";
        $stmt = db_execute($sql, "i", [$user_id]);
        
        if ($stmt) {
            $stmt->close();
            set_flash('success', 'Profile picture removed successfully!');
            $user = get_user(); // Refresh user data
        } else {
            $errors[] = 'Failed to remove profile picture';
        }
    }
    // ==================== END NEW CODE BLOCK 2 ====================
}

// Get user's orders
$sql = "SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC LIMIT 5";
$recent_orders = db_fetch_all($sql, "i", [$user_id]);

// Get cart count
$cart_count = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .account-section {
            animation: fadeInUp 0.6s ease-out;
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeInUp 0.4s ease-out;
        }
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Notification Container -->
    <div id="notificationContainer">
        <?php if (!empty($errors)): ?>
            <div class="notification bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-bold mb-1">Error</div>
                        <div class="text-sm"><?= $errors[0] ?></div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4">✕</button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php $flash = get_flash(); foreach ($flash as $message): ?>
            <div class="notification bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md">
                <div class="flex items-center justify-between">
                    <span class="font-semibold"><?= clean($message['message']) ?></span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4">✕</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-2">
            <div class="container mx-auto px-4 flex justify-between items-center text-sm">
                <div class="flex items-center gap-6">
                    <span class="hidden md:inline">📍 Delivering across Nepal</span>
                    <span class="hidden md:inline">📞 +977-9866114411</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:underline">Help</a>
                    <a href="#" class="hover:underline">Track Order</a>
                </div>
            </div>
        </div>

        <nav class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="project.php" class="flex items-center gap-2 text-2xl font-bold text-purple-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                    <span>Sajilo</span>
                </a>
                <div class="flex items-center gap-6">
                    <a href="project.php" class="text-purple-600 hover:underline font-semibold">Continue Shopping</a>
                    <a href="cart.php" class="flex items-center hover:text-purple-600 transition relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="h-32"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- ==================== UPDATED: Welcome Banner with Profile Picture ==================== -->
            <!-- Welcome Banner -->
            <div class="account-section bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-8 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <img src="<?= !empty($user['profile_image']) && $user['profile_image'] !== 'default-avatar.png' ? UPLOADS_URL . '/' . $user['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&size=150&background=ffffff&color=667eea&bold=true' ?>" 
                             alt="Profile" 
                             class="w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-white shadow-lg object-cover">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold mb-2">Welcome back, <?= clean($user['full_name']) ?>! 👋</h1>
                            <p class="text-lg opacity-90">Manage your account and track your orders</p>
                        </div>
                    </div>
                    <div class="hidden lg:block text-9xl opacity-20">👤</div>
                </div>
            </div>
            <!-- ==================== END UPDATED SECTION ==================== -->

            <!-- Quick Stats -->
            <div class="account-section grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="text-3xl font-bold text-purple-600"><?= count($recent_orders) ?></span>
                    </div>
                    <h3 class="font-semibold text-gray-700">Total Orders</h3>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-3xl font-bold text-green-600"><?= $user['is_verified'] ? 'Yes' : 'No' ?></span>
                    </div>
                    <h3 class="font-semibold text-gray-700">Account Verified</h3>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-blue-600"><?= ucfirst($user['role']) ?></span>
                    </div>
                    <h3 class="font-semibold text-gray-700">Account Type</h3>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="account-section bg-white rounded-xl shadow-md mb-8">
                <div class="flex flex-wrap border-b overflow-x-auto">
                    <button class="tab-btn active px-6 py-4 font-semibold transition" data-tab="profile">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </span>
                    </button>
                    <button class="tab-btn px-6 py-4 font-semibold transition hover:bg-gray-50" data-tab="orders">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Orders
                        </span>
                    </button>
                    <button class="tab-btn px-6 py-4 font-semibold transition hover:bg-gray-50" data-tab="security">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Security
                        </span>
                    </button>
                    <button class="tab-btn px-6 py-4 font-semibold transition hover:bg-gray-50" data-tab="settings">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Settings
                        </span>
                    </button>
                </div>

                <!-- ==================== NEW: Profile Tab with Profile Picture Upload ==================== -->
                <!-- Profile Tab -->
                <div id="profile-tab" class="tab-content active p-8">
                    <h2 class="text-2xl font-bold gradient-text mb-6">Profile Information</h2>
                    
                    <!-- Profile Picture Section -->
                    <div class="mb-8 pb-8 border-b-2 border-gray-200">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">Profile Picture</h3>
                        <div class="flex flex-col md:flex-row items-center gap-6">
                            <div class="relative">
                                <img id="profilePreview" 
                                     src="<?= !empty($user['profile_image']) && $user['profile_image'] !== 'default-avatar.png' ? UPLOADS_URL . '/' . $user['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&size=200&background=667eea&color=fff&bold=true' ?>" 
                                     alt="Profile Picture" 
                                     class="w-32 h-32 rounded-full object-cover border-4 border-purple-200 shadow-lg">
                                <div class="absolute bottom-0 right-0 bg-purple-600 rounded-full p-2 cursor-pointer hover:bg-purple-700 transition" onclick="document.getElementById('profileImageInput').click()">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <form method="POST" action="" enctype="multipart/form-data" id="profileImageForm">
                                    <?= csrf_field() ?>
                                    <input type="file" name="profile_image" id="profileImageInput" accept="image/*" class="hidden" onchange="previewAndSubmit(this)">
                                    <div class="space-y-2">
                                        <button type="button" onclick="document.getElementById('profileImageInput').click()" 
                                                class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700 transition">
                                            Choose Photo
                                        </button>
                                        <?php if (!empty($user['profile_image']) && $user['profile_image'] !== 'default-avatar.png'): ?>
                                            <button type="button" onclick="removeProfilePicture()" 
                                                    class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition ml-2">
                                                Remove Photo
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF. Max size 5MB</p>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Information Form -->
                    <form method="POST" action="" class="space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Full Name *</label>
                                <input type="text" name="full_name" value="<?= clean($user['full_name']) ?>" required
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Email Address</label>
                                <input type="email" value="<?= clean($user['email']) ?>" disabled
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                                <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Phone Number</label>
                                <input type="tel" name="phone" value="<?= clean($user['phone'] ?? '') ?>"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition"
                                    placeholder="98XXXXXXXX">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">City</label>
                                <select name="city" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition">
                                    <option value="">Select City</option>
                                    <option value="Kathmandu" <?= $user['city'] === 'Kathmandu' ? 'selected' : '' ?>>Kathmandu</option>
                                    <option value="Pokhara" <?= $user['city'] === 'Pokhara' ? 'selected' : '' ?>>Pokhara</option>
                                    <option value="Lalitpur" <?= $user['city'] === 'Lalitpur' ? 'selected' : '' ?>>Lalitpur</option>
                                    <option value="Bhaktapur" <?= $user['city'] === 'Bhaktapur' ? 'selected' : '' ?>>Bhaktapur</option>
                                    <option value="Biratnagar" <?= $user['city'] === 'Biratnagar' ? 'selected' : '' ?>>Biratnagar</option>
                                    <option value="Other" <?= $user['city'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Address</label>
                            <textarea name="address" rows="3"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition"
                                placeholder="Enter your complete address"><?= clean($user['address'] ?? '') ?></textarea>
                        </div>

                        <button type="submit"
                            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:from-purple-700 hover:to-indigo-700 transition">
                            Update Profile
                        </button>
                    </form>
                </div>

                <!-- Orders Tab -->
                <div id="orders-tab" class="tab-content p-8">
                    <h2 class="text-2xl font-bold gradient-text mb-6">Recent Orders</h2>
                    
                    <?php if (empty($recent_orders)): ?>
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">📦</div>
                            <h3 class="text-xl font-bold text-gray-700 mb-2">No orders yet</h3>
                            <p class="text-gray-600 mb-6">Start shopping and your orders will appear here</p>
                            <a href="project.php" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                                Start Shopping
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-purple-300 transition">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h3 class="font-bold text-lg">Order #<?= clean($order['order_number']) ?></h3>
                                            <p class="text-sm text-gray-600">Placed on <?= format_date($order['created_at']) ?></p>
                                        </div>
                                        <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full font-semibold text-sm">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600">Total Amount</p>
                                            <p class="text-2xl font-bold text-purple-600">Rs <?= number_format($order['total_amount'], 2) ?></p>
                                        </div>
                                        <button class="text-purple-600 hover:text-purple-700 font-semibold">View Details →</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Security Tab -->
                <div id="security-tab" class="tab-content p-8">
                    <h2 class="text-2xl font-bold gradient-text mb-6">Change Password</h2>
                    <form method="POST" action="" class="space-y-6 max-w-2xl">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="change_password">
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Current Password *</label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">New Password *</label>
                            <input type="password" name="new_password" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Confirm New Password *</label>
                            <input type="password" name="confirm_password" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition">
                        </div>

                        <button type="submit"
                            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:from-purple-700 hover:to-indigo-700 transition">
                            Change Password
                        </button>
                    </form>
                </div>

                <!-- Settings Tab -->
                <div id="settings-tab" class="tab-content p-8">
                    <h2 class="text-2xl font-bold gradient-text mb-6">Account Settings</h2>
                    
                    <div class="space-y-6 max-w-2xl">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="font-bold text-lg mb-4">Account Information</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Username:</span>
                                    <span class="font-semibold"><?= clean($user['username']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-semibold"><?= clean($user['email']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account Type:</span>
                                    <span class="font-semibold"><?= ucfirst($user['role']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Member Since:</span>
                                    <span class="font-semibold"><?= format_date($user['created_at']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 p-6 rounded-lg border-2 border-red-200">
                            <h3 class="font-bold text-lg text-red-700 mb-2">Danger Zone</h3>
                            <p class="text-sm text-gray-600 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                            <button class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                                Delete Account
                            </button>
                        </div>

                        <div class="flex justify-end">
                            <a href="auth/logout.php" class="text-red-600 hover:text-red-700 font-semibold flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-2 text-xl font-bold text-white mb-4">
                <svg class="w-6 h-6 text-purple-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                </svg>
                <span><?= SITE_NAME ?></span>
            </div>
            <p class="text-sm mb-4">&copy; <?= PROJECT_YEAR ?> <?= SITE_NAME ?>. All rights reserved. A Project by <?= PROJECT_AUTHORS ?></p>
            <div class="text-xs opacity-75">
                <p><?= PROJECT_INSTITUTION ?></p>
                <p class="mt-1">Bachelor of Computer Application - Project I Proposal</p>
                <p class="mt-1">Supervised by: <?= PROJECT_SUPERVISOR ?></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Tab switching functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.getAttribute('data-tab');
                
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });

        // Auto-hide notifications after 5 seconds
        setTimeout(() => {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            });
        }, 5000);

        // Page load animation
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s ease';
                document.body.style.opacity = '1';
            }, 100);
        });

        // Console message
        console.log('%c🛒 Sajilo Account Page', 'color: #667eea; font-size: 20px; font-weight: bold;');
        console.log('%cDeveloped by: Aditya Rai & Prakash Thapa', 'color: #764ba2; font-size: 14px;');
        console.log('%cUnited College - BCA Project', 'color: #666; font-size: 12px;');

      
        // Profile picture preview and auto-submit
        function previewAndSubmit(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
                    input.value = '';
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    input.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Auto-submit form
                setTimeout(() => {
                    if (confirm('Upload this profile picture?')) {
                        document.getElementById('profileImageForm').submit();
                    } else {
                        input.value = '';
                        // Reload to restore original image
                        location.reload();
                    }
                }, 100);
            }
        }

        // Remove profile picture
        function removeProfilePicture() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                // Create a form to submit the remove action
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?= get_csrf_token() ?>';
                form.appendChild(csrfInput);
                
                // Add action input
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'remove_profile_picture';
                form.appendChild(actionInput);
                
                // Submit form
                document.body.appendChild(form);
                form.submit();
            }
        }
        

        console.log('%cUnited College - BCA Project', 'color: #666; font-size: 12px;');
    </script>
    </script>
</body>
</html>