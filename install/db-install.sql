DROP TABLE IF EXISTS `mabl_reports`, `mabl_users`, `mabl_issues`, `mabl_logs`, `mabl_increments`;

CREATE TABLE `mabl_users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `user_password` char(32) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  
  PRIMARY KEY (`user_id`)
) ENGINE MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `mabl_users` (user_name, user_password, user_email) VALUES ('admin', MD5('password'), '');

CREATE TABLE `mabl_reports` (
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
  
  PRIMARY KEY (`report_id`),
  UNIQUE (`report_key`)
) ENGINE MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `mabl_issues` (
  `issue_id` INT NOT NULL AUTO_INCREMENT,
  `issue_key` VARCHAR(50) NOT NULL,
  `issue_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `issue_cause` VARCHAR(255) NOT NULL,
  `issue_state` INT(1) NOT NULL DEFAULT 1, 
  `issue_priority` INT(1) NOT NULL DEFAULT 1,
  
  PRIMARY KEY (`issue_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `mabl_logs` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `log_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_severity` CHAR(1) NOT NULL DEFAULT 'D',
  `log_tag` VARCHAR(50) NULL,
  `log_message` TEXT NULL,

  PRIMARY KEY (`log_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `mabl_increments` (
  `inc` int(11) NOT NULL,
  UNIQUE KEY `inc_UNIQUE` (`inc`)
  
) DEFAULT CHARSET=utf8;

#### PROCEDURES

#DROP PROCEDURE IF EXISTS `LogD`;
#DROP FUNCTION IF EXISTS `FctDeleteReport`;

# create procedure to log message
#CREATE PROCEDURE LogD (IN tag VARCHAR(50), IN message TEXT)
#  INSERT INTO %PREFIX%logs (log_tag, log_message) VALUES (tag, message);
  
# create procedure to delete report
#CREATE FUNCTION `FctDeleteReport` (reportId INTEGER)
#RETURNS INTEGER
#BEGIN
#	DECLARE issueId INTEGER DEFAULT 0;
#	DECLARE res INTEGER DEFAULT 0;

#	SET @issueId = (SELECT %PREFIX%issue FROM %PREFIX%reports WHERE report_id=reportId);

#	DELETE FROM %PREFIX%reports WHERE report_id=reportId;
#	SELECT ROW_COUNT() INTO @res;

#	CALL LogD("FCT_DEL_REPORT", CONCAT("Delete report #", reportId, " result : ", @res));

#	IF (@res=1) THEN
#		CALL LogD("FCT_DEL_REPORT", CONCAT("Report deleted with success, check if issue #", @issueId, " must be deleted..."));

#		IF ((SELECT COUNT(*) FROM %PREFIX%reports WHERE report_issue=@issueId)=0) THEN
#			CALL LogD("FCT_DEL_REPORT", "No more reports for this issue then delete it now");
#			DELETE FROM %PREFIX%issues WHERE issue_id=@issueId;

#			SELECT ROW_COUNT()+@res INTO @res;
#		END IF;
#	END IF;

#	RETURN @res;
#END;


#### TRIGGERS 

#DROP TRIGGER IF EXISTS `trigger_1_delete_issue_reports_on_delete_issue`;
#DROP TRIGGER IF EXISTS `trigger_2_update_issue_reports_state`;
#DROP TRIGGER IF EXISTS `trigger_3_update_issue_state_on_new_report_viewed`;
#DROP TRIGGER IF EXISTS `trigger_4_update_issue_state_on_new_report_insert`;

# delete issue reports on issue delete
#CREATE TRIGGER `trigger_1_delete_issue_reports_on_delete_issue`
#BEFORE DELETE ON `%PREFIX%issues` 
#FOR EACH ROW
#BEGIN
#  CALL LogD("TRIGGER #1", CONCAT("Before deleting issue #", OLD.issue_id, ", delete issue reports"));
#  DELETE FROM %PREFIX%reports WHERE report_issue=OLD.issue_id;
#END;
  
# /!\ Use a function delete_report instead
#CREATE TRIGGER `trigger_2_delete_issue_on_delete_report`
#AFTER DELETE ON `%PREFIX%reports` 
#FOR EACH ROW
#BEGIN
#  CALL LogD('TRIGGER #2', CONCAT('Report #', OLD.report_issue, ' deleted, check if report issue should be deleted'));
  
#  IF ((SELECT COUNT(*) FROM %PREFIX%reports WHERE report_issue=OLD.report_issue)=0) THEN
#    CALL LogD('TRIGGER #2', CONCAT('No reports found for issue #', OLD.report_issue, ' then delete issue'));
  
#    DELETE FROM %PREFIX%issues WHERE issue_id=OLD.report_issue;
#  END IF;
#END;

# update issue reports state on issue state update to resolved or archived
# new or viewed state is only set on reports state update to viewed (cf trigger below)
#CREATE TRIGGER `trigger_2_update_issue_reports_state`
#BEFORE UPDATE ON `%PREFIX%issues`
#FOR EACH ROW
#BEGIN
#  IF (OLD.issue_state<>NEW.issue_state AND (NEW.issue_state=0 OR NEW.issue_state=3)) THEN
#    CALL LogD("TRIGGER #2", CONCAT("Update reports state to ", NEW.issue_state, " with report_issue ", NEW.issue_id));
  
#    UPDATE %PREFIX%reports SET report_state=NEW.issue_state WHERE report_issue=NEW.issue_id;
#  END IF;
#END;

# update issue state from new to viewed on report state change from new to viewed
#CREATE TRIGGER `trigger_3_update_issue_state_on_new_report_viewed`
#AFTER UPDATE ON `%PREFIX%reports`
#FOR EACH ROW 
#BEGIN  
#  CALL LogD("TRIGGER #4", CONCAT("Report #", NEW.report_id, " state updated to ", NEW.report_state));

#  IF (NEW.report_state=2) THEN    
#    IF ((SELECT COUNT(*) FROM %PREFIX%reports WHERE report_issue=NEW.report_issue AND report_state=1)=0) THEN
#      CALL LogD("TRIGGER #4", "Issue state must be updated");
#      UPDATE %PREFIX%issues SET issue_state=2 WHERE issue_id=NEW.report_issue;
#    END IF;
#  END IF;
#END;

# update issue state to new when a new report is inserted 
#CREATE TRIGGER `trigger_4_update_issue_state_on_new_report_insert`
#AFTER INSERT ON `%PREFIX%reports`
#FOR EACH ROW 
#  UPDATE %PREFIX%issues SET issue_state=1 WHERE issue_id=NEW.report_issue;