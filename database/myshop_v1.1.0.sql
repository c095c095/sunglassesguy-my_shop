-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2024 at 06:00 AM
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
(1, 1, 'Nat&Son Shop', '1198247797'),
(2, 1, 'Nat&Son Shop', '1581642187');

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `img` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `update_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `detail` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `detail`, `img`, `price`, `stock`, `type_id`) VALUES
(1, 'คันเบ็ดสปินนิ่ง', 'คันเบ็ดสปินนิ่งที่เหมาะสำหรับการตกปลาทั่วไป', 'Spinningrod.jpg', 390.00, 15, 1),
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
(16, 'ตะขอตกปลา กล่อง', 'ตะขอตกปลาขนาดกลางสำหรับตกปลา', 'hookC.jpg', 50.00, 199, 6),
(17, 'ตะขอตกปลาสำหรับเกี่ยวหนอน', 'ตะขอขนาดเล็กสำหรับการตกปลา', 'hookB.jpg', 45.00, 149, 6),
(18, 'ตะขอตกปลาแบบพิเศษ (circle)', 'ตะขอที่ออกแบบมาเพื่อการตกปลาเฉพาะทาง', 'hookA.jpg', 60.00, 100, 6),
(19, 'ตะกั่ว', 'ตัวจมสำหรับการตกปลาแบบเฉพาะจุด', 'sinkerA.jpg', 10.00, 0, 7),
(20, 'ตะกั่ว (เหลี่ยม)', 'ตัวจมที่เหมาะสำหรับการตกปลาในน้ำลึก', 'sinkerB.jpg', 12.00, 100, 7),
(21, 'ตะกั่ว (หนอนยาง)', 'ตัวจมขนาดใหญ่สำหรับตกปลาใหญ่', 'sinkerC.jpg', 15.00, 79, 7),
(25, 'ทุ่นโฟมตกปลา', 'ทุ่นตกปลาที่สามารถใช้ได้ในทุกสภาพน้ำ', 'floatA.jpg', 10.00, 80, 9),
(26, 'ทุ่นจรวดตกปลา', 'ทุ่นตกปลาที่ออกแบบมาให้มีน้ำหนักมาก', 'floatB.jpg', 25.00, 70, 9),
(27, 'ทุ่นกระสือ', 'ทุ่นที่ออกแบบมาใช้ในน้ำลึก', 'floatC.jpg', 30.00, 88, 9);

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
  `permission` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `firstname`, `lastname`, `phone`, `address`, `permission`) VALUES
(1, 'c095c095', '$2y$10$Cobw3c1CUM6S8CZAOSXyqeYxht5bxzeHfoL68.ed8pgREzMF2oj2u', 'paaoneza851@gmail.com', 'ศรศิววงศ์', 'สุขเลิศ', '0889502125', '10 หมู่2 ตำบลนิคมสร้างตนเอง อำเภอเมือง จังหวัดลพบุรี 15000', 1),
(2, 'JustJess', '$2y$10$70tw59VzcgQtfC58j2vGVeOnolHMIiOYFvOZjNrx5IEo4qyt9fEyO', 'gan2499500@gmail.com', 'Somjet', 'Rithbunreng', '0649985666', '51 ม.1 ต.บางคู้', 1),
(4, 'Newuser01', '$2y$10$xU7/LlMKW6YyIix/DO.D3.N4.inyPIPY.mGGP589LpsAPy11t9JSa', 'GAN247733@gmail.com', 'Thinrit', 'Ritbunreng', '0898013087', '51 ม.1 ต.บางคู้\r\n15150', 1),
(5, 'bob', '$2y$10$vLEf/B1QxPxb0Mm5a21hPOgrJiTT0juJlB9mDChCI69Z8vMfCD6CK', 'bob@gmail.com', 'Bob', 'The Roblox', '0123456789', '123 ตำบลทะเลชุบศร อำเภอเมือง จังหวัดลพบุรี 15000', 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `product_type`
--
ALTER TABLE `product_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
