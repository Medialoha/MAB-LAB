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

// get reports preferences
$cfg = CfgHelper::getInstance();

// restore from session
$opts = null;
if (isset($_SESSION['issueListOpts'])) {
	$opts = $_SESSION['issueListOpts'];
	
} else { $opts = null; }

// overload if passed by request or no options set before
if (isset($_GET['showArchived']) || $opts==null) {
	$opts = array(
			'showArchived'=>Helper::getHTTPGetBooleanValue('showArchived', true),
			'state'=>Helper::getHTTPGetStringValue('state', null),
			'priority'=>intval(Helper::getHTTPGetStringValue('priority', '-1')),
			'package'=>Helper::getHTTPGetStringValue('package', null),
			'version'=>Helper::getHTTPGetStringValue('version', null),
			'android'=>Helper::getHTTPGetStringValue('android', null),
			'sortCol'=>Helper::getHTTPGetStringValue('sortCol', ISSUE_DATETIME),
			'sortOrder'=>Helper::getHTTPGetStringValue('sortOrder', 'DESC'),
			'limit'=>intval(Helper::getHTTPGetStringValue('limit', '10')),
			'start'=>intval(Helper::getHTTPGetStringValue('start', '0'))
	);

	// update session
	$_SESSION['issueListOpts'] = $opts;
}

// build where clauses
$where = array();

if (!$opts['showArchived']) {
	$where[] = ISSUE_STATE.'<>'.ISSUE_STATE_ARCHIVED;
}

if (!empty($opts['state'])) {
	$where[] = ISSUE_STATE.' IN ('.$opts['state'].')';
}

if ($opts['priority']>=0) {
	$where[] = ISSUE_PRIORITY.'='.$opts['priority'];
}

if ($opts['package']!=null) {
	$where[] = REPORT_PACKAGE_NAME.'="'.$opts['package'].'"';
}

if ($opts['version']!=null) {
	$tmp = explode('|', $opts['version']);
	$where[] = REPORT_VERSION_NAME.'="'.$tmp[0].'" AND '.REPORT_VERSION_CODE.'="'.$tmp[1].'"';
}

if ($opts['android']!=null) {
	$where[] = REPORT_ANDROID_VERSION.'="'.$opts['android'].'"';
}

$where = implode(' AND ', $where);
$orderBy = $opts['sortCol'].' '.$opts['sortOrder'];
$limit = $opts['start'].', '.$opts['limit'];

// get issues
$issues = DBHelper::fetchIssues($where, $orderBy, ISSUE_ID, $limit);
$tmp = DBHelper::fetchIssues($where, $orderBy, null, null, "COUNT(DISTINCT issue_id)");
$totalRows = $tmp[0][0];

// define select options array
$sortCols = array(
		REPORT_CRASH_DATE=>'Crash date',
		REPORT_PACKAGE_NAME=>'Package name',
		REPORT_VERSION_NAME=>'Version name',
		REPORT_VERSION_CODE=>'Version code',
		REPORT_ANDROID_VERSION=>'Android version'
);

$limits = array(5, 10, 15, 20, 50, 100, 200);

$packages = DBHelper::selectRows(TBL_REPORTS, null, REPORT_PACKAGE_NAME.' ASC', REPORT_PACKAGE_NAME, REPORT_PACKAGE_NAME, null, false);
$versions = DBHelper::selectRows(TBL_REPORTS, null, REPORT_VERSION_CODE.' DESC', REPORT_VERSION_NAME.','.REPORT_VERSION_CODE, REPORT_VERSION_NAME.','.REPORT_VERSION_CODE, null, false);
$androidVersions = DBHelper::selectRows(TBL_REPORTS, null, REPORT_ANDROID_VERSION.' ASC', REPORT_ANDROID_VERSION, REPORT_ANDROID_VERSION, null, false);

$actionPriority = new IssuePriority(IssuePriority::CRITICAL);
?>
<style>
.tooltip-inner {
	max-width:700px;	
	text-align:left;	
	font-size:15px;
}
</style>

