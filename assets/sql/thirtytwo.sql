-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 06, 2022 at 09:45 AM
-- Server version: 5.7.37-cll-lve
-- PHP Version: 7.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`apt_id`, `requestee`, `name`, `phone`, `email`, `address`, `service`, `date`, `time`, `issue`, `ticket`, `status`, `notif_status`) VALUES
(1, 69, 'Vince John Perez', 9359148135, 'perezvj.social@gmail.com', 'Quezon City', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-11', '9:00 AM', 'Cleaning', 'Ct87BwEd2Vn+JA==', 'declined', 1),
(2, 86, 'vincent halili', 9669435194, 'vincenthalili014@gmail.com', 'santolan', 'RESTORATION OR PASTA', '2022-05-13', '11:00 AM', 'dasdasdasd', '0iPD3gghYbZcqg==', 'approved', 1),
(3, 88, 'John Anthony Marquez', 9156689652, 'qjanmarquez@tip.edu.ph', '26 Makabayan, Brgy Obrero', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-15', '11:00 AM', 'I want to clean my teeth.', '9Bl3VvXOaymR9Q==', 'completed', 1),
(4, 89, 'Josalie  Martinez', 9310576675, 'josaliemartinez@gmail.com', 'Cogeo, Antipolo City', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-15', '2:00 PM', 'Problem', 'MM3STXCy361aUA==', 'pending', 1),
(5, 92, 'Angelo  Llaguna', 9171039933, 'angelo.ncdllgn@gmail.com', 'Kamuning, Quezon City', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-12', '8:00 AM', 'Cleaning', 'e3NK29j2pMFuGg==', 'pending', 1),
(6, 93, 'John Micko Rapanot', 9194282431, 'johnmickooo28@gmail.com', '17c address ko to Angono Rizal', '', '2022-05-18', '11:00 AM', 'masak8 ipin q\r\n', 't1mRLtA00reL9g==', 'approved', 1),
(7, 93, 'John Micko Rapanot', 9194282431, 'johnmickooo28@gmail.com', 'fafasfa', 'DENTURES', '2022-05-24', '9:00 AM', 'fasfasf', 'TBviZL31LXvi3A==', 'pending', 1),
(8, 94, 'Jade Santarinala', 9212741630, 'jmsantarinala02@gmail.com', 'N/as', 'RESTORATION OR PASTA', '2022-05-12', '2:00 PM', 'Website testing ', 'PI1ZfHwsZ1zQ5Q==', 'pending', 1),
(9, 94, 'Jade Santarinala', 9212741630, 'jmsantarinala02@gmail.com', '31-B L. Sianghio Street, Kamuning, Quezon City', 'RESTORATION OR PASTA', '2022-05-16', '3:00 PM', 'Both upper molars have deep tooth decay', 'fidlpOcxU6fklA==', 'approved', 1),
(10, 95, 'Casey Mhae Bardelosa', 9567255236, 'caseymhaebardelosa10@gmail.com', 'Cubao, Quezon City', 'DENTURES', '2022-05-13', '9:00 AM', 'Inquire for braces', 'd5VV+uJ+lcci9Q==', 'pending', 1),
(11, 96, 'Kimberly Santiago Borbe', 639208682169, 'qksborbe@tip.edu.ph', 'ronin:b5ea4c512cb834191e8d0492fe1acd17f64d3b5e', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-12', '3:00 PM', ',,,', '4P+wk1fXy7R7bg==', 'pending', 1),
(12, 97, 'Mark Frederick Obrero', 9276383818, 'markobrero14@gmail.com', 'Blk67 Lot10 Greater Lagro Quezon City', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-12', '2:00 PM', 'Masakit na ho gilagid na mamaga', 'bU0DJIo18/6PJw==', 'pending', 1),
(14, 98, 'Jonas Baybay', 9175360323, 'jeyels15@gmail.com', 'MB-26 U-406 Pamayanang Diego Silang', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-19', '5:00 PM', 'my ngipin needs to be malines because meron akong date sa 20 shawty ', 'jxJGG58wBsfHzg==', 'pending', 1),
(15, 99, 'Joanne Santarinala', 9953048815, 'joanneabad@gmail.com', '39 Rolling Road Brgy.Obrero Quezon City', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-30', '9:00 AM', 'I have teeth restoration on my upper left lateral incisor', 'maaAUOX76nqpUw==', 'pending', 1),
(16, 100, 'Shannie Abarca', 9106782471, 'qsdcabarca@tip.edu.ph', 'Phase 2 Eastwood Greenview San Isidro Rodriguez Rizal', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-17', '1:00 PM', 'Need Cleaning', '3IwmoQyrm63DEQ==', 'pending', 1),
(17, 101, 'Roi Guiao', 9953541089, 'realrobert66@gmail.com', 'North California Village, Lagro Quezon City', 'DENTURES', '2022-05-19', '5:00 PM', 'Di na po ako makahinga', 'KrjtxYMejJObmQ==', 'pending', 1),
(18, 102, 'Angela Jane Rebancos', 9668054608, 'janerebancos03@gmail.com', 'Block 63 Lot 1 Phase 1 Eastwood Residences', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-14', '10:00 AM', 'Some of my teeth have tartar so my agenda for your clinic cleaning. ', '8CyB9PtupU7L4w==', 'approved', 1),
(19, 103, 'Mark Anthony Villadiego', 9515801424, 'markanthonyvilladiego@gmail.com', 'Marikina', 'TOOTH EXTRACTION', '2022-05-28', '2:00 PM', 'Wisdom tooth removal', '9HnEOALh3dzImA==', 'completed', 1),
(20, 104, 'Christian Paul Fototana', 9669234928, 'cfototana@gmail.com', 'Kamuning QC', '', '2022-05-20', '9:00 AM', 'Pampapogi daw po pag may brace', 'eeFyCCB4H8Qchg==', 'declined', 1),
(21, 105, 'Jam Santarinala', 639053903805, 'jam.santarinala@gmail.com', '31 l sianghio kamuning', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-13', '1:00 PM', 'Cleaning', 'eYT1hsgrRmlUPw==', 'pending', 1),
(22, 106, 'Adrian Patrick Perlado', 9278346502, 'adrianperlado06@gmail.com', 'Makati', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-12', '8:00 AM', 'My teeth is my teeth', 'GERj6twuLIkSgg==', 'approved', 1),
(23, 106, 'Adrian Patrick Perlado', 9278346502, 'adrianperlado06@gmail.com', 'Makati', 'ORAL PROPHYLAXYS OR CLEANING', '2003-10-30', '4:00 PM', 'dsadsadasdasas', 'DbVLJtsrCMcswQ==', 'approved', 1),
(24, 106, 'Adrian Patrick Perlado', 9278346502, 'adrianperlado06@gmail.com', 'dsadsadasdsa', '', '2022-05-12', '8:00 AM', 'sadsadsadsadsadasdsa', 'WHyN25/RHs9j/Q==', 'pending', 1),
(25, 86, 'vincent halili', 9669435194, 'vincenthalili014@gmail.com', 'santolan', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-17', '3:00 PM', 'cleaning ', 'v2nagpJl1kQ4Zg==', 'approved', 1),
(26, 86, ' halili', 9669435194, 'vincenthalili014@gmail.com', 'santolan', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-23', '8:00 AM', 'cleaning', 'NS6ruNnUnlmkFw==', 'approved', 1),
(29, 88, ' Marquez', 9156689652, 'qjanmarquez@tip.edu.ph', '998 Quirino Highway', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-25', '10:00 AM', 'sakit ngipin ko po', '+x3Gh2UtII+cZQ==', 'approved', 1),
(31, 86, 'vincent halili', 9669435194, 'vincenthalili014@gmail.com', 'santolan', 'ORAL PROPHYLAXYS OR CLEANING', '2022-05-31', '8:00 AM', 'cleaning', '/9eI4cp/v+tXJg==', 'declined', 1),
(32, 86, 'vincent halili', 9669435194, 'vincenthalili014@gmail.com', 'santolan', 'RESTORATION OR PASTA', '2022-05-30', '1:00 PM', 'pasta', 'qS2qKmm9GdtDxQ==', 'declined', 1),
(33, 87, 'Christian Paul Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street Brgy. Kamuning, QC', 'TOOTH EXTRACTION', '2022-06-13', '8:00 AM', 'Tooth extraction', 'ZuThJp0cNjqXwg==', 'approved', 1),
(34, 87, 'Christian Paul Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street brgy. kamuning, qc', 'TOOTH EXTRACTION', '2022-06-17', '1:00 PM', 'eqweqweqweqw', 'BnbkkryA8vAHKg==', 'declined', 1),
(35, 87, 'Christian Paul  Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street brgy. kamuning, qc', 'DENTURES', '2022-06-16', '1:00 PM', 'Braces', 'W4TnArrAoxBolg==', 'approved', 1),
(36, 87, 'Christian Paul  Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street brgy. kamuning, qc', 'ORAL PROPHYLAXYS OR CLEANING', '2022-06-07', '1:00 PM', 'cleaning', 'CiXQQ2myNc6tJg==', 'declined', 1),
(37, 87, 'Christian Paul  Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street brgy. kamuning, qc', 'RESTORATION OR PASTA', '2022-06-10', '8:00 AM', 'pasta', 'WoD8KqrUPE430w==', 'approved', 1),
(39, 87, 'Christian Paul  Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street brgy. kamuning, qc', 'DENTURES', '2022-06-20', '10:00 AM', 'asd', 'AVn4Sk46/pnQvA==', 'approved', 1),
(40, 87, 'Christian Paul  Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street brgy. kamuning, qc', 'RESTORATION OR PASTA', '2022-06-06', '2:00 PM', 'asd', '820m6nmryrCp8Q==', 'approved', 1),
(41, 87, 'Christian Paul Eugenio Fototana', 9669234928, 'qcpefototana@tip.edu.ph', '3-A, 15A K-5TH Street brgy. kamuning, qc', 'DENTURES', '2022-06-30', '1:00 PM', 'bracess...', 'b1leBfgvKatg5w==', 'approved', 1);

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
  `phone` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `registered_accounts`
--

INSERT INTO `registered_accounts` (`id`, `firstname`, `middle`, `lastname`, `email`, `password`, `phone`) VALUES
(64, 'vincent', '', 'halili', 'vincent@gmail.com', '4297f44b13955235245b2497399d7a93', 9123456789),
(65, 'eugene', '', 'halili', 'eugene@gmail.com', '4297f44b13955235245b2497399d7a93', 9669435194),
(66, 'anthon', '', 'marquez', 'anthon@gmail.com', '4297f44b13955235245b2497399d7a93', 12312312312),
(67, 'qwe', '', 'rty', 'qwerty@gmail.com', '4297f44b13955235245b2497399d7a93', 9669435194),
(68, 'vincent', '', 'halili', 'vincent14@gmail.com', '4297f44b13955235245b2497399d7a93', 9669435194),
(69, 'Vince John', '', 'Perez', 'perezvj.social@gmail.com', '356531c7cf37111656f9e782b7c5efa5', 9359148135),
(86, 'vincent', '', 'halili', 'vincenthalili014@gmail.com', '4297f44b13955235245b2497399d7a93', 9669435194),
(87, 'Christian Paul', 'Eugenio', 'Fototana', 'qcpefototana@tip.edu.ph', '4297f44b13955235245b2497399d7a93', 9669234928),
(88, 'John Anthony', '', 'Marquez', 'qjanmarquez@tip.edu.ph', '6eea9b7ef19179a06954edd0f6c05ceb', 9156689652),
(89, 'Josalie ', '', 'Martinez', 'josaliemartinez@gmail.com', '3d76b7ce61d47ab424e205a3a869d316', 9310576675),
(90, 'Lenard', '', 'Esguerra', 'Lenard', '202cb962ac59075b964b07152d234b70', 99555555),
(92, 'Angelo ', '', 'Llaguna', 'angelo.ncdllgn@gmail.com', '92ecf93ee56cca363baa1d79ea9adff3', 9171039933),
(93, 'John Micko', '', 'Rapanot', 'johnmickooo28@gmail.com', '482c811da5d5b4bc6d497ffa98491e38', 9194282431),
(94, 'Jade', '', 'Santarinala', 'jmsantarinala02@gmail.com', '66acf62156170fb1ec11c25c5bd954b0', 9212741630),
(95, 'Casey Mhae', '', 'Bardelosa', 'caseymhaebardelosa10@gmail.com', 'd6e97efa4bba3cc705ba6c98b1ee035a', 9567255236),
(96, 'Kimberly', '', 'Borbe', 'qksborbe@tip.edu.ph', 'f53abe6aa13758f7b3fe9638654a4408', 9208682169),
(97, 'Mark Frederick', '', 'Obrero', 'Markobrero14@gmail.com', '580f0bce7288fcaf1294d9252c80a08d', 9276383818),
(98, 'Jonas', '', 'Baybay', 'jeyels15@gmail.com', 'c3102bdd95ea0b1c55bc7ff8d41abaf0', 9175360323),
(99, 'Joanne', '', 'Santarinala', 'joanneabad@gmail.com', '44ae1c14450121258a8c5f6692539ccc', 9953048815),
(100, 'Shannie', '', 'Abarca', 'qsdcabarca@tip.edu.ph', '3c4d6474e9d1f281a30c52315c118e2c', 9106782471),
(101, 'Roi', '', 'Guiao', 'realrobert66@gmail.com', '244750f5e8f26daccf6e3e1228209c53', 9993541089),
(102, 'Angela Jane', '', 'Rebancos', 'janerebancos03@gmail.com', 'bcc67d8524948bbd873e4df12c89b182', 9452856136),
(103, 'Mark Anthony', '', 'Villadiego', 'Markanthonyvilladiego@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 9515801424),
(104, 'Christian Paul', '', 'Fototana', 'cfototana@gmail.com', '25f9e794323b453885f5181f1b624d0b', 9669234928),
(105, 'Jam', '', 'Santarinala', 'jam.santarinala@gmail.com', '44eb52943cdd9555c0bb435cd3ae8ada', 9053903805),
(106, 'Adrian Patrick', '', 'Perlado', 'adrianperlado06@gmail.com', 'c20ad4d76fe97759aa27a0c99bff6710', 9278346502);

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
