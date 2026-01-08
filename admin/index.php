<?php

require_once "../config/config.php";

// Require admin access
require_admin();

// Get statistics
$stats = [];

// Total users
$sql = "SELECT COUNT(*) as count FROM users";
$result = db_fetch_one($sql);
$stats['total_users'] = $result['count'] ?? 0;

// Total products
$sql = "SELECT COUNT(*) as count FROM products";
$result = db_fetch_one($sql);
$stats['total_products'] = $result['count'] ?? 0;

// Total orders
$sql = "SELECT COUNT(*) as count FROM orders";
$result = db_fetch_one($sql);
$stats['total_orders'] = $result['count'] ?? 0;

// Total revenue
$sql = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'";
$result = db_fetch_one($sql);
$stats['total_revenue'] = $result['total'] ?? 0;

// Pending orders
$sql = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'";
$result = db_fetch_one($sql);
$stats['pending_orders'] = $result['count'] ?? 0;

// Recent orders
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.buyer_id = u.user_id 
        ORDER BY o.created_at DESC 
        LIMIT 5";
$recent_orders = db_fetch_all($sql);

// Recent users
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = db_fetch_all($sql);

// Low stock products
$sql = "SELECT * FROM products WHERE stock_quantity < 10 AND status = 'active' ORDER BY stock_quantity ASC LIMIT 5";
$low_stock = db_fetch_all($sql);

// Sales chart data (last 7 days)
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sql = "SELECT COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue 
            FROM orders 
            WHERE DATE(created_at) = ? AND payment_status = 'paid'";
    $result = db_fetch_one($sql, "s", [$date]);
    $sales_data[] = [
        'date' => date('M d', strtotime($date)),
        'orders' => $result['orders'] ?? 0,
        'revenue' => $result['revenue'] ?? 0
    ];
}