<div class="accordion" id="accordion2" >
	<div class="accordion-group">
		<div class="accordion-heading">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
				<i class="icon-filter" ></i>&nbsp;&nbsp;Filter & Sort Options</a>
		</div>
		<div id="collapseOne" class="accordion-body collapse">
			<div class="accordion-inner">
				<form name="filterForm" action="index.php" method="get" class="form-horizontal" >
					<input type="hidden" name="p" value="<?php echo PAGE_ID_ISSUES; ?>" />
					<input type="hidden" id="start" name="start" value="<?php echo $opts['start']; ?>" />
								
					<div class="control-group">
						<label class="control-label" for="showArchived" >Archived issues</label>
						<div class="controls" >
						  <select id="showArchived" name="showArchived" style="width:80px;" >
				    		<option value="1" <?php if ($opts['showArchived']) echo 'selected="selected"'; ?> >Show</option>
				    		<option value="0" <?php if (!$opts['showArchived']) echo 'selected="selected"'; ?> >Hide</option>
				    	</select>
				    	
				    	<label class="control-label-inline" for="state" >State</label>
						  <select id="state" name="state" style="width:110px;" >
				    		<option value="" <?php if ($opts['state']=='') echo 'selected="selected"'; ?> >-----------</option>
				    		<option value="1" <?php if ($opts['state']=='1') echo 'selected="selected"'; ?> >New</option>
				    		<option value="1,2" <?php if ($opts['state']=='1,2') echo 'selected="selected"'; ?> >Not fixed (new or opened)</option>
				    		<option value="3" <?php if ($opts['state']=='3') echo 'selected="selected"'; ?> >Closed</option>
				    		<option value="0" <?php if ($opts['state']=='0') echo 'selected="selected"'; ?> >Archived</option>
				    	</select>
				    	
				    	<label class="control-label-inline" for="priority" >Priority</label>
						  <select id="priority" name="priority" style="width:110px;" >
						  	<?php $actionPriority = new IssuePriority(IssuePriority::CRITICAL); ?>
				    		<option value="-1" <?php if ($opts['priority']==0) echo 'selected="selected"'; ?> >-----------</option>
				    		<option value="<?php echo $actionPriority->getId(); ?>" <?php if ($opts['priority']==IssuePriority::CRITICAL) echo 'selected="selected"'; ?> ><?php echo $actionPriority->getName(); $actionPriority->setPriority(IssuePriority::NORMAL); ?></option>
				    		<option value="<?php echo $actionPriority->getId(); ?>" <?php if ($opts['priority']==IssuePriority::NORMAL) echo 'selected="selected"'; ?> ><?php echo $actionPriority->getName(); $actionPriority->setPriority(IssuePriority::LOW); ?></option>
				    		<option value="<?php echo $actionPriority->getId(); ?>" <?php if ($opts['priority']==IssuePriority::LOW) echo 'selected="selected"'; ?> ><?php echo $actionPriority->getName(); ?></option>
				    	</select>
						</div>
					</div>
					
					<div class="control-group" >
						<label class="control-label" for="package" >Package</label>
						<div class="controls" >
							<select id="package" name="package" style="width:160px;" >
								<option value="" >----------------</option>
							<?php foreach ($packages as $v) { ?>
								<option value="<?php echo $v[0]; ?>" <?php if ($v[0]==$opts['package']) echo 'selected="selected"'; ?> >
									<?php echo ReportHelper::formatPackageName($v[0], $cfg->shrinkPackageName()); ?></option>
							<?php } ?>
							</select>
						
							<label class="control-label-inline" for="version" >Version</label>
							<select id="version" name="version" style="width:160px;" >
								<option value="" >----------------</option>
							<?php foreach ($versions as $v) { ?>
								<option value="<?php echo $v[0].'|'.$v[1]; ?>" <?php if ($v[0].'|'.$v[1]==$opts['version']) echo 'selected="selected"'; ?> ><?php echo $v[0].' #'.$v[1]; ?></option>
							<?php } ?>
							</select>
						
							<label class="control-label-inline" for="android" >Android</label>
							<select id="android" name="android" style="width:80px;" >
								<option value="" >-----</option>
							<?php foreach ($androidVersions as $v) { ?>
								<option value="<?php echo $v[0]; ?>" <?php if ($v[0]==$opts['android']) echo 'selected="selected"'; ?> ><?php echo $v[0]; ?></option>
							<?php } ?>
							</select>
						</div>
					</div>
					
					<div class="control-group">
						<label class="control-label" for="sortBy" >Sort by</label>
						<div class="controls" >
						  <select id="sortCol" name="sortCol" style="width:160px;" >
							<?php foreach ($sortCols as $value=>$text) { ?>
								<option value="<?php echo $value; ?>" <?php if ($value==$opts['sortCol']) echo 'selected="selected"'; ?> ><?php echo $text; ?></option>
							<?php } ?>
				    	</select>
				    	<select id="sortOrder" name="sortOrder" style="width:140px;" >
				    		<option value="ASC" <?php if ($opts['sortOrder']=='ASC') echo 'selected="selected"'; ?> >Ascending</option>
				    		<option value="DESC" <?php if ($opts['sortOrder']=='DESC') echo 'selected="selected"'; ?> >Descending</option>
				    	</select>

				    	<label class="control-label-inline" for="limit" >Page rows</label>
							<select id="limit" name="limit" onchange="$('#start').val(0);"  style="width:110px;" >
								<?php foreach ($limits as $value) { ?>
									<option value="<?php echo $value; ?>" <?php if ($value==$opts['limit']) echo 'selected="selected"'; ?> ><?php echo $value; ?>&nbsp;rows</option>
								<?php } ?>
							</select>
						</div>
						<br/>
						<div class="control-group" style="text-align:right;" >
				    	<button type="submit" class="btn" ><i class="icon-ok" ></i>&nbsp;&nbsp;Apply</button>
				    </div>
				  </fieldset>
				</form>
			</div>
		</div>
	</div>
