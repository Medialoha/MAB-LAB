# change app_version_code type from float to integer
ALTER TABLE `%PREFIX%reports` CHANGE `app_version_code` `app_version_code` INT( 11 ) NULL DEFAULT NULL;

# create increments table needed for the reports evolution chart
DROP TABLE IF EXISTS `%PREFIX%increments`;
CREATE TABLE `%PREFIX%increments` (
  `inc` int(11) NOT NULL,
  UNIQUE KEY `inc_UNIQUE` (`inc`)
  
) DEFAULT CHARSET=utf8;

# drop triggers, procedures and functions
DROP TRIGGER IF EXISTS `trigger_1_delete_issue_reports_on_delete_issue`;
DROP TRIGGER IF EXISTS `trigger_2_update_issue_reports_state`;
DROP TRIGGER IF EXISTS `trigger_3_update_issue_state_on_new_report_viewed`;
DROP TRIGGER IF EXISTS `trigger_4_update_issue_state_on_new_report_insert`;

DROP PROCEDURE IF EXISTS `LogD`;
DROP FUNCTION IF EXISTS `FctDeleteReport`;

DROP TABLE IF EXISTS `%PREFIX%issues`;
CREATE TABLE IF NOT EXISTS `%PREFIX%issues` (
  `issue_id` INT NOT NULL AUTO_INCREMENT,
  `issue_key` VARCHAR(50) NOT NULL,
  `issue_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `issue_cause` VARCHAR(255) NOT NULL,
  `issue_state` INT(1) NOT NULL DEFAULT 1, 
  `issue_priority` INT(1) NOT NULL DEFAULT 1,
  
  PRIMARY KEY (`issue_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%PREFIX%logs` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `log_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_severity` CHAR(1) NOT NULL DEFAULT 'D',
  `log_tag` VARCHAR(50) NULL,
  `log_message` TEXT NULL,

  PRIMARY KEY (`log_id`)
) DEFAULT CHARSET=utf8;