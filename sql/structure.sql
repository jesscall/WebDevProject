CREATE TABLE `authenticationToken` (
  `userId` int(11) NOT NULL,
  `authenticationToken` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `match` (
  `userId` int(11) NOT NULL,
  `placeId` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` float DEFAULT NULL,
  `priceLevel` tinyint(3) UNSIGNED DEFAULT NULL,
  `vicinity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `matchedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user` (
  `userId` int(11) NOT NULL,
  `emailAddress` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashedPassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `displayName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `authenticationToken`
  ADD PRIMARY KEY (`authenticationToken`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `match`
  ADD PRIMARY KEY (`userId`,`placeId`),
  ADD KEY `user_id` (`userId`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `email_address` (`emailAddress`);

ALTER TABLE `user`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `authenticationToken`
  ADD CONSTRAINT `authenticationToken_belongsToUser` FOREIGN KEY (`userId`) REFERENCES `user` (`userId`);

ALTER TABLE `match`
  ADD CONSTRAINT `match_belongsToUser` FOREIGN KEY (`userId`) REFERENCES `user` (`userId`);
