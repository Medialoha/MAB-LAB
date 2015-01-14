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

require_once(BASE_PATH.'includes/dbdescriptor.php');


class DBHelper {
	
	private static $dbo;
	

	public static function getConnection() {
		return self::$dbo;
	}
	
	public static function countRows($table, $where=null, $noPrefix=false) {
		$count = -1;
		
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

		$result = mysqli_query(self::$dbo, 'SELECT '.$projection.' FROM '.$table.(empty($where)?'':' WHERE '.$where));
		if ($result) {
			if (mysqli_num_rows($result)>0) {
				$arr = mysqli_fetch_array($result);
			}
			
			mysqli_free_result($result);
				
		} else { die('DATABASE ERROR : '.mysqli_error(self::$dbo));	}
		
		return $arr;	
	}
	
	public static function fetchMilestones($where=null, $order=null) {
		return self::selectRows(TBL_MILESTONES.' LEFT JOIN '.TBL_APPLICATIONS.' ON '.APP_ID.'='.MILE_APP_ID,
														$where,
														$order==null?MILE_DUEDATE.' ASC':$order,
														TBL_MILESTONES.'.*, '.
														APP_NAME.', '.
														'(SELECT COUNT(*) FROM '.TBL_ISSUES.' WHERE '.ISSUE_MILESTONE_ID.'='.MILE_ID.' AND '.ISSUE_STATE.'<>'.IssueState::STATE_ARCHIVED.') count_all, '.
														'(SELECT COUNT(*) FROM '.TBL_ISSUES.' WHERE '.ISSUE_MILESTONE_ID.'='.MILE_ID.' AND '.ISSUE_STATE.'='.IssueState::STATE_CLOSED.') count_closed, '.
														'(SELECT COUNT(*) FROM '.TBL_ISSUES.' WHERE '.ISSUE_MILESTONE_ID.'='.MILE_ID.' AND '.ISSUE_STATE.'='.IssueState::STATE_TESTING.') count_testing',
														null, null, false);
	}
	
	public static function fetchOrInsertApplication($packageName, $appName) {
		// check if application already exists
		$res = self::selectRow(TBL_APPLICATIONS, APP_ID.'=(SELECT '.APP_ID.' FROM '.TBL_APPLICATIONS.' WHERE '.APP_PACKAGE.'="'.$packageName.'")', APP_ID);
		if ($res!=null)
			return $res[0];		
		
		// if not then insert
		$query = 'INSERT INTO '.TBL_APPLICATIONS.' ('.APP_PACKAGE.', '.APP_NAME.') VALUE ("'.$packageName.'", "'.$appName.'")';
		
		if (mysqli_query(self::$dbo, $query)) {
			$res = self::selectRow(TBL_APPLICATIONS, APP_ID.'=(SELECT '.APP_ID.' FROM '.TBL_APPLICATIONS.' WHERE '.APP_PACKAGE.'="'.$packageName.'")', APP_ID);
			
			if ($res!=null)
				return $res[0];
		}
		
		return -1;
	}
	
	public static function updateApplication($id, $name, $package=null) {
		if ($id>0) {
			if (!self::exec('UPDATE '.TBL_APPLICATIONS.' SET '.APP_NAME.'="'.$name.'", '.APP_PACKAGE.'='.(is_null($package)?'null':'"'.$package.'"').' WHERE '.APP_ID.'='.$id, false))
				return 0;
						
		} else {
			$query = 'INSERT INTO '.TBL_APPLICATIONS.' ('.APP_NAME.', '.APP_PACKAGE.') VALUES ("'.$name.'", '.(is_null($package)?'null':'"'.$package.'"').')';
			
			if (mysqli_query(self::$dbo, $query)) {
				$res = self::selectRow(TBL_APPLICATIONS, APP_ID.'=(SELECT MAX('.APP_ID.') FROM '.TBL_APPLICATIONS.')', APP_ID);
					
				if ($res!=null)
					$id = $res[0];
			}
		}
				
		return $id;
	}
	
