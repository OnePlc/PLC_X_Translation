ALTER TABLE `translation` ADD `translation` VARCHAR(255) NOT NULL DEFAULT '' AFTER `label`,
ADD `language_idfs` INT(11) NOT NULL DEFAULT '0' AFTER `Translation_ID`;