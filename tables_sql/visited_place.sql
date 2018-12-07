CREATE TABLE `visited_place` (
  `visited_on` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `rating` varchar(20) NOT NULL,
  `price_level` varchar(10) NOT NULL,
  `vicinity` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `visited_place`
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `visited_place`
  ADD CONSTRAINT `visited_place_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);



--`place_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL