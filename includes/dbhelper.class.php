<?php defined('DIRECT_ACCESS_CHECK') or die('DIRECT ACCESS NOT ALLOWED');
/**
 * Copyright (c) 2013 EIRL DEVAUX J. - Medialoha.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the GNU Public License v3.0
 * which accompanies this distribution, and is available at
 * http://www.gnu.org/licenses/gpl.html
 *
 * Contributors:
 *     EIRL DEVAUX J. - Medialoha - initial API and implementation
 */

define('TBL_USERS', 'users');
define('USER_ID', 'user_id');
define('USER_NAME', 'user_name');
define('USER_PASSWORD', 'user_password');
define('USER_EMAIL', 'user_email');

define('TBL_ISSUES', 'issues');
define('ISSUE_ID', 'issue_id');
define('ISSUE_KEY', 'issue_key');
define('ISSUE_DATETIME', 'issue_datetime');
define('ISSUE_CAUSE', 'issue_cause');
define('ISSUE_STATE', 'issue_state');
define('ISSUE_PRIORITY', 'issue_priority');

define('TBL_REPORTS', 'reports');
define('REPORT_ID', 'report_id');
define('REPORT_KEY', 'report_key');
define('REPORT_CRASH_DATE', 'user_crash_date');
define('REPORT_PACKAGE_NAME', 'package_name');
define('REPORT_VERSION_NAME', 'app_version_name');
define('REPORT_VERSION_CODE', 'app_version_code');
define('REPORT_ANDROID_VERSION', 'android_version');
define('REPORT_PHONE_MODEL', 'phone_model');
define('REPORT_BRAND', 'brand');
define('REPORT_PRODUCT', 'product');
define('REPORT_USER_COMMENT', 'user_comment');
define('REPORT_STACK_TRACE', 'stack_trace');
define('REPORT_LOGCAT', 'logcat');
define('REPORT_INSTALLATION_ID', 'installation_id');

define('REPORT_STATE', 'report_state');		 // 1 new => 2 viewed => 3 closed => 0 archived
define('REPORT_ISSUE', 'report_issue');

define('TBL_LOGS', 'logs');
define('LOG_TIMESTAMP', 'log_timestamp');


class DBHelper {
	
	private static $dbo;
	

	public static function getConnection() {
		return self::$dbo;
	}
	
	public static function countRows($table, $where=null, $noPrefix=false) {
		$count = -1;
		
		if (!$noPrefix && !strpos($table, ' '))
			$table = self::getTblName($table);
		
		$result = mysqli_query(self::$dbo, 'SELECT COUNT(*) FROM '.$table.(empty($where)?'':' WHERE '.$where), MYSQLI_USE_RESULT);
		if ($result) {
			$row = mysqli_fetch_array($result);
			$count = $row[0];
			
			mysqli_free_result($result);
			
		} else { die('DATABASE ERROR : '.mysqli_error(self::$dbo)); }
		
		return $count;
	}
	
	public static function selectRows($table, $where=null, $order=null, $projection='*', $groupby=null, $limit=null, $returnAsObjects=true) {
		$arr = null;
		
		if (!strpos($table, ' '))
			$table = self::getTblName($table);
		
		$result = mysqli_query(self::$dbo, 'SELECT '.$projection.
																				' FROM '.$table.
																				(empty($where)?'':' WHERE '.$where).
																				($groupby==null?'':' GROUP BY '.$groupby).
																				($order==null?'':' ORDER BY '.$order).
																				($limit==null?'':' LIMIT '.$limit));
		if ($result) {
			if (mysqli_num_rows($result)>0) {
				if ($returnAsObjects) {
					while ($obj = $result->fetch_object()) {
        		$arr[] = $obj;
    			}
    			
				} else {
					while ($obj = $result->fetch_array()) {
        		$arr[] = $obj;
    			} 
				}
			}
				
			mysqli_free_result($result);
		
		} else { die('DATABASE ERROR : '.mysqli_error(self::$dbo)); }
		
		return $arr;
	}
	
	public static function selectRow($table, $where=null, $projection='*') {
		$arr = null;
		
		if (!strpos($table, ' '))
			$table = self::getTblName($table);

		$result = mysqli_query(self::$dbo, 'SELECT '.$projection.' FROM '.$table.(empty($where)?'':' WHERE '.$where));
		if ($result) {
			if (mysqli_num_rows($result)>0) {
				$arr = mysqli_fetch_array($result);
			}
			
			mysqli_free_result($result);
				
		} else { die('DATABASE ERROR : '.mysqli_error(self::$dbo));	}
		
		return $arr;	
	}
	
