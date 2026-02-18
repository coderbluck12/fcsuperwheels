-- Add status column to car_request table
ALTER TABLE `car_request` ADD COLUMN `status` VARCHAR(20) DEFAULT 'pending' AFTER `others`;

-- Update existing records to have default status
UPDATE `car_request` SET `status` = 'pending' WHERE `status` IS NULL;
