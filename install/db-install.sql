DROP TABLE IF EXISTS `%PREFIX%reports`, `%PREFIX%users`, `%PREFIX%issues`, `%PREFIX%logs`, `%PREFIX%increments`;

CREATE TABLE `%PREFIX%users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `user_password` char(32) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  
  PRIMARY KEY (`user_id`)
) ENGINE MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `%PREFIX%users` (user_name, user_password, user_email) VALUES ('admin', MD5('password'), '');

CREATE TABLE `%PREFIX%reports` (
  `report_id` INT NOT NULL AUTO_INCREMENT,
  `report_key` VARCHAR( 50 ) NOT NULL,
  
  `app_version_code` int(11) DEFAULT NULL,
  `app_version_name` varchar(50) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `phone_model` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `product` varchar(50) DEFAULT NULL,
  `android_version` varchar(10) DEFAULT NULL,
  `build` text,
  `total_mem_size` varchar(25) DEFAULT NULL,
  `available_mem_size` varchar(25) DEFAULT NULL,
  `custom_data` varchar(255) DEFAULT NULL,
  `stack_trace` text,
  `initial_configuration` text,
  `crash_configuration` text,
  `display` text,
  `user_comment` TEXT,
  `user_app_start_date` timestamp NULL DEFAULT NULL,
  `user_crash_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `dumpsys_meminfo` text,
  `dropbox` text,
  `logcat` text,
  `eventslog` text,
  `radiolog` text,
  `is_silent` varchar(10) DEFAULT NULL,
  `device_id` varchar(50) DEFAULT NULL,
  `installation_id` varchar(50) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `device_features` text,
  `environment` text,
  `shared_preferences` text,
  `settings_system` text,
  `settings_secure` text,
  `settings_global` text,
  
  `report_state` INT(1) NOT NULL DEFAULT 1,
  `report_issue` INT(11) NOT NULL DEFAULT 0,
  `report_app_id` INT NOT NULL,
  
  PRIMARY KEY (`report_id`),
  UNIQUE (`report_key`)
) ENGINE MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%PREFIX%issues` (
  `issue_id` INT NOT NULL AUTO_INCREMENT,
  `issue_key` VARCHAR(50) NOT NULL,
  `issue_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `issue_cause` VARCHAR(255) NOT NULL,
  `issue_state` INT(1) NOT NULL DEFAULT 1, 
  `issue_priority` INT(1) NOT NULL DEFAULT 1,
  `issue_app_id` INT NOT NULL,
  `issue_milestone_id` INT NOT NULL,
  `issue_comment` TEXT NULL,
  
  PRIMARY KEY (`issue_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `%PREFIX%logs` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `log_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_severity` CHAR(1) NOT NULL DEFAULT 'D',
  `log_tag` VARCHAR(50) NULL,
  `log_message` TEXT NULL,

  PRIMARY KEY (`log_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `%PREFIX%increments` (
  `inc` int(11) NOT NULL,
  UNIQUE KEY `inc_UNIQUE` (`inc`)
  
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%PREFIX%applications` (
  `app_id` int(11) NOT NULL AUTO_INCREMENT,
  `app_name` varchar(100) NOT NULL,
  `app_package` varchar(255) NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

# create google play sales and earnings tables
CREATE TABLE IF NOT EXISTS `%PREFIX%googleplay_sales` (
  `sale_order_number` bigint(20) NOT NULL,
  `sale_order_charged_date` datetime NOT NULL,
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


# create indexes
CREATE INDEX `idx_reports_report_issue` ON `%PREFIX%reports` (`report_issue`);