	public static function insertIssue($values) {
		$query = 'INSERT INTO '.self::getTblName(TBL_ISSUES).self::convertArrToInsertValues($values);
		
		if (mysqli_query(self::$dbo, $query)) {
			$res = self::selectRow(TBL_ISSUES, ISSUE_ID.'=(SELECT MAX('.ISSUE_ID.') FROM '.self::getTblName(TBL_ISSUES).')', ISSUE_ID);
			
			if ($res!=null)
				return $res[0];
		}
		
		return -1;
	}
	
	public static function fetchIssues($where=null, $orderBy=null, $groupBy=null, $limit=null, $projection=null) {
		$tbl = self::getTblName(TBL_ISSUES).' LEFT JOIN '.self::getTblName(TBL_REPORTS).' ON '.REPORT_ISSUE.'='.ISSUE_ID;
		
		if ($projection==null) {
			$projection = self::getTblName(TBL_ISSUES).'.*, '.REPORT_PACKAGE_NAME.', '.REPORT_VERSION_NAME.', '.REPORT_VERSION_CODE;
		}
		
		return self::selectRows($tbl, $where, $orderBy, $projection, $groupBy, $limit, false);
	}
	
	public static function fetchNewReports($limit=null) {
		$reportsTbl = self::getTblName(TBL_REPORTS);
		
		$tables = $reportsTbl.' LEFT JOIN '.self::getTblName(TBL_ISSUES).' ON '.ISSUE_ID.'='.REPORT_ISSUE;

		$projection = 'MAX('.REPORT_ID.') last_report_id, MAX('.REPORT_CRASH_DATE.') last_crash_date,'.REPORT_STATE.','.REPORT_PACKAGE_NAME.', '.REPORT_VERSION_NAME.', '.REPORT_VERSION_CODE.','.
									ISSUE_ID.','.ISSUE_PRIORITY.','.ISSUE_CAUSE.
									', (SELECT COUNT(*) FROM '.$reportsTbl.' WHERE '.REPORT_ISSUE.'='.ISSUE_ID.' AND '.REPORT_STATE.'='.REPORT_STATE_NEW.') count_new'.
									', (SELECT COUNT(*) FROM '.$reportsTbl.' WHERE '.REPORT_ISSUE.'='.ISSUE_ID.') count_reports';

		return self::selectRows($tables, REPORT_STATE.'='.REPORT_STATE_NEW, REPORT_CRASH_DATE.' DESC', $projection, ISSUE_ID, $limit, true);
	}
	
	public static function deleteIssues($ids) {
		$where = ISSUE_ID;
		
		if (is_array($ids))
			$where .= ' IN ('.implode(',', $ids).')';
		else
			$where .= '='.$ids;
		
		return mysqli_query(self::$dbo, 'DELETE FROM '.self::getTblName(TBL_ISSUES).' WHERE '.$where);
	}
	
	public static function deleteReports($ids) {
		$where = REPORT_ID;
		
		if (!is_array($ids))
			$ids = array($ids);
		
		$count = sizeOf($ids);
		
		foreach ($ids as $id) {
			$res = mysqli_query(self::$dbo, 'SELECT FctDeleteReport('.$id.');');
			
			if ($res) {
				if (mysqli_num_rows($res)>0) {
					$res = mysqli_fetch_array($res);
					if ($res[0]>0)
						--$count;
				}
			}
		}
		
		return ($count==0);
	}
	
	public static function updateIssuesState($ids, $stateId) {		
		// new or viewed state is updated on report state update (trigger) then update issue reports state
		if ($stateId==ISSUE_STATE_NEW || $stateId==ISSUE_STATE_VIEWED) {
			$query = 'UPDATE '.self::getTblName(TBL_REPORTS).' SET '.REPORT_STATE.'='.$stateId.' WHERE '.REPORT_ISSUE;
			
		// else update the issue state then trigger will update the reports state
		} else { $query = 'UPDATE '.self::getTblName(TBL_ISSUES).' SET '.ISSUE_STATE.'='.$stateId.' WHERE '.ISSUE_ID; }
		
		Debug::logd($query, '');
		
		if (is_array($ids))
			$query .= ' IN ('.implode(',', $ids).')';
		else
			$query .= '='.$ids;
		
		Debug::logd($query, '');
		
		return mysqli_query(self::$dbo, $query);
	}
	
