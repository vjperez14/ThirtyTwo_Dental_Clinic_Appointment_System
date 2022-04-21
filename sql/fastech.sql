-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 18, 2020 at 05:40 AM
-- Server version: 5.7.32-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fastech`
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
  `status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`apt_id`, `requestee`, `name`, `phone`, `email`, `address`, `service`, `date`, `time`, `issue`, `ticket`, `status`) VALUES
(57, 46, 'Vince Perez', 9359148135, 'perezvj14@gmail.com', 'testing', 'Hardware & Software Repair and Installation', '2020-09-08', '9:07 PM', 'test', '0r4mKlLZ2fbxKQ==', 'payment'),
(58, 46, 'Vince Perez', 9359148135, 'perezvj14@gmail.com', 'Unit 5315 Flora Vista Peacock St.', 'Hardware & Software Repair and Installation', '2020-09-30', '12:00 PM', 'My computer is not working.', 'YV4ExI7qdm47Iw==', 'pending'),
(59, 60, 'Kestrel Cervantes', 9999231139, 'qkkgcervantes@tip.edu.ph', 'Cubao, QC', 'Troubleshooting & Networking Installation', '2020-09-30', '9:00 AM', 'Testing', 'LbK3bksAi7Ta9Q==', 'servicing'),
(60, 60, 'Kestrel Cervantes', 9999231139, 'qkkgcervantes@tip.edu.ph', '24 Harvard Street, Brgy. Socorro, Cubao, Quezon City', 'Virus and Malware Removal', '2020-10-01', '10:30 AM', 'My computer has a virus.', 'zziXIO1LJD9hGw==', 'servicing');

-- --------------------------------------------------------

--
-- Table structure for table `registered_accounts`
--

CREATE TABLE `registered_accounts` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(35) NOT NULL,
  `phone` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `registered_accounts`
--

INSERT INTO `registered_accounts` (`id`, `firstname`, `lastname`, `email`, `password`, `phone`) VALUES
(46, 'Vince', 'Perez', 'perezvj14@gmail.com', 'ae2b1fca515949e5d54fb22b8ed95575', 9359148135),
(48, 'John Micko ', 'Rapanot', 'qwer1234@gmail.com', '5d93ceb70e2bf5daa84ec3d0cd2c731a', 9123456789),
(50, 'Kestrel', 'Cervantes', 'kestrel.0425@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 9999231139),
(51, 'Andrea', 'Evangelista', 'andrea@gmail.com', 'cd6ae72f7584100155a2dbb4240a79f9', 9975627347),
(54, 'Hanz', 'Rondin', 'hrrondin@gmail.com', '25d55ad283aa400af464c76d713c07ad', 9879903347),
(55, 'John Philip', 'Garcia', 'garciajp935@gmail.com', 'e8dc4081b13434b45189a720b77b6818', 9489901234),
(56, 'Venice Kellner', 'Cervantes', 'venice0907@yahoo.com', '25d55ad283aa400af464c76d713c07ad', 9876541234),
(60, 'Kestrel', 'Cervantes', 'qkkgcervantes@tip.edu.ph', '25d55ad283aa400af464c76d713c07ad', 9999231139),
(61, 'Jed Ryann', 'Loyola', 'loyolajedryann@gmail.com', '2ae0457a568bb5749d2cf0fbaa5660f3', 9557229777),
(62, 'Reginald', 'Bollosa', 'regibollosa@gmail.com', '3ae94dbdeaae636e220d86cb1c40852b', 9179668731),
(63, 'Vince John', 'Perez', 'perezvj.main@gmail.com', 'ae2b1fca515949e5d54fb22b8ed95575', 9359148135);

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
  MODIFY `apt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;
--
-- AUTO_INCREMENT for table `registered_accounts`
--
ALTER TABLE `registered_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `FK_id` FOREIGN KEY (`requestee`) REFERENCES `registered_accounts` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
