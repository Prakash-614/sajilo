<?php

// Start session and include config
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize flash messages
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = [];
    
}

// Base URL for redirects
define('BASE_URL', 'http://localhost/sajilo');

// Helper function for flash messages
function set_flash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// Sample products data (simulating database)
$products_db = [
    1 => [
        'id' => 1,
        'name' => 'Wireless Headphones',
        'price' => 2399.00,
        'original_price' => 2999.00,
        'image' => 'https://hukut.com/_next/image?url=https%3A%2F%2Fcdn.hukut.com%2Fjbl-tune-720bt-blue-Price-in-Nepal.webp1728365210341&w=3840&q=75',
        'stock' => 50,
        'seller' => 'TechStore Nepal'
    ],
    2 => [
        'id' => 2,
        'name' => 'Smart Watch',
        'price' => 4999.00,
        'original_price' => 4999.00,
        'image' => 'https://ultima.com.np/_next/image?url=https%3A%2F%2Fapi.ultima.com.np%2Fmodules%2Ffiles%2F145803131020257TvAWC.png&w=3840&q=75',
        'stock' => 30,
        'seller' => 'Gadget Hub'
    ],
    3 => [
        'id' => 3,
        'name' => 'Designer Handbag',
        'price' => 3399.00,
        'original_price' => 3999.00,
        'image' => 'https://my.louisvuitton.com/images/is/image/lv/1/PP_VP_L/louis-vuitton-nano-diane--M83300_PM2_Front%20view.jpg',
        'stock' => 20,
        'seller' => 'Fashion Boutique'
    ],
    4 => [
        'id' => 4,
        'name' => 'Gaming Console',
        'price' => 45999.00,
        'original_price' => 45999.00,
        'image' => 'https://allureinternational.com.np/image/variation_product_image/iHQ7eE7hEtYeCxQugzKCaoXESkDD1W1EHBaUiabA.webp',
        'stock' => 15,
        'seller' => 'GameZone Nepal'
    ],
    5 => [
        'id' => 5,
        'name' => 'Laptop Stand',
        'price' => 1299.00,
        'original_price' => 1299.00,
        'image' => 'https://s3-eu-west-1.amazonaws.com/backcslimages/newsite/product-images/1500-1500/FoldineX-Laptop-Stand.jpg',
        'stock' => 40,
        'seller' => 'Office Supplies'
    ],
    6 => [
        'id' => 6,
        'name' => 'Running Shoes',
        'price' => 3499.00,
        'original_price' => 4999.00,
        'image' => 'https://images.ctfassets.net/xanbi6q061ft/651xuiU20gbZWkVRTxzmdl/7bb90c6530d84454ab65d5c9632d91ca/20250422_nike_bp_vomero18_shopWomens.png',
        'stock' => 25,
        'seller' => 'Sports World'
    ],
    7 => [
        'id' => 7,
        'name' => 'Coffee Maker',
        'price' => 5999.00,
        'original_price' => 5999.00,
        'image' => 'https://img.drz.lazcdn.com/static/lk/p/da2afe61b93f0a4f632c97238bc80aab.jpg_960x960q80.jpg_.webp',
        'stock' => 10,
        'seller' => 'Home Appliances'
    ],
    8 => [
        'id' => 8,
        'name' => 'Yoga Mat Set',
        'price' => 1799.00,
        'original_price' => 1799.00,
        'image' => 'https://static-01.daraz.com.np/p/c217a6b15ccb3fac238197f89c98f677.jpg',
        'stock' => 35,
        'seller' => 'Fitness Store'
    ]
];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_to_cart':
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if (isset($products_db[$product_id])) {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                
                // Calculate total items
                $total_items = array_sum($_SESSION['cart']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Product added to cart!',
                    'cart_count' => $total_items
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
            }
            exit;
            
        case 'update_quantity':
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            
            echo json_encode(['success' => true]);
            exit;
            
        case 'remove_item':
            $product_id = intval($_POST['product_id'] ?? 0);
            unset($_SESSION['cart'][$product_id]);
            
            echo json_encode(['success' => true]);
            exit;
            
        case 'clear_cart':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true]);
            exit;
    }
}

// Handle cart count request
if (isset($_GET['get_count'])) {
    header('Content-Type: application/json');
    echo json_encode(['cart_count' => array_sum($_SESSION['cart'])]);
    exit;
}