	public static function updateIssuesPriority($ids, $priorityId) {
		$where = ISSUE_ID;
		
		if (is_array($ids))
			$where .= ' IN ('.implode(',', $ids).')';
		else
			$where .= '='.$ids;
		
		return mysqli_query(self::$dbo, 'UPDATE '.self::getTblName(TBL_ISSUES).' SET '.ISSUE_PRIORITY.'='.$priorityId.' WHERE '.$where);
	}
	
	public static function updateReportsState($ids, $stateId) {
		$where = REPORT_ID;
		
		if (is_array($ids))
			$where .= ' IN ('.implode(',', $ids).')';
		else
			$where .= '='.$ids;
		
		return mysqli_query(self::$dbo, 'UPDATE '.self::getTblName(TBL_REPORTS).' SET '.REPORT_STATE.'='.$stateId.' WHERE '.$where);
	}
	
	public static function updateReportsIssue($reportIds, $issueId) {
		if (is_string($reportIds)) {
			$where = REPORT_ID.' IN ("'.str_replace(',', '", "', $reportIds).'")';
			
		} else if (is_array($reportIds)) { 
			$where = REPORT_ID.' IN ("'.implode('","', $reportIds).'")'; 
		
		} else { return false; }
		
		return mysqli_query(self::$dbo, 'UPDATE '.self::getTblName(TBL_REPORTS).' SET '.REPORT_ISSUE.'='.$issueId.' WHERE '.$where);
	}
		
	public static function fetchReport($reportId) {
		$reportsTbl = self::getTblName(TBL_REPORTS);
		$arr = self::selectRow($reportsTbl.' LEFT JOIN '.self::getTblName(TBL_ISSUES).' ON '.ISSUE_ID.'='.REPORT_ISSUE, 
														REPORT_ID."=".$reportId,
														$reportsTbl.'.*, '.ISSUE_PRIORITY.', '.ISSUE_CAUSE);
		$report = Report::createFromArray($arr);
		
		if ($report->isNew()) {
			DBHelper::updateReportsState($reportId, REPORT_STATE_VIEWED);
		}
		
		return $report;
	}
	
	public static function insertReport($values) {		
		$newIssue = false;
	
		// search for issue id
		$issue_key = self::getReportIssueKey($values);
		
		Debug::logd('Issue key '.$issue_key, 'INSERT REPORT');
		
		// search existing issue which is not archived
		$issue = self::selectRow(TBL_ISSUES, ISSUE_KEY.'="'.$issue_key.'" AND '.ISSUE_STATE.'<>'.ISSUE_STATE_ARCHIVED, ISSUE_ID);
		
		Debug::logd('Issue found '.($issue==null?'NO':print_r($issue, true)), 'INSERT REPORT');
		
		if ($issue==null) {
			$stacktrace = explode('\n', $values[REPORT_STACK_TRACE]);

			$issue = array(	ISSUE_KEY=>$issue_key,
											ISSUE_DATETIME=>$values[REPORT_CRASH_DATE],
											ISSUE_CAUSE=>$stacktrace[0],
											ISSUE_PRIORITY=>IssuePriority::NORMAL,
											ISSUE_STATE=>ISSUE_STATE_NEW);
			
			$issue[ISSUE_ID] = self::insertIssue($issue);
			$newIssue = true;
			
			Debug::logd('New issue id #'.$issue[ISSUE_ID], 'INSERT REPORT');
		}

		$values[REPORT_ISSUE] = $issue[ISSUE_ID];
		
		if (intval($values[REPORT_ISSUE])>0) {
			Debug::logd('Insert new report', 'INSERT REPORT');
						
			$result = mysqli_query(self::$dbo, 'INSERT INTO '.self::getTblName(TBL_REPORTS).self::convertArrToInsertValues($values));
			if (!$result && $newIssue) {
				Debug::logd('Insert failed, remove newly inserted issue...', 'INSERT REPORT');
			
				// remove newly inserted issue if report insertion failed
				self::deleteIssues($issue[ISSUE_ID]);
			}
			
		} else { $result = false; }
		
		return $result;
	}
	
	public static function getReportIssueKey(&$reportArr) {
		$arr = explode("\n", $reportArr[REPORT_STACK_TRACE]);
		
		return md5($reportArr[REPORT_VERSION_CODE].$reportArr[REPORT_VERSION_NAME].$reportArr[REPORT_PACKAGE_NAME].$arr[0]);
	}
	
