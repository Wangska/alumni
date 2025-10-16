-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2025 at 04:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Ensure database exists and is selected for import without manual creation
CREATE DATABASE IF NOT EXISTS `alumni_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `alumni_db`;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alumni_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumnus_bio`
--

CREATE TABLE `alumnus_bio` (
  `id` int(30) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `middlename` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `batch` year(4) NOT NULL,
  `course_id` int(30) NOT NULL,
  `email` varchar(250) NOT NULL,
  `contact` varchar(20) NOT NULL DEFAULT '',
  `address` text NOT NULL DEFAULT '',
  `connected_to` text NOT NULL,
  `avatar` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0= Unverified, 1= Verified',
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumnus_bio`
--

INSERT INTO `alumnus_bio` (`id`, `firstname`, `middlename`, `lastname`, `gender`, `batch`, `course_id`, `email`, `contact`, `address`, `connected_to`, `avatar`, `status`, `date_created`) VALUES
(3, 'Jaymar', 'A.', 'Candol', 'Male', '2020', 5, 'jaymarcandol9@gmail.com', '', '', 'Saint Ce Celias College', '1756486500_ym.jpg', 1, '2025-08-30 00:00:00'),
(4, 'Crystil Mae ', '', 'Padin ', 'Female', '2025', 3, 'crystilmaepadin@gmail.com', '', '', 'Saint Ce Celias College', '1756486620_513022380_1552648982772616_6591895865363501148_n.jpg', 1, '2025-08-30 00:00:00'),
(5, 'Sachie', '', 'Dumangcas', 'Female', '2021', 7, 'sachiedumangcas@gmail.com', '', '', 'Saint Ce Celias College', '1756486740_525396717_1025033549526814_4739684317873942870_n.jpg', 1, '2025-08-30 00:00:00'),
(6, 'Jeziel Mae', '', 'Canada', 'Male', '2023', 1, 'jezielmaecanada@gmail.com', '', '', 'Saint Ce Celias College', '1756486800_522952046_1090451893041010_3793615622569182573_n.jpg', 1, '2025-08-30 00:00:00'),
(7, 'John Rey', '', 'Pangan', 'Male', '2026', 4, 'johnreypangan@gmail.com', '', '', 'Saint Ce Celias College', '1756486860_527452223_1281034280308319_1629961708546955681_n.jpg', 1, '2025-08-30 00:00:00'),
(13, 'zxc', 'zxc', 'zxc', 'Female', '2009', 4, 'zxc@zxc.zxc', '', '', 'zxc', 'avatar_68cc0cf550d4d.png', 1, '2025-09-18 00:00:00'),
(14, 'asd', 'asd', 'asd', 'Male', '2006', 6, 'asd@asd.asd', '', '', 'asd', 'avatar_68cc0e8141339.gif', 0, '2025-09-18 00:00:00'),
(15, 'qwe', 'qwe', 'qwe', 'Female', '2004', 4, 'qwe@qwe.qwe', '', '', 'qwe', 'avatar_68cc0f4657d84.png', 0, '2025-09-18 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `date_posted` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `content`, `date_posted`) VALUES
(10, 'adsadasd', '2025-09-17 11:16:35'),
(11, 'dsad', '2025-09-17 11:23:27');

-- --------------------------------------------------------

--
-- Table structure for table `careers`
--

CREATE TABLE `careers` (
  `id` int(30) NOT NULL,
  `company` varchar(250) NOT NULL,
  `location` text NOT NULL,
  `job_title` text NOT NULL,
  `description` text NOT NULL,
  `user_id` int(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `careers`
--

INSERT INTO `careers` (`id`, `company`, `location`, `job_title`, `description`, `user_id`, `date_created`) VALUES
(4, 'Death ', 'Cantao-an', 'Killer', 'we need special talent of killing&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 1, '2025-09-09 11:58:18'),
(6, 'sas', 'sas', 'sasa', 'ssaa', 1, '2025-09-16 21:36:52'),
(7, 'dasd', 'asdasd', 'ads', 'asd', 1, '2025-09-16 21:40:08'),
(11, 'asd', 'asd', 'asd', 'asd', 1, '2025-09-16 21:40:36'),
(13, 'sa', 'as', 'as', 'as', 1, '2025-09-16 21:53:38'),
(14, 'das', 'asd', 'asd', 'asd', 1, '2025-09-16 21:57:38');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(30) NOT NULL,
  `course` text NOT NULL,
  `about` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course`, `about`) VALUES
