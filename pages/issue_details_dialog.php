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
require_once('includes/milestone.class.php');

$cfg = CfgHelper::getInstance();

$issue = DbHelper::fetchIssue($issueId); 
$reports = $issue->getReports();

$userComments = "";
$reportsPerInstall = array();
$reportTabs = array(array(), array());

$group = 0;
foreach ($reports as $r) {
	
	if ($group==3) {
		$reportTabs[0][] = '<li class="dropdown" ><a class="dropdown-toggle" data-toggle="dropdown" href="#">'.($issue->getReportsCount()-3).' more&nbsp;&nbsp;<b class="caret"></b></a><ul class="dropdown-menu">';
	}
	++$group;
	
	$reportTabs[0][] = '<li><a data-toggle="tab" href="#report-'.$r->report_id.'" ><i class="icon-file" ></i>&nbsp;&nbsp;'.$r->getFormattedDate().'</a></li>';
	$reportTabs[1][] = '<div class="tab-pane" id="report-'.$r->report_id.'" ></div>';
	
	// check user comment
	if (strlen($r->user_comment)>0)
		$userComments .= '<p>'.$r->user_comment.'</p>';
	
	// count report from same install
	if (array_key_exists($r->installation_id, $reportsPerInstall)) {
		++$reportsPerInstall[$r->installation_id][1];
		
	} else { $reportsPerInstall[$r->installation_id] = array($r->phone_model, 1); }
}

if ($group>2)
	$reportTabs[0][] = '</ul></li>';

