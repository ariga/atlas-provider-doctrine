-- Create "users" table
CREATE TABLE `users` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  `name` varchar NOT NULL
);
-- Create "bugs" table
CREATE TABLE `bugs` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  `description` varchar NOT NULL,
  `created` datetime NOT NULL,
  `status` varchar NOT NULL,
  `engineer_id` integer NULL DEFAULT (NULL),
  `reporter_id` integer NULL DEFAULT (NULL),
  CONSTRAINT `FK_1E197C9E1CFE6F5` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_1E197C9F8D8CDF1` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
-- Create index "IDX_1E197C9F8D8CDF1" to table: "bugs"
CREATE INDEX `IDX_1E197C9F8D8CDF1` ON `bugs` (`engineer_id`);
-- Create index "IDX_1E197C9E1CFE6F5" to table: "bugs"
CREATE INDEX `IDX_1E197C9E1CFE6F5` ON `bugs` (`reporter_id`);