	public static function insertIssue($values) {
		$query = 'INSERT INTO '.TBL_ISSUES.self::convertArrToInsertValues($values);
		
		if (mysqli_query(self::$dbo, $query)) {
			$res = self::selectRow(TBL_ISSUES, ISSUE_ID.'=(SELECT MAX('.ISSUE_ID.') FROM '.TBL_ISSUES.')', ISSUE_ID);
			
			if ($res!=null)
				return $res[0];
		}
		
		return -1;
	}
	
	public static function fetchIssuesTable($where=null, $orderBy=null, $groupBy=null, $limit=null, $projection=null) {
		$tbl = TBL_ISSUES.' LEFT JOIN '.TBL_APPLICATIONS.' ON '.APP_ID.'='.ISSUE_APP_ID;
		$tbl .= ' JOIN '.TBL_REPORTS.' ON '.TBL_ISSUES.'.'.ISSUE_ID.' = '.TBL_REPORTS.'.'.REPORT_ISSUE;
		
		return self::fetchIssuesFromTable($tbl, $where, $orderBy, $groupBy, $limit, $projection);
	}
	
	public static function fetchIssues($where=null, $orderBy=null, $groupBy=null, $limit=null, $projection=null) {
		$tbl = TBL_ISSUES.' LEFT JOIN '.TBL_APPLICATIONS.' ON '.APP_ID.'='.ISSUE_APP_ID;

		return self::fetchIssuesFromTable($tbl, $where, $orderBy, $groupBy, $limit, $projection);
	}
	
	public static function fetchIssuesFromTable($tbl, $where=null, $orderBy=null, $groupBy=null, $limit=null, $projection=null) {

		if ($projection==null) {
			$projection = TBL_ISSUES.'.*, '.APP_PACKAGE.', '.APP_NAME;
		}
		
		return self::selectRows($tbl, $where, $orderBy, $projection, $groupBy, $limit, false);
	}
	
	public static function fetchNewIssues($limit=null) {
		return self::fetchIssues(	
											ISSUE_STATE.'='.IssueState::STATE_NEW,
											ISSUE_DATETIME.' DESC',
											null,
											$limit,
											TBL_ISSUES.'.*, '.APP_NAME.', '.
											'(SELECT COUNT(*) FROM '.TBL_REPORTS.' WHERE '.REPORT_ISSUE.'='.ISSUE_ID.' AND '.REPORT_STATE.'='.REPORT_STATE_NEW.') count_new_reports,'.
											'(SELECT COUNT(*) FROM '.TBL_REPORTS.' WHERE '.REPORT_ISSUE.'='.ISSUE_ID.') count_reports'
										);
	}
	
	public static function deleteIssues($ids) {		
		$where = ' IN ('.(is_array($ids)?implode(',', $ids):$ids).')';
		
		mysqli_query(self::$dbo, 'DELETE FROM '.TBL_REPORTS.' WHERE '.REPORT_ISSUE.$where);
		mysqli_query(self::$dbo, 'DELETE FROM '.TBL_ISSUES.' WHERE '.ISSUE_ID.$where);
	}
	
	public static function deleteReports($ids) {
		$where = REPORT_ID;
		
		if (!is_array($ids))
			$ids = array($ids);
		
		// number of reports to delete
		$count = sizeOf($ids);
		
		foreach ($ids as $id) {
			// get report issue id
			$issueId = self::selectRow(TBL_REPORTS, REPORT_ID."=".$id, REPORT_ISSUE);
			
			// if report issue is not valid then do nothing
			if (empty($issueId) || !($issueId[0]>0))
				continue;
			
			$issueId = $issueId[0];
			
			// delete report
			$res = mysqli_query(self::$dbo, 'DELETE FROM '.TBL_REPORTS.' WHERE '.REPORT_ID.'='.$id);
			if ($res) {
				// check if issue should be deleted
				$res = self::countRows(TBL_REPORTS, REPORT_ISSUE.'='.$issueId);
				
				// if no more reports for this issue then delete it
				if ($res==0)
					mysqli_query(self::$dbo, 'DELETE FROM '.TBL_ISSUES.' WHERE '.ISSUE_ID.'='.$issueId);
				
				--$count;
			}
		}
		
		return ($count==0);
	}
	