?>
<div class="modal-body" style="clear:both; height:800px; max-height:620px;" >
	<ul id="issueTabs" class="nav nav-tabs" >
	  <li><a data-toggle="tab" href="#issue-details" ><i class="icon-tag" ></i>&nbsp;&nbsp;Issue</a></li>
	  <?php echo implode("\n", $reportTabs[0]); ?>
	</ul>
	
	<div class="tab-content">	
		<div class="tab-pane" id="issue-details" >
			<form id="issueForm" action="<?php echo $mNavCtl->buildActionURL('issues', 'updateIssueDetails', null); ?>" method="post" >
				<input type="hidden" name="issue_id" value="<?php echo $issue->issue_id; ?>" />
	  	
	  		<div class="row-fluid" >
	  			<div class="span12" > 
						<dl class="dl-horizontal" >
			  			<dt style="text-align:left;" ><i class="icon-tag" ></i>&nbsp;ISSUE INFO</dt>
			  			<dd><?php echo $issue->getState()->getLabel(), '&nbsp;&nbsp;', $issue->getPriority()->getLabel(true, true); ?></dd>
			  		</dl>
			  	</div>
			  </div>
	  		
	  		<div class="row-fluid" >
	  			<div class="span7" >
	  				<dl class="dl-horizontal">
							<dt>Creation Date</dt>
							<dd><?php echo Helper::formatDate($issue->issue_datetime, $cfg->getDateFormat()); ?></dd>
							
							<dt>Application</dt>
							<dd><?php echo $issue->app_name; ?></dd>
	
	  				</dl>
	  			</div>
	  			
	  			<div class="span5" >
	  			</div>
	  		</div>
	  		
	  		<div class="row-fluid" >
	  			<div class="span12" >				
	  				<dl class="dl-horizontal">
		  				<dt>Cause</dt>
		  				<dd>
		  					<blockquote><p>
		  					<?php 
		  						$arr = explode(':', $issue->issue_cause);
		  						$arr[0] = '<b class="priority-critical-text-color" >'.$arr[0].'</b>';
		  					
		  						foreach ($arr as $i=>$text) 
		  							echo ($i==0?'<b>'.$text.'</b>':'<small>'.$text.'</small>'), '<br/>';  							
		  					?>
		  					</p></blockquote>
		  				</dd>
		  			</dl>
	  			</div>
	  		</div>
	  		
	  		<?php 
	  			if ($issue->issue_milestone_id>0) { 
						$milesArr = DbHelper::fetchMilestones(MILE_ID.'='.$issue->issue_milestone_id);
						$m = Milestone::createFromArr($milesArr[0]);
						
					} else { $m = null; }
	  		?>
	  		<div class="row-fluid" >
	  			<div class="span12" >
	  				<dl class="dl-horizontal" >
							<dt>Milestone</dt>
							<?php if (!is_null($m)) { ?>
							<dd id="milestoneView" >
								<?php $m->printOverview(); ?>
								<a href="javascript:editIssueMilestone()" >&nbsp;<i class="icon-pencil" ></i>&nbsp;</a>
							</dd>
							<?php } ?>
							<dd id="milestoneEdit" style="<?php echo !is_null($m)?'display:none':''; ?>" >
								
								<?php 
									$arr = DbHelper::selectRows(TBL_MILESTONES, MILE_APP_ID.'='.$issue->issue_app_id, MILE_DUEDATE.' ASC', MILE_ID.', '.MILE_NAME);

									if (empty($arr)) {
										echo '<span class="muted text-i" >No milestone found</span>';
										
									} else {
										echo '<select name="new_milestone" ><option value="null" >No milestone</option>';

										foreach ($arr as $mile) {
											?><option value="<?php echo $mile->mile_id; ?>" <?php if ($mile->mile_id==$issue->issue_milestone_id) echo 'selected="selected"'; ?> ><?php echo $mile->mile_name; ?></option><?php 
										}
										
										echo '</select>';
									}
								?>
							</dd>
						</dl>
					</div>
				</div>
	  		
	  		<div class="row-fluid" >
	  			<div class="span12" >
	  				<dl class="dl-horizontal" >
							<dt>Comment</dt>
							<dd id="commentView" class="well" >
								<span class="pull-right" ><a href="javascript:editIssueComment()" >&nbsp;<i class="icon-pencil" ></i>&nbsp;</a></span>
								<?php echo empty($issue->issue_comment)?'<span class="muted text-i" >No comment</span>':nl2br(stripslashes($issue->issue_comment)); ?>
							</dd>
							<dd id="commentEdit" style="display:none;" >
								<textarea name="new_comment" style="width:75%; height:70px;" ><?php echo $issue->issue_comment; ?></textarea>
							</dd>
						</dl>
	  			</div>
	  		</div>
	  	
	  		<div class="row-fluid" >
	  			<div class="span12" > 
						<dl>
			  			<dt><i class="icon-file" ></i>&nbsp;REPORTS (<?php echo $issue->getReportsCount(); ?>)</dt>
			  			<dd>
			  				<br/>
	
			  				<dl class="dl-horizontal" >
									<dt>User comments</dt>
									<dd><?php echo $userComments==''?'<i class="muted" >No user comments</i>':$userComments; ?></dd>
									
									<dt style="margin-top:10px;" >Reports per install.</dt>
									<dd style="margin-top:10px;" >
									<?php 
										foreach ($reportsPerInstall as $id=>$install) {
											echo '<p>', $install[0], ' {', $id, '} &nbsp;&nbsp;<span class="badge ', $install[1]>1?'badge-warning':'', '">', $install[1],'</span></p>';
										} ?>
									</dd>
								</dl>
			  			
			  			</dd>
			  		</dl>
			  	</div>
			  </div>
			
			</form>
		</div>	
		
		<?php echo implode("", $reportTabs[1]); ?>
	</div>

	<script>	
	  $(function () { $('#issueTabs a:first').tab('show'); });

	  $('a[data-toggle="tab"]').on('shown', function (e) {
		  var tabId = $(e.target).attr('href');
			var parts = $(e.target).attr('href').split('#report-');
			
			if (parts.length==2/* && $(tabId).is(':empty')*/) {				
				$(tabId).html(LOADER_HTML);

				doRequest("getreport", {ctl:'issues', reportId:parts[1], issueFormat:1}, 
						function(data) {
							try {
								$(tabId).html(data);
										
							} catch (err) { console.error(err); }
						});	
				
			}	//else { console.log('Issue tab selected or report already loaded...'); }  
	  });
	</script>
	
</div>
<div class="modal-footer">
  <a href="javascript:closeDialog()" class="btn" >Close</a>

  <a href="#" onclick="$('form#issueForm').submit();" class="btn btn-success" ><i class="icon-ok icon-white" ></i>&nbsp;Save changes</a>
</div>