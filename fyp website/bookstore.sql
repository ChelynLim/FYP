-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2025 at 06:16 AM
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
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super','normal') DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$3.PVHhy7Si6ctRRDtbcalOb0MvE5zoEkTL/dvf3qD6OSUNK6HrFym', 'normal'),
(2, 'davin', '$2y$10$EV8woFsE//RmeBUkD5CxtO2xTaIYx.X1GTa3XYsT73Cxd1y5AxHmS', 'normal'),
(4, 'super admin', '$2y$10$dvI.spPWJ6FbIIb5xZmhF.JBqS8WXdOLgH4LUFznE0Z6XE0TThAzO', 'super'),
(0, 'admin1', '123', 'normal'),
(5, 'admin2', '1234', 'normal');

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `book_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ISBN` varchar(13) NOT NULL,
  `publisher` varchar(100) NOT NULL,
  `author` varchar(100) NOT NULL,
  `price` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`book_id`, `name`, `ISBN`, `publisher`, `author`, `price`) VALUES
(1, 'Solo Leveling 1', '9783963585258', 'ALTRAVERSE (DE)', 'CHUGONG', 'S$26.05');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `phone`, `email`, `address`) VALUES
(1, 'John Lim', '9888 1234', 'john.lim@example.com', '88 Yishun Ring Rd, #04-12, Singapore 760088'),
(2, 'Sarah Lim', '81234567', 'sarah.lim@example.com', '45 Bedok North Street 3, Singapore'),
(3, 'John Tan', '91239876', 'john.tan@email.com', '60 Bishan Street 13, Singapore'),
(4, 'Clara Goh', '82345678', 'clara.goh@gmail.com', '99 Hougang Avenue 2, Singapore'),
(5, 'Timothy Ong', '83456789', 'tim.ong@yahoo.com', '35 Sengkang East Ave, Singapore'),
(6, 'Amanda Lee', '84567890', 'amanda.lee@outlook.com', '24 Bukit Batok Street 52, Singapore'),
(7, 'Marcus Teo', '85678901', 'marcus.teo@hotmail.com', '12 Punggol Walk, Singapore'),
(8, 'Felicia Ng', '86789012', 'felicia.ng@singmail.sg', '18 Yishun Ring Road, Singapore'),
(9, 'Isaac Yeo', '87890123', 'isaac.yeo@bookmail.com', '31 Jurong West St 41, Singapore');

-- --------------------------------------------------------

--
-- Table structure for table `store`
--

CREATE TABLE `store` (
  `store_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store`
--

INSERT INTO `store` (`store_id`, `name`, `address`) VALUES
(1, 'Kinobuniya', '390 Orchard Road');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `name`, `contact_person`, `phone`, `email`, `address`) VALUES
(1, 'Paper & Ink Distributors', 'Sarah Tan', '9123 4567', 'sarah@paperink.sg', '15 Ubi Avenue 3, #03-04, Singapore 408859'),
(2, 'PageTurner Supplies', 'Alice Tan', '91234567', 'alice@pageturner.com', '10 Bukit Timah Road, Singapore'),
(3, 'BookFlow Distributors', 'James Lim', '98765432', 'james@bookflow.sg', '21 Pasir Ris Central, Singapore'),
(4, 'Paper & Ink Ltd', 'Nora Goh', '81239876', 'nora@paperink.co', '55 Ubi Avenue 3, Singapore'),
(5, 'ReadMore Co.', 'Kelvin Ong', '90011223', 'kelvin@readmore.com', '77 Woodlands Ave 2, Singapore'),
(6, 'Classic Books Supply', 'Rachel Lee', '83334455', 'rachel@classicbooks.com', '88 Clementi Road, Singapore'),
(7, 'Book Haven Ltd', 'Eric Tan', '81112233', 'eric@bookhaven.sg', '120 Serangoon North, Singapore'),
(8, 'The Book Source', 'Jasmine Yeo', '82990011', 'jasmine@booksource.com', '66 Tampines Avenue 4, Singapore');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `store`
--
ALTER TABLE `store`
  ADD PRIMARY KEY (`store_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `store`
--
ALTER TABLE `store`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
