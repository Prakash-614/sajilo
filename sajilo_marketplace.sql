SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sajilo_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_sales_statistics` (IN `days` INT)   BEGIN
    SELECT 
        DATE(created_at) as sale_date,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL days DAY)
    GROUP BY DATE(created_at)
    ORDER BY sale_date DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_product_stock` (IN `p_product_id` INT, IN `p_quantity` INT)   BEGIN
    UPDATE products 
    SET stock_quantity = stock_quantity - p_quantity
    WHERE product_id = p_product_id;

    UPDATE products 
    SET status = 'sold'
    WHERE product_id = p_product_id AND stock_quantity <= 0;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT '?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `parent_category_id`, `icon`, `created_at`) VALUES
(1, 'Electronics', 'Electronic devices and gadgets', NULL, '📱', '2025-12-20 17:39:16'),
(2, 'Fashion', 'Clothing and accessories', NULL, '👕', '2025-12-20 17:39:16'),
(3, 'Home & Garden', 'Home appliances and garden tools', NULL, '🏠', '2025-12-20 17:39:16'),
(4, 'Books', 'Books and educational materials', NULL, '📚', '2025-12-20 17:39:16'),
(5, 'Sports', 'Sports equipment and accessories', NULL, '⚽', '2025-12-20 17:39:16'),
(6, 'Gaming', 'Video games and gaming consoles', NULL, '🎮', '2025-12-20 17:39:16'),
(7, 'Beauty', 'Beauty and personal care products', NULL, '💄', '2025-12-20 17:39:16'),
(8, 'Art & Crafts', 'Art supplies and handmade items', NULL, '🎨', '2025-12-20 17:39:16');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('khalti','esewa','cod') NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `calculate_subtotal` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.price * NEW.quantity;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `main_image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_trending` tinyint(1) DEFAULT 0,
  `condition_type` enum('new','used','refurbished') DEFAULT 'new',
  `location` varchar(100) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `status` enum('active','inactive','sold','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `seller_id`, `category_id`, `product_name`, `description`, `price`, `original_price`, `discount_percentage`, `stock_quantity`, `main_image`, `is_featured`, `is_trending`, `condition_type`, `location`, `views`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Wireless Bluetooth Headphones', 'Premium quality wireless headphones with noise cancellation', 2399.00, 2999.00, 20, 50, 'headphones.jpg', 1, 1, 'new', 'Kathmandu', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16'),
(2, 2, 1, 'Smart Watch Pro', 'Latest smartwatch with health tracking features', 4999.00, 4999.00, 0, 30, 'smartwatch.jpg', 1, 1, 'new', 'Kathmandu', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16'),
(3, 2, 2, 'Designer Handbag', 'Elegant leather handbag for women', 3399.00, 3999.00, 15, 20, 'handbag.jpg', 0, 1, 'new', 'Lalitpur', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16'),
(4, 2, 1, 'Gaming Console', 'Latest generation gaming console', 45999.00, 45999.00, 0, 15, 'console.jpg', 1, 1, 'new', 'Kathmandu', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16'),
(5, 2, 3, 'Laptop Stand', 'Ergonomic adjustable laptop stand', 1299.00, 1299.00, 0, 40, 'laptop-stand.jpg', 0, 0, 'new', 'Bhaktapur', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16'),
(6, 2, 4, 'Running Shoes', 'Comfortable running shoes for athletes', 3499.00, 4999.00, 30, 25, 'shoes.jpg', 0, 1, 'new', 'Pokhara', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16'),
(7, 2, 3, 'Coffee Maker', 'Automatic coffee maker machine', 5999.00, 5999.00, 0, 10, 'coffee-maker.jpg', 0, 0, 'new', 'Kathmandu', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16'),
(8, 2, 4, 'Yoga Mat Set', 'Premium yoga mat with accessories', 1799.00, 1799.00, 0, 35, 'yoga-mat.jpg', 0, 1, 'new', 'Lalitpur', 0, 'active', '2025-12-20 17:39:16', '2025-12-20 17:39:16');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('buyer','seller','admin') DEFAULT 'buyer',
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default-avatar.png',
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `address`, `city`, `profile_image`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@sajilo.com.np', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '9841234567', 'admin', NULL, 'Kathmandu', 'default-avatar.png', 1, NULL, NULL, NULL, '2025-12-20 17:39:15', '2025-12-20 17:39:15'),
(2, 'seller1', 'seller@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ram Sharma', '9851234567', 'seller', NULL, 'Kathmandu', 'default-avatar.png', 1, NULL, NULL, NULL, '2025-12-20 17:39:15', '2025-12-20 17:39:15'),
(3, 'buyer1', 'buyer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sita Thapa', '9861234567', 'buyer', NULL, 'Pokhara', 'default-avatar.png', 1, NULL, NULL, NULL, '2025-12-20 17:39:15', '2025-12-20 17:39:15');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_order_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_order_summary` (
`order_id` int(11)
,`buyer_id` int(11)
,`order_number` varchar(20)
,`total_amount` decimal(10,2)
,`payment_method` enum('khalti','esewa','cod')
,`payment_status` enum('pending','paid','failed','refunded')
,`order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled')
,`shipping_address` text
,`shipping_city` varchar(50)
,`phone` varchar(15)
,`transaction_id` varchar(100)
,`notes` text
,`created_at` timestamp
,`updated_at` timestamp
,`buyer_name` varchar(50)
,`buyer_email` varchar(100)
,`item_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_product_list`
-- (See below for the actual view)
--
CREATE TABLE `view_product_list` (
`product_id` int(11)
,`seller_id` int(11)
,`category_id` int(11)
,`product_name` varchar(200)
,`description` text
,`price` decimal(10,2)
,`original_price` decimal(10,2)
,`discount_percentage` int(11)
,`stock_quantity` int(11)
,`main_image` varchar(255)
,`is_featured` tinyint(1)
,`is_trending` tinyint(1)
,`condition_type` enum('new','used','refurbished')
,`location` varchar(100)
,`views` int(11)
,`status` enum('active','inactive','sold','pending')
,`created_at` timestamp
,`updated_at` timestamp
,`seller_name` varchar(50)
,`seller_email` varchar(100)
,`category_name` varchar(100)
,`avg_rating` decimal(14,4)
,`review_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `view_order_summary`
--
DROP TABLE IF EXISTS `view_order_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_order_summary`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`buyer_id` AS `buyer_id`, `o`.`order_number` AS `order_number`, `o`.`total_amount` AS `total_amount`, `o`.`payment_method` AS `payment_method`, `o`.`payment_status` AS `payment_status`, `o`.`order_status` AS `order_status`, `o`.`shipping_address` AS `shipping_address`, `o`.`shipping_city` AS `shipping_city`, `o`.`phone` AS `phone`, `o`.`transaction_id` AS `transaction_id`, `o`.`notes` AS `notes`, `o`.`created_at` AS `created_at`, `o`.`updated_at` AS `updated_at`, `u`.`username` AS `buyer_name`, `u`.`email` AS `buyer_email`, count(`oi`.`order_item_id`) AS `item_count` FROM ((`orders` `o` left join `users` `u` on(`o`.`buyer_id` = `u`.`user_id`)) left join `order_items` `oi` on(`o`.`order_id` = `oi`.`order_id`)) GROUP BY `o`.`order_id` ;

-- --------------------------------------------------------

--
-- Structure for view `view_product_list`
--
DROP TABLE IF EXISTS `view_product_list`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_product_list`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`seller_id` AS `seller_id`, `p`.`category_id` AS `category_id`, `p`.`product_name` AS `product_name`, `p`.`description` AS `description`, `p`.`price` AS `price`, `p`.`original_price` AS `original_price`, `p`.`discount_percentage` AS `discount_percentage`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`main_image` AS `main_image`, `p`.`is_featured` AS `is_featured`, `p`.`is_trending` AS `is_trending`, `p`.`condition_type` AS `condition_type`, `p`.`location` AS `location`, `p`.`views` AS `views`, `p`.`status` AS `status`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `u`.`username` AS `seller_name`, `u`.`email` AS `seller_email`, `c`.`category_name` AS `category_name`, coalesce(avg(`r`.`rating`),0) AS `avg_rating`, count(`r`.`review_id`) AS `review_count` FROM (((`products` `p` left join `users` `u` on(`p`.`seller_id` = `u`.`user_id`)) left join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) left join `reviews` `r` on(`p`.`product_id` = `r`.`product_id`)) GROUP BY `p`.`product_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin` (`admin_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_parent` (`parent_category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_buyer` (`buyer_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_seller` (`seller_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_seller` (`seller_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_trending` (`is_trending`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_search` (`product_name`,`description`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`product_id`,`user_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
