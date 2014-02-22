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

switch ($action) {

//////// GET ISSUE
	case 'getissue' :
			$issueId = @$_POST['issueId'];
			if (!empty($issueId)) {
				require_once('pages/issue_details_dialog.php');
		
			} else { echo 'Issue id is not valid !'; }
		break;
	
//////// GET REPORT
	case 'getreport' :
			$reportId = @$_POST['reportId'];
			if (!empty($reportId)) {
				if (isset($_POST['issueFormat'])) {
					require_once('pages/issue_details_dialog_report_details.php');
						
				} else { require_once('pages/report_details_dialog.php'); }
		
			} else { echo 'Report id is not valid !'; }
		break;
	
//////// UPDATE ISSUES STATE
	case 'setissuesstate' :
			$issueIds = @$_POST['issueIds'];
			$state = @$_POST['state'];
				
			if (ReportHelper::checkState($state)) {
				if (!empty($issueIds)) {
					if (DBHelper::updateIssuesState(explode(',', $issueIds), $state)) {
						echo 'O:';
						
						$state = new IssueState($state);
						$priority = new IssuePriority();

						$label = $state->getLabel(true);
						
						$issueIds = explode(',', $issueIds); $sep = '';
						foreach ($issueIds as $issueId) {
							$res = DbHelper::selectRow(TBL_ISSUES, ISSUE_ID.'='.$issueId, ISSUE_PRIORITY);
							$priority->setPriority($res[0][0]);
							
							echo $sep, $label, '|', IssueHelper::getHiliteBgColorClass($state, $priority), '|', strtolower($state->getName());
								
							$sep = '||';
						}
		
					} else { echo "K:Error occured while trying to update issue(s) state :\n\n".DBHelper::getLastError(); }
						
				} else { echo 'K:Issue id(s) is not valid !'; }
		
			} else { echo 'K:This state is not valid and can\'t be applyed !'; }
		break;
	
//////// UPDATE ISSUES PRIORITY
	case 'setissuespriority' :
			$issueIds = @$_POST['issueIds'];
			$priority = new IssuePriority(@$_POST['priority']);
		
			if (!empty($issueIds)) {
				DBHelper::updateIssuesPriority(explode(',', $issueIds), $priority->getId());
				$label = $priority->getLabel(false);
				
				echo 'O:';
				
				$issueIds = explode(',', $issueIds); $sep = '';
				foreach ($issueIds as $issueId) {					
					$res = DbHelper::selectRow(TBL_ISSUES, ISSUE_ID.'='.$issueId, ISSUE_STATE);
					echo $sep, $label, '|', IssueHelper::getHiliteBgColorClass(new IssueState($res[0][0]), $priority);
					
					$sep = '||';
				}
					
			} else { echo 'K:Issue id(s) is not valid !'; }
		break;
	
		//////// UPDATE REPORTS STATE
		// 	case 'setstate' :
		// 			$reportIds = @$_POST['reportIds'];
		// 			$state = @$_POST['state'];
			
		// 			if (ReportHelper::checkState($state)) {
		// 				if (!empty($reportIds)) {
		// 					$reportIds = explode(',', $reportIds);
		// 					DBHelper::updateReportsState($reportIds, $state);
			
		// 					echo 'Report(s) state updated to '.ReportHelper::getStateTitle($state).' with success !';
			
		// 				} else { echo 'Report id(s) is not valid !'; }
	
		// 			} else { echo 'This state is not valid and can\'t be applyed !'; }
		// 		break;
	
//////// DEL ISSUES
	case 'delissues' :
			$issueIds = @$_POST['issueIds'];
			if (!empty($issueIds)) {
				$reportIds = explode(',', $issueIds);
				DBHelper::deleteIssues($issueIds);
		
				echo 'O:Issue(s) deleted with success !';
		
			} else { echo 'K:Issue id(s) is not valid !'; }
		break;
	
//////// DEL REPORTS
	case 'delreports' :
			$reportIds = @$_POST['reportIds'];
			if (!empty($reportIds)) {
				$reportIds = explode(',', $reportIds);
				if (DBHelper::deleteReports($reportIds))
					echo 'O:Report(s) deleted with success !';
				else
					echo 'K:Error occured while deleting report(s) !';
		
			} else { echo 'K:Report id(s) is not valid !'; }
		break;

//////// GET CHANGE ISSUE DIALOG
	case 'getchangeissuedlg' :
			$reportIds = @$_POST['reportIds'];
			if (!empty($reportIds)) {
				require_once('pages/change_issue_dialog.php');
		
			} else { echo 'You missed report ids !'; }
		break;

//////// SET ISSUE FOR REPORTS
	case 'setReportsIssue' :
			$reportIds = @$_POST['reportIds'];
			$currentIssueId = @$_POST['currentIssueId'];
			$newIssueId = @$_POST['newIssueId'];

			if (!empty($reportIds) && !empty($currentIssueId) && !empty($newIssueId)) {				
				// update reports
				DbHelper::exec('UPDATE '.TBL_REPORTS.' SET '.REPORT_ISSUE.'='.$newIssueId.' WHERE '.REPORT_ID.' IN ('.$reportIds.')');
				
				// delete issue if no more reports attached
				$count = DbHelper::countRows(TBL_REPORTS, REPORT_ISSUE.'='.$currentIssueId);
				if ($count==0)
					DbHelper::deleteIssues($currentIssueId);
				
			} else { $error = "Invalid parameters !"; }
			
			if ($error==null) {
				Helper::pushAlert(ALERT_SUCCESS, 'Reports switched with success !');
					
			} else { Helper::pushAlert(ALERT_ERROR, $error); }
		
			header('Location:index.php?p='.PAGE_ID_ISSUES);
		break;

//////// GET ISSUES TABLE
	case 'getIssuesTbl' :
			// get reports preferences
			$cfg = CfgHelper::getInstance();
			
			$mSelectedAppId = Helper::getHTTPGetBooleanValue('app', -1);
		
			require_once('pages/issues_table.inc.php');
		break;

//////// UPDATE MILESTONE
	case 'updateMilestone' :
			$values = array();
			foreach ($_POST as $k=>$v) {
				if (strncmp($k, 'mile_', 5)==0) {
					$values[$k] = $v;
				}				
			}
			
			$error = DbHelper::exec('REPLACE INTO '.TBL_MILESTONES.' '.DbHelper::convertArrToInsertValues($values));
			if ($error==null) {
				Helper::pushAlert(ALERT_SUCCESS, 'Milestone updated with success !');
				
			} else { Helper::pushAlert(ALERT_ERROR, 'An error occured while trying to insert/update the milestone !'); }
		
			header('Location:index.php?p='.PAGE_ID_ISSUES.'&n=mil');
		break;

//////// UPDATE ISSUE DETAILS
	case 'updateIssueDetails' :			
			$error = DbHelper::exec('UPDATE '.TBL_ISSUES.' SET '.ISSUE_MILESTONE_ID.'='.$_POST['new_milestone'].', '.ISSUE_COMMENT.'="'.$_POST['new_comment'].'" WHERE '.ISSUE_ID.'='.$_POST['issue_id']);
			if ($error==null) {
				Helper::pushAlert(ALERT_SUCCESS, 'Issue updated with success !');
				
			} else { Helper::pushAlert(ALERT_ERROR, 'An error occured while updating the issue !'); }
		
			header('Location:index.php?p='.PAGE_ID_ISSUES);
		break;

//////// DELETE MILESTONE
	case 'delMilestone' :
			$error = null;
		
			if (isset($_POST['mId']) && $_POST['mId']>0) {
				
				if (DbHelper::exec('DELETE FROM '.TBL_MILESTONES.' WHERE '.MILE_ID.'='.$_POST['mId'])!=null) {
					$error = 'An error occured while deleting milestone !';
				}
				
			} else { $error = 'Invalid milestone id !'; }
			
			if ($error==null) {
				Helper::pushAlert(ALERT_SUCCESS, 'Milestone deleted with success !');
				
			} else { Helper::pushAlert(ALERT_ERROR, $error); }
		
			header('Location:index.php?p='.PAGE_ID_ISSUES.'&n=mil');
		break;


	default : die("UNHANDLED ACTION REQUESTED !");
}