</div>

<br/>

<table class="table table-condensed table-bordered issues-tbl" >
<thead>
	<td style="width:12px;" ><input type="checkbox" onclick="toggleCheckboxes(this);" /></td>
	<th style="width:160px" >Date</th>
	<th>Application</th>
	<th style="width:35px" ><i class="icon-bullhorn" ></i></th>
	<th style="width:35px" ><i class="icon-file" ></i></th>
	
	<th style="width:65px;" >
		<div class="btn-group">
		  <a class="btn btn-inverse btn-small dropdown-toggle" data-toggle="dropdown" href="#" >
		    <i class="icon-cog icon-white" ></i>&nbsp;&nbsp;<span class="caret"></span>
		  </a>
			  
		  <ul class="dropdown-menu" >
		  	<li class="dropdown-submenu pull-left text-left" >
  				<a tabindex="-1" href="#" ><i class="icon-signal" ></i>&nbsp;Priority</a>		
  					<ul class="dropdown-menu" >
							<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::CRITICAL; ?>)" >
								<?php $actionPriority->setPriority(IssuePriority::CRITICAL); echo $actionPriority->getLabel(false); echo '&nbsp;&nbsp;'; echo $actionPriority->getName(); ?></a></li>
							<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::NORMAL; ?>)" >
								<?php $actionPriority->setPriority(IssuePriority::NORMAL); echo $actionPriority->getLabel(false); echo '&nbsp;&nbsp;'; echo $actionPriority->getName(); ?></a></li>
							<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::LOW; ?>)" >
								<?php $actionPriority->setPriority(IssuePriority::LOW); echo $actionPriority->getLabel(false); echo '&nbsp;&nbsp;'; echo $actionPriority->getName(); ?></a></li>
						</ul>
  			</li>

   			<li class="text-left" ><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_CLOSED; ?>)" ><i class="icon-check" ></i>&nbsp;Close selected</a></li>
   			<li class="text-left" ><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_ARCHIVED; ?>)" ><i class="icon-folder-open" ></i>&nbsp;Archive selected</a></li>
   			<li class="text-left" ><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_VIEWED; ?>)" ><i class="icon-refresh" ></i>&nbsp;Re-open selected</a></li>
    		<li class="text-left" ><a href="javascript:delIssues()" ><i class="icon-trash" ></i>&nbsp;Delete selected</a></li>
		  </ul>
		</div>
	</th>
