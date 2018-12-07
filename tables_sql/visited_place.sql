-- This table must be imported after user.sql

CREATE TABLE `visited_place` (
  `visited_on` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `place_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `visited_place`
  ADD PRIMARY KEY (`user_id`,`place_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `visited_place`
  ADD CONSTRAINT `visited_place_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);