// Calculate cart totals
$cart_items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products_db[$product_id])) {
        $product = $products_db[$product_id];
        $item_total = $product['price'] * $quantity;
        $subtotal += $item_total;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'item_total' => $item_total
        ];
    }
}

// Calculate shipping and total
$shipping_charge = $subtotal >= 5000 ? 0 : 100;
$cod_charge = 0; // Will be added if COD is selected
$total = $subtotal + $shipping_charge;

// Get cart count
$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Sajilo Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .cart-item {
            animation: fadeInUp 0.5s ease-out;
        }
        .summary-card {
            animation: slideInRight 0.7s ease-out;
        }
        .empty-cart-icon {
            animation: bounce 2s ease-in-out infinite;
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            animation: fadeIn 0.3s ease;
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            animation: fadeInUp 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal">
        <div class="modal-content bg-white rounded-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold gradient-text">Checkout</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="checkoutForm" class="space-y-6">
                <div>
                    <h3 class="text-xl font-bold mb-4">Shipping Information</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="full_name" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500"
                                   placeholder="Enter your full name">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" name="phone" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500"
                                   placeholder="98XXXXXXXX">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-gray-700 mb-2">Email Address *</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500"
                               placeholder="your@email.com">
                    </div>
                    <div class="mt-4">
                        <label class="block text-gray-700 mb-2">Shipping Address *</label>
                        <textarea name="address" required rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500"
                                  placeholder="Enter your complete address"></textarea>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-gray-700 mb-2">City *</label>
                            <select name="city" required 
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500">
                                <option value="">Select City</option>
                                <option value="Kathmandu">Kathmandu</option>
                                <option value="Pokhara">Pokhara</option>
                                <option value="Lalitpur">Lalitpur</option>
                                <option value="Bhaktapur">Bhaktapur</option>
                                <option value="Biratnagar">Biratnagar</option>
                                <option value="Birgunj">Birgunj</option>
                                <option value="Chitwan">Chitwan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500"
                                   placeholder="Optional">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-bold mb-4">Payment Method</h3>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-purple-500 transition">
                            <input type="radio" name="payment_method" value="khalti" class="mr-3" required>
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-100 px-3 py-1 rounded text-purple-600 font-bold">Khalti</div>
                                <span class="text-gray-700">Pay with Khalti (Simulated)</span>
                            </div>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-purple-500 transition">
                            <input type="radio" name="payment_method" value="esewa" class="mr-3">
                            <div class="flex items-center gap-3">
                                <div class="bg-green-100 px-3 py-1 rounded text-green-600 font-bold">eSewa</div>
                                <span class="text-gray-700">Pay with eSewa (Simulated)</span>
                            </div>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-purple-500 transition">
                            <input type="radio" name="payment_method" value="cod" class="mr-3">
                            <div class="flex items-center gap-3">
                                <div class="bg-gray-100 px-3 py-1 rounded text-gray-700 font-bold">COD</div>
                                <span class="text-gray-700">Cash on Delivery (+Rs 50)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-gray-700 mb-2">Order Notes (Optional)</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-purple-500"
                              placeholder="Any special instructions for your order"></textarea>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-bold mb-2">Order Summary</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rs <?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping:</span>
                            <span id="shippingDisplay">Rs <?= number_format($shipping_charge, 2) ?></span>
                        </div>
                        <div class="flex justify-between" id="codChargeRow" style="display: none;">
                            <span>COD Charge:</span>
                            <span>Rs 50.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total:</span>
                            <span class="text-purple-600" id="totalDisplay">Rs <?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition">
                    Place Order
                </button>
            </form>
        </div>
    </div>

    <!-- Processing Modal -->
    <div id="processingModal" class="modal">
        <div class="modal-content bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center">
            <div class="loading-spinner mx-auto mb-4"></div>
            <h3 class="text-2xl font-bold mb-2">Processing Order...</h3>
            <p class="text-gray-600">Please wait while we process your payment</p>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
        <!-- Top Bar -->
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

        <!-- Main Navigation -->
        <nav class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="project.php" class="flex items-center gap-2 text-2xl font-bold text-purple-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                    <span>Sajilo</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="project.php" class="text-purple-600 hover:underline font-semibold">Continue Shopping</a>
                    <div class="relative">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $cart_count ?></span>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-32"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold gradient-text mb-8">Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <div class="text-9xl mb-6 empty-cart-icon inline-block">🛒</div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Your cart is empty</h2>
                <p class="text-gray-600 mb-8">Looks like you haven't added anything to your cart yet</p>
                <a href="project.php" class="inline-block bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Cart Items List -->
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($cart_items as $index => $item): ?>
                        <div class="cart-item bg-white rounded-xl shadow-md p-6" style="animation-delay: <?= $index * 0.1 ?>s" data-product-id="<?= $item['product']['id'] ?>">
                            <div class="flex gap-6">
                                <img src="<?= $item['product']['image'] ?>" alt="<?= htmlspecialchars($item['product']['name']) ?>" 
                                     class="w-32 h-32 object-cover rounded-lg">
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($item['product']['name']) ?></h3>
                                            <p class="text-sm text-gray-600">Sold by: <?= htmlspecialchars($item['product']['seller']) ?></p>
                                        </div>
                                        <button class="remove-item text-red-500 hover:text-red-700" data-id="<?= $item['product']['id'] ?>">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-4 mb-4">
                                        <span class="text-2xl font-bold text-purple-600">Rs <?= number_format($item['product']['price'], 2) ?></span>
                                        <?php if ($item['product']['original_price'] > $item['product']['price']): ?>
                                            <span class="text-gray-400 line-through">Rs <?= number_format($item['product']['original_price'], 2) ?></span>
                                            <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-sm font-semibold">
                                                <?= round((($item['product']['original_price'] - $item['product']['price']) / $item['product']['original_price']) * 100) ?>% OFF
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <button class="quantity-btn decrease-qty bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300" data-id="<?= $item['product']['id'] ?>">-</button>
                                            <span class="quantity-display text-xl font-bold px-4"><?= $item['quantity'] ?></span>
                                            <button class="quantity-btn increase-qty bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300" data-id="<?= $item['product']['id'] ?>">+</button>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">Item Total</p>
                                            <p class="text-xl font-bold text-purple-600">Rs <?= number_format($item['item_total'], 2) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="flex justify-between items-center pt-4">
                        <button id="clearCart" class="text-red-600 hover:text-red-700 font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear Cart
                        </button>
                        <a href="project.php" class="text-purple-600 hover:text-purple-700 font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="summary-card bg-white rounded-xl shadow-md p-6 sticky top-36">
                        <h2 class="text-2xl font-bold mb-6">Order Summary</h2>
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-gray-700">
                                <span>Subtotal (<?= $cart_count ?> items)</span>
                                <span class="font-semibold">Rs <?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>Shipping Fee</span>
                                <span class="font-semibold">
                                    <?php if ($shipping_charge == 0): ?>
                                        <span class="text-green-600">FREE</span>
                                    <?php else: ?>
                                        Rs <?= number_format($shipping_charge, 2) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($subtotal < 5000): ?>
                                <div class="bg-blue-50 p-3 rounded-lg text-sm">
                                    <p class="text-blue-700">
                                        🎉 Add <strong>Rs <?= number_format(5000 - $subtotal, 2) ?></strong> more for FREE shipping!
                                    </p>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="flex justify-between text-xl font-bold">
                                <span>Total</span>
                                <span class="text-purple-600">Rs <?= number_format($total, 2) ?></span>
                            </div>
                        </div>

                        <button id="proceedCheckout" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition mb-4">
                            Proceed to Checkout
                        </button>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Secure Payment</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>7 Days Return Policy</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Cash on Delivery Available</span>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg">
                            <h3 class="font-bold mb-2 flex items-center gap-2">
                                <span>💳</span>
                                <span>We Accept</span>
                            </h3>
                            <div class="flex gap-2 flex-wrap">
                                <div class="bg-white px-3 py-1 rounded text-xs font-semibold text-purple-600 shadow-sm">Khalti</div>
                                <div class="bg-white px-3 py-1 rounded text-xs font-semibold text-green-600 shadow-sm">eSewa</div>
                                <div class="bg-white px-3 py-1 rounded text-xs font-semibold text-gray-700 shadow-sm">COD</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-2 text-xl font-bold text-white mb-4">
                <svg class="w-6 h-6 text-purple-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                <span>Sajilo</span>
            </div>
            <p class="text-sm mb-4">&copy; 2025 Sajilo. All rights reserved. A Project by Aditya Rai & Prakash Thapa</p>
            <div class="text-xs opacity-75">
                <p>Tribhuvan University | Faculty of Humanities and Social Sciences | United College</p>
                <p class="mt-1">Bachelor of Computer Application - Project I Proposal</p>
                <p class="mt-1">Supervised by: Samir Thapa</p>
            </div>
        </div>
    </footer>

    <script>
        // Notification function
        function showNotification(message, type = 'success') {
            const container = document.getElementById('notificationContainer');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            const notification = document.createElement('div');
            notification.className = `notification ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg max-w-md`;
            notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="font-semibold">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
                </div>
            `;
            container.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        // Update quantity
        document.querySelectorAll('.increase-qty, .decrease-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
                const cartItem = this.closest('.cart-item');
                const quantityDisplay = cartItem.querySelector('.quantity-display');
                let quantity = parseInt(quantityDisplay.textContent);
                
                if (this.classList.contains('increase-qty')) {
                    quantity++;
                } else if (this.classList.contains('decrease-qty')) {
                    quantity--;
                    if (quantity < 1) {
                        if (confirm('Remove this item from cart?')) {
                            removeItem(productId);
                        }
                        return;
                    }
                }
                
                updateQuantity(productId, quantity);
            });
        });

        function updateQuantity(productId, quantity) {
            fetch('cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=update_quantity&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        // Remove item
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
                if (confirm('Are you sure you want to remove this item?')) {
                    removeItem(productId);
                }
            });
        });

        function removeItem(productId) {
            fetch('cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=remove_item&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Item removed from cart');
                    setTimeout(() => location.reload(), 500);
                }
            });
        }

        // Clear cart
        document.getElementById('clearCart')?.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=clear_cart'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Cart cleared');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            }
        });

        // Checkout modal
        const checkoutModal = document.getElementById('checkoutModal');
        const processingModal = document.getElementById('processingModal');
        const proceedBtn = document.getElementById('proceedCheckout');
        const closeModalBtn = document.getElementById('closeModal');
        const checkoutForm = document.getElementById('checkoutForm');

        proceedBtn?.addEventListener('click', () => {
            checkoutModal.classList.add('active');
        });

        closeModalBtn.addEventListener('click', () => {
            checkoutModal.classList.remove('active');
        });

        checkoutModal.addEventListener('click', (e) => {
            if (e.target === checkoutModal) {
                checkoutModal.classList.remove('active');
            }
        });

        // Update total based on payment method
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const subtotal = <?= $subtotal ?>;
                const shipping = <?= $shipping_charge ?>;
                const codCharge = this.value === 'cod' ? 50 : 0;
                const total = subtotal + shipping + codCharge;
                
                document.getElementById('totalDisplay').textContent = `Rs ${total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
                
                const codRow = document.getElementById('codChargeRow');
                if (this.value === 'cod') {
                    codRow.style.display = 'flex';
                } else {
                    codRow.style.display = 'none';
                }
            });
        });

        // Handle form submission
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const paymentMethod = formData.get('payment_method');
            
            // Close checkout modal and show processing
            checkoutModal.classList.remove('active');
            processingModal.classList.add('active');
            
            // Simulate payment processing
            setTimeout(() => {
                processingModal.classList.remove('active');
                
                // Generate order number
                const orderNumber = 'ORD' + Date.now().toString().slice(-8);
                
                // Clear cart
                fetch('cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=clear_cart'
                });
                
                // Show success message
                const successMessage = `
                    <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-70">
                        <div class="bg-white rounded-2xl p-8 max-w-md mx-4 text-center">
                            <div class="text-6xl mb-4">🎉</div>
                            <h2 class="text-3xl font-bold mb-4 text-green-600">Order Placed Successfully!</h2>
                            <p class="text-gray-600 mb-4">Your order number is: <strong>${orderNumber}</strong></p>
                            <p class="text-sm text-gray-500 mb-6">We'll send you a confirmation email shortly.</p>
                            <button onclick="window.location.href='project.php'" 
                                    class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700">
                                Continue Shopping
                            </button>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', successMessage);
                
            }, 2000);
        });

        // Page load animation
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s ease';
                document.body.style.opacity = '1';
            }, 100);
        });

        // Console message
        console.log('%c🛒 Sajilo Shopping Cart', 'color: #667eea; font-size: 20px; font-weight: bold;');
        console.log('%cDeveloped by: Aditya Rai & Prakash Thapa', 'color: #764ba2; font-size: 14px;');
    </script>
</body>
</html>