-- Delete the resources table
DROP TABLE IF EXISTS `${cqrs/resources/table}`;
-- Delete the bookings-resources relationship table
DROP TABLE IF EXISTS `${cqrs/booking_resources/table}`;

UPDATE `${wpdb_prefix}postmeta`
SET `meta_key` = "eddbk_session_lengths"
WHERE `meta_key` = "eddbk_session_types";

-- Re-add the resource_id column to the bookings table
ALTER table `${cqrs/bookings/table}` ADD `resource_id` int NOT NULL;
-- Set all resource IDs in the bookings table to be equal to service IDs
UPDATE `${cqrs/bookings/table}` SET `resource_id` = `service_id`;

-- Remove `resource_id` column from sessions table
ALTER TABLE `${cqrs/sessions/table}` DROP COLUMN `resource_ids`;
-- Re-add the resource_id column to the bookings table
ALTER table `${cqrs/sessions/table}` ADD `resource_id` int NOT NULL;
-- Re-add the rule_id column to the bookings table
ALTER table `${cqrs/sessions/table}` ADD `rule_id` int NOT NULL;
-- Set all resource IDs in the sessions table to be equal to service IDs
UPDATE `${cqrs/sessions/table}` SET `resource_id` = `service_id`;

-- Change `resource_id` in availability rules back to `service_id`
ALTER TABLE `${cqrs/session_rules/table}` CHANGE `resource_id` `service_id` bigint NOT NULL;
