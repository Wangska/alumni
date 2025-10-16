-- Add missing 'address' column to alumnus_bio table
-- Run this SQL on your Coolify database

-- Check if address column exists, if not add it
ALTER TABLE `alumnus_bio` 
ADD COLUMN IF NOT EXISTS `address` TEXT NOT NULL DEFAULT '' AFTER `contact`;

-- Verify the column was added
SHOW COLUMNS FROM `alumnus_bio`;

