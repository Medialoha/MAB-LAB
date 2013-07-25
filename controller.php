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

$action = $_REQUEST['a'];


switch ($action) {
		
//////// LOGOUT
	case 'logout' :
			unset($_SESSION['LOGGEDIN']); 
			unset($_SESSION[USER_ID]); 
			unset($_SESSION[USER_NAME]); 
			unset($_SESSION[USER_EMAIL]); 
			
			header('Location:index.php');   
		break;
		
//////// UPDATE CONFIG
	case 'updateconfig' :
			$cfg = CfgHelper::getInstance();
			
			$tmpCfg = array();
			foreach ($_POST as $k=>$v) {
				if (strpos('in-', $k, 0)==0) {
					$key = str_replace('-', '.', substr($k, 3));
					if (!empty($key)) {
						// check for boolean value
						if (strcmp($key, 'report.packagename.shrink')==0 || strcmp($key, 'report.sendmail')==0) {
							$tmpCfg[$key] = $v=='1'?true:false;

						} else { $tmpCfg[$key] = $v; }
					}
				}
			}
						
			$error = CfgHelper::writeConfig($tmpCfg);
			if ($error==null) {
				CfgHelper::init(true);
				Helper::pushAlert(ALERT_SUCCESS, 'Configuration saved.');
						
			} else { Helper::pushAlert(ALERT_ERROR, $error); }
			
			header('Location:index.php?p='.PAGE_ID_CONFIG);
		break;
		
//////// GET REPORT
	case 'getreport' :
			$reportId = @$_POST['reportId'];
			if (!empty($reportId)) {
				$report = DBHelper::fetchReport($reportId);
				
				require_once('pages/report_details_dialog.php');
				
				// set report as viewd
				if ($report->isNew())
					DBHelper::updateReportsState($reportId, REPORT_STATE_VIEWED);
				
			} else { echo 'Report id is not valid !'; }
		break;
	
//////// ARCHIVE REPORTS
	case 'archreports' :
			$reportIds = @$_POST['reportIds'];
			if (!empty($reportIds)) {
				$reportIds = explode(',', $reportIds);				
				DBHelper::updateReportsState($reportIds, REPORT_STATE_ARCHIVED);
				
				echo 'Report(s) archived with success !';
				
			} else { echo 'Report id(s) is not valid !'; }
		break;
	
//////// DEL REPORTS
	case 'delreports' :
			$reportIds = @$_POST['reportIds'];
			if (!empty($reportIds)) {
				$reportIds = explode(',', $reportIds);		
				DBHelper::deleteReports($reportIds);
				
				echo 'Report(s) deleted with success !';
				
			} else { echo 'Report id(s) is not valid !'; }
		break;
	
//////// DEL USER
	case 'deluser' :
			$userId = @$_POST['userId'];
			if (!empty($userId)) {
				if (DBHelper::countRows(TBL_USERS)>1) {
					if (DBHelper::deleteUsers($userId))
						Helper::pushAlert(ALERT_SUCCESS, 'User deleted with success !');
					else
						Helper::pushAlert(ALERT_ERROR, 'Unable to delete user with id #'.$userId);
					
				} else { Helper::pushAlert(ALERT_ERROR, 'You must keep at least one user !!!'); }
				
			} else { Helper::pushAlert(ALERT_ERROR, 'User id is not valid !'); }
			
			header('Location:index.php?p='.PAGE_ID_USERS);
		break;
	
//////// EDIT USER
	case 'edituser' :
			$u = array('user_id'=>@$_POST['user_id'], 'user_name'=>@$_POST['user_name'], 'user_password'=>@$_POST['user_password'], 'user_email'=>@$_POST['user_email']);
			$error = null;
			
			if (!empty($u['user_name'])) {
				// if new user then password must be set
				if (!($u['user_id']>0) && empty($u['user_password'])) {
					$error = 'You must specify a password !';
					
				} else { 
					$result = DBHelper::updateUser($u['user_id']>0?$u['user_id']:null, $u['user_name'], $u['user_password'], $u['user_email']);
					
					if (!$result)
						$error = 'Unable to update user ! '.DBHelper::getLastError();
				}
				
			} else { $error = 'Username can not be empty !'; } 
			
			if ($error!=null)
				Helper::pushAlert(ALERT_ERROR, $error);
			else 
				Helper::pushAlert(ALERT_SUCCESS, 'User updated with success !');
			
			header('Location:index.php?p='.PAGE_ID_USERS);
		break;
		
//////// GET LAST REPORTS BOX
	case 'getlastreports' :
			require_once('pages/last_reports_box.php');
		break;
		
//////// GET CHART DATA
	case 'getmostaffecteddev' :
			require_once('pages/most_affected_devices_box.php');
		break;		
		
//////// GET CHART DATA
	case 'getchartdata' :
			require_once('includes/charthelper.class.php');

			$data = null; $message = 'OK';
			
			$chartId = $_GET['chartId'];
			switch ($chartId) {
				case REPORTS_PER_ANDROID_VERSION_PIE_CHART_ID :
						$arr = DBHelper::selectRows(TBL_REPORTS, null, null, REPORT_ANDROID_VERSION.', COUNT(*)', REPORT_ANDROID_VERSION, null, false);
						if ($arr!=null) {
							$data = ChartHelper::convertMySQLArrToPieChartJSON($arr);
							
						} else { $message = 'No data yet recorded.|'; }
					break;
					
				case REPORTS_PER_APPLICATION_PIE_CHART_ID :
						$arr = DBHelper::selectRows(TBL_REPORTS, null, null, REPORT_PACKAGE_NAME.',COUNT(*)', REPORT_PACKAGE_NAME, null, false);
						if ($arr!=null) {
							for($i=0; $i<sizeof($arr); ++$i)
								$arr[$i][0] = ReportHelper::formatPackageName($arr[$i][0], true);
							
							$data = ChartHelper::convertMySQLArrToPieChartJSON($arr);
							
						} else { $message = 'No data yet recorded.|'; }
					break;
					
				case REPORTS_EVOLUTION_LINE_CHART_ID :
					$projection = 'DATE(user_crash_date) date, (SELECT COUNT(*) FROM '.DBHelper::getTblName(TBL_REPORTS).' WHERE DATE(user_crash_date)=date) count';
					$groupby = 'date';
					$orderby = 'date DESC LIMIT 7';
					
						$arr = DBHelper::selectRows(TBL_REPORTS, null, $orderby, $projection, $groupby, null, false);
						if ($arr!=null) {
							$data = ChartHelper::convertMySQLArrToBarChartJSON($arr);
							
						} else { $message = 'No data yet recorded.|'; }
					break;
					

				default : $message = 'Unhandled chart id requested !!!'; 
			}
			
			echo $message.'|'.$data;
		
		break;
	
	
	
	default : die("UNHANDLED ACTION REQUESTED !");
}