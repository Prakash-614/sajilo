<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sajilo - Your Trusted Digital Marketplace in Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    @keyframes slideIn {
        from {
            opacity: 0;
            
            transform: translateX(20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

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

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .product-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .product-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 25px 50px rgba(102, 126, 234, 0.3);
    }

    .category-card {
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .category-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: left 0.3s ease;
        z-index: 0;
    }

    .category-card:hover::before {
        left: 0;
    }

    .category-card:hover {
        color: white;
        transform: scale(1.08) rotate(2deg);
    }

    .category-card>* {
        position: relative;
        z-index: 1;
    }

    .hero-slide {
        display: none;
        opacity: 0;
        transition: opacity 0.6s ease;
    }

    .hero-slide.active {
        display: block;
        opacity: 1;
        animation: fadeInUp 0.8s ease-out;
    }

    .search-dropdown {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        opacity: 0;
    }

    .search-dropdown.active {
        max-height: 400px;
        opacity: 1;
    }

    .badge-pulse {
        animation: pulse 2s infinite;
    }

    .shine-effect {
        position: relative;
        overflow: hidden;
    }

    .shine-effect::after {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%);
        transform: rotate(45deg);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }

        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    .floating {
        animation: floating 3s ease-in-out infinite;
    }

    @keyframes floating {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
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
        animation: slideInUp 0.3s ease;
    }

    @keyframes slideInUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .notification {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9998;
        animation: slideInRight 0.3s ease;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <!-- Mobile Menu Overlay -->
    <div id="mobileMenu" class="modal">
        <div class="modal-content bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold gradient-text">Menu</h2>
                <button id="closeMobileMenu" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="space-y-4">
                <a href="#categories" class="block py-3 px-4 hover:bg-purple-50 rounded-lg transition">Categories</a>
                <a href="#deals" class="block py-3 px-4 hover:bg-purple-50 rounded-lg transition">Today's Deals</a>
                <a href="#trending" class="block py-3 px-4 hover:bg-purple-50 rounded-lg transition">Trending</a>
                <a href="#sellers" class="block py-3 px-4 hover:bg-purple-50 rounded-lg transition">Become a Seller</a>
                <a href="#help" class="block py-3 px-4 hover:bg-purple-50 rounded-lg transition">Help & Support</a>
                <hr class="my-4" />
                <a href="#login"
                    class="block py-3 px-4 bg-purple-600 text-white text-center rounded-lg hover:bg-purple-700 transition">Login
                    / Sign Up</a>
            </nav>
        </div>
    </div>

    <!-- Product Quick View Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content bg-white rounded-2xl p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-3xl font-bold gradient-text" id="modalProductName">
                    Product Name
                </h2>
                <button id="closeProductModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <img id="modalProductImage" src="" alt="Product" class="w-full rounded-xl shadow-lg" />
                </div>
                <div>
                    <div class="flex items-center mb-4">
                        <span class="text-yellow-400 text-xl">⭐⭐⭐⭐⭐</span>
                        <span class="text-gray-600 ml-2">(4.5/5 - 234 reviews)</span>
                    </div>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-purple-600" id="modalProductPrice">Rs 2,399</span>
                        <span class="text-gray-400 line-through text-xl ml-3">Rs 2,999</span>
                        <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-semibold ml-2">20%
                            OFF</span>
                    </div>
                    <div class="mb-6">
                        <h3 class="font-bold text-lg mb-3">Product Details:</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li>✓ Premium Quality Material</li>
                            <li>✓ 1 Year Warranty</li>
                            <li>✓ Free Delivery in Kathmandu Valley</li>
                            <li>✓ Cash on Delivery Available</li>
                            <li>✓ 7 Days Return Policy</li>
                        </ul>
                    </div>
                    <div class="mb-6">
                        <label class="font-semibold mb-2 block">Quantity:</label>
                        <div class="flex items-center gap-4">
                            <button class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 transition"
                                onclick="decreaseQuantity()">
                                -
                            </button>
                            <span id="quantity" class="text-xl font-bold">1</span>
                            <button class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 transition"
                                onclick="increaseQuantity()">
                                +
                            </button>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <button
                            class="flex-1 bg-purple-600 text-white py-4 rounded-xl hover:bg-purple-700 transition font-bold text-lg"
                            onclick="addToCartFromModal()">
                            Add to Cart
                        </button>
                        <button
                            class="flex-1 bg-green-600 text-white py-4 rounded-xl hover:bg-green-700 transition font-bold text-lg">
                            Buy Now
                        </button>
                    </div>
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>🔒 Secure Payment:</strong> Pay safely with Khalti,
                            eSewa, or Cash on Delivery
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Header -->
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
                    <a href="#" class="hover:underline hidden md:inline">Become a Seller</a>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-4">
                <!-- Logo -->
                <div class="flex items-center gap-2 text-2xl font-bold text-purple-600 cursor-pointer">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                    </svg>
                    <span class="hidden sm:inline">Sajilo</span>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Search Bar -->
                <div class="hidden md:flex flex-1 max-w-2xl relative">
                    <div class="relative w-full">
                        <input type="text" id="searchInput" placeholder="Search for products, categories, or cities..."
                            class="w-full px-6 py-3 pr-32 border-2 border-gray-300 rounded-full focus:outline-none focus:border-purple-500 transition" />
                        <!-- <button
                id="searchFilterBtn"
                class="absolute right-24 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-purple-600 px-3 border-l border-gray-300"
              >
                📍 All Cities
              </button> -->
                        <button
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-purple-600 text-white px-6 py-2 rounded-full hover:bg-purple-700 transition">
                            Search
                        </button>
                    </div>

                    <!-- Search Filter Dropdown -->
                    <div id="searchDropdown"
                        class="search-dropdown absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg mt-2 shadow-lg">
                        <div class="p-4">
                            <p class="font-semibold mb-3 text-gray-700">Search in:</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    class="city-btn px-4 py-2 bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-lg transition text-sm">
                                    All Nepal
                                </button>
                                <button
                                    class="city-btn px-4 py-2 bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-lg transition text-sm">
                                    Kathmandu
                                </button>
                                <button
                                    class="city-btn px-4 py-2 bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-lg transition text-sm">
                                    Pokhara
                                </button>
                                <button
                                    class="city-btn px-4 py-2 bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-lg transition text-sm">
                                    Lalitpur
                                </button>
                                <button
                                    class="city-btn px-4 py-2 bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-lg transition text-sm">
                                    Bhaktapur
                                </button>
                                <button
                                    class="city-btn px-4 py-2 bg-gray-100 hover:bg-purple-100 hover:text-purple-600 rounded-lg transition text-sm">
                                    Biratnagar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Actions -->
                <div class="flex items-center gap-3 md:gap-6">
                    <button class="hidden md:flex flex-col items-center hover:text-purple-600 transition group">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span class="text-xs mt-1">Wishlist</span>
                    </button>
                    <button id="cartBtn" class="flex flex-col items-center hover:text-purple-600 transition relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span id="cartCount"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center badge-pulse">0</span>
                        <span class="text-xs mt-1 hidden md:inline">Cart</span>
                    </button>
                    <a href="account.php" class="hidden md:flex flex-col items-center hover:text-purple-600 transition">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
    <span class="text-xs mt-1">Account</span>
</a>
                </div>
            </div>

            <!-- Mobile Search -->
            <div class="md:hidden mt-4">
                <input type="text" placeholder="Search products..."
                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-full focus:outline-none focus:border-purple-500" />
            </div>
        </nav>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-32 md:h-36"></div>

    <!-- Hero Carousel -->
    <section class="container mx-auto px-4 py-6">
        <div class="relative overflow-hidden rounded-2xl shadow-2xl h-64 md:h-96">
            <!-- Slide 1 -->
            <div class="hero-slide active absolute inset-0 bg-gradient-to-r from-purple-600 to-indigo-600">
                <div class="flex items-center justify-between h-full px-8 md:px-16 text-white">
                    <div class="max-w-xl">
                        <h1 class="text-3xl md:text-5xl font-bold mb-4">
                            🔒 Verified Sellers You Can Trust
                        </h1>
                        <p class="text-lg md:text-xl mb-6">
                            Shop with confidence from authenticated sellers across Nepal
                        </p>
                        <button
                            class="bg-white text-purple-600 px-6 md:px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition shine-effect">
                            Start Shopping
                        </button>
                    </div>
                    <div class="text-6xl md:text-9xl opacity-20 floating hidden md:block">
                        🛡️
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="hero-slide absolute inset-0 bg-gradient-to-r from-green-500 to-teal-500">
                <div class="flex items-center justify-between h-full px-8 md:px-16 text-white">
                    <div class="max-w-xl">
                        <h1 class="text-3xl md:text-5xl font-bold mb-4">
                            💳 Secure Payments
                        </h1>
                        <p class="text-lg md:text-xl mb-6">
                            Pay safely with Khalti, eSewa, or Cash on Delivery
                        </p>
                        <button
                            class="bg-white text-green-600 px-6 md:px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition shine-effect">
                            Explore Products
                        </button>
                    </div>
                    <div class="text-6xl md:text-9xl opacity-20 floating hidden md:block">
                        💰
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="hero-slide absolute inset-0 bg-gradient-to-r from-orange-500 to-red-500">
                <div class="flex items-center justify-between h-full px-8 md:px-16 text-white">
                    <div class="max-w-xl">
                        <h1 class="text-3xl md:text-5xl font-bold mb-4">
                            ⚡ Fast Delivery Across Nepal
                        </h1>
                        <p class="text-lg md:text-xl mb-6">
                            Get your products delivered quickly to your doorstep
                        </p>
                        <button
                            class="bg-white text-orange-600 px-6 md:px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition shine-effect">
                            Shop Now
                        </button>
                    </div>
                    <div class="text-6xl md:text-9xl opacity-20 floating hidden md:block">
                        🚚
                    </div>
                </div>
            </div>

            <!-- Carousel Controls -->
            <button id="prevSlide"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-100 rounded-full p-2 md:p-3 transition">
                <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button id="nextSlide"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 hover:bg-opacity-100 rounded-full p-2 md:p-3 transition">
                <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Carousel Indicators -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2">
                <button class="carousel-indicator w-2 h-2 md:w-3 md:h-3 rounded-full bg-white opacity-50 transition"
                    data-slide="0"></button>
                <button class="carousel-indicator w-2 h-2 md:w-3 md:h-3 rounded-full bg-white opacity-50 transition"
                    data-slide="1"></button>
                <button class="carousel-indicator w-2 h-2 md:w-3 md:h-3 rounded-full bg-white opacity-50 transition"
                    data-slide="2"></button>
            </div>
        </div>
    </section>

    <!-- Flash Deals Banner -->
    <section class="container mx-auto px-4 py-6">
        <div
            class="bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400 rounded-xl p-6 md:p-8 text-white text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <div class="absolute top-4 left-4 text-6xl">⚡</div>
                <div class="absolute bottom-4 right-4 text-6xl">🔥</div>
                <div class="absolute top-1/2 left-1/4 text-4xl">💥</div>
            </div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-bold mb-2">
                    ⚡ Flash Deals - Up to 50% OFF!
                </h2>
                <p class="text-lg md:text-xl mb-4">Limited time offer - Hurry up!</p>
                <div class="flex justify-center items-center gap-4 text-2xl md:text-3xl font-bold">
                    <div class="bg-white text-orange-600 px-4 py-2 rounded-lg">
                        <span id="hours">02</span>
                        <p class="text-xs">Hours</p>
                    </div>
                    <span>:</span>
                    <div class="bg-white text-orange-600 px-4 py-2 rounded-lg">
                        <span id="minutes">34</span>
                        <p class="text-xs">Minutes</p>
                    </div>
                    <span>:</span>
                    <div class="bg-white text-orange-600 px-4 py-2 rounded-lg">
                        <span id="seconds">56</span>
                        <p class="text-xs">Seconds</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Grid -->
    <section id="categories" class="container mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">📱</div>
                <p class="font-semibold text-sm">Electronics</p>
            </div>

            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">👕</div>
                <p class="font-semibold text-sm">Fashion</p>
            </div>

            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">🏠</div>
                <p class="font-semibold text-sm">Home Goods</p>
            </div>

            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">📚</div>
                <p class="font-semibold text-sm">Books</p>
            </div>

            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">⚽</div>
                <p class="font-semibold text-sm">Sports</p>
            </div>

            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">🎮</div>
                <p class="font-semibold text-sm">Gaming</p>
            </div>

            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">💄</div>
                <p class="font-semibold text-sm">Beauty</p>
            </div>

            <div class="category-card bg-white p-4 md:p-6 rounded-xl shadow-md text-center">
                <div class="text-3xl md:text-4xl mb-3">🎨</div>
                <p class="font-semibold text-sm">Art & Crafts</p>
            </div>
        </div>
    </section>

    <!-- Top Trending Products -->
    <section id="trending" class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">
                🔥 Top Trending Products
            </h2>
            <a href="#" class="text-purple-600 hover:underline font-semibold">View All →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Product Card 1 -->
            <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Wireless Headphones"
                data-price="2399" data-id="1">
                <div class="relative">
                    <img src="https://hukut.com/_next/image?url=https%3A%2F%2Fcdn.hukut.com%2Fjbl-tune-720bt-blue-Price-in-Nepal.webp1728365210341&w=3840&q=75"
                        alt="Wireless Headphones" class="w-full h-64 object-cover" loading="lazy" />

                    <span
                        class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">-20%</span>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        Wireless Headphones
                    </h3>
                    <div class="flex items-center mb-2">
                        <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                        <span class="text-gray-600 text-sm ml-2">(4.5)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-2xl font-bold text-purple-600">Rs 2,399</span>
                            <span class="text-gray-400 line-through text-sm ml-2">Rs 2,999</span>
                        </div>
                    </div>
                    <button
                        class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Smart Watch"
                data-price="4999" data-id="2">
                <div class="relative">
                    <img src="https://ultima.com.np/_next/image?url=https%3A%2F%2Fapi.ultima.com.np%2Fmodules%2Ffiles%2F145803131020257TvAWC.png&w=3840&q=75"
                        alt="Product" class="w-full h-64 object-cover" loading="lazy" />
                    <span
                        class="absolute top-2 right-2 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">New</span>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Smart Watch</h3>
                    <div class="flex items-center mb-2">
                        <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                        <span class="text-gray-600 text-sm ml-2">(4.8)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-2xl font-bold text-purple-600">Rs 4,999</span>
                        </div>
                    </div>
                    <button
                        class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Designer Handbag"
                data-price="3399" data-id="3">
                <div class="relative">
                    <img src="https://my.louisvuitton.com/images/is/image/lv/1/PP_VP_L/louis-vuitton-nano-diane--M83300_PM2_Front%20view.jpg"
                        alt="Product" class="w-full h-64 object-cover" loading="lazy" />
                    <span
                        class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">-15%</span>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Designer Handbag</h3>
                    <div class="flex items-center mb-2">
                        <span class="text-yellow-400">⭐⭐⭐⭐☆</span>
                        <span class="text-gray-600 text-sm ml-2">(4.2)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-2xl font-bold text-purple-600">Rs 3,399</span>
                            <span class="text-gray-400 line-through text-sm ml-2">Rs 3,999</span>
                        </div>
                    </div>
                    <button
                        class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Gaming Console"
                data-price="45999" data-id="4">
                <div class="relative">
                    <img src="https://allureinternational.com.np/image/variation_product_image/iHQ7eE7hEtYeCxQugzKCaoXESkDD1W1EHBaUiabA.webp"
                        alt="Product" class="w-full h-64 object-cover" loading="lazy" />
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Gaming Console</h3>
                    <div class="flex items-center mb-2">
                        <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                        <span class="text-gray-600 text-sm ml-2">(4.9)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-2xl font-bold text-purple-600">Rs 45,999</span>
                        </div>
                    </div>
                    <button
                        class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Recommended for You -->
    <section class="container mx-auto px-4 py-12 bg-gray-100 -mx-4">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">
                    ✨ Recommended for You
                </h2>
                <a href="#" class="text-purple-600 hover:underline font-semibold">View All →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Product Card 5 -->
                <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Laptop Stand"
                    data-price="1299" data-id="5">
                    <div class="relative">
                        <img src="https://s3-eu-west-1.amazonaws.com/backcslimages/newsite/product-images/1500-1500/FoldineX-Laptop-Stand.jpg"
                            alt="Product" class="w-full h-64 object-cover" loading="lazy" />
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Laptop Stand</h3>
                        <div class="flex items-center mb-2">
                            <span class="text-yellow-400">⭐⭐⭐⭐☆</span>
                            <span class="text-gray-600 text-sm ml-2">(4.3)</span>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-2xl font-bold text-purple-600">Rs 1,299</span>
                            </div>
                        </div>
                        <button
                            class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                            Add to Cart
                        </button>
                    </div>
                </div>

                <!-- Product Card 6 -->
                <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Running Shoes"
                    data-price="3499" data-id="6">
                    <div class="relative">
                        <img src="https://images.ctfassets.net/xanbi6q061ft/651xuiU20gbZWkVRTxzmdl/7bb90c6530d84454ab65d5c9632d91ca/20250422_nike_bp_vomero18_shopWomens.png"
                            alt="Product" class="w-full h-64 object-cover" loading="lazy" />
                        <span
                            class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">-30%</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Running Shoes</h3>
                        <div class="flex items-center mb-2">
                            <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                            <span class="text-gray-600 text-sm ml-2">(4.7)</span>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-2xl font-bold text-purple-600">Rs 3,499</span>
                                <span class="text-gray-400 line-through text-sm ml-2">Rs 4,999</span>
                            </div>
                        </div>
                        <button
                            class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                            Add to Cart
                        </button>
                    </div>
                </div>

                <!-- Product Card 7 -->
                <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Coffee Maker"
                    data-price="5999" data-id="7">
                    <div class="relative">
                        <img src="https://img.drz.lazcdn.com/static/lk/p/da2afe61b93f0a4f632c97238bc80aab.jpg_960x960q80.jpg_.webp"
                            alt="Product" class="w-full h-64 object-cover" loading="lazy" />
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Coffee Maker</h3>
                        <div class="flex items-center mb-2">
                            <span class="text-yellow-400">⭐⭐⭐⭐☆</span>
                            <span class="text-gray-600 text-sm ml-2">(4.1)</span>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-2xl font-bold text-purple-600">Rs 5,999</span>
                            </div>
                        </div>
                        <button
                            class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                            Add to Cart
                        </button>
                    </div>
                </div>

                <!-- Product Card 8 -->
                <div class="product-card bg-white rounded-xl shadow-md overflow-hidden" data-product="Yoga Mat Set"
                    data-price="1799" data-id="8">
                    <div class="relative">
                        <img src="https://static-01.daraz.com.np/p/c217a6b15ccb3fac238197f89c98f677.jpg" alt="Product"
                            class="w-full h-64 object-cover" loading="lazy" />
                        <span
                            class="absolute top-2 right-2 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">New</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Yoga Mat Set</h3>
                        <div class="flex items-center mb-2">
                            <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                            <span class="text-gray-600 text-sm ml-2">(4.6)</span>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-2xl font-bold text-purple-600">Rs 1,799</span>
                            </div>
                        </div>
                        <button
                            class="add-to-cart w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Banner -->
    <section class="bg-gradient-to-r from-purple-600 to-indigo-600 py-12 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-white text-center">
                <div class="flex flex-col items-center">
                    <div class="text-5xl mb-3">🚚</div>
                    <h3 class="text-xl font-bold mb-2">Fast Delivery</h3>
                    <p class="text-sm opacity-90">
                        Quick delivery across all major cities in Nepal
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="text-5xl mb-3">🔒</div>
                    <h3 class="text-xl font-bold mb-2">Secure Payments</h3>
                    <p class="text-sm opacity-90">
                        Multiple payment options including Khalti & eSewa
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="text-5xl mb-3">↩️</div>
                    <h3 class="text-xl font-bold mb-2">Easy Returns</h3>
                    <p class="text-sm opacity-90">
                        Hassle-free returns within 7 days of purchase
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 pt-12 pb-6 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center gap-2 text-2xl font-bold text-white mb-4">
                        <svg class="w-8 h-8 text-purple-500" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                        </svg>
                        <span>Sajilo</span>
                    </div>
                    <p class="text-sm mb-4">
                        Your trusted digital marketplace connecting sellers and buyers
                        across Nepal.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="hover:text-purple-400 transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </a>
                        <a href="#" class="hover:text-purple-400 transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                            </svg>
                        </a>
                        <a href="#" class="hover:text-purple-400 transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">About Us</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Contact Us</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Careers</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Blog</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Sitemap</a>
                        </li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Help Center</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Track Order</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Returns & Refunds</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">Shipping Info</a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-purple-400 transition">FAQs</a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Contact Us</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-2">
                            <span>📍</span>
                            <span>United College, Kathmandu, Nepal</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span>📞</span>
                            <span>+977-9866114411</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span>📧</span>
                            <span>support@sajilo.com.np</span>
                        </li>
                    </ul>
                    <div class="mt-4">
                        <h4 class="text-white font-semibold mb-2">We Accept</h4>
                        <div class="flex gap-2 flex-wrap">
                            <div class="bg-white px-3 py-1 rounded text-xs font-semibold text-purple-600">
                                Khalti
                            </div>
                            <div class="bg-white px-3 py-1 rounded text-xs font-semibold text-green-600">
                                eSewa
                            </div>
                            <div class="bg-white px-3 py-1 rounded text-xs font-semibold text-gray-700">
                                COD
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="border-t border-gray-700 pt-6 mt-6">
                <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                    <p>
                        &copy; 2025 Sajilo. All rights reserved. A Project by Aditya Rai &
                        Prakash Thapa
                    </p>
                    <div class="flex gap-6 mt-4 md:mt-0">
                        <a href="#" class="hover:text-purple-400 transition">Privacy Policy</a>
                        <a href="#" class="hover:text-purple-400 transition">Terms of Service</a>
                        <a href="#" class="hover:text-purple-400 transition">Cookie Policy</a>
                    </div>
                </div>
                <div class="text-center mt-4 text-xs opacity-75">
                    <p>
                        Tribhuvan University | Faculty of Humanities and Social Sciences |
                        United College
                    </p>
                    <p class="mt-1">
                        Bachelor of Computer Application - Project I Proposal
                    </p>
                    <p class="mt-1">Supervised by: Samir Thapa</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
    // Cart count
    let cartCount = 0;

    // Quantity management for modal
    let modalQuantity = 1;

    function increaseQuantity() {
        modalQuantity++;
        document.getElementById("quantity").textContent = modalQuantity;
    }

    function decreaseQuantity() {
        if (modalQuantity > 1) {
            modalQuantity--;
            document.getElementById("quantity").textContent = modalQuantity;
        }
    }

    // Notification function
    function showNotification(message, type = "success") {
        const container = document.getElementById("notificationContainer");
        const notification = document.createElement("div");
        notification.className = `notification bg-${
          type === "success" ? "green" : "red"
        }-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md`;
        notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="font-semibold">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
                </div>
            `;
        container.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Hero Carousel Functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll(".hero-slide");
    const indicators = document.querySelectorAll(".carousel-indicator");
    const totalSlides = slides.length;

    function showSlide(n) {
        slides.forEach((slide) => slide.classList.remove("active"));
        indicators.forEach((indicator) =>
            indicator.classList.remove("opacity-100")
        );
        indicators.forEach((indicator) =>
            indicator.classList.add("opacity-50")
        );

        currentSlide = (n + totalSlides) % totalSlides;
        slides[currentSlide].classList.add("active");
        indicators[currentSlide].classList.remove("opacity-50");
        indicators[currentSlide].classList.add("opacity-100");
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    // Auto-advance carousel every 5 seconds
    let autoSlide = setInterval(nextSlide, 5000);

    // Manual navigation
    document.getElementById("nextSlide").addEventListener("click", () => {
        clearInterval(autoSlide);
        nextSlide();
        autoSlide = setInterval(nextSlide, 5000);
    });

    document.getElementById("prevSlide").addEventListener("click", () => {
        clearInterval(autoSlide);
        prevSlide();
        autoSlide = setInterval(nextSlide, 5000);
    });

    // Indicator navigation
    indicators.forEach((indicator, index) => {
        indicator.addEventListener("click", () => {
            clearInterval(autoSlide);
            showSlide(index);
            autoSlide = setInterval(nextSlide, 5000);
        });
    });

    // Initialize first slide
    showSlide(0);

    // Search Dropdown Functionality
    const searchFilterBtn = document.getElementById("searchFilterBtn");
    const searchDropdown = document.getElementById("searchDropdown");
    const searchInput = document.getElementById("searchInput");

    if (searchFilterBtn && searchDropdown) {
        searchFilterBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            searchDropdown.classList.toggle("active");
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", (e) => {
            if (
                !searchDropdown.contains(e.target) &&
                e.target !== searchFilterBtn
            ) {
                searchDropdown.classList.remove("active");
            }
        });

        // City selection
        document.querySelectorAll(".city-btn").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const city = e.target.textContent;
                searchFilterBtn.textContent = "📍 " + city;
                searchDropdown.classList.remove("active");
                showNotification(`Search location set to ${city}`);
            });
        });
    }

    // Mobile Menu
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");
    const mobileMenu = document.getElementById("mobileMenu");
    const closeMobileMenu = document.getElementById("closeMobileMenu");

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener("click", () => {
            mobileMenu.classList.add("active");
        });
    }

    if (closeMobileMenu) {
        closeMobileMenu.addEventListener("click", () => {
            mobileMenu.classList.remove("active");
        });
    }

    // Close mobile menu when clicking outside
    mobileMenu.addEventListener("click", (e) => {
        if (e.target === mobileMenu) {
            mobileMenu.classList.remove("active");
        }
    });

    // Product Modal
    const productModal = document.getElementById("productModal");
    const closeProductModal = document.getElementById("closeProductModal");

    if (closeProductModal) {
        closeProductModal.addEventListener("click", () => {
            productModal.classList.remove("active");
        });
    }

    // Close modal when clicking outside
    productModal.addEventListener("click", (e) => {
        if (e.target === productModal) {
            productModal.classList.remove("active");
        }
    });

    // Product Card Click Handler - Open Modal
    document.querySelectorAll(".product-card").forEach((card) => {
        card.addEventListener("click", function(e) {
            // Don't open modal if clicking the add to cart button
            if (e.target.classList.contains("add-to-cart")) {
                return;
            }

            const productName = this.getAttribute("data-product");
            const productPrice = this.getAttribute("data-price");
            const productImage = this.querySelector("img").src;

            document.getElementById("modalProductName").textContent = productName;
            document.getElementById("modalProductPrice").textContent =
                "Rs " + productPrice;
            document.getElementById("modalProductImage").src = productImage;

            modalQuantity = 1;
            document.getElementById("quantity").textContent = modalQuantity;

            productModal.classList.add("active");
        });
    });

    // Add to Cart from Modal
    function addToCartFromModal() {
        const productName =
            document.getElementById("modalProductName").textContent;
        cartCount += modalQuantity;
        document.getElementById("cartCount").textContent = cartCount;
        showNotification(`${modalQuantity} x ${productName} added to cart!`);
        productModal.classList.remove("active");
    }

    // Add to Cart Functionality
    document.querySelectorAll(".add-to-cart").forEach((btn) => {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            e.preventDefault();
            const productName =
                this.closest(".product-card").querySelector("h3").textContent;

            // Visual feedback
            const originalText = this.textContent;
            this.textContent = "✓ Added!";
            this.classList.remove("bg-purple-600", "hover:bg-purple-700");
            this.classList.add("bg-green-600");

            // Update cart count
            cartCount++;
            document.getElementById("cartCount").textContent = cartCount;

            // Show notification
            showNotification(`${productName} added to cart!`);

            // Reset button after 2 seconds
            setTimeout(() => {
                this.textContent = originalText;
                this.classList.remove("bg-green-600");
                this.classList.add("bg-purple-600", "hover:bg-purple-700");
            }, 2000);
        });
    });

    // Product Card Animations on Scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }, index * 100);
            }
        });
    }, observerOptions);

    document.querySelectorAll(".product-card").forEach((card) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "all 0.5s ease";
        observer.observe(card);
    });

    // Category Card Click Handler
    document.querySelectorAll(".category-card").forEach((card) => {
        card.addEventListener("click", () => {
            const category = card.querySelector("p").textContent;
            showNotification(`Browsing ${category} category`);
        });
    });

    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
                // Close mobile menu if open
                mobileMenu.classList.remove("active");
            }
        });
    });

    // Search Functionality
    if (searchInput) {
        searchInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                const searchTerm = searchInput.value.trim();
                if (searchTerm) {
                    showNotification(`Searching for: ${searchTerm}`);
                    // In a real application, this would trigger a search API call
                }
            }
        });
    }

    // Flash Deal Countdown Timer
    function updateCountdown() {
        const hoursElement = document.getElementById("hours");
        const minutesElement = document.getElementById("minutes");
        const secondsElement = document.getElementById("seconds");

        if (hoursElement && minutesElement && secondsElement) {
            let hours = parseInt(hoursElement.textContent);
            let minutes = parseInt(minutesElement.textContent);
            let seconds = parseInt(secondsElement.textContent);

            seconds--;

            if (seconds < 0) {
                seconds = 59;
                minutes--;
            }

            if (minutes < 0) {
                minutes = 59;
                hours--;
            }

            if (hours < 0) {
                hours = 23;
                minutes = 59;
                seconds = 59;
            }

            hoursElement.textContent = hours.toString().padStart(2, "0");
            minutesElement.textContent = minutes.toString().padStart(2, "0");
            secondsElement.textContent = seconds.toString().padStart(2, "0");
        }
    }

    // Update countdown every second
    setInterval(updateCountdown, 1000);

    // Cart Button Click
   document.getElementById('cartBtn').addEventListener('click', () => {
    window.location.href = 'cart.php';
});

    // Page Load Animation
    window.addEventListener("load", () => {
        document.body.style.opacity = "0";
        setTimeout(() => {
            document.body.style.transition = "opacity 0.5s ease";
            document.body.style.opacity = "1";
        }, 100);
    });

    // Add some interactivity to hero buttons
    document.querySelectorAll(".hero-slide button").forEach((btn) => {
        btn.addEventListener("click", () => {
            showNotification(
                "Welcome to Sajilo! Start exploring amazing products."
            );
            document
                .querySelector("#categories")
                .scrollIntoView({
                    behavior: "smooth"
                });
        });
    });

    // Console welcome message
    console.log(
        "%c🛒 Welcome to Sajilo!",
        "color: #667eea; font-size: 20px; font-weight: bold;"
    );
    console.log(
        "%cDeveloped by: Aditya Rai & Prakash Thapa",
        "color: #764ba2; font-size: 14px;"
    );
    console.log(
        "%cUnited College - BCA Project",
        "color: #666; font-size: 12px;"
    );
    // Make Add to Cart buttons actually work
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const productCard = this.closest('.product-card');
        const productName = productCard.getAttribute('data-product');
        const productPrice = productCard.getAttribute('data-price');
        
        // Get product ID from the card
        let productId = 1; // default
        if (productName.includes('Wireless Headphones')) productId = 1;
        if (productName.includes('Smart Watch')) productId = 2;
        if (productName.includes('Designer Handbag')) productId = 3;
        if (productName.includes('Gaming Console')) productId = 4;
        if (productName.includes('Laptop Stand')) productId = 5;
        if (productName.includes('Running Shoes')) productId = 6;
        if (productName.includes('Coffee Maker')) productId = 7;
        if (productName.includes('Yoga Mat')) productId = 8;
        
        // Send to cart.php
        fetch('cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=add_to_cart&product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                document.getElementById('cartCount').textContent = data.cart_count;
                
                // Show success
                this.textContent = '✓ Added!';
                this.classList.add('bg-green-600');
                setTimeout(() => {
                    this.textContent = 'Add to Cart';
                    this.classList.remove('bg-green-600');
                }, 2000);
                
                showNotification(data.message);
            }
        });
    });
});

// Load cart count when page loads
window.addEventListener('load', () => {
    fetch('cart.php?get_count=1')
        .then(response => response.json())
        .then(data => {
            if (data.cart_count) {
                document.getElementById('cartCount').textContent = data.cart_count;
            }
        })
        .catch(() => {});
});
    </script>
</body>

</html>