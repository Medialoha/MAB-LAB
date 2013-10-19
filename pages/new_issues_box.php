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

$arr = DBHelper::fetchNewReports($cfg->getDashboardNewIssuesToDisplay()); 
?>
<style>
.tooltip-inner {
	max-width:700px;
	text-align:left;	
	font-size:15px;
}
</style>

<?php if (is_array($arr) && sizeof($arr)>0) { ?>
<table class="table table-condensed table-hover newreports-tbl" >
<thead>
	<tr>
		<th style="width:80px;" >Last</th>
		<th style="width:25px; text-align:center;" >P</th>
		<th style="width:140px;" >Application</th>
 		<th>Cause</th>
 		<th style="width:20px; text-align:center;" ><i class="icon-file" title="Total reports count for this issue" ></i></th>
		<th style="width:35px; text-align:center;" ><?php echo Helper::getBadge(true); ?></th>
	</tr>		
</thead>
<tbody>
<?php foreach ($arr as $r) { ?>
	<tr style="cursor:pointer;" onclick="showReportDetails(<?php echo $r->last_report_id; ?>)" >
		<td class="datetime" ><?php echo Helper::formatDate($r->last_crash_date, $cfg->getDateFormat(), false); ?></td>
		<td class="priority" ><?php $p = new IssuePriority($r->issue_priority); echo $p->getLabel(false); ?></td>
		<td class="application" >
			<?php echo ReportHelper::formatPackageName($r->package_name, true); ?>
			<br/>
			<?php echo $r->app_version_name." #".$r->app_version_code; ?>
		</td>
	 	<td class="cause" >
	 		<?php 
	 			$cause = explode(':', Helper::shrinkString($r->issue_cause, 160));
	 			$causelen = strlen($r->issue_cause);
	 			$cause[0] = '<b>'.$cause[0].'</b>';	?>
	 			
			<p <?php if ($causelen>160) { echo 'title="'.$r->issue_cause.'" rel="tooltip" data-toggle="tooltip" data-placement="bottom" data-html="true"'; } ?> >
				<?php echo implode(': ', $cause); ?>
			</p>
	 	</td>
		<td class="count-reports" ><?php echo $r->count_reports; ?></td>
	 	<td class="count-new" ><?php echo $r->count_new; ?></td>
	</tr>
<?php } ?>
	<tr><td colspan="6" class="viewmore" ><a href="javascript:showAllNewIssues();" ><i class="icon-tags" ></i>&nbsp;View all</a></td></tr> 
</tbody>
</table>

<div id="reportDialog" class="modal hide fade" style="width:900px; margin-left:-450px;" ></div>

<script type="text/javascript" >
$(function(){
	$('P[rel=tooltip]').tooltip();
});
</script>

<?php } else { ?><div class="muted" style="font-style:italic" ><i class="icon-thumbs-up" ></i>&nbsp;No new repords found...</div><?php } ?>
