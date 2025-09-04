-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 10:13 AM
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
-- Database: `stayfind3nf_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_statuses`
--

CREATE TABLE `booking_statuses` (
  `id` int(11) NOT NULL,
  `status_name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_statuses`
--

INSERT INTO `booking_statuses` (`id`, `status_name`) VALUES
(3, 'approved'),
(2, 'awaiting_approval'),
(5, 'cancelled'),
(6, 'completed'),
(1, 'pending'),
(4, 'rejected');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(6, 'admin'),
(1, 'guest'),
(4, 'manager'),
(3, 'owner'),
(2, 'user'),
(5, 'worker');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `location_link` varchar(512) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `owner_id`, `title`, `description`, `price_per_night`, `location`, `location_link`, `capacity`, `image_path`, `created_at`) VALUES
(1, 13, 'sample1', 'sample 1\r\nhttps://www.google.com/', 2500.00, 'Sample 2', 'https://earth.google.com/web/search/PHINMA+-+COC,+Cagayan+De+Oro+City,+Misamis+Oriental/@8.4817536,124.6359652,8.19604416a,835.04414947d,35y,0h,0t,0r/data=CqIBGnQSbgolMHgzMmZmZjMyNjc4ZGI0YTRkOjB4M2ZjNmYxMGY0ZDA5NjZlNRnldWloqPYgQCE52WGnsyhfQCozUEhJTk1BIC0gQ09DLCBDYWdheWFuIERlIE9ybyBDaXR5LCBNaXNhbWlzIE9yaWVudGFsGAIgASImCiQJKyuvLhYQNUARKSuvLhYQNcAZwbuvasp_SUAhYKWREwD9ScBCAggBOgMKATBCAggASg0I____________ARAA', 5, 'assets/uploads/b9287ed5d7a7a7f3.jpg', '2025-08-15 07:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `room_amenities`
--

CREATE TABLE `room_amenities` (
  `room_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_images`
--

CREATE TABLE `room_images` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `password_hash`, `name`, `email`, `phone`, `role_id`, `created_at`) VALUES
(10, NULL, NULL, '$2y$10$CIFWYFPEz/kNwMfYmsalr.BZioDgHB3qHpWiqq5sweeFfO/GTvqWC', 'sample1', 'sample1@gmail.com', '11111111111', 2, '2025-08-15 07:20:45'),
(11, NULL, NULL, '$2y$10$FbV5SwUyJnmGimevBkqaAe8.72xMQc3A3ShDK0fnudgJi5qmHrOLW', 'sample1', 'samole12@gmail.com', '123213123', 2, '2025-08-15 07:24:41'),
(12, NULL, '123123123', '$2y$10$RqSaquZvVlsBuBOzY3YU1e3gS6X4Co/AbtC.pvfL.kWHbHOOaDmy.', 'venice1', 'venice@gmail.com', '09762495080', 2, '2025-08-15 07:26:06'),
(13, NULL, NULL, '$2y$10$MwX4TGOcoEdeI3Yvy7iAZuzJVSn14O3cmqCH2VpHJoAT9cflpq4b.', 'Jhomariel Baldado', 'jhomarielbaldado6@gmail.com', '09762495080', 3, '2025-08-15 07:33:12');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_users_default_role` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
  IF NEW.role_id IS NULL THEN
    SET NEW.role_id = (SELECT id FROM roles WHERE role_name='user' LIMIT 1);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_rooms_with_rating`
-- (See below for the actual view)
--
CREATE TABLE `view_rooms_with_rating` (
`id` int(11)
,`title` varchar(200)
,`location` varchar(255)
,`price_per_night` decimal(10,2)
,`capacity` int(11)
,`image_path` varchar(255)
,`avg_rating` decimal(6,2)
,`reviews_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `view_rooms_with_rating`
--
DROP TABLE IF EXISTS `view_rooms_with_rating`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_rooms_with_rating`  AS SELECT `r`.`id` AS `id`, `r`.`title` AS `title`, `r`.`location` AS `location`, `r`.`price_per_night` AS `price_per_night`, `r`.`capacity` AS `capacity`, `r`.`image_path` AS `image_path`, round(avg(`rv`.`rating`),2) AS `avg_rating`, count(`rv`.`id`) AS `reviews_count` FROM (`rooms` `r` left join `reviews` `rv` on(`rv`.`room_id` = `r`.`id`)) GROUP BY `r`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_room` (`room_id`),
  ADD KEY `idx_bookings_user` (`user_id`),
  ADD KEY `idx_bookings_status` (`status_id`);

--
-- Indexes for table `booking_statuses`
--
ALTER TABLE `booking_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receipts_booking` (`booking_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reviews_room` (`room_id`),
  ADD KEY `idx_reviews_user` (`user_id`),
  ADD KEY `fk_reviews_booking` (`booking_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rooms_owner` (`owner_id`),
  ADD KEY `idx_rooms_location` (`location`),
  ADD KEY `idx_rooms_price` (`price_per_night`);

--
-- Indexes for table `room_amenities`
--
ALTER TABLE `room_amenities`
  ADD PRIMARY KEY (`room_id`,`amenity_id`),
  ADD KEY `fk_ra_amenity` (`amenity_id`);

--
-- Indexes for table `room_images`
--
ALTER TABLE `room_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_images_room` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_statuses`
--
ALTER TABLE `booking_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `room_images`
--
ALTER TABLE `room_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_status` FOREIGN KEY (`status_id`) REFERENCES `booking_statuses` (`id`),
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `fk_receipts_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reviews_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_rooms_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `room_amenities`
--
ALTER TABLE `room_amenities`
  ADD CONSTRAINT `fk_ra_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ra_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_images`
--
ALTER TABLE `room_images`
  ADD CONSTRAINT `fk_room_images_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
