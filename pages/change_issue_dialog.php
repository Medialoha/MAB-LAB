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

$reportIdsArr = explode(',', $reportIds);
$report = DbHelper::fetchReport($reportIdsArr[0]);

$currentIssue = DbHelper::fetchIssue($report->report_issue);

$issuesArr = DbHelper::fetchIssues(ISSUE_APP_ID.'='.$currentIssue->issue_app_id.
																		' AND '.ISSUE_ID.'<>'.$currentIssue->issue_id.
																		' AND '.ISSUE_STATE.' NOT IN ('.IssueState::STATE_ARCHIVED.','.IssueState::STATE_CLOSED.')', ISSUE_DATETIME.' DESC');
?>
<div class="modal-body" style="clear:both;" >	
	<form id="switchIssueForm" class="form-horizontal" action="<?php echo $mNavCtl->buildActionURL('issues', null, null); ?>" method="post" >
		<input type="hidden" name="a" value="setReportsIssue" />
		<input type="hidden" name="reportIds" value="<?php echo $reportIds; ?>" />
		<input type="hidden" name="currentIssueId" value="<?php echo $currentIssue->issue_id; ?>" />
		
		<div class="control-group">
    	<label class="control-label" for="" >Current issue</label>
    	<div class="controls">
				<input type="text" value="<?php echo $currentIssue->issue_cause; ?>" class="input-xxlarge" disabled="disabled" />
    	</div>
  	</div>
  	
		<div class="control-group" >
    	<label class="control-label" for="" >New issue</label>
    	<div class="controls">
				<select name="newIssueId" class="input-xxlarge" >
				<?php foreach($issuesArr as $i) { ?>
					<option style="padding:7px;" value="<?php echo $i[ISSUE_ID]; ?>" ><?php echo IssueHelper::formatCause($i[ISSUE_CAUSE]); ?></option>
				<?php } ?>
				</select>
    	</div>
    	
  	</div>
	</form>
</div>


<div class="modal-footer">
  <a href="javascript:closeDialog()" class="btn" >Close</a>
 	
  <a href="#" onclick="$('form#switchIssueForm').submit();" class="btn btn-success" ><i class="icon-ok icon-white" ></i>&nbsp;Save changes</a>
</div>