-- Import after `user`

CREATE TABLE `authenticationToken` (
  `user_id` int(11) NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `authenticationToken`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `token` (`token`);

ALTER TABLE `authenticationToken`
  ADD CONSTRAINT `authenticationToken_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