	public static function updateIssuesState($ids, $stateId, $updateReports=true) {
		$res = false;
		
		switch ($stateId) {
			case ISSUE_STATE_NEW : $reportState = REPORT_STATE_NEW;
				break;
			case ISSUE_STATE_VIEWED : $reportState = REPORT_STATE_VIEWED;
				break;
			case ISSUE_STATE_CLOSED : $reportState = REPORT_STATE_CLOSED;
				break;
			case ISSUE_STATE_TESTING : $reportState = REPORT_STATE_TESTING;
				break;
			case ISSUE_STATE_ARCHIVED : $reportState = REPORT_STATE_ARCHIVED;
				break;
		}
		
		if (!is_array($ids)) {
			$ids = array($ids);
		}
		
		for ($i=0; $i<sizeOf($ids); ++$i) {
			$issueId = $ids[$i];

			$updateIssue = true;
			if ($updateReports) {
				// update issue reports state
				$updateIssue = mysqli_query(self::$dbo, 'UPDATE '.TBL_REPORTS.' SET '.REPORT_STATE.'='.$reportState.' WHERE '.REPORT_ISSUE.'='.$issueId);
			}

			// update issue state
			if ($updateIssue) {
				$res = mysqli_query(self::$dbo, 'UPDATE '.TBL_ISSUES.' SET '.ISSUE_STATE.'='.$stateId.' WHERE '.ISSUE_ID.'='.$issueId);
			}
		}
		
		return $res;
	}
	
	public static function updateIssuesPriority($ids, $priorityId) {
		$where = ISSUE_ID;
		
		if (is_array($ids))
			$where .= ' IN ('.implode(',', $ids).')';
		else
			$where .= '='.$ids;
		
		return mysqli_query(self::$dbo, 'UPDATE '.TBL_ISSUES.' SET '.ISSUE_PRIORITY.'='.$priorityId.' WHERE '.$where);
	}
	
	public static function updateReportsState($ids, $stateId) {
		if (!is_array($ids))
			$ids = array($ids);

		for ($i=0; $i<sizeOf($ids); ++$i) {
			$reportId = $ids[$i];
			
			// update report state
			mysqli_query(self::$dbo, 'UPDATE '.TBL_REPORTS.' SET '.REPORT_STATE.'='.$stateId.' WHERE '.REPORT_ID.'='.$reportId);
			
			// if new report state is VIEWED then update issue state if needed
			if ($stateId==REPORT_STATE_VIEWED) {
				// check if issue has reports sill not viewed
				$count = self::countRows(TBL_REPORTS, REPORT_STATE.'='.REPORT_STATE_NEW.' AND '.REPORT_ISSUE.'=(SELECT '.REPORT_ISSUE.' FROM '.TBL_REPORTS.' WHERE '.REPORT_ID.'='.$reportId.')');
					
				// update to viewed if true
				if ($count==0)
					mysqli_query(self::$dbo, 'UPDATE '.TBL_ISSUES.' SET '.ISSUE_STATE.'='.IssueState::STATE_VIEWED.
																		' WHERE '.ISSUE_ID.'=(SELECT '.REPORT_ISSUE.' FROM '.TBL_REPORTS.' WHERE '.REPORT_ID.'='.$reportId.')');
			}
		}
	}
	
	public static function updateReportsIssue($reportIds, $issueId) {
		if (is_string($reportIds)) {
			$where = REPORT_ID.' IN ("'.str_replace(',', '", "', $reportIds).'")';
			
		} else if (is_array($reportIds)) { 
			$where = REPORT_ID.' IN ("'.implode('","', $reportIds).'")'; 
		
		} else { return false; }
		
		return mysqli_query(self::$dbo, 'UPDATE '.TBL_REPORTS.' SET '.REPORT_ISSUE.'='.$issueId.' WHERE '.$where);
	}
		
