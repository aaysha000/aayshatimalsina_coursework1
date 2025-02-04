-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 01, 2025 at 12:24 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `plants_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `name`, `price`, `image_url`, `created_at`) VALUES
(28, 'Gardening Shovel', 500.00, '../img/shovel.jpg', '2025-01-26 05:05:10'),
(29, 'Watering Can', 900.00, '../img/can.jpg', '2025-01-26 05:05:10'),
(30, 'Plant Fertilizer', 1200.00, '../img/fertilizer.jpg', '2025-01-26 05:05:10'),
(31, 'Garden Gloves', 400.00, '../img/gloves.jpg', '2025-01-26 05:05:10'),
(32, 'Pruning Shears', 1500.00, '../img/shears.jpg', '2025-01-26 05:05:10'),
(33, 'Compost Bin', 2000.00, '../img/compost.jpg', '2025-01-26 05:05:10'),
(34, 'Soil Tester Kit', 2500.00, '../img/soil-tester.jpg', '2025-01-26 05:05:10'),
(35, 'Garden Rake', 600.00, '../img/rake.jpg', '2025-01-26 05:05:10'),
(36, 'Sprinkler System', 3500.00, '../img/sprinkler.jpg', '2025-01-26 05:05:10'),
(37, 'Hanging Plant Basket', 700.00, '../img/hanging-basket.jpg', '2025-01-26 05:05:10'),
(38, 'Garden Kneeling Pad', 300.00, '../img/kneeling-pad.jpg', '2025-01-26 05:05:10'),
(39, 'Plant Ties', 200.00, '../img/plant-ties.jpg', '2025-01-26 05:05:10'),
(40, 'Plant Labels', 150.00, '../img/plant-labels.jpg', '2025-01-26 05:05:10'),
(41, 'Weed Barrier Fabric', 1200.00, '../img/weed-barrier.jpg', '2025-01-26 05:05:10'),
(42, 'Garden Trowel', 500.00, '../img/trowel.jpg', '2025-01-26 05:05:10'),
(43, 'Propagation Tray', 800.00, '../img/propagation-tray.jpg', '2025-01-26 05:05:10'),
(44, 'Garden Edging Tool', 1500.00, '../img/edging-tool.jpg', '2025-01-26 05:05:10'),
(45, 'Wheelbarrow', 4500.00, '../img/wheelbarrow.jpg', '2025-01-26 05:05:10'),
(46, 'Pest Control Net', 1000.00, '../img/pest-net.jpg', '2025-01-26 05:05:10'),
(47, 'Seed Starter Kit', 900.00, '../img/seed-starter-kit.jpg', '2025-01-26 05:05:10'),
(48, 'Irrigation Timer', 2500.00, '../img/irrigation-timer.jpg', '2025-01-26 05:05:10'),
(49, 'Garden Lanterns', 2000.00, '../img/garden-lanterns.jpg', '2025-01-26 05:05:10'),
(50, 'Bird House', 750.00, '../img/bird-house.jpg', '2025-01-26 05:05:10'),
(51, 'Organic Plant Fertilizer', 1200.00, '../img/organic-fertilizer.jpg', '2025-01-26 05:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `viewed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_plants`
--

CREATE TABLE `pending_plants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('Indoor','Outdoor') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plants`
--

CREATE TABLE `plants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('Indoor','Outdoor') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plants`
--

INSERT INTO `plants` (`id`, `name`, `category`, `price`, `image_url`) VALUES
(1, 'Spider Plant', 'Indoor', 715.00, '../img/spider_plant.jpg'),
(2, 'Snake Plant', 'Indoor', 884.00, '../img/snake_plant.jpg'),
(3, 'Peace Lily', 'Indoor', 910.00, '../img/peace_lily.jpg'),
(4, 'Pothos', 'Indoor', 585.00, '../img/pothos.jpg'),
(5, 'Rubber Plant', 'Indoor', 1157.00, '../img/rubber_plant.jpg'),
(6, 'ZZ Plant', 'Indoor', 936.00, '../img/zz_plant.jpg'),
(7, 'Fiddle Leaf Fig', 'Indoor', 1599.00, '../img/fiddle_leaf_fig.jpg'),
(8, 'Monstera Deliciosa', 'Indoor', 1950.00, '../img/monstera_deliciosa.jpg'),
(9, 'Boston Fern', 'Indoor', 877.50, '../img/boston_fern.jpg'),
(10, 'Areca Palm', 'Indoor', 1352.00, '../img/areca_palm.jpg'),
(11, 'Aloe Vera', 'Indoor', 676.00, '../img/aloe_vera.jpg'),
(12, 'Calathea', 'Indoor', 975.00, '../img/calathea.jpg'),
(13, 'Chinese Evergreen', 'Indoor', 1118.00, '../img/chinese_evergreen.jpg'),
(14, 'Jade Plant', 'Indoor', 754.00, '../img/jade_plant.jpg'),
(15, 'Money Plant', 'Indoor', 637.00, '../img/money_plant.jpg'),
(16, 'Anthurium', 'Indoor', 1326.00, '../img/anthurium.jpg'),
(17, 'Parlor Palm', 'Indoor', 793.00, '../img/parlor_palm.jpg'),
(18, 'Cast Iron Plant', 'Indoor', 988.00, '../img/cast_iron_plant.jpg'),
(19, 'Dumb Cane', 'Indoor', 1157.00, '../img/dumb_cane.jpg'),
(20, 'Dracaena Marginata', 'Indoor', 1196.00, '../img/dracaena_marginata.jpg'),
(21, 'Bird\'s Nest Fern', 'Indoor', 845.00, '../img/birds_nest_fern.jpg'),
(22, 'Swiss Cheese Plant', 'Indoor', 1404.00, '../img/swiss_cheese_plant.jpg'),
(23, 'Elephant Ear Plant', 'Indoor', 1092.00, '../img/elephant_ear_plant.jpg'),
(24, 'Prayer Plant', 'Indoor', 806.00, '../img/prayer_plant.jpg'),
(25, 'Rose', 'Outdoor', 975.00, '../img/rose.jpg'),
(26, 'Lavender', 'Outdoor', 624.00, '../img/lavender.jpg'),
(27, 'Tulip', 'Outdoor', 780.00, '../img/tulip.jpg'),
(28, 'Sunflower', 'Outdoor', 650.00, '../img/sunflower.jpg'),
(29, 'Jasmine', 'Outdoor', 897.00, '../img/jasmine.jpg'),
(30, 'Hibiscus', 'Outdoor', 1066.00, '../img/hibiscus.jpg'),
(31, 'Bougainvillea', 'Outdoor', 1209.00, '../img/bougainvillea.jpg'),
(32, 'Chrysanthemum', 'Outdoor', 715.00, '../img/chrysanthemum.jpg'),
(33, 'Daffodil', 'Outdoor', 871.00, '../img/daffodil.jpg'),
(34, 'Marigold', 'Outdoor', 494.00, '../img/marigold.jpg'),
(35, 'Hydrangea', 'Outdoor', 1248.00, '../img/hydrangea.jpg'),
(36, 'Dahlia', 'Outdoor', 1053.00, '../img/dahlia.jpg'),
(37, 'Geranium', 'Outdoor', 637.00, '../img/geranium.jpg'),
(38, 'Petunia', 'Outdoor', 728.00, '../img/petunia.jpg'),
(39, 'Begonia', 'Outdoor', 949.00, '../img/begonia.jpg'),
(40, 'Camellia', 'Outdoor', 1365.00, '../img/camellia.jpg'),
(41, 'Azalea', 'Outdoor', 1118.00, '../img/azalea.jpg'),
(42, 'Pansy', 'Outdoor', 442.00, '../img/pansy.jpg'),
(43, 'Cosmos', 'Outdoor', 585.00, '../img/cosmos.jpg'),
(44, 'Foxglove', 'Outdoor', 1014.00, '../img/foxglove.jpg'),
(45, 'Zinnia', 'Outdoor', 767.00, '../img/zinnia.jpg'),
(46, 'Snapdragon', 'Outdoor', 806.00, '../img/snapdragon.jpg'),
(47, 'Lily of the Valley', 'Outdoor', 1092.00, '../img/lily_of_the_valley.jpg'),
(48, 'Gardenia', 'Outdoor', 1196.00, '../img/gardenia.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review` text NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ;

-- --------------------------------------------------------

--
-- Table structure for table `user_wishlist`
--

CREATE TABLE `user_wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plant_id` (`plant_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `pending_plants`
--
ALTER TABLE `pending_plants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plants`
--
ALTER TABLE `plants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plant_id` (`plant_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plant_id` (`plant_id`),
  ADD KEY `material_id` (`material_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pending_plants`
--
ALTER TABLE `pending_plants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `plants`
--
ALTER TABLE `plants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD CONSTRAINT `user_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_cart_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_cart_ibfk_3` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD CONSTRAINT `user_wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_wishlist_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_wishlist_ibfk_3` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