(1, 'BS Information Technology', 'Sample'),
(3, 'BS Education', ''),
(4, 'BS Marine', ''),
(5, 'BS Criminology', ''),
(6, 'BS Tourism', ''),
(7, 'BS Hospitality Management', ''),
(10, 'dfgdfgdfg', '');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(30) NOT NULL,
  `title` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `schedule` datetime NOT NULL,
  `banner` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `content`, `schedule`, `banner`, `date_created`) VALUES
(24, 'Sport Fest', 'Time to shineeeeeee', '2025-12-01 19:10:00', '1757124180_SportsFest.jpg', '2025-09-06 10:03:00'),
(25, 'Alumni Awards Night', 'Come Alumnies', '2025-12-03 19:11:00', '1757243520_AlumniAwardsNight.jpg', '2025-09-07 19:12:15'),
(26, 'Alumni Homecoming', 'Homecoming of Alumnies', '2025-10-31 19:12:00', '1757243580_AlumniHomecoming.jpg', '2025-09-07 19:13:04'),
(28, 'dasdADADS', 'asdASASSA', '2025-09-25 10:12:00', '1758075120_localhost_5173_staff_appointment-requests.png', '2025-09-17 10:12:33');

-- --------------------------------------------------------

--
-- Table structure for table `event_commits`
--