	public static function fetchUsers() {
		return self::selectRows(TBL_USERS, null, USER_NAME.' ASC');
	}
	
	public static function updateUser($user_id, $name, $clear_password, $email) {
		if ($user_id!=null && $user_id>0) {
			$values = ''; $sep = '';
			if (!empty($name)) {
				$values .= $sep.USER_NAME.'="'.$name.'"'; $sep = ','; 
			}
			if (!empty($clear_password)) {
				$values .= $sep.USER_PASSWORD.'="'.md5($clear_password).'"'; $sep = ','; 
			}
			if (!empty($email)) {
				$values .= $sep.USER_EMAIL.'="'.$email.'"'; 
			}
			
			$result = mysqli_query(self::$dbo, 'UPDATE '.self::getTblName(TBL_USERS).' SET '.$values.' WHERE '.USER_ID.'='.$user_id);
			
		} else { 
			$result = mysqli_query(self::$dbo, 'INSERT INTO '.self::getTblName(TBL_USERS).
																					' ('.USER_NAME.', '.USER_PASSWORD.', '.USER_EMAIL.') '.
																					' VALUES ("'.$name.'", "'.md5($clear_password).'", "'.$email.'")');
		}

		return $result;		
	}
	
	public static function deleteUsers($ids) {
		$where = USER_ID;
		
		if (is_array($ids))
			$where .= ' IN ("'.implode('","', $ids).'")';
		else
			$where .= '="'.$ids.'"';
		
		return mysqli_query(self::$dbo, 'DELETE FROM '.self::getTblName(TBL_USERS).' WHERE '.$where);
	}
	
	public static function deleteLogs() {
		return mysqli_query(self::$dbo, 'DELETE FROM '.self::getTblName(TBL_LOGS));
	}
	
	public static function exec($sql, $multi=false) {
		if ($multi) {
			if (mysqli_multi_query(self::$dbo, $sql)===true) { return null; }
			
		} else if (mysqli_query(self::$dbo, $sql)===true) { return null; }
		
		return self::getLastError();
	}
	
	public static function fetchLastLogs(&$total, $maxRows) {
		$total = self::countRows(TBL_LOGS);
		$limit = ($total>$maxRows?$total-$maxRows:0).', '.$maxRows;
		
		return self::selectRows(TBL_LOGS, null, LOG_TIMESTAMP.' ASC', '*', null, $limit, false);
	}
	
	public static function getTblName($name) {
		$cfg = CfgHelper::getInstance();
		
		return $cfg->getTablePrefix().$name;
	}
	
	public static function open() {
		global $mGlobalCfg;
		
		if (isset(self::$dbo) && self::$dbo instanceof mysqli) return;
		
		self::$dbo = new mysqli($mGlobalCfg['db.host'], $mGlobalCfg['db.user'], $mGlobalCfg['db.pwd'], $mGlobalCfg['db.name']);

		if (self::$dbo->connect_error) {
			die('CONNECT ERROR #'.self::$dbo->connect_errno.' : '.self::$dbo->connect_error);
		}
		
		if (mysqli_connect_error()) {
			die('CONNECT ERROR #'.mysqli_connect_errno().' : '.mysqli_connect_error());
		}
		
		mysqli_set_charset(self::$dbo, "utf8");
	}
		
	public static function close() {  
		if (isset(self::$dbo))
			mysqli_close(self::$dbo);
	}
	
	public static function clearStoredResults() {
		while(mysqli_more_results(self::$dbo)) {
			mysqli_next_result(self::$dbo);
		
			if($r = mysqli_store_result(self::$dbo)){
				mysqli_free_result($r);
			}
		}
	}
	
	public static function getLastError() {
		return '#'.mysqli_errno(self::$dbo).' : '.mysqli_error(self::$dbo);
	}
	
	public static function escapeString($string) {
		return mysqli_real_escape_string(self::$dbo, $string);
	}	
	
	public static function convertArrToInsertValues($keyValuePairArray) {
		$insertKeys = ''; $insertValues = ''; $sep = '';
		foreach ($keyValuePairArray as $k=>$v) {
			$insertKeys .= $sep.'`'.$k.'`';
			$insertValues .= $sep.'"'.$v.'"';
			$sep = ',';
		}
		
		return ' ('.$insertKeys.') VALUES ('.$insertValues.');';
	}
}