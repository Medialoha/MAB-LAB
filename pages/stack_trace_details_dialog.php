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
 *     MAREK MASLANKA - Logintar - added review reports by the same errors and formatting logcat
 */

// get reports preferences
$cfg = CfgHelper::getInstance();
$reportsWithStackTrace = DBHelper::selectRows(TBL_REPORTS, REPORT_STACK_TRACE.'= "'.addslashes($stacktrace->stack_trace).'"', REPORT_CRASH_DATE.' DESC', REPORT_ID.','.REPORT_CRASH_DATE.','.REPORT_PHONE_MODEL.','.REPORT_ANDROID_VERSION, null, "10", false);

?>
<div class="modal-header">
	<img id="stdlgloader" src="assets/images/loader.gif" style="float:right; height:25px; margin-right:0px;" />
	<script >$('#stdlgloader').hide();</script>
  <h4>Error: <?php echo $stacktrace->getTitle(); ?>
  </h4>
</div>

<div class="modal-body" style="clear:both; height:800px; max-height:620px;" >
	<ul id="stacktraceTabs" class="nav nav-tabs" >
	  <li class="active" ><a href="#stacktrace-details" data-toggle="tab" >Details</a></li>
	  <li><a href="#stacktrace-other" data-toggle="tab" >Reports</a></li>
	  <li><a href="#stacktrace-stack" data-toggle="tab" >Stack Trace</a></li>
	</ul>
	
	<div class="tab-content">	
		<div class="tab-pane active" id="stacktrace-details" >
			<dl class="dl-horizontal">
				<dt>Application</dt><dd><?php echo $stacktrace->getApplicationDesc(); ?></dd>
				<dt>Package</dt><dd><?php echo $stacktrace->package_name; ?></dd>
				<dt>Times</dt><dd><?php echo $stacktrace->count; ?></dd>
				<dt>Last time crash</dt><dd><?php echo $stacktrace->last_crash_date; ?></dd>
			</dl>
		</div>
		
		<div class="tab-pane" id="stacktrace-other" >
			<table class="table table-condensed table-hover reports-tbl" >
				<thead>
					<tr>
						<th>ID</th>
						<th>Date</th>
				 		<th>Android</th>
				 		<th>Phone Model</th>
					</tr>		
				</thead>
				<tbody>
				<?php 
					foreach($reportsWithStackTrace as $values)
					{ 
						$st = Report::createFromArray($values);
				?>
					<tr style="cursor:pointer;" onclick="showReportDetails('<?php echo $st->report_id; ?>');" >
						<td class="id" ><?php echo $st->report_id; ?></td>
						<td class="date" ><?php echo ReportHelper::formatDate($st->user_crash_date, $cfg->getDateFormat()); ?></td>
						<td class="android-version" ><?php echo $st->android_version; ?></td>
					 	<td class="phone-model" ><?php echo $st->phone_model; ?></td>
					</tr>
				<?php
					}
				?>
				</tbody>
			</table>
		</div>
		
		<div class="tab-pane" id="stacktrace-stack" style="color:red" >
			<?php echo $stacktrace->getFormatedSystrace(); ?>
		</div>
	</div>
 
	<script>
	  $(function () { $('#stacktraceTabs a:first').tab('show'); });
	</script>
</div>

<div class="modal-footer">
  <a href="#" class="btn" data-dismiss="modal" >Close</a>
  
  <a href="#" class="btn" onclick="archiveReportByStacktrace('<?php echo $stacktrace->report_id; ?>', '#stdlgloader');" >
  	<i class="icon-folder-open" ></i>&nbsp;Archive (<?php echo $stacktrace->count; ?>) reports</a>
  	
  <a href="#" class="btn btn-danger" onclick="delReportByStacktrace('<?php echo $stacktrace->report_id; ?>', '#stdlgloader');" >
  	<i class="icon-trash icon-white" ></i>&nbsp;Delete (<?php echo $stacktrace->count; ?>) reports</a>
</div>