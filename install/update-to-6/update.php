<?php 
define('DIRECT_ACCESS_CHECK', true);

define('BASE_PATH', '../../');


require_once('../updatehelper.php');

require_once(BASE_PATH.'includes/reporthelper.class.php');


$mUpdateHelper = new UpdateHelper();

$mUpdateHelper->begin();

$mUpdateHelper->applySQLUpdateFile();

$mUpdateHelper->exitOnError();

$mUpdateHelper->printStartNextStepMsg("Start updating issues table");

$succeeded = true;

$packages = DbHelper::selectRows(TBL_REPORTS, null, REPORT_PACKAGE_NAME, REPORT_ISSUE.', '.REPORT_PACKAGE_NAME, REPORT_ISSUE, null, false);
foreach ($packages as $package) { 
	$issueId = $package[0];
	$packageName = $package[1]; 
	
	// skip empty package name
	if (strlen($packageName)==0) {
		$mUpdateHelper->printStepMsg("Found empty package name !", true, false);
		continue;
	}
	
	$appId = DbHelper::fetchOrInsertApplication($packageName, ReportHelper::formatPackageName($packageName, true));
	
	if ($appId==-1) {
		$succeeded = false;
	}
	
	$mUpdateHelper->printStepMsg("Inserted application ".$packageName.", id is ".$appId, $appId==-1, false);
	
	$mUpdateHelper->printStepMsg('Update issued #'.$issueId.' with application id #'.$appId, false, false);
	DbHelper::exec('UPDATE '.TBL_ISSUES.' SET '.ISSUE_APP_ID.'='.$appId.' WHERE '.ISSUE_ID.'='.$issueId, false);
}

$mUpdateHelper->printEndStepMsg($succeeded, null, false);


$mUpdateHelper->printEndStepMsg(true, null, true);

$mUpdateHelper->end();