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

// filter options, restore from session
$filterOpts = IssueHelper::getFilterOptsArr();

// define select options array
$filterSortCols = array(
	'Issue state&nbsp;&nbsp;&nbsp;&nbsp;&darr;',
	'Issue state&nbsp;&nbsp;&nbsp;&nbsp;&uarr;',
	'Issue creation date&nbsp;&nbsp;&nbsp;&nbsp;&darr;',
	'Issue creation date&nbsp;&nbsp;&nbsp;&nbsp;&uarr;'
);

$filterLimits = array(5, 10, 15, 20, 50, 100, 200);

$mSelectedAppId = $mNavCtl->getParam('app', '-1');
?>
<style> .tooltip-inner { max-width:700px; text-align:left; font-size:15px; } </style>

<div id="filterOpts" class="collapse form-horizontal" >				
	<div class="control-group" >
		<label class="control-label" for="showArchived" >Archived issues</label>
		<div class="controls" >
		  <select id="showArchived" name="showArchived" style="width:80px;" >
	    		<option value="1" <?php if ($filterOpts['showArchived']) echo 'selected="selected"'; ?> >Show</option>
	    		<option value="0" <?php if (!$filterOpts['showArchived']) echo 'selected="selected"'; ?> >Hide</option>
	    	</select>
	    	
	    	<label class="control-label-inline" for="state" >State</label>
			  <select id="state" name="state" style="width:110px;" >
	    		<option value="-1" <?php if ($filterOpts['state']=='') echo 'selected="selected"'; ?> >-----------</option>
	    		<option value="<?php echo IssueState::STATE_NEW; ?>" <?php if ($filterOpts['state']==IssueState::STATE_NEW) echo 'selected="selected"'; ?> >New</option>
	    		<option value="<?php echo IssueState::STATE_NEW,",",IssueState::STATE_VIEWED; ?>" <?php if ($filterOpts['state']==IssueState::STATE_NEW.",".IssueState::STATE_VIEWED) echo 'selected="selected"'; ?> >Not fixed (new or opened)</option>
	    		<option value="<?php echo IssueState::STATE_TESTING; ?>" <?php if ($filterOpts['state']==IssueState::STATE_TESTING) echo 'selected="selected"'; ?> >Testing</option>
	    		<option value="<?php echo IssueState::STATE_CLOSED; ?>" <?php if ($filterOpts['state']==IssueState::STATE_CLOSED) echo 'selected="selected"'; ?> >Closed</option>
	    		<option value="<?php echo IssueState::STATE_ARCHIVED; ?>" <?php if ($filterOpts['state']==IssueState::STATE_ARCHIVED) echo 'selected="selected"'; ?> >Archived</option>
	    	</select>
	    	
	    	<label class="control-label-inline" for="priority" >Priority</label>
			  <select id="priority" name="priority" style="width:110px;" >
		  	<?php $p = new IssuePriority(IssuePriority::CRITICAL); ?>
	    		<option value="-1" <?php if ($filterOpts['priority']==0) echo 'selected="selected"'; ?> >-----------</option>
	    		<option value="<?php echo $p->getId(); ?>" <?php if ($filterOpts['priority']==IssuePriority::CRITICAL) echo 'selected="selected"'; ?> ><?php echo $p->getName(); $p->setPriority(IssuePriority::NORMAL); ?></option>
	    		<option value="<?php echo $p->getId(); ?>" <?php if ($filterOpts['priority']==IssuePriority::NORMAL) echo 'selected="selected"'; ?> ><?php echo $p->getName(); $p->setPriority(IssuePriority::LOW); ?></option>
	    		<option value="<?php echo $p->getId(); ?>" <?php if ($filterOpts['priority']==IssuePriority::LOW) echo 'selected="selected"'; ?> ><?php echo $p->getName(); ?></option>
	    	</select>
			</div>
		</div>
		
		<div class="control-group" >
			<label class="control-label" >Milestone</label>
			<div class="controls" >
				<select id="milestone" >
					<option value="-1" >-----------</option>
					<?php 
						$arr = DbHelper::selectRows(TBL_MILESTONES, null, MILE_DUEDATE.' ASC', MILE_ID.', '.MILE_NAME);												
						foreach ($arr as $mile) {
							?><option value="<?php echo $mile->mile_id; ?>" <?php if ($filterOpts['mId']==$mile->mile_id) echo 'selected="selected"'; ?> ><?php echo $mile->mile_name; ?></option><?php 
						}
					?>
				</select>
			</div>
		</div>
		
		<div class="control-group" >
			<label class="control-label" >Application version</label>
			<div class="controls" >
				<select id="versionName" >
					<option value="-1" >-----------</option>
					<?php 
					    $versionWhere = null;
						
						$versionJoin = " JOIN ".TBL_ISSUES." ON ".TBL_ISSUES.".".ISSUE_ID." = ".TBL_REPORTS.".".REPORT_ISSUE;
						
						if(isset($mSelectedAppId) && intval($mSelectedAppId) > 0) {
							$versionJoin .= " AND ".TBL_ISSUES.".".ISSUE_APP_ID." = ".intval($mSelectedAppId);
						}
						
						$versionArr = DbHelper::selectRows(TBL_REPORTS.$versionJoin, $versionWhere, 'size DESC', TBL_REPORTS.'.'.REPORT_VERSION_NAME.', '.TBL_REPORTS.'.'.REPORT_VERSION_CODE.', COUNT('.TBL_REPORTS.'.'.REPORT_VERSION_NAME.') as size', TBL_REPORTS.'.'.REPORT_VERSION_NAME, 100, false) ;										
						foreach ($versionArr as $ver) {
							?><option value="<?php echo $ver[REPORT_VERSION_NAME]; ?>" <?php if ($filterOpts['versionName']==$ver[REPORT_VERSION_NAME]) echo 'selected="selected"'; ?> ><?php echo $ver[REPORT_VERSION_NAME] ?> (<?php echo $ver[REPORT_VERSION_CODE] ?>)</option><?php 
						}
					?>
				</select>
			</div>
		</div>
		
		<div class="control-group" >
			<label class="control-label" >Android version</label>
			<div class="controls" >
				<select id="androidVersion" >
					<option value="-1" >-----------</option>
					<?php 
					    $versionWhere = null;
						
						$reporsJoin = " JOIN ".TBL_ISSUES." ON ".TBL_ISSUES.".".ISSUE_ID." = ".TBL_REPORTS.".".REPORT_ISSUE;
						
						if(isset($mSelectedAppId) && intval($mSelectedAppId) > 0) {
							$reporsJoin .= " AND ".TBL_ISSUES.".".ISSUE_APP_ID." = ".intval($mSelectedAppId);
						}
						
						$versionArr = DbHelper::selectRows(TBL_REPORTS.$reporsJoin, $versionWhere, TBL_REPORTS.'.'.REPORT_ANDROID_VERSION.' DESC', TBL_REPORTS.'.'.REPORT_ANDROID_VERSION, TBL_REPORTS.'.'.REPORT_ANDROID_VERSION, 100, false) ;										
						foreach ($versionArr as $ver) {
							?><option value="<?php echo $ver['android_version']; ?>" <?php if ($filterOpts['androidVersion']==$ver[REPORT_ANDROID_VERSION]) echo 'selected="selected"'; ?> ><?php echo $ver[REPORT_ANDROID_VERSION] ?></option><?php 
						}
					?>
				</select>
			</div>
		</div>
		
		<div class="control-group">
	  
		  <label class="control-label" for="deviceName" >Device name</label>
		  <div class="controls" >
				  <input type="text" id="deviceName" value="<?php echo $filterOpts['deviceName']; ?>"></input>
		  </div>
		</div>
		
		<div class="control-group">
	  
		  <label class="control-label" for="issueCause" >Issue cause</label>
		  <div class="controls" >
				  <input type="text" id="issueCause" value="<?php echo $filterOpts['issueCause']; ?>"></input>
		  </div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="sortBy" >Sort by</label>
			<div class="controls" >
			  <select id="order" style="width:160px;" >
			<?php foreach ($filterSortCols as $idx=>$text) { ?>
				<option value="<?php echo $idx; ?>" <?php if (strcmp($idx, $filterOpts['order'])==0) echo 'selected="selected"'; ?> style="vertical-align:center; padding:4px;" ><?php echo $text; ?></option>
			<?php } ?>
	    	</select>
	
	    	<label class="control-label-inline" for="limit" >Page rows</label>
				<select id="limit" name="limit" onchange="$('#start').val(0);"  style="width:110px;" >
				<?php foreach ($filterLimits as $value) { ?>
					<option value="<?php echo $value; ?>" <?php if ($value==$filterOpts['limit']) echo 'selected="selected"'; ?> ><?php echo $value; ?>&nbsp;rows</option>
				<?php } ?>
			</select>
		</div>
		<br/>
		<div class="control-group" style="text-align:right;" >
	  	<button type="submit" class="btn" onclick="loadTable(0, 1)" ><i class="icon-ok" ></i>&nbsp;&nbsp;Apply</button>
	  </div>
	  
	  
	 
	 </div>
