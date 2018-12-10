-- Import this table after importing `user`

CREATE TABLE `match` (
  `place_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `matched_at` datetime NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` float NOT NULL,
  `price_level` int(11) NOT NULL,
  `vicinity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `match`
  ADD PRIMARY KEY (`place_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `match`
  ADD CONSTRAINT `match_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
