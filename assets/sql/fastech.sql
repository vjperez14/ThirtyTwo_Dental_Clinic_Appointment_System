-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2022 at 11:02 AM
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
  `status` varchar(11) NOT NULL,
  `notif_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`apt_id`, `requestee`, `name`, `phone`, `email`, `address`, `service`, `date`, `time`, `issue`, `ticket`, `status`, `notif_status`) VALUES
(114, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', '5315 Flora Vista, Peacock St., Brgy. Commonwealth', 'RESTORATION OR PASTA', '2022-04-26', '8:00 AM', 'asdasd', 'jnMoFV9SefKrFw==', 'approved', 1),
(115, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', '5315 Flora Vista, Peacock St., Brgy. Commonwealth', 'DENTURES', '2022-04-25', '8:00 AM', 'asdasdasd', '1XUyA+n6qCfmTw==', 'approved', 1),
(116, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', '5315 Flora Vista, Peacock St., Brgy. Commonwealth', '', '2022-04-25', '8:00 AM', 'asdasd', 'YChfX7oNVXX5IQ==', 'pending', 1),
(120, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', '5315 Flora Vista, Peacock St., Brgy. Commonwealth', 'ORAL PROPHYLAXYS OR CLEANING', '2022-04-26', '11:00 AM', '', 'VjYCQeyC0ThOsg==', 'pending', 1),
(121, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', '5315 Flora Vista, Peacock St., Brgy. Commonwealth', 'ORAL PROPHYLAXYS OR CLEANING', '2022-04-26', '9:00 AM', 'asdasdasd', 'ex1DlKHkM5puqg==', 'approved', 1),
(122, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', '5315 Flora Vista, Peacock St., Brgy. Commonwealth', 'ORAL PROPHYLAXYS OR CLEANING', '2022-04-27', '10:00 AM', 'agaefghafgadfgasdfgafad', 'J4SRaMKpN34nKw==', 'approved', 1),
(123, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', '5315 Flora Vista, Peacock St., Brgy. Commonwealth', 'ORAL PROPHYLAXYS OR CLEANING', '2022-04-28', '8:00 AM', 'SDFSADGASD', 'tlwvhnrwXg7ijQ==', 'pending', 1);

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
(10, 'perezvj.social@gmail.com', '5ee47fb5023ecee505593aeb29580802c9372d8eef88202fc3cfd4696a4cd91f9b8868c2d77270bb94336aee7b2a17e6bec9'),
(11, 'perezvj.social@gmail.com', '48d6eee2fcbc75f2758873f776dd1f83fcb6a535f97c1d85124dfdde091e80f520d876a54387eb1e9f65a9eba1cc74e25be2');

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
(69, 'Vince John', 'Perez', 'perezvj.social@gmail.com', '8f18d7c7390e1be8edb8b0013f75e0cc', 9359148135),
(70, 'Vince John', 'Perez', 'perezvj14@gmail.com', '356531c7cf37111656f9e782b7c5efa5', 9359148135),
(71, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(72, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(73, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(74, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(75, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(76, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(77, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(78, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(79, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(80, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(81, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(82, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(83, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(84, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0),
(85, '', '', '', 'd41d8cd98f00b204e9800998ecf8427e', 0);

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
  MODIFY `apt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `registered_accounts`
--
ALTER TABLE `registered_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

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
