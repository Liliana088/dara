-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2025 at 04:13 PM
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
-- Database: `dara`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(100) NOT NULL,
  `category` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category`) VALUES
(1, 'Canned Goods'),
(2, 'Snacks'),
(3, 'Noodles & Instant Meals'),
(4, 'Beverages'),
(5, 'Rice & Grains'),
(6, 'Condiments & Sauces'),
(7, 'Personal Care & Household'),
(8, 'Cooking Ingredients'),
(9, 'Frozen Foods'),
(10, 'Health & Medicine'),
(11, 'Baby Needs'),
(12, 'Toiletries'),
(13, 'Alcohol & Cigarettes'),
(14, 'Miscellaneous');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `Code` varchar(100) NOT NULL,
  `Category` varchar(255) NOT NULL,
  `Description` varchar(250) NOT NULL,
  `Stock` int(250) NOT NULL,
  `cost` decimal(65,0) NOT NULL,
  `markup` int(100) NOT NULL,
  `Date Added` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `Code`, `Category`, `Description`, `Stock`, `cost`, `markup`, `Date Added`) VALUES
(2, '6565', '4', 'plus', 3, 14, 30, '2025-05-10'),
(4, '521512', '2', 'Lava Cake', 71, 6, 20, '2025-05-10'),
(5, '414199', '2', 'Skyflakes', 8, 6, 20, '2025-05-10'),
(6, '563463', '4', 'Coca Cola Mismo 290ml', 19, 16, 25, '2025-05-12'),
(7, '4346346', '4', 'Royal Mismo 290ml', 40, 16, 25, '2025-05-12'),
(8, '3535', '4', 'Sprite Mismo 290ml', 42, 16, 25, '2025-05-12'),
(9, '3451513', '4', 'Mountain Dew Mismo 290ml', 34, 16, 25, '2025-05-12'),
(10, '124124', '4', '7Up Mismo 290ml', 18, 16, 25, '2025-05-12'),
(11, '2141254', '4', 'C2 Solo 230 ml', 8, 15, 20, '2025-05-12'),
(12, '4646', '7', 'Creamsilk Green', 49, 7, 25, '2025-05-12'),
(13, '521512', '7', 'Dove Shampoo', 60, 6, 25, '2025-05-12'),
(14, '23525', '7', 'Sunsilk Pink', 38, 6, 25, '2025-05-12'),
(15, '124124', '2', 'Buttercream', 50, 6, 20, '2025-05-14');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(200) NOT NULL,
  `seller` varchar(250) NOT NULL,
  `payment_method` varchar(250) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `markup` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `date` datetime(1) NOT NULL,
  `cash_received` decimal(10,2) DEFAULT 0.00,
  `change_given` decimal(10,2) DEFAULT 0.00,
  `voided` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `seller`, `payment_method`, `subtotal`, `markup`, `total_cost`, `date`, `cash_received`, `change_given`, `voided`) VALUES
(1, 'Dara', 'Cash', 6.00, 1.20, 7.20, '2025-05-14 12:40:53.0', 10.00, 3.00, 0),
(2, 'Dara', 'Cash', 16.00, 4.00, 20.00, '2025-05-14 12:40:59.0', 20.00, 0.00, 0),
(4, 'Dara', 'Cash', 6.00, 1.00, 7.00, '2025-05-14 12:48:48.0', 10.00, 3.00, 1),
(5, 'Dara', 'Cash', 23.00, 5.75, 28.75, '2025-05-14 12:49:46.0', 30.00, 1.00, 0),
(6, 'Dara', 'Cash', 6.00, 1.20, 7.20, '2025-05-23 14:26:47.0', 10.00, 3.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_sale` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `product_id`, `quantity`, `price_at_sale`) VALUES
(1, 1, 4, 1, 7.20),
(2, 2, 6, 1, 20.00),
(4, 4, 5, 1, 7.20),
(5, 5, 8, 1, 20.00),
(6, 6, 5, 1, 7.20);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `name` varchar(250) NOT NULL,
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `last_login`) VALUES
(1, 'Pia', 'liliana', '4321', '2025-05-18 12:52:55'),
(2, 'Dara', 'admin', '123', '2025-05-23 20:54:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