$current_user = get_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .stat-card {
            animation: fadeInUp 0.6s ease-out;
        }
        .sidebar-item {
            animation: slideInLeft 0.4s ease-out;
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .notification-badge {
            animation: pulse 2s infinite;
        }
        #mobileSidebar {
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

    <!-- Sidebar -->
    <aside id="mobileSidebar" class="fixed left-0 top-0 h-full w-64 bg-white shadow-xl z-50 transform -translate-x-full lg:translate-x-0 transition-transform">
        <div class="p-6 border-b">
            <div class="flex items-center gap-2 text-2xl font-bold text-purple-600">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                </svg>
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="p-4 space-y-2">
            <a href="index.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="font-semibold">Dashboard</span>
            </a>

            <a href="users.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="font-semibold">Users</span>
            </a>

            <a href="products.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="font-semibold">Products</span>
            </a>

            <a href="orders.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span class="font-semibold">Orders</span>
                <?php if ($stats['pending_orders'] > 0): ?>
                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full notification-badge"><?= $stats['pending_orders'] ?></span>
                <?php endif; ?>
            </a>

            <a href="categories.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span class="font-semibold">Categories</span>
            </a>

            <a href="reviews.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span class="font-semibold">Reviews</span>
            </a>

            <a href="settings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="font-semibold">Settings</span>
            </a>

            <div class="border-t pt-4 mt-4">
                <a href="../project.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="font-semibold">View Store</span>
                </a>

                <a href="../auth/logout.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-50 text-red-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="font-semibold">Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Header -->
        <header class="bg-white shadow-md sticky top-0 z-30">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button id="sidebarToggle" class="lg:hidden text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-2xl font-bold gradient-text">Dashboard Overview</h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span><?= date('l, F d, Y') ?></span>
                    </div>
                    
                    <div class="flex items-center gap-3 pl-4 border-l">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($current_user['full_name']) ?>&size=40&background=667eea&color=fff&bold=true" 
                             alt="Admin" class="w-10 h-10 rounded-full">
                        <div class="hidden md:block">
                            <div class="font-semibold text-sm"><?= clean($current_user['full_name']) ?></div>
                            <div class="text-xs text-gray-500">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="p-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Revenue -->
                <div class="stat-card bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg" style="animation-delay: 0.1s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold">Rs <?= number_format($stats['total_revenue'], 0) ?></div>
                            <div class="text-sm opacity-90">Total Revenue</div>
                        </div>
                    </div>
                    <div class="text-sm opacity-75">+12.5% from last month</div>
                </div>

                <!-- Total Orders -->
                <div class="stat-card bg-gradient-to-br from-green-500 to-teal-600 rounded-xl p-6 text-white shadow-lg" style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold"><?= $stats['total_orders'] ?></div>
                            <div class="text-sm opacity-90">Total Orders</div>
                        </div>
                    </div>
                    <div class="text-sm opacity-75"><?= $stats['pending_orders'] ?> pending orders</div>
                </div>

                <!-- Total Products -->
                <div class="stat-card bg-gradient-to-br from-orange-500 to-red-600 rounded-xl p-6 text-white shadow-lg" style="animation-delay: 0.3s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold"><?= $stats['total_products'] ?></div>
                            <div class="text-sm opacity-90">Total Products</div>
                        </div>
                    </div>
                    <div class="text-sm opacity-75"><?= count($low_stock) ?> low stock items</div>
                </div>

                <!-- Total Users -->
                <div class="stat-card bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl p-6 text-white shadow-lg" style="animation-delay: 0.4s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold"><?= $stats['total_users'] ?></div>
                            <div class="text-sm opacity-90">Total Users</div>
                        </div>
                    </div>
                    <div class="text-sm opacity-75">5 new this week</div>
                </div>
            </div>

            <!-- Charts and Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Sales Chart -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Sales Overview (Last 7 Days)</h2>
                    <canvas id="salesChart" height="200"></canvas>
                </div>

                <!-- Order Status Distribution -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Order Status Distribution</h2>
                    <canvas id="orderStatusChart" height="200"></canvas>
                </div>
            </div>

            <!-- Recent Orders & Low Stock -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Recent Orders</h2>
                        <a href="orders.php" class="text-purple-600 hover:underline text-sm font-semibold">View All →</a>
                    </div>
                    <div class="space-y-3">
                        <?php if (empty($recent_orders)): ?>
                            <p class="text-gray-500 text-center py-8">No orders yet</p>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="border-l-4 <?= $order['order_status'] === 'pending' ? 'border-yellow-500' : 'border-green-500' ?> bg-gray-50 p-4 rounded">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-gray-800">#<?= clean($order['order_number']) ?></span>
                                        <span class="text-sm text-gray-600"><?= time_ago($order['created_at']) ?></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm text-gray-600"><?= clean($order['username']) ?></div>
                                            <div class="font-bold text-purple-600">Rs <?= number_format($order['total_amount'], 2) ?></div>
                                        </div>
                                        <span class="px-3 py-1 bg-<?= $order['order_status'] === 'pending' ? 'yellow' : 'green' ?>-100 text-<?= $order['order_status'] === 'pending' ? 'yellow' : 'green' ?>-700 rounded-full text-xs font-semibold">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Low Stock Alert</h2>
                        <a href="products.php" class="text-purple-600 hover:underline text-sm font-semibold">View All →</a>
                    </div>
                    <div class="space-y-3">
                        <?php if (empty($low_stock)): ?>
                            <p class="text-gray-500 text-center py-8">All products have sufficient stock</p>
                        <?php else: ?>
                            <?php foreach ($low_stock as $product): ?>
                                <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-gray-800"><?= clean($product['product_name']) ?></div>
                                            <div class="text-sm text-gray-600">Rs <?= number_format($product['price'], 2) ?></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold text-red-600"><?= $product['stock_quantity'] ?></div>
                                            <div class="text-xs text-gray-600">in stock</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        sidebarToggle?.addEventListener('click', () => {
            mobileSidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay?.addEventListener('click', () => {
            mobileSidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode(array_column($sales_data, 'date')) ?>,
                    datasets: [{
                        label: 'Revenue (Rs)',
                        data: <?= json_encode(array_column($sales_data, 'revenue')) ?>,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Order Status Chart
        const orderStatusCtx = document.getElementById('orderStatusChart');
        if (orderStatusCtx) {
            new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Processing', 'Shipped', 'Delivered'],
                    datasets: [{
                        data: [<?= $stats['pending_orders'] ?>, 5, 8, 12],
                        backgroundColor: ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        console.log('%c🛡️ Sajilo Admin Panel', 'color: #667eea; font-size: 20px; font-weight: bold;');
        console.log('%cDeveloped by: Aditya Rai & Prakash Thapa', 'color: #764ba2; font-size: 14px;');
    </script>
</body>
</html>