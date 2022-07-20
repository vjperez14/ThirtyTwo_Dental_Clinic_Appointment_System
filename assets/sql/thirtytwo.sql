-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2022 at 11:20 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thirtytwo`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_account`
--

CREATE TABLE `admin_account` (
  `id` int(11) NOT NULL,
  `user` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin_account`
--

INSERT INTO `admin_account` (`id`, `user`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `apt_id` int(11) NOT NULL,
  `requestee` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` bigint(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `service` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(50) DEFAULT NULL,
  `issue` varchar(255) NOT NULL,
  `ticket` varchar(255) NOT NULL,
  `status` varchar(11) NOT NULL,
  `notif_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`) VALUES
(17, 'perezvj.social@gmail.com', '78a66d102279a4ae89bccfe63079ca49f0a998304abf40cc1ca2e0e680e93ba0b4491930f4c0daa1f38095fcb0d86c5d0511'),
(18, 'qjanmarquez@tip.edu.ph', 'ad8bfec75d2784169b18df0333a963249bbac878dcc1846c77d0ff58a59e5ff86b32dc690ed05d049e5625d7b0e46d2ebac4'),
(19, 'qjanmarquez@tip.edu.ph', 'cce5e47dd3b5095e1043105b18132bda1169b38316f3b8734cf1356226e9932934797c6ee744fe0e9b7b9895dc5e4c8f10a1'),
(20, 'vincenthalili014@gmail.com', 'e6aaacdd56f99795a6e12bb4d170d8a20af2b1bd460d2b3a97dedff0decd369161fb23582e4a0ee90dbf57151fc6174a30c8'),
(21, 'vincenthalili014@gmail.com', '6497f8b2a62e99505039c31708b196ea22359da837b4a8465c4e6cdf466ca9f45f2ac7ed21b584ee8d3a670d2b409c54208c'),
(22, 'vincenthalili014@gmail.com', '547e7a8b90d2d467817945249db1c8e96ec92b5f1fd597c9e79702d6ff9036ad875b2e349ae7b56b1ee9615a78a9b92763e2'),
(23, 'vincenthalili014@gmail.com', 'b6d29ea45c254a8ec8632dfc7a7740b9aab12e3a5c475470d3e23a04d36526912743abb1f416f6fe27899bcbe759ac706ff5'),
(24, 'vincenthalili014@gmail.com', 'e4c2ffb729bc1128e1b0acd3894ea06ced98b2287da6251bb15d5e1669bdf261429966021aef86df5ff28eeb5fb47e47698c'),
(25, 'vincenthalili014@gmail.com', 'eb2da97bbfd8acd84b812e1472d7b88e057c375730273929c0085eab5fb36f829cc7fb6a81dd81aaa18f2ba75d2803bd8640'),
(26, 'vincenthalili014@gmail.com', 'be37d0d5997e10f71c7f07ae953cc119651960e076e1246ffec136606cd80772b6461e8d593cc2c28ae2e02c8454b747351b'),
(27, 'vincenthalili014@gmail.com', '0ce187bb99cfcfeea230438ffd2a74d3aec4c523ce1c2e1b60963af5ec25f4fab63ccfff00f170656f30d16dee4556fe189c'),
(28, 'vincenthalili014@gmail.com', '99971e30482df153a21a344f5b8e1bde4611e9166e39b609651aa56475c0b18b6d24698f96e7a929e10f6ad7903633414c2c'),
(29, 'adrianperlado06@gmail.com', 'd9bb6d4b7730f77ddc1bf661e622c2dd742bf38a2f60ed7341c943d39d48c5c974aaf6cfd45b0f5d9b9a3b94851ba24196a1'),
(30, 'vincenthalili014@gmail.com', 'ca9bf012e646d2041debd4e6cdcccb2264bf10f800c92c47002e94dd710487e3a9113aa0667e7a21b13a306fed7bd545424b');

-- --------------------------------------------------------

--
-- Table structure for table `registered_accounts`
--

CREATE TABLE `registered_accounts` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middle` varchar(10) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(35) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `phone` bigint(11) NOT NULL,
  `verified` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_account`
--
ALTER TABLE `admin_account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`apt_id`),
  ADD KEY `FK_id` (`requestee`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `registered_accounts`
--
ALTER TABLE `registered_accounts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_account`
--
ALTER TABLE `admin_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `apt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `registered_accounts`
--
ALTER TABLE `registered_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `FK_id` FOREIGN KEY (`requestee`) REFERENCES `registered_accounts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
