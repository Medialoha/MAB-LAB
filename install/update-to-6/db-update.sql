# update issues table structure
ALTER TABLE `%PREFIX%issues` ADD `issue_app_id` INT NOT NULL;
ALTER TABLE `%PREFIX%issues` ADD `issue_milestone_id` INT NOT NULL;
ALTER TABLE `%PREFIX%issues` ADD `issue_comment` TEXT NULL;

# update reports table structure
ALTER TABLE `%PREFIX%reports` ADD `report_app_id` INT NOT NULL;

# create applications table
CREATE TABLE IF NOT EXISTS `%PREFIX%applications` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `app_name` varchar(100) NOT NULL,
  `app_package` varchar(255) NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

# create google play sales and earnings tables
CREATE TABLE IF NOT EXISTS `%PREFIX%googleplay_sales` (
  `sale_order_number` bigint(20) NOT NULL,
  `sale_order_charged_date` char(10) NOT NULL,
  `sale_charged_timestamp` int(11) NOT NULL,
  `sale_financial_status` varchar(25) NOT NULL,
  `sale_device_model` varchar(25) NOT NULL,
  `sale_product_title` varchar(255) NOT NULL,
  `sale_product_id` varchar(255) NOT NULL,
  `sale_product_type` varchar(50) NOT NULL,
  `sale_sku_id` varchar(50) NOT NULL,
  `sale_currency_code` varchar(10) NOT NULL,
  `sale_item_price` float NOT NULL,
  `sale_taxes_collected` float NOT NULL,
  `sale_charged_amount` float NOT NULL,
  `sale_buyer_city` varchar(255) NOT NULL,
  `sale_buyer_state` varchar(25) NOT NULL,
  `sale_buyer_postal_code` varchar(25) NOT NULL,
  `sale_buyer_country` varchar(10) NOT NULL,
  `sale_app_id` int(11) NOT NULL,
  PRIMARY KEY (`sale_order_number`),
  KEY `sale_charged_timestamp` (`sale_charged_timestamp`),
  KEY `sale_app_id` (`sale_app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# create miletones table
CREATE TABLE IF NOT EXISTS `%PREFIX%milestones` (
  `mile_id` int(11) NOT NULL AUTO_INCREMENT,
  `mile_app_id` int(11) NOT NULL,
  `mile_name` varchar(255) NOT NULL,
  `mile_description` text,
  `mile_duedate` double DEFAULT NULL,
  PRIMARY KEY (`mile_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

# update issues and reports closed state value
UPDATE `%PREFIX%reports` SET report_state=4 WHERE report_state=3;
UPDATE `%PREFIX%issues` SET issue_state=4 WHERE issue_state=3;