CREATE TABLE `event_commits` (
  `id` int(30) NOT NULL,
  `event_id` int(30) NOT NULL,
  `user_id` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_commits`
--

INSERT INTO `event_commits` (`id`, `event_id`, `user_id`) VALUES
(1, 1, 3),
(2, 11, 4),
(3, 26, 4);

-- --------------------------------------------------------

--
-- Table structure for table `forum_comments`
--

CREATE TABLE `forum_comments` (
  `id` int(30) NOT NULL,
  `topic_id` int(30) NOT NULL,
  `comment` text NOT NULL,
  `user_id` int(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_comments`
--

INSERT INTO `forum_comments` (`id`, `topic_id`, `comment`, `user_id`, `date_created`) VALUES
(6, 1, 'jhasjdhjsahdjkhjsahdkajsd', 4, '2025-09-06 09:30:56'),
(7, 7, 'aaaaaaaasssasddddzzzzzz', 4, '2025-09-16 01:00:06'),
(13, 7, 'asdasdasdasdasdas', 4, '2025-09-16 01:18:56'),
(15, 7, 'asdasdasdasd', 4, '2025-09-16 01:19:24'),
(16, 8, 'asdasdasasdasd', 1, '2025-09-16 01:28:11');

-- --------------------------------------------------------

--
-- Table structure for table `forum_topics`
--

CREATE TABLE `forum_topics` (
  `id` int(30) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `user_id` int(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_topics`
--

INSERT INTO `forum_topics` (`id`, `title`, `description`, `user_id`, `date_created`) VALUES
(7, 'hsajdgjsajdg', 'whdjhashdjasdasd', 1, '2025-09-09 11:59:32'),
(8, 'asdasd', 'asdasd', 1, '2025-09-16 01:27:50'),
(9, 'asdasd', 'asdasdas', 1, '2025-09-16 01:30:13'),
(10, 'asdasd', 'asdasd', 1, '2025-09-16 01:33:17'),
(16, 'sad', 'sadads', 1, '2025-09-16 02:11:01'),
(17, 'a', 'asd', 1, '2025-09-16 02:13:50'),
(21, 'adsfdsf', 'asdsdfsdfsd', 1, '2025-09-17 10:43:58'),
(22, 'qwwrwer', 'werwer', 1, '2025-09-17 12:32:41'),
(23, 'yi', 'dasd', 1, '2025-09-17 12:33:00'),
(24, 'lololollolol', 'olololololol', 1, '2025-09-17 12:33:13'),
(25, 'gdfg', 'dfgdfgdfggdf', 1, '2025-09-17 12:33:41'),
(26, 'gdfgdfgdfg', 'dfgdfgdfggdfdfgdfg', 1, '2025-09-17 12:33:46'),
(27, 'asd', 'asdasd', 13, '2025-09-17 12:34:59'),
(28, 'ads', 'asd', 14, '2025-09-18 21:58:16');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(30) NOT NULL,
  `about` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `about`, `created`) VALUES
(8, '“Class of 2010 Reunion – A night of laughter, memories, and reconnections held on July 19, 2025.”', '2025-08-30 00:45:14'),
(9, '“Guest lecture by Mr. Raj Patel (Batch of 2005) on ‘Leadership in the Digital Era’.”', '2025-08-30 00:46:15'),
(10, '“Outstanding Alumni Award presented to Ms. Aisha Khan (Batch of 1998) for excellence in social entrepreneurship.”', '2025-08-30 00:47:13'),
(11, '“Alumni Homecoming 2025 – Welcoming back our proud graduates to campus.”', '2025-08-30 00:47:56'),
(12, '“Alumni Cultural Evening – A vibrant night of talent, nostalgia, and unity.”\r\n', '2025-08-30 00:49:00'),
(13, '“Global Meetup – US-based alumni of Class of 2000 enjoying a get-together in New York.”', '2025-08-30 00:50:13');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `cover_img` text NOT NULL,
  `about_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `email`, `contact`, `cover_img`, `about_content`) VALUES
(1, 'Alumni Management System', 'jaymarcandol9@gmail.com', '09674371023', '1756484340_Alumni-Organization-Header.jpg', '&lt;p data-start=&quot;114&quot; data-end=&quot;374&quot;&gt;At Alumni Management System, we are dedicated to fostering lifelong connections between institutions and their alumni. Our Alumni Management System is a comprehensive platform designed to streamline alumni engagement, communication, and data management.&lt;/p&gt;&lt;p data-start=&quot;376&quot; data-end=&quot;748&quot;&gt;We empower educational institutions, universities, and organizations to build vibrant alumni communities through innovative tools for event management, networking, fundraising, and career support. Whether it&amp;#x2019;s staying connected with past graduates, tracking their achievements, or encouraging meaningful contributions, our system makes it simple, efficient, and impactful.&lt;/p&gt;&lt;p style=&quot;text-align: center; background: transparent; position: relative;&quot;&gt;&lt;/p&gt;&lt;p data-start=&quot;750&quot; data-end=&quot;915&quot;&gt;Driven by a passion for connection and community, our goal is to bridge the gap between the past and the future&mdash;helping alumni stay involved and institutions thrive.&lt;/p&gt;&lt;p style=&quot;text-align: center; background: transparent; position: relative;&quot;&gt;&lt;br&gt;&lt;/p&gt;&lt;p style=&quot;text-align: center; background: transparent; position: relative;&quot;&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;/p&gt;');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` text NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 3 COMMENT '1=Admin,2=Alumni officer, 3= alumnus',
  `auto_generated_pass` text NOT NULL,
  `alumnus_id` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `type`, `auto_generated_pass`, `alumnus_id`) VALUES
(1, 'Admin', 'admin', '0192023a7bbd73250516f069df18b500', 1, '', 0),
(4, 'Jaymar Candol', 'jaymarcandol9@gmail.com', '202cb962ac59075b964b07152d234b70', 3, '', 3),
(5, 'Crystil Mae  Padin ', 'crystilmaepadin@gmail.com', '202cb962ac59075b964b07152d234b70', 3, '', 4),
(6, 'Sachie Dumangcas', 'sachiedumangcas@gmail.com', '202cb962ac59075b964b07152d234b70', 3, '', 5),
(7, 'Jeziel Mae Canada', 'jezielmaecanada@gmail.com', '202cb962ac59075b964b07152d234b70', 3, '', 6),
(8, 'John Rey Pangan', 'johnreypangan@gmail.com', '202cb962ac59075b964b07152d234b70', 3, '', 7),
(14, 'zxc zxc zxc', 'zxc', '5fa72358f0b4fb4f2c5d7de8c9a41846', 3, '', 13),
(15, 'asd asd asd', 'asd', '7815696ecbf1c96e6894b779456d330e', 3, '', 14),
(16, 'qwe qwe qwe', 'qwe', '76d80224611fc919a5d54f0ff9fba446', 3, '', 15);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumnus_bio`
--
ALTER TABLE `alumnus_bio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `careers`
--
ALTER TABLE `careers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_commits`
--
ALTER TABLE `event_commits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alumnus_bio`
--
ALTER TABLE `alumnus_bio`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `careers`
--
ALTER TABLE `careers`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `event_commits`
--
ALTER TABLE `event_commits`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
