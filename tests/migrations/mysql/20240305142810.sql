-- Create "users" table
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) CHARSET utf8mb3 COLLATE utf8mb3_unicode_ci;
-- Create "bugs" table
CREATE TABLE `bugs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `engineer_id` int NULL,
  `reporter_id` int NULL,
  `description` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `IDX_1E197C9E1CFE6F5` (`reporter_id`),
  INDEX `IDX_1E197C9F8D8CDF1` (`engineer_id`),
  CONSTRAINT `FK_1E197C9E1CFE6F5` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_1E197C9F8D8CDF1` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
) CHARSET utf8mb3 COLLATE utf8mb3_unicode_ci;