	public static function fetchReport($reportId) {
		$arr = self::selectRow(TBL_REPORTS.' LEFT JOIN '.TBL_ISSUES.' ON '.ISSUE_ID.'='.REPORT_ISSUE, 
														REPORT_ID."=".$reportId,
														TBL_REPORTS.'.*, '.ISSUE_PRIORITY.', '.ISSUE_CAUSE);
		$report = Report::createFromArray($arr);
		
		if ($report->isNew()) {
			DBHelper::updateReportsState($reportId, REPORT_STATE_VIEWED);
		}
		
		return $report;
	}
	
	public static function fetchIssue($id) {
		$arr = self::fetchIssues(ISSUE_ID.'='.$id);
		if (count($arr)==1) {
			$issue = Issue::createFromArray($arr[0]);
			
			if ($issue->getState()->isNew())
				self::updateIssuesState($id, IssueState::STATE_VIEWED, true);
			
			return $issue;
		}
		
		return null;
	}
	
	public static function insertReport($values) {
		$newIssue = false;
		
		$packageName = $values[REPORT_PACKAGE_NAME];
		
		Debug::logd('Application package '.$packageName, 'INSERT REPORT');
		
		// check if application exists
		$appId = DbHelper::fetchOrInsertApplication($packageName, ReportHelper::formatPackageName($packageName, true));
		Debug::logd('Application id #'.$appId, 'INSERT REPORT');
	
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
											ISSUE_STATE=>ISSUE_STATE_NEW,
											ISSUE_APP_ID=>$appId
										);
			
			$issue[ISSUE_ID] = self::insertIssue($issue);
			$newIssue = true;
			
