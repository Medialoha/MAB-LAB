<?php
define('DIRECT_ACCESS_CHECK', true); 
define('BASE_PATH', '../../');

session_start();

require_once(BASE_PATH.'includes/define.php');
require_once(BASE_PATH.'includes/config.php');
require_once(BASE_PATH.'includes/confighelper.class.php');
require_once(BASE_PATH.'includes/helper.class.php');
require_once(BASE_PATH.'includes/debug.class.php');
require_once(BASE_PATH.'includes/dbhelper.class.php');
require_once(BASE_PATH.'includes/report.class.php');
require_once(BASE_PATH.'includes/issue.class.php');


///////////////// CREATE ISSUE FROM
define('_REPORT_ID', 0);
define('_APP_VERSION_CODE', 1);
define('_APP_VERSION_NAME', 2);
define('_PACKAGE_NAME', 3);
define('_STACK_TRACE', 4);
define('_REPORT_STATE', 5);
define('_CRASH_DATE', 6);

$cfg = CfgHelper::getInstance();

DBHelper::open();

echo '<b>Step 1.</b> Read SQL update file ...</b> ';
// read sql update file and set the table prefix
$sql = str_replace('%PREFIX%', $cfg->getTablePrefix(), @file_get_contents('db-update.sql'));
$sql = explode('#### TRIGGERS', $sql);

// SQL file is split in two parts
if (sizeOf($sql)!=2) {
	echo '/!\ SQL update file is not valid !<br/>';
	exit;
}

echo 'OK<br/>';

echo '<b>Step 2.</b> Alter reports table, create issues table, logs table, procedures and functions ...</b> ';
$res = DBHelper::exec($sql[0], true);
if ($res!=null) {
	echo '<br/><b style="padding-left:30px; color:red;" >'.$res.'</b>';
	exit;
}

DBHelper::clearStoredResults();

echo 'OK<br/>';

echo '<b>Step 3.</b> Get reports ...</b> ';
$reports = DBHelper::selectRows(TBL_REPORTS, null, null, 'report_id, app_version_code, app_version_name, package_name, stack_trace, report_state, user_crash_date', null, null, false);

echo 'OK<br/>';

echo '<b>Step 4.</b> Walk through reports array to create issues :<br/>';
$issues = array();

try {
	foreach ($reports as $report) {

		$arr = explode("\n", $report[_STACK_TRACE]);
		$cause = $arr[0];

		// create a key to identify the issue from all reports
		$k = DBHelper::getReportIssueKey($report);
		if (!array_key_exists($k, $issues)) {
			echo '<i style="padding-left:30px;" >|_ Create new issue '.$k.'</i><br/>';
			
			$issues[$k] = array('reports'=>array(), 'cause'=>$cause, 'state'=>$report[_REPORT_STATE], 'datetime'=>$report[_CRASH_DATE]);
		}
	
		$issues[$k]['reports'][] = $report[_REPORT_ID];
	
		// check if state is different (if one report of the issue is archived or new then the issue is considered as archived or new)
		if ($issues[$k]['state']!=$report[_REPORT_STATE]) {
			
			switch ($report[_REPORT_STATE]) {
				case REPORT_STATE_ARCHIVED : $issues[$k]['state'] = REPORT_STATE_ARCHIVED;
					break;
				case REPORT_STATE_NEW : $issues[$k]['state'] = REPORT_STATE_NEW;
					break;
			}
		}
	}
	
} catch (Exception $e) { echo '<b style="padding-left:30px; color:red;" >'.$e->getMessage().'</b><br/>'; exit; }

echo '<b>Step 4.</b> ... OK<br/>';

echo '<b>Step 5.</b> Insert issues :<br/>';
try {
	foreach ($issues as $k=>$issue) {
		$values = array(ISSUE_KEY=>$k, ISSUE_DATETIME=>$issue['datetime'], ISSUE_CAUSE=>$issue['cause'], ISSUE_STATE=>$issue['state']);
	
		// check cause length (<255)
		if (strlen($values[ISSUE_CAUSE])>254) {
			$values[ISSUE_CAUSE] = substr($values[ISSUE_CAUSE], 0, 250).'...';
		}
	
		// insert new issue
		echo '<i style="padding-left:30px;" >|_ Insert issue '.$k.' ... </i>';
		$issueId = DBHelper::insertIssue($values);
	
		// update reports
		if ($issueId>0) {
			echo '<i>OK</i><br/>';
			
			echo '<i style="padding-left:30px;" >|_ Update reports in ('.implode(', ', $issue['reports']).') with issue id '.$issueId.' ... </i>';
			if (DBHelper::updateReportsIssue($issue['reports'], $issueId)) {
				echo '<i>OK</i><br/>';
				
			} else { echo '<i style="color:red" >FAILED</i><br/>'; }
			
		} else { 
			echo '<i style="color:red" >FAILED</i><br/>';
			echo '<i style="padding-left:30px; color:red; font-weight:bold;" >'.DBHelper::getLastError().'</i><br/>';
		}
	}
	
} catch (Exception $e) { echo '<b style="padding-left:30px; color:red;" >'.$e->getMessage().'</b><br/>'; exit; }

echo '<b>Step 5.</b> ... OK<br/>';

echo '<b>Step 6.</b> Create database triggers ... ';
$res = DBHelper::exec($sql[1], true);

if ($res!=null) {
	echo '<br/><b style="padding-left:30px; color:red;" >'.$res.'</b>';	
	exit;
}

DBHelper::clearStoredResults();

// count triggers
$res = DBHelper::countRows('information_schema.triggers', null, true);
if ($res!=4)
	echo '<b style="color:red" >FAILED</b><br/><b style="padding-left:30px; color:red;" >All triggers not created properly !</b>';
else
	echo 'OK<br/>';


echo '<p><b style="color:green" >UPDATE IS COMPLETE !</b></p>';