</thead>
<tbody>
<?php 
	if (empty($issues)) {
		?><tr><td colspan="6" class="muted" >No issues recorded yet...</td></tr><?php
		 
	} else {
		foreach ($issues as $issueArr) { 
			$issue = Issue::createFromArray($issueArr); 
			$lastReport = $issue->getLastReport();
			$priority = $issue->getPriority();
			$reports = $issue->getReports();
?>
			
	<tr issue="<?php echo $issue->issue_id; ?>" class="<?php echo $issue->isArchived()?'archived':($issue->isOpen()?'':'closed'); ?>" >
		<td rowspan="2" ><input type="checkbox" name="itemChecked" value="<?php echo $issue->issue_id; ?>" /></td>
		<td colspan="4" class="cause" >
			<?php 
				$cause = explode(':', Helper::shrinkString($issue->issue_cause, 175));
				$causelen = strlen($issue->issue_cause);
				$cause[0] = '<b>'.$cause[0].'</b>';	?>
					
			<a href="<?php echo $lastReport->report_id>0?"javascript:showReportDetails('".$lastReport->report_id."')":"#"; ?>" 
					<?php if ($causelen>175) { echo 'title="'.$issue->issue_cause.'" rel="tooltip" data-toggle="tooltip" data-placement="bottom" data-html="true"'; } ?> >
				<?php echo implode(': ', $cause); ?>
			</a>
		</td>
		<td rowspan="2" >
			<div class="btn-group">
			  <button class="btn btn-small" onclick="showIssueReportsTbl('<?php echo $issue->issue_id; ?>')" ><i class="icon-list-alt" ></i></button>
  			<button class="btn btn-small dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
  			<ul class="dropdown-menu">
    			<?php if (!$issue->isArchived()) : ?>
  					<li class="dropdown-submenu pull-left text-left" >
  						<a tabindex="-1" href="#" ><i class="icon-signal" ></i>&nbsp;Priority</a>
  						
  						<ul class="dropdown-menu" >
								<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::CRITICAL; ?>, <?php echo $issue->issue_id; ?>)" >
										<?php $actionPriority->setPriority(IssuePriority::CRITICAL); echo $actionPriority->getLabel(false); echo '&nbsp;&nbsp;'; echo $actionPriority->getName(); ?></a></li>
								<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::NORMAL; ?>, <?php echo $issue->issue_id; ?>)" >
										<?php $actionPriority->setPriority(IssuePriority::NORMAL); echo $actionPriority->getLabel(false); echo '&nbsp;&nbsp;'; echo $actionPriority->getName(); ?></a></li>
								<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::LOW; ?>, <?php echo $issue->issue_id; ?>)" >
										<?php $actionPriority->setPriority(IssuePriority::LOW); echo $actionPriority->getLabel(false); echo '&nbsp;&nbsp;'; echo $actionPriority->getName(); ?></a></li>
							</ul>
  					</li>
    			
	    			<?php if ($issue->isOpen()) : ?>
	    				<li><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_CLOSED; ?>, <?php echo $issue->issue_id; ?>)" ><i class="icon-check" ></i>&nbsp;Resolved</a></li>
	    			<?php else: ?>
	    				<li><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_VIEWED; ?>, <?php echo $issue->issue_id; ?>)" ><i class="icon-refresh" ></i>&nbsp;Re-open</a></li>
	    			<?php endif; ?>

	    			<li><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_ARCHIVED; ?>, <?php echo $issue->issue_id; ?>)" ><i class="icon-folder-open" ></i>&nbsp;Archive</a></li>
    			<?php endif; ?>
    			<li><a href="javascript:delIssues('<?php echo $issue->issue_id; ?>')" ><i class="icon-trash" ></i>&nbsp;Delete</a></li>
  			</ul>
			</div>
		</td>
	</tr>
	<tr issue="<?php echo $issue->issue_id; ?>" class="<?php echo $issue->isArchived()?'archived':($issue->isOpen()?'':'closed'); ?>" >	
		<td class="datetime" >
			<?php	echo Helper::formatDate($issue->issue_datetime, $cfg->getDateFormat(), $issue->isNew()); ?>
		</td>
		<td><?php echo $issue->getApplicationDesc(); ?></td>
		<td class="priority" ><?php echo $priority->getLabel(false); ?></td>		
		<td id="count<?php echo $issue->issue_id; ?>" class="count" ><?php echo $issue->getReportsCount(); ?></td>
	</tr>
	<tr issue="<?php echo $issue->issue_id; ?>" class="<?php echo $issue->isArchived()?'archived':($issue->isOpen()?'':'closed'); ?>" >
		<td colspan="6" style="background:#f9f9f9; text-align:center;" >
			<div id="reportsTbl<?php echo $issue->issue_id; ?>" style="display:none; margin:15px 5px;" >
				<table id="reportsTbl<?php echo $issue->issue_id; ?>" class="table table-condensed table-bordered table-hover reports-tbl" >
				<thead>
					<tr>
						<th colspan="6" style="text-align:left" ><?php echo $issue->getPackageName(); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($reports as $r) { ?>
					<tr id="reportRow<?php echo $r->report_id; ?>" class="report" >
						<td style="width:25px; text-align:center;" ><i class="icon-file" ></i></td>
						<td class="datetime" >
							<?php echo Helper::formatDate($r->user_crash_date, $cfg->getDateFormat(), $r->isNew());	?>
						</td>
						<td class="android-version" ><?php echo $r->android_version; ?></td>
						<td class="phone-model" ><?php echo $r->phone_model; ?></td>
						<td class="user-comment" ><i class="muted" ><?php echo empty($r->user_comment)?'No user comment':$r->user_comment; ?></i></td>
						<td style="text-align:center; width:35px; text-decoration:none;" >
							<div class="btn-group">
			  				<button class="btn btn-small" onclick="javascript:showReportDetails('<?php echo $r->report_id; ?>')" ><i class="icon-eye-open" ></i></button>
  							<button class="btn btn-small dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
			  
		  					<ul class="dropdown-menu" >
									<li class="text-left" ><a href="javascript:delReports(<?php echo $r->report_id; ?>, <?php echo $issue->issue_id; ?>);" ><i class="icon-trash" ></i>&nbsp;Delete</a></li>
								</ul>
							</div>
						</td>
					</tr>
				<?php } ?>
				</tbody>
				</table>
			</div>
		</td>
	</tr>