</div>

<table class="table table-condensed table-bordered issues-tbl" >
<thead>
	<tr>
		<td style="width:12px; text-align:center;" ><input type="checkbox" onclick="toggleCheckboxes(this);" /></td>
		<td class="issue-hilitecol" ></td>
		<th style="width:160px;" >Date</th>
		<th colspan="2" >Application</th>
		<th style="width:35px" ><i class="icon-signal" ></i></th>
		<th style="width:35px" ><i class="icon-file" ></i></th>
		<th style="width:35px" >V</th>
	
		<th style="width:65px;" >
			<div class="btn-group">
				<button type="button" class="btn btn-inverse btn-small" data-toggle="collapse" data-target="#filterOpts" ><i class="icon-filter icon-white" ></i></button>
	  		<button class="btn btn-inverse btn-small dropdown-toggle" data-toggle="dropdown" ><span class="caret"></span></button>

		  	<ul class="dropdown-menu pull-right" >
		  		<li class="dropdown-submenu pull-left text-left" >
  					<a tabindex="-1" href="#" ><i class="icon-signal" ></i>&nbsp;Priority</a>		
  						<ul class="dropdown-menu" >
  							<?php $p = new IssuePriority(IssuePriority::CRITICAL); ?>
								<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::CRITICAL; ?>)" >
									<?php $p->setPriority(IssuePriority::CRITICAL); echo $p->getLabel(false); echo '&nbsp;&nbsp;'; echo $p->getName(); ?></a></li>
								<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::NORMAL; ?>)" >
									<?php $p->setPriority(IssuePriority::NORMAL); echo $p->getLabel(false); echo '&nbsp;&nbsp;'; echo $p->getName(); ?></a></li>
								<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::LOW; ?>)" >
									<?php $p->setPriority(IssuePriority::LOW); echo $p->getLabel(false); echo '&nbsp;&nbsp;'; echo $p->getName(); ?></a></li>
							</ul>
  				</li>

   				<li class="text-left" ><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_CLOSED; ?>)" ><i class="icon-check" ></i>&nbsp;Close selected</a></li>
   				<li class="text-left" ><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_ARCHIVED; ?>)" ><i class="icon-folder-open" ></i>&nbsp;Archive selected</a></li>
   				<li class="text-left" ><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_VIEWED; ?>)" ><i class="icon-refresh" ></i>&nbsp;Re-open selected</a></li>
    			<li class="text-left" ><a href="javascript:delIssues()" ><i class="icon-trash" ></i>&nbsp;Delete selected</a></li>
		  	</ul>
			</div>
		</th>
	</tr>
