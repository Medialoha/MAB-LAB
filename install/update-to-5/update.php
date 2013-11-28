<?php 
define('DIRECT_ACCESS_CHECK', true);

define('BASE_PATH', '../../');

define('_REPORT_ID', 0);
define('_APP_VERSION_CODE', 1);
define('_APP_VERSION_NAME', 2);
define('_PACKAGE_NAME', 3);
define('_STACK_TRACE', 4);
define('_REPORT_STATE', 5);
define('_CRASH_DATE', 6);

require_once('../updatehelper.php');

$mUpdateHelper = new UpdateHelper();

$mUpdateHelper->begin();

$mUpdateHelper->applySQLUpdateFile();

$mUpdateHelper->exitOnError();

$mUpdateHelper->printStartNextStepMsg('Populate increments table');

// populate table increments
for ($i=0; $i<=180; ++$i) {
	$mUpdateHelper->execSQL('INSERT INTO '.DBHelper::getTblName(TBL_INCREMENTS).'('.INC_VALUE.') VALUES ('.$i.');');
}

// recreate issue keys with new algorithm
$mUpdateHelper->printStartNextStepMsg('Get reports');
$reports = DBHelper::selectRows(TBL_REPORTS, null, null, 'report_id, app_version_code, app_version_name, package_name, stack_trace, report_state, user_crash_date', null, null, false);

$mUpdateHelper->printStartNextStepMsg('Walk through reports array to create issues');

$issues = array();

try {
	foreach ($reports as $report) {

		$arr = explode("\n", $report[_STACK_TRACE]);
		$cause = $arr[0];

		// create a key to identify the issue from all reports
		$k = DBHelper::getReportIssueKey($report);
		if (!array_key_exists($k, $issues)) {
			$mUpdateHelper->printStepMsg('Create new issue '.$k);
				
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

} catch (Exception $e) { $mUpdateHelper->printStepMsg($e->getMessage(), true);  }

$mUpdateHelper->exitOnError();

$mUpdateHelper->printStartNextStepMsg('Insert issues');

try {
	foreach ($issues as $k=>$issue) {
		$values = array(ISSUE_KEY=>$k, ISSUE_DATETIME=>$issue['datetime'], ISSUE_CAUSE=>$issue['cause'], ISSUE_STATE=>$issue['state']);

		// check cause length (<255)
		if (strlen($values[ISSUE_CAUSE])>254) {
			$values[ISSUE_CAUSE] = substr($values[ISSUE_CAUSE], 0, 250).'...';
		}

		// insert new issue
		$mUpdateHelper->printStartSubStepMsg('Insert issue '.$k);
		
		$issueId = DBHelper::insertIssue($values);

		// update reports
		if ($issueId>0) {
			$mUpdateHelper->printEndSubStepMsg(true);
				
			$mUpdateHelper->printStartSubStepMsg('Update reports in ('.implode(', ', $issue['reports']).') with issue id '.$issueId);
			if (DBHelper::updateReportsIssue($issue['reports'], $issueId)) {
				$mUpdateHelper->printEndSubStepMsg(true);

			} else { $mUpdateHelper->printEndSubStepMsg(false); }
				
		} else { $mUpdateHelper->printEndSubStepMsg(false, DBHelper::getLastError()); }
	}

} catch (Exception $e) { $mUpdateHelper->printStepMsg($e->getMessage(), true); }

$mUpdateHelper->printEndStepMsg(true, null, true);

$mUpdateHelper->end();