<?php 
		} 
	} 
?>
</tbody>
</table>

<?php // create pagination
$nbPage = $totalRows/$opts['limit'];
$currentPage = ($opts['start']/$opts['limit'])+1;
?>
<div class="pagination pagination-right" >
  <ul>
    <li <?php if ($currentPage==1) echo 'class="disabled"'; ?> >
    	<a href="#" onclick="<?php if ($currentPage>1) echo 'gotoPage('.(($currentPage-2)*$opts['limit']).')'; ?>" >Prev</a>
    </li>
    
    <?php for ($page=0; $page<$nbPage; ++$page) { ?>
    	<li <?php if (($page+1)==$currentPage) echo 'class="active"'; ?> >
    		<a href="#" onclick="gotoPage(<?php echo ($page*$opts['limit']); ?>);" ><?php echo $page+1; ?></a>
    	</li>
    <?php } ?>
    
    <li <?php if ($currentPage>=$nbPage) echo 'class="disabled"'; ?> >
    	<a href="#" onclick="<?php if ($currentPage<$nbPage) echo 'gotoPage('.($currentPage*$opts['limit']).')'; ?>" >Next</a>
    </li>
  </ul>
</div>

<div id="reportDialog" class="modal hide fade" style="width:1000px; margin-left:-500px; height:700px;" ></div>

<script type="text/javascript" src="assets/functions-issues.js" ></script>
<script type="text/javascript" >
var HOME_PAGE = false;

$(function(){
	$('a[rel=tooltip]').tooltip();
});
</script>