</thead>
<tbody>
<?php
	$reporsJoin = " JOIN ".TBL_REPORTS." ON ".TBL_ISSUES.".".ISSUE_ID." = ".TBL_REPORTS.".".REPORT_ISSUE;
						
	$totalRows = DbHelper::countRows(TBL_ISSUES.$reporsJoin, ($mSelectedAppId>0?ISSUE_APP_ID.'='.$mSelectedAppId.' AND ':'').IssueHelper::buildIssuesWhereClause($filterOpts));

	$issues = IssueHelper::fetchIssuesTable($filterOpts);

	if (empty($issues)) {
		?><tr><td colspan="8" class="muted" >No issues recorded yet...</td></tr><?php
	
	} else {
		$pos = 0;

		foreach ($issues as $issueArr) {
			$issue = Issue::createFromArray($issueArr);
			$reports = $issue->getReports();
			$priority = $issue->getPriority();
			$state = $issue->getState();
			 
			$trClasses = ($pos++%2==0?'even ':'odd ').strtolower($state->getName());
?>
		<tr issue="<?php echo $issue->issue_id; ?>" class="<?php echo $trClasses; ?>" >
			<td rowspan="2" ><input type="checkbox" name="itemChecked" value="<?php echo $issue->issue_id; ?>" /></td>
			<td rowspan="2" class="issue-hilitecol <?php echo IssueHelper::getHiliteBgColorClass($state, $priority); ?>" ></td>
			<td colspan="5" class="cause" >
			<?php 
				if (isset($reports[0]) && $reports[0]->is_silent)
					echo '<i class="icon-volume-off" ></i>&nbsp;';	
				
				IssueHelper::printIssueLink($issue->issue_id, $issue->issue_cause);
			?>
			</td>
			<td />
			<td rowspan="2" >
				<div class="btn-group pull-right">
				  <button class="btn btn-small" onclick="showIssueDetails(<?php echo $issue->issue_id; ?>)" ><i class="icon-eye-open" ></i></button>
	  			<button class="btn btn-small dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
	  			<ul class="dropdown-menu">
	    			<?php if (!$state->isArchived()) : ?>
	  					<li class="dropdown-submenu pull-left" >
	  						<a tabindex="-1" href="#" ><i class="icon-signal" ></i>&nbsp;Priority</a>
	  						
	  						<ul class="dropdown-menu" >
	  							<?php $p = new IssuePriority(IssuePriority::CRITICAL); ?>
									<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::CRITICAL; ?>, <?php echo $issue->issue_id; ?>)" >
											<?php $p->setPriority(IssuePriority::CRITICAL); echo $p->getLabel(false); echo '&nbsp;&nbsp;'; echo $p->getName(); ?></a></li>
									<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::NORMAL; ?>, <?php echo $issue->issue_id; ?>)" >
											<?php $p->setPriority(IssuePriority::NORMAL); echo $p->getLabel(false); echo '&nbsp;&nbsp;'; echo $p->getName(); ?></a></li>
									<li><a tabindex="-1" href="javascript:updateIssuesPriority(<?php echo IssuePriority::LOW; ?>, <?php echo $issue->issue_id; ?>)" >
											<?php $p->setPriority(IssuePriority::LOW); echo $p->getLabel(false); echo '&nbsp;&nbsp;'; echo $p->getName(); ?></a></li>
								</ul>
	  					</li>
	  					
	  					<li class="dropdown-submenu pull-left" >
	  						<a tabindex="-1" href="#" ><i class="icon-flag" ></i>&nbsp;State</a>
	  						
	  						<ul class="dropdown-menu" >
		    					<?php if ($state->isOpen()) : ?>
		    						<li><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_TESTING; ?>, <?php echo $issue->issue_id; ?>)" ><i class="icon-cog" ></i>&nbsp;Testing</a></li>
		    						<li><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_CLOSED; ?>, <?php echo $issue->issue_id; ?>)" ><i class="icon-check" ></i>&nbsp;Resolved</a></li>
		    					<?php else: ?>
		    						<li><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_VIEWED; ?>, <?php echo $issue->issue_id; ?>)" ><i class="icon-refresh" ></i>&nbsp;Re-open</a></li>
		    					<?php endif; ?>
	
		    					<li><a href="javascript:updateIssuesState(<?php echo REPORT_STATE_ARCHIVED; ?>, <?php echo $issue->issue_id; ?>)" ><i class="icon-folder-open" ></i>&nbsp;Archive</a></li>
								</ul>
	  					</li>
		    			
		    			<li><a href="javascript:showChangeIssueDialog('<?php echo $issue->getReportIds(); ?>')" ><i class="icon-random" ></i>&nbsp;Change issue</a>
	    			<?php endif; ?>
	    			<li><a href="javascript:delIssues('<?php echo $issue->issue_id; ?>')" ><i class="icon-trash" ></i>&nbsp;Delete</a></li>
	  			</ul>
				</div>
			</td>
		</tr>
		
		<tr issue="<?php echo $issue->issue_id; ?>" class="<?php echo $trClasses; ?>" >	
			<td class="datetime" >
				<?php	echo Helper::formatDate($issue->issue_datetime, $cfg->getDateFormat(), $state->isNew()); ?>
			</td>
			<td><?php echo $issue->app_name; ?></td>
			<td class="state" ><?php echo $state->getLabel(true); ?></td>
			<td class="priority" ><?php echo $priority->getLabel(false); ?></td>		
			<td id="count<?php echo $issue->issue_id; ?>" class="count" ><?php echo $issue->getReportsCount(); ?></td>
			<td><?php echo $reports[0]->getApplicationVersion(); ?></td>
		</tr>
		
		<tr issue="<?php echo $issue->issue_id; ?>" class="<?php echo $trClasses; ?>" >
			<td colspan="8" style="background:#dfdfdf; text-align:center;" >
				<div id="reportsTbl<?php echo $issue->issue_id; ?>" style="display:none; margin:15px 5px;" >
					<table id="reportsTbl<?php echo $issue->issue_id; ?>" class="table table-condensed table-bordered table-hover reports-tbl" >
					<tbody>
					<?php foreach ($reports as $r) { ?>
						<tr id="reportRow<?php echo $r->report_id; ?>" class="report" >
							<td style="width:25px; text-align:center;" ><i class="icon-file" ></i></td>
							<td class="datetime" >
								<?php echo Helper::formatDate($r->user_crash_date, $cfg->getDateFormat(), $r->isNew());	?>
							</td>
							<td class="android-version" ><?php echo $r->android_version; ?></td>
							<td class="phone-model" ><?php echo $r->getApplicationVersion(); ?></td>
							<td class="user-comment" ><i class="muted" ><?php echo empty($r->user_comment)?'No user comment':$r->user_comment; ?></i></td>
							<td style="text-align:center; width:35px; text-decoration:none;" >
								<div class="btn-group pull-right">
				  				<button class="btn btn-small" onclick="javascript:showReportDetails('<?php echo $r->report_id; ?>')" ><i class="icon-eye-open" ></i></button>
	  							<button class="btn btn-small dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
				  
			  					<ul class="dropdown-menu" >
			  						<li class="text-left" ><a href="javascript:showChangeIssueDialog(<?php echo $r->report_id; ?>)" ><i class="icon-random" ></i>&nbsp;Change issue</a>
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
	$nbPage = $totalRows/$filterOpts['limit'];
	$currentPage = ($filterOpts['start']/$filterOpts['limit'])+1;
?>
<div class="pagination pagination-right" >
	<ul>
		<li <?php if ($currentPage==1) echo 'class="disabled"'; ?> >
	  	<a href="#" onclick="<?php if ($currentPage>1) echo 'loadTable('.(($currentPage-2)*$filterOpts['limit']).')'; ?>" >Prev</a>
	  </li>
	    
	  <?php for ($page=0; $page<$nbPage; ++$page) { ?>
	  	<li <?php if (($page+1)==$currentPage) echo 'class="active"'; ?> >
	    	<a href="#" onclick="loadTable(<?php echo ($page*$filterOpts['limit']); ?>);" ><?php echo $page+1; ?></a>
	    </li>
	  <?php } ?>
	    
	  <li <?php if ($currentPage>=$nbPage) echo 'class="disabled"'; ?> >
	  	<a href="#" onclick="<?php if ($currentPage<$nbPage) echo 'loadTable('.($currentPage*$filterOpts['limit']).')'; ?>" >Next</a>
	  </li>
	</ul>
</div>


<script type="text/javascript" >
function onNewAppSelected() {
	loadTable(0);
}
</script>
