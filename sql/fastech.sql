-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2022 at 04:55 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
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
(65, 64, 'vincent halili', 12312312312, 'vincent@gmail.com', '123123', 'ORAL PROPHYLAXYS OR CLEANING', '2022-03-13', '5:13 PM', '123123', 'HUerlY/D8aQMJQ==', 'payment'),
(66, 64, 'vincent halili', 12312312312, 'vincent@gmail.com', '123123321', 'DENTURES', '2022-03-22', '5:15 PM', '123123', '0fOB7CNAFdJopA==', 'declined'),
(67, 64, 'vincent halili', 12312312321, 'vincent@gmail.com', '123123', 'ORAL PROPHYLAXYS OR CLEANING', '2022-03-23', '5:29 PM', '123123', '19mfVI1ylqY31A==', 'payment'),
(68, 64, 'vincent halili', 12312312312, 'vincent@gmail.com', '123123', 'DENTURES', '2022-03-04', '10:27 AM', '123123', 'DSA/pztHyGBOxQ==', 'payment'),
(69, 65, 'eugene halili', 9669435194, 'eugene@gmail.com', '123', 'DENTURES', '2022-03-16', '8:20 AM', '12321', 'zF4dns8DWvSz7A==', 'payment'),
(71, 64, 'vincent halili', 9669435194, 'vincent@gmail.com', 'santolan', 'RESTORATION OR PASTA', '2022-03-15', '8:40 AM', '123123213', 'J1zS3PFTI7CPBA==', 'payment'),
(72, 67, 'qwe rty', 9123123132, 'qwerty@gmail.com', 'pasig city', 'RESTORATION OR PASTA', '2022-03-10', '3:24 PM', 'sadasdasd', '3wizp3S5F3XA/A==', 'payment'),
(73, 64, 'vincent halili', 96694354194, 'vincent@gmail.com', '135 santolan', 'ORAL PROPHYLAXYS OR CLEANING', '2022-03-30', '2:03 PM', '', 'AwOma70UqDlxtA==', 'approved'),
(74, 64, 'vincent halili', 9123456789, 'vincent@gmail.com', 'santolan', 'RESTORATION OR PASTA', '2022-03-30', '11:35 AM', '123123', 'oR+5rgFKsxdRfQ==', 'approved'),
(77, 68, 'vincent halili', 9123123123, 'vincent14@gmail.com', 'santolan', 'ORAL PROPHYLAXYS OR CLEANING', '2022-04-18', '10:48 AM', 'asdas', 'yDlePnJ9wEXqbg==', 'approve'),
(78, 68, 'vincent halili', 9669435194, 'vincent14@gmail.com', 'santolan pasig city', 'ORAL PROPHYLAXYS OR CLEANING', '2022-04-20', '11:58 AM', '', 'Bf5Kx0rtFnARng==', 'approve'),
(79, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', 'Quezon City', 'ORAL PROPHYLAXYS OR CLEANING', '2022-04-07', '9:30 AM', 'Cleaning Teeth', 'BIhKqagcni64WA==', 'approved');

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
(9, 'perezvj.social@gmail.com', 'b34ebd983485d196c0841caf9eff62b86cdee950e94dcbc4f8c17785da8d144c3979da43ee124ff16a2af6b9a38e2d5d3083'),
(10, 'perezvj.social@gmail.com', '5ee47fb5023ecee505593aeb29580802c9372d8eef88202fc3cfd4696a4cd91f9b8868c2d77270bb94336aee7b2a17e6bec9');

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
(64, 'vincent', 'halili', 'vincent@gmail.com', '4297f44b13955235245b2497399d7a93', 9123456789),
(65, 'eugene', 'halili', 'eugene@gmail.com', '4297f44b13955235245b2497399d7a93', 9669435194),
(66, 'anthon', 'marquez', 'anthon@gmail.com', '4297f44b13955235245b2497399d7a93', 12312312312),
(67, 'qwe', 'rty', 'qwerty@gmail.com', '4297f44b13955235245b2497399d7a93', 9669435194),
(68, 'vincent', 'halili', 'vincent14@gmail.com', '4297f44b13955235245b2497399d7a93', 9669435194),
(69, 'Vince John', 'Perez', 'perezvj.social@gmail.com', '8f18d7c7390e1be8edb8b0013f75e0cc', 9359148135);

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
  MODIFY `apt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `registered_accounts`
--
ALTER TABLE `registered_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

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