			Debug::logd('New issue id #'.$issue[ISSUE_ID], 'INSERT REPORT');
		}

		$values[REPORT_ISSUE] = $issue[ISSUE_ID];
		
		if (intval($values[REPORT_ISSUE])>0) {
			Debug::logd('Insert new report', 'INSERT REPORT');
						
			$result = mysqli_query(self::$dbo, 'INSERT INTO '.TBL_REPORTS.self::convertArrToInsertValues($values));
			if (!$result && $newIssue) {
				Debug::logd('Insert failed, remove newly inserted issue...', 'INSERT REPORT');
			
				// remove newly inserted issue if report insertion failed
				self::deleteIssues($issue[ISSUE_ID]);
				
			} else if ($result && !$newIssue) {
				Debug::logd('Update issue #'.$issue[ISSUE_ID].' state to new and date to '.$values[REPORT_CRASH_DATE], 'INSERT REPORT');
				
				// update issue state as new and date
				$error = self::exec('UPDATE '.TBL_ISSUES.
															' SET '.ISSUE_STATE.'='.IssueState::STATE_NEW.', '.ISSUE_DATETIME.'="'.$values[REPORT_CRASH_DATE].'"'.
														' WHERE '.ISSUE_ID.'='.$issue[ISSUE_ID]);
				
				if ($error!=null) {
					Debug::logd(is_string($error)?$error:'Error while updating issue state and date !', 'INSERT REPORT');
					$result = false;
				}
			}
			
		} else { $result = false; }
		
		return $result;
	}
	
	public static function insertSale($values) {
		$error = null;
		$packageName = $values[SALE_PRODUCT_ID];
		
		$appId = DbHelper::fetchOrInsertApplication($packageName, ReportHelper::formatPackageName($packageName, true));
		Debug::logd('Application id #'.$appId, 'INSERT SALE');
		
		if ($appId>0) {
			$values[SALE_APP_ID] = $appId;
		
			$error = self::exec('REPLACE INTO '.TBL_SALES.' '.self::convertArrToInsertValues($values));
			if ($error!=null) {
				if (self::countRows(TBL_SALES, SALE_ORDER_NUMBER.'='.$values[SALE_ORDER_NUMBER])==0)
					$error = 'Row insertion check failed !';
			}
			
		} else { $error = 'Retreive or insert application failed !'; }
		
		return $error;
	}
	
	/**
	 * Update googleplay_sales merchant currency and convert amount to merchant currency
	 */
	public static function updateSalesCurrency($currency) {
		if (!$currency instanceof Currency) {
			return false;
		}
		
		$error = 0;
		$query = 'UPDATE '.TBL_SALES.' SET '.SALE_MERCHANT_CURRENCY.'="'.$currency->getCurrencyCode().'", '.SALE_CHARGED_AMOUNT_MERCHANT_CURRENCY.'=';
		
		$sales = self::selectRows(TBL_SALES, SALE_MERCHANT_CURRENCY."<>'".$currency->getCurrencyCode()."'", null, SALE_ORDER_NUMBER.','.SALE_CURRENCY_CODE.','.SALE_CHARGED_AMOUNT, null, null, false);
		
		if ($sales!=null) {
			foreach ($sales as $s) {
				if (self::exec($query.$currency->convert($s[SALE_CHARGED_AMOUNT], $s[SALE_CURRENCY_CODE]).' WHERE '.SALE_ORDER_NUMBER.'='.$s[SALE_ORDER_NUMBER])!=null) {
					++$error;
				}
			}
		}
		
		return $error==0;
	}
	
	public static function getReportIssueKey(&$reportArr) {
// 		$arr = explode("\n", $reportArr[REPORT_STACK_TRACE]);
// 		return md5($reportArr[REPORT_VERSION_CODE].$reportArr[REPORT_VERSION_NAME].$reportArr[REPORT_PACKAGE_NAME].$arr[0]);

		$stack = preg_replace('#^([^\t]+\n)#m', "", $reportArr[REPORT_STACK_TRACE]);
		if ($stack!=null) {
			$stack = preg_replace('#\t(at (com\.android|android|java)\..*?)\n#', "", $stack);
			$stack = preg_replace('#\t\.\.\. \d+ more\n#', "", $stack);
		}
		
		return md5(/*$reportArr[REPORT_VERSION_CODE].$reportArr[REPORT_VERSION_NAME].*/
								(isset($reportArr[IS_SILENT])?$reportArr[IS_SILENT]:0).$reportArr[REPORT_PACKAGE_NAME].$stack);
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
			
			$result = mysqli_query(self::$dbo, 'UPDATE '.TBL_USERS.' SET '.$values.' WHERE '.USER_ID.'='.$user_id);
			
		} else { 
			$result = mysqli_query(self::$dbo, 'INSERT INTO '.TBL_USERS.
																					' ('.USER_NAME.', '.USER_PASSWORD.', '.USER_EMAIL.') '.
																					' VALUES ("'.$name.'", "'.md5($clear_password).'", "'.$email.'")');
		}

		return $result;		
	}
	
	public static function deleteApplication($ids) {
		$where = APP_ID;
		
		if (is_array($ids))
			$where .= ' IN ("'.implode('","', $ids).'")';
		else
			$where .= '="'.$ids.'"';
		
		return mysqli_query(self::$dbo, 'DELETE FROM '.TBL_APPLICATIONS.' WHERE '.$where);
	}
	
	public static function deleteUsers($ids) {
		$where = USER_ID;
		
		if (is_array($ids))
			$where .= ' IN ("'.implode('","', $ids).'")';
		else
			$where .= '="'.$ids.'"';
		
		return mysqli_query(self::$dbo, 'DELETE FROM '.TBL_USERS.' WHERE '.$where);
	}
	
	public static function deleteLogs() {
		return mysqli_query(self::$dbo, 'DELETE FROM '.TBL_LOGS);
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
	
	
	
	
	public static function open() {
		global $mGlobalCfg;
		
		if (isset(self::$dbo) && self::$dbo instanceof mysqli) return;
		
		if (empty($mGlobalCfg['db.port']) || !is_integer($mGlobalCfg['db.port']))
			$dbPort = ini_get("mysqli.default_port");
		else
			$dbPort = $mGlobalCfg['db.port'];
		
		self::$dbo = new mysqli($mGlobalCfg['db.host'], $mGlobalCfg['db.user'], $mGlobalCfg['db.pwd'], $mGlobalCfg['db.name'], $dbPort);

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