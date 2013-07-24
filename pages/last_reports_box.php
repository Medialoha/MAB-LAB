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

$arr = DBHelper::selectRows(TBL_REPORTS, REPORT_STATE.'!='.REPORT_STATE_ARCHIVED, REPORT_CRASH_DATE." DESC LIMIT 10", '*', null, false);
?>
<table class="table table-condensed table-hover reports-tbl" >
<thead>
	<tr>
		<th>Date</th>
		<th>Application</th>
 		<th>Android</th>
 		<th>Phone Model</th>
 		<th style="width:20px;" ></th>
	</tr>		
</thead>
<tbody>
<?php 
	if (is_array($arr) && sizeof($arr)>0) {
		foreach ($arr as $values) { 
			$r = Report::createFromArray($values); ?>
	<tr style="cursor:pointer;" onclick="showReportDetails('<?php echo $r->report_id; ?>');" >
		<td class="date" ><?php echo ReportHelper::formatDate($r->user_crash_date, $cfg->getDateFormat()); ?></td>
		<td class="application" ><?php echo $r->getApplicationDesc(); ?></td>
		<td class="android-version" ><?php echo $r->android_version; ?></td>
	 	<td class="phone-model" ><?php echo $r->phone_model; ?></td>
	 	<td><?php echo ReportHelper::getBadge($r->isNew()); ?></td>
	</tr>
<?php } 
	} else { ?><tr><td colspan="5" class="muted" >No repords recorded yet...</td></tr><?php } ?>
</tbody>
</table>

<div id="reportDialog" class="modal hide fade" style="width:900px; margin-left:-450px;" ></div>