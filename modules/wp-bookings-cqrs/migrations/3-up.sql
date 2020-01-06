-- Create resources table
CREATE TABLE `${cqrs/resources/table}`
(
	`id` bigint AUTO_INCREMENT PRIMARY KEY,
	`type` varchar(100) NOT NULL,
	`name` varchar(255) NOT NULL,
	`data` longtext,
  `timezone` VARCHAR(100) DEFAULT "UTC"
);
-- Create booking-resources relationship table
CREATE TABLE `${cqrs/booking_resources/table}`
(
  `id` bigint AUTO_INCREMENT PRIMARY KEY,
  `booking_id` int NOT NULL,
  `resource_id` int NOT NULL
);

-- Rename session lengths to session types
UPDATE `${wpdb_prefix}postmeta`
SET `meta_key` = "eddbk_session_types"
WHERE `meta_key` = "eddbk_session_lengths";

-- Create schedule resources for every service
INSERT INTO `${cqrs/resources/table}` (`id`, `type`, `name`)
SELECT `ID`, "schedule", CONCAT("Schedule for \"", post_title, "\"")
FROM `${wpdb_prefix}posts` AS `post`
LEFT JOIN `${wpdb_prefix}postmeta` AS `meta` ON
  `meta`.`post_id` = `post`.`ID` AND
  `meta`.`meta_key` = "eddbk_bookings_enabled" AND
  `meta`.`meta_value` = "1"
WHERE `post`.`post_type` = "download";
-- Set schedule timezones equal to service timezones
UPDATE `${cqrs/resources/table}` AS `resource`
RIGHT JOIN `${wpdb_prefix}postmeta` AS `meta` ON
  `meta`.`post_id` = `resource`.`id` AND
  `meta`.`meta_key` = "eddbk_timezone"
SET `resource`.`timezone` = `meta`.`meta_value`;

-- Populate booking resources table with existing booking.resource_id data
INSERT INTO `${cqrs/booking_resources/table}` (`booking_id`, `resource_id`)
SELECT `id` as `booking_id`, `resource_id`
FROM `${cqrs/bookings/table}`;
-- Remove `resource_id` column from bookings table
ALTER TABLE `${cqrs/bookings/table}` DROP COLUMN `resource_id`;

-- Remove `rule_id` column from sessions table
ALTER TABLE `${cqrs/sessions/table}` DROP COLUMN `rule_id`;
-- Remove `resource_id` column from sessions table
ALTER TABLE `${cqrs/sessions/table}` DROP COLUMN `resource_id`;
-- Add the new `resources` column to the sessions table
ALTER TABLE `${cqrs/sessions/table}` ADD `resource_ids` VARCHAR(100) NOT NULL;
-- Set values for the new resources column in the sessions table
UPDATE `${cqrs/sessions/table}` SET `resource_ids` = `service_id`;

-- Change `service_id` in availability rules to `resource_id`
ALTER TABLE `${cqrs/session_rules/table}` CHANGE `service_id` `resource_id` bigint NOT NULL;
