CREATE DATABASE itpMidtermLabExam;
USE itpMidtermLabExam;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL UNIQUE,
  `email` varchar(255) NOT NULL UNIQUE,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address_label` varchar(50) DEFAULT 'Home',
  `street_address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `food_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'cash_on_delivery',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `food_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `food_id` (`food_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `food_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `food_id` (`food_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `food_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Drop foreign key constraints first (if they exist)
SET FOREIGN_KEY_CHECKS = 0;

-- Clear tables in correct order (child tables first)
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `cart`;
TRUNCATE TABLE `user_addresses`;
TRUNCATE TABLE `food_items`;
TRUNCATE TABLE `users`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert all menu items
INSERT INTO `food_items` (`id`, `name`, `description`, `price`, `image_url`) VALUES
-- Main Dishes
(1, 'Classic Burger', 'Juicy beef patty with fresh vegetables', 75.00, 'img/Main Dishes/burger.jpg'),
(2, 'Pepperoni Pizza', 'Traditional pizza with pepperoni and cheese', 130.00, 'img/Main Dishes/pizza.jpg'),
(3, 'Spaghetti Meatballs', 'Pasta with homemade meatballs', 95.00, 'img/Main Dishes/pasta.jpg'),
(4, 'Grilled Chicken', 'Herb-marinated grilled chicken breast', 120.00, 'img/Main Dishes/grilled_chicken.jpg'),
(5, 'Beef Steak', 'Premium cut beef steak with vegetables', 350.00, 'img/Main Dishes/steak.jpg'),
(6, 'Fish & Chips', 'Crispy battered fish with fries', 180.00, 'img/Main Dishes/fish_chips.jpg'),
(7, 'Chicken Curry', 'Spicy chicken curry with rice', 140.00, 'img/Main Dishes/chicken_curry.jpg'),
(8, 'Beef Tacos', 'Three soft tacos with seasoned beef', 110.00, 'img/Main Dishes/tacos.jpg'),
(9, 'Pork Chop', 'Grilled pork chop with apple sauce', 160.00, 'img/Main Dishes/pork_chop.jpg'),
(10, 'Salmon Fillet', 'Grilled salmon with lemon butter', 220.00, 'img/Main Dishes/salmon_fillet.jpg'),

-- Appetizers
(11, 'Buffalo Wings', 'Spicy chicken wings with blue cheese dip', 140.00, 'img/Appetizers/buffalo_wings.jpg'),
(12, 'Mozzarella Sticks', 'Breaded cheese sticks with marinara', 90.00, 'img/Appetizers/mozzarella_sticks.jpg'),
(13, 'Nachos Grande', 'Loaded nachos with cheese and toppings', 120.00, 'img/Appetizers/nachos_grande.jpg'),
(14, 'Spinach Dip', 'Creamy spinach and artichoke dip', 95.00, 'img/Appetizers/spinach_dip.jpg'),
(15, 'Calamari', 'Fried squid rings with aioli', 110.00, 'img/Appetizers/calamari.jpg'),
(16, 'Bruschetta', 'Toasted bread with tomatoes and herbs', 85.00, 'img/Appetizers/bruschetta.jpg'),
(17, 'Spring Rolls', 'Vegetable spring rolls with sweet chili sauce', 75.00, 'img/Appetizers/spring_rolls.jpg'),
(18, 'Garlic Bread', 'Toasted bread with garlic butter', 60.00, 'img/Appetizers/garlic_bread.jpg'),
(19, 'Potato Skins', 'Loaded potato skins with bacon and cheese', 95.00, 'img/Appetizers/potato_skins.jpg'),
(20, 'Shrimp Cocktail', 'Chilled shrimp with cocktail sauce', 130.00, 'img/Appetizers/shrimp_cocktail.jpg'),

-- Salads
(21, 'Caesar Salad', 'Classic caesar with romaine and croutons', 100.00, 'img/Salads/caesar_salad.jpg'),
(22, 'Greek Salad', 'Mixed greens with feta and olives', 110.00, 'img/Salads/greek_salad.jpg'),
(23, 'Cobb Salad', 'Chopped salad with chicken and bacon', 120.00, 'img/Salads/cobb_salad.jpg'),
(24, 'Quinoa Salad', 'Healthy quinoa with roasted vegetables', 115.00, 'img/Salads/quinoa_salad.jpg'),
(25, 'Caprese Salad', 'Tomato and mozzarella with basil', 95.00, 'img/Salads/caprese_salad.jpg'),
(26, 'Tuna Niçoise', 'Tuna salad with eggs and olives', 130.00, 'img/Salads/tuna_nicoise.jpg'),
(27, 'Asian Chicken Salad', 'Chicken salad with sesame dressing', 125.00, 'img/Salads/asian_chicken_salad.jpg'),
(28, 'Waldorf Salad', 'Apple and walnut salad with celery', 105.00, 'img/Salads/waldorf_salad.jpg'),
(29, 'Southwest Salad', 'Mexican-style salad with corn and beans', 115.00, 'img/Salads/southwest_salad.jpg'),
(30, 'Garden Salad', 'Mixed greens with fresh vegetables', 90.00, 'img/Salads/garden_salad.jpg'),

-- Desserts
(31, 'Chocolate Cake', 'Rich chocolate layer cake', 85.00, 'img/Desserts/chocolate_cake.jpg'),
(32, 'Cheesecake', 'New York style cheesecake', 95.00, 'img/Desserts/cheese_cake.jpg'),
(33, 'Apple Pie', 'Homemade apple pie with ice cream', 80.00, 'img/Desserts/apple_pie.jpg'),
(34, 'Tiramisu', 'Classic Italian coffee dessert', 90.00, 'img/Desserts/tiramisu.jpg'),
(35, 'Crème Brûlée', 'French vanilla custard dessert', 100.00, 'img/Desserts/creme_brulee.jpg'),
(36, 'Ice Cream Sundae', 'Three scoops with toppings', 75.00, 'img/Desserts/ice_cream_sundae.jpg'),
(37, 'Lemon Tart', 'Tangy lemon custard tart', 85.00, 'img/Desserts/lemon_tart.jpg'),
(38, 'Chocolate Mousse', 'Light and airy chocolate dessert', 80.00, 'img/Desserts/chocolate_mousee.jpg'),
(39, 'Fruit Parfait', 'Fresh fruits with yogurt and granola', 70.00, 'img/Desserts/fruit_parfait.jpg'),
(40, 'Bread Pudding', 'Warm bread pudding with caramel', 75.00, 'img/Desserts/bread_pudding.jpg');

-- Insert a test user with extended fields
INSERT INTO `users` (`username`, `email`, `full_name`, `phone`, `password`) 
VALUES ('test', 'test@alingnena.com', 'Test User', '09123456789', 'password');

