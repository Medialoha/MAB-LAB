DROP TABLE IF EXISTS `mabl_reports`,`mabl_users`;

CREATE TABLE `mabl_users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `user_password` char(32) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  
  PRIMARY KEY (`USER_ID`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `mabl_reports` (
  `report_id` varchar(50) NOT NULL,
  `app_version_code` float DEFAULT NULL,
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
  
  `report_state` int DEFAULT 1,
  `report_tag` char(50) NULL DEFAULT NULL,
  
  PRIMARY KEY (`report_id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `mabl_users` (`user_name`, `user_password`, `user_email`) VALUES ('admin', MD5('password'), '');