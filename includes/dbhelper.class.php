<?php
defined('DIRECT_ACCESS_CHECK') or die('DIRECT ACCESS NOT ALLOWED');

define('TBL_USERS', 'users');
define('USER_ID', 'user_id');
define('USER_NAME', 'user_name');
define('USER_PASSWORD', 'user_password');
define('USER_EMAIL', 'user_email');

define('TBL_REPORTS', 'reports');
define('REPORT_ID', 'report_id');
define('REPORT_CRASH_DATE', 'user_crash_date');
define('REPORT_PACKAGE_NAME', 'package_name');
define('REPORT_VERSION_NAME', 'app_version_name');
define('REPORT_VERSION_CODE', 'app_version_code');
define('REPORT_ANDROID_VERSION', 'android_version');
define('REPORT_PHONE_MODEL', 'phone_model');
define('REPORT_BRAND', 'brand');
define('REPORT_PRODUCT', 'product');

define('REPORT_STACK_TRACE', 'stack_trace');
define('REPORT_ST_LAST_CRASH_DATE', 'last_crash_date');

define('REPORT_STATE', 'report_state');
define('REPORT_TAG', 'report_tag');


class DBHelper {
	
	private static $dbo;
	

	public static function countRows($table, $where=null) {
		$count = -1;
		
		$result = mysqli_query(self::$dbo, 'SELECT COUNT(*) FROM '.self::getTblName($table).(empty($where)?'':' WHERE '.$where), MYSQLI_USE_RESULT);
		if ($result) {
			$row = mysqli_fetch_array($result);
			$count = $row[0];
			
			mysqli_free_result($result);
			
		} else { die('DATABASE ERROR : '.mysqli_error(self::$dbo)); }
		
		return $count;
	}
	
	public static function selectRows($table, $where=null, $order=null, $projection='*', $groupby=null, $limit=null, $returnAsObjects=true) {
		$arr = null;
		
		$result = mysqli_query(self::$dbo, 'SELECT '.$projection.
																				' FROM '.self::getTblName($table).
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

		$result = mysqli_query(self::$dbo, 'SELECT '.$projection.' FROM '.self::getTblName($table).(empty($where)?'':' WHERE '.$where));
		if ($result) {
			if (mysqli_num_rows($result)>0) {
				$arr = mysqli_fetch_array($result);
			}
			
			mysqli_free_result($result);
				
		} else { die('DATABASE ERROR : '.mysqli_error(self::$dbo));	}
		
		return $arr;	
	}
	
	public static function deleteReports($ids) {
		$where = REPORT_ID;
		
		if (is_array($ids))
			$where .= ' IN ("'.implode('","', $ids).'")';
		else
			$where .= '="'.$ids.'"';
		
		return mysqli_query(self::$dbo, 'DELETE FROM '.self::getTblName(TBL_REPORTS).' WHERE '.$where);
	}
	
	public static function updateReportsState($ids, $stateId) {
		$where = REPORT_ID;
		
		if (is_array($ids))
			$where .= ' IN ("'.implode('","', $ids).'")';
		else
			$where .= '="'.$ids.'"';
		
		if ($stateId==REPORT_STATE_VIEWED)
			$where .= ' AND '.REPORT_STATE.'='.REPORT_STATE_NEW;
		
		return mysqli_query(self::$dbo, 'UPDATE '.self::getTblName(TBL_REPORTS).' SET '.REPORT_STATE.'='.$stateId.' WHERE '.$where);
	}
	
	public static function fetchReport($reportId) {
		$arr = self::selectRow(TBL_REPORTS, REPORT_ID."='".$reportId."'");
		return Report::createFromArray($arr);
	}

	public static function fetchStackTrace($reportId)
	{
		$arr = self::selectRows(TBL_REPORTS,  REPORT_STACK_TRACE." = (SELECT ".REPORT_STACK_TRACE." FROM ".self::getTblName(TBL_REPORTS)." WHERE ".REPORT_ID."='".$reportId."')", REPORT_PACKAGE_NAME.", ".REPORT_VERSION_NAME, REPORT_ID.", ".REPORT_PACKAGE_NAME.", ".REPORT_VERSION_NAME.", ".REPORT_STACK_TRACE.", ".REPORT_VERSION_CODE.", COUNT(*) AS count, MAX(".REPORT_CRASH_DATE.") AS ".REPORT_ST_LAST_CRASH_DATE, REPORT_STACK_TRACE.", ".REPORT_PACKAGE_NAME.", ".REPORT_VERSION_NAME, 1, false);

		if(is_array($arr) && sizeof($arr) > 0)
		{
			return StackTrace::createFromArray($arr[0]);
		}
		else
		{
			return null;
		}
	}
	
	public static function fetchStackTraces($limit)
	{
		$arr = self::selectRows(TBL_REPORTS, REPORT_STATE.'!='.REPORT_STATE_ARCHIVED, "count DESC, ".REPORT_PACKAGE_NAME.", ".REPORT_VERSION_NAME, REPORT_ID.", ".REPORT_PACKAGE_NAME.", ".REPORT_VERSION_NAME.", SUBSTRING_INDEX( ".REPORT_STACK_TRACE.",  '\n', 1 ) AS  '".REPORT_STACK_TRACE."', COUNT(*) AS  count, MAX(".REPORT_CRASH_DATE.") AS ".REPORT_ST_LAST_CRASH_DATE, REPORT_STACK_TRACE.", ".REPORT_PACKAGE_NAME.", ".REPORT_VERSION_NAME, $limit, false);
		$result = array();
		if(is_array($arr) && sizeof($arr) > 0)
		{
			foreach ($arr as $values)
			{
				$result[] = StackTrace::createFromArray($values);
			}
		}
		return $result;
	}
	
	public static function deleteReportsByStacktrace($id) {
		$where = REPORT_STACK_TRACE.' = (SELECT '.REPORT_STACK_TRACE.' FROM (SELECT * FROM '.self::getTblName(TBL_REPORTS).' WHERE '.REPORT_ID.' = "'.$id.'") AS t)';
		
		return mysqli_query(self::$dbo, 'DELETE FROM '.self::getTblName(TBL_REPORTS).' WHERE '.$where);
	}
	
	public static function updateReportsStateByStacktrace($id, $stateId) {

		$where = REPORT_STACK_TRACE.' = (SELECT '.REPORT_STACK_TRACE.' FROM (SELECT * FROM '.self::getTblName(TBL_REPORTS).' WHERE '.REPORT_ID.' = "'.$id.'") AS t)';
		return mysqli_query(self::$dbo, 'UPDATE '.self::getTblName(TBL_REPORTS).' SET '.REPORT_STATE.'='.$stateId.' WHERE '.$where);
	}
	
	public static function insertReport($values) {
		$query = 'INSERT INTO '.self::getTblName(TBL_REPORTS);
		
		$insertKeys = ''; $insertValues = ''; $sep = '';
		foreach ($values as $k=>$v) {
			$insertKeys .= $sep.'`'.$k.'`';
			$insertValues .= $sep.'"'.$v.'"';
			$sep = ','; 
		}
		
		$query .= ' ('.$insertKeys.') VALUES ('.$insertValues.');';

		Debug::logd($query, 'REPORT');
		
		return mysqli_query(self::$dbo, $query);
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
	
	public static function exec($sql, $multi=false) {
		if ($multi) {
			if (mysqli_multi_query(self::$dbo, $sql)===true) { return null; }
			
		} else if (mysqli_query(self::$dbo, $sql)===true) { return null; }
		
		return self::getLastError();
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
}