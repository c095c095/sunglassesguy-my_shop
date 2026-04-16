-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2025 at 09:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank`
--

CREATE TABLE `bank` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank`
--

INSERT INTO `bank` (`id`, `type`, `name`, `number`) VALUES
(1, 1, 'กสิกร', '1198247797'),
(2, 1, 'กรุงไทย', '1581642187');

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `img` varchar(255) NOT NULL,
  `sort` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `update_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banner`
--

INSERT INTO `banner` (`id`, `img`, `sort`, `status`, `update_date`) VALUES
(1, 'IMG_20250321062608_661c051d7b04a3fd5cbc9ef5afcfdcb7.png', 1, 1, '2025-03-21 07:53:31'),
(2, 'IMG_20250321063510_56fea5f347cac3e7b64c9de1e37e0631.png', 2, 1, '2025-03-21 07:56:09'),
(3, 'IMG_20250321063514_045a9ade1af3084e38da46680a3f027c.png', 3, 1, '2025-03-21 07:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `qty`) VALUES
(4, 1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL,
  `delivery_type` int(11) NOT NULL,
  `tracking` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `note` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `user_id`, `order_date`, `name`, `phone`, `address`, `total_price`, `delivery_fee`, `delivery_type`, `tracking`, `status`, `note`, `timestamp`) VALUES
(1, 5, '2024-11-10 18:22:06', 'Bob The Roblox', '0123456789', '123 ตำบลทะเลชุบศร อำเภอเมือง จังหวัดลพบุรี 15000', 390.00, 0.00, 2, '', 1, '', '2024-11-10 11:22:06'),
(2, 1, '2024-11-18 09:22:22', 'ศรศิววงศ์ สุขเลิศ', '0889502125', '10 หมู่2 ตำบลนิคมสร้างตนเอง อำเภอเมือง จังหวัดลพบุรี 15000', 390.00, 0.00, 2, '', 4, '', '2024-11-18 02:22:33'),
(3, 5, '2025-03-23 00:10:36', 'Bob The Roblox', '0123456789', '123 ตำบลทะเลชุบศร อำเภอเมือง จังหวัดลพบุรี 15000', 390.00, 50.00, 1, '', 2, '', '2025-03-22 19:39:53'),
(4, 5, '2025-03-23 02:48:26', 'Bob The Roblox', '0123456789', '123 ตำบลทะเลชุบศร อำเภอเมือง จังหวัดลพบุรี 15000', 120.00, 50.00, 1, '', 2, '', '2025-03-22 20:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `order_detail`
--

CREATE TABLE `order_detail` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_img` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_detail`
--

INSERT INTO `order_detail` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `product_img`, `qty`) VALUES
(1, 1, 1, 'คันเบ็ดสปินนิ่ง', 390.00, 'Spinningrod.jpg', 1),
(2, 2, 1, 'คันเบ็ดสปินนิ่ง', 390.00, 'Spinningrod.jpg', 1),
(3, 3, 1, 'คันเบ็ดสปินนิ่ง', 390.00, 'Spinningrod.jpg', 1),
(4, 4, 21, 'ตะกั่ว (หนอนยาง)', 15.00, 'sinkerC.jpg', 8);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `pay_date` date NOT NULL,
  `pay_time` time NOT NULL,
  `img` varchar(255) NOT NULL,
  `submit_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `order_id`, `bank_id`, `pay_date`, `pay_time`, `img`, `submit_date`) VALUES
(1, 3, 1, '2025-03-23', '00:10:00', 'IMG_20250323001050_79a324408fda1475429dcd669feb94a6.png', '2025-03-22 17:10:50'),
(2, 4, 1, '2025-03-23', '02:48:00', 'IMG_20250323024847_6c80d044a0fd16d54225470f78c01b40.jpg', '2025-03-22 19:48:47');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `detail` text NOT NULL,
  `img` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `detail`, `img`, `price`, `stock`, `type_id`) VALUES
(1, 'คันเบ็ดสปินนิ่ง', 'คันเบ็ดสปินนิ่งที่เหมาะสำหรับการตกปลาทั่วไป', 'Spinningrod.jpg', 390.00, 12, 1),
(2, 'คันเบ็ดเบส', 'คันเบ็ดที่เหมาะสำหรับการตกปลาขนาดใหญ่', 'Baitrod.jpg', 690.00, 6, 1),
(4, 'รอกตกปลาสปินนิ่ง', 'รอกที่มีความละเอียดสูงสำหรับการตกปลา', 'Spining.jpg', 590.00, 15, 2),
(5, 'รอกตกเบท', 'รอกที่มีน้ำหนักเบา เหมาะสำหรับการตกปลาเบา', 'Baitcas.jpg', 790.00, 20, 2),
(7, 'สายเบ็ด 20lb', 'สายเบ็ดสำหรับการตกปลาขนาดกลาง', 'Sline.jpg', 195.00, 50, 3),
(8, 'สายเบ็ด 30lb', 'สายเบ็ดที่มีความทนทานสูง', 'Sline.jpg', 195.00, 40, 3),
(9, 'สายเบ็ด 10lb', 'สายเบ็ดสำหรับการตกปลาขนาดเล็ก', 'Sline.jpg', 195.00, 60, 3),
(10, 'เหยื่อปลอมกระดี่', 'เหยื่อล่อคุณภาพดีสำหรับตกปลา', 'lureA.jpg', 40.00, 98, 4),
(11, 'เหยื่อกบยาง(ยักษ์ยาง)', 'เหยื่อล่อที่ออกแบบมาสำหรับปลาขนาดเล็ก', 'lureB.jpg', 150.00, 80, 4),
(12, 'เหยื่อกบยาง', 'เหยื่อล่อที่เหมาะสำหรับการตกปลาช่อน', 'lureC.jpg', 150.00, 97, 4),
(13, 'กล่องอุปกรณ์ตกปลา', 'กล่องที่สามารถเก็บอุปกรณ์ตกปลาได้ครบ', 'boxA.jpg', 200.00, 24, 5),
(14, 'กล่องเก็บอุปกรณ์ตกปลา ใหญ่', 'กล่องที่ออกแบบมาสำหรับเก็บอุปกรณ์', 'boxB.jpg', 500.00, 24, 5),
(16, 'ตะขอตกปลา กล่อง', 'ตะขอตกปลาขนาดกลางสำหรับตกปลา', 'hookC.jpg', 50.00, 200, 6),
(17, 'ตะขอตกปลาสำหรับเกี่ยวหนอน', 'ตะขอขนาดเล็กสำหรับการตกปลา', 'hookB.jpg', 45.00, 149, 6),
(18, 'ตะขอตกปลาแบบพิเศษ (circle)', 'ตะขอที่ออกแบบมาเพื่อการตกปลาเฉพาะทาง', 'hookA.jpg', 60.00, 100, 6),
(19, 'ตะกั่ว', 'ตัวจมสำหรับการตกปลาแบบเฉพาะจุด', 'sinkerA.jpg', 10.00, 0, 7),
(20, 'ตะกั่ว (เหลี่ยม)', 'ตัวจมที่เหมาะสำหรับการตกปลาในน้ำลึก', 'sinkerB.jpg', 12.00, 100, 7),
(21, 'ตะกั่ว (หนอนยาง)', 'ตัวจมขนาดใหญ่สำหรับตกปลาใหญ่', 'sinkerC.jpg', 15.00, 71, 7),
(25, 'ทุ่นโฟมตกปลา', 'ทุ่นตกปลาที่สามารถใช้ได้ในทุกสภาพน้ำ', 'floatA.jpg', 10.00, 80, 9),
(26, 'ทุ่นจรวดตกปลา', 'ทุ่นตกปลาที่ออกแบบมาให้มีน้ำหนักมาก', 'floatB.jpg', 25.00, 70, 9),
(27, 'ทุ่นกระสือ', 'ทุ่นที่ออกแบบมาใช้ในน้ำลึก', 'IMG_20250321030329_46332e4440b8b00d157c20782ce619e7.jpg', 30.00, 88, 9);

-- --------------------------------------------------------

--
-- Table structure for table `product_type`
--

CREATE TABLE `product_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_type`
--

INSERT INTO `product_type` (`id`, `name`, `img`) VALUES
(1, 'คันเบ็ด', 'rod.png'),
(2, 'รอกตกปลา', 'reel.png'),
(3, 'สายเบ็ดตกปลา', 'line.png'),
(4, 'เหยื่อปลอม', 'lures.png'),
(5, 'กล่องอุปกรณ์ตกปลา', 'tacklebox.png'),
(6, 'ตะขอตกปลา', 'hooks.png'),
(7, 'ตัวจมและตัวดิ่ง', 'sinkers.png'),
(9, 'ทุ่นตกปลา', 'Float.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `firstname` varchar(120) NOT NULL,
  `lastname` varchar(120) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `permission` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `firstname`, `lastname`, `phone`, `address`, `permission`, `created_at`) VALUES
(1, 'c095c095', '$2y$10$Cobw3c1CUM6S8CZAOSXyqeYxht5bxzeHfoL68.ed8pgREzMF2oj2u', 'paaoneza851@gmail.com', 'ศรศิววงศ์', 'สุขเลิศ', '0889502125', '10 หมู่2 ตำบลนิคมสร้างตนเอง อำเภอเมือง จังหวัดลพบุรี 15000', 2, '2024-10-01 09:51:38'),
(2, 'JustJess', '$2y$10$70tw59VzcgQtfC58j2vGVeOnolHMIiOYFvOZjNrx5IEo4qyt9fEyO', 'gan2499500@gmail.com', 'Somjet', 'Rithbunreng', '0649985666', '51 ม.1 ต.บางคู้', 1, '2024-10-01 09:51:38'),
(4, 'Newuser01', '$2y$10$xU7/LlMKW6YyIix/DO.D3.N4.inyPIPY.mGGP589LpsAPy11t9JSa', 'GAN247733@gmail.com', 'Thinrit', 'Ritbunreng', '0898013087', '51 ม.1 ต.บางคู้\r\n15150', 1, '2024-11-01 09:51:38'),
(5, 'bob', '$2y$10$vLEf/B1QxPxb0Mm5a21hPOgrJiTT0juJlB9mDChCI69Z8vMfCD6CK', 'bob@gmail.com', 'Bob', 'The Roblox', '0123456789', '123 ตำบลทะเลชุบศร อำเภอเมือง จังหวัดลพบุรี 15000', 1, '2024-11-01 09:51:38'),
(6, 'rosadin', '$2y$10$q1BdDTA52K2B5ZSKtPGyyODOf.mz7TDMle87A4HCjXFhQK5hQunHG', 'rosadin@gmail.com', 'Rosadin', 'Roblox', '0123456788', 'test test', 1, '2024-11-18 10:04:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_type`
--
ALTER TABLE `product_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `product_type`
--
ALTER TABLE `product_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
