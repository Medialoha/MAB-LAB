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

$r = DBHelper::fetchReport($reportId);

if (empty($r->report_key)) {
	?><b class="color:red;" >Report with id <?php echo $reportId; ?> not found !!!</b><?php
	exit();
}

$priority = new IssuePriority($r->issue_priority);
?>
<div class="modal-header">
	<span style="float:right; width:60px;" >&nbsp;
		<img id="dlgloader" src="assets/images/loader.gif" style="float:right; height:25px;" />
	</span>
	<script >$('#dlgloader').hide();</script>
	
  <h3>ID <?php echo $r->report_key; ?>  
  	<?php if ($r->isArchived()) { 
  					?><span class="label" style="float:right; margin:5px 0px 0px 20px;" >ARCHIVED</span>
  	<?php } else if (!$r->isOpen()) { 
  					?><span class="label label-success" style="float:right; margin:5px 0px 0px 20px;" >RESOLVED</span>
  	<?php } ?>

		<span style="float:right; margin:-5px 0px 0px 0px;" ><?php echo $priority->getLabel();	?></span>
  </h3>
</div>
<div class="modal-body" style="clear:both; height:800px; max-height:620px;" >
	<ul id="reportTabs" class="nav nav-tabs" >
	  <li class="active" ><a href="#report-details" data-toggle="tab" >Details</a></li>
	  <li><a href="#report-config" data-toggle="tab" >Config.</a></li>
	  <li><a href="#report-prefs" data-toggle="tab" >Pref.</a></li>
	  <li><a href="#report-settings" data-toggle="tab" >Settings</a></li>
	  <li><a href="#report-environments" data-toggle="tab" >Environments</a></li>
	  <li><a href="#report-stacktrace" data-toggle="tab" >Stack Trace</a></li>
	  <li><a href="#report-logcat" data-toggle="tab" >Log</a></li>
	  <li><a href="#report-eventslog" data-toggle="tab" >Events Log</a></li>
	  <li><a href="#report-meminfo" data-toggle="tab" >Mem Info</a></li>
	  <li><a href="#report-radiolog" data-toggle="tab" >Radio Log</a></li>
	  <?php echo $r->hasCustomData()?'<li><a href="#report-custom" data-toggle="tab" >Custom</a></li>':''; ?>
	</ul>
	
	<div class="tab-content">	
  	<div class="tab-pane active" id="report-details" >			
  		<dl class="dl-horizontal" >
				<dt>Crash Date</dt>
				<dd><?php echo $r->getFormattedDate(); ?>&nbsp;<?php echo Helper::getBadge($r->isNew()); ?></dd>
			</dl>
  	
  		<div class="row-fluid" >
  			<div class="span12" >
  				<dl class="dl-horizontal">
  					<dt>Cause</dt>
  					<dd><?php 
  						$cause = explode(':', $r->issue_cause);
							$cause[0] = '<b class="priority-critical-text-color" >'.$cause[0].'</b>';
  						echo implode(':<br/>', $cause);
  					?></dd>
  				</dl>
  			</div>
  		</div>
  	
  		<div class="row-fluid" >
  			<div class="span7" >  			
  				<dl class="dl-horizontal">
  					<dt>Application</dt><dd><?php echo $r->getApplicationDesc(); ?></dd>
  					<dt>Package</dt><dd><?php echo $r->package_name; ?></dd>
  					<dt>Installation ID</dt><dd><?php echo $r->installation_id; ?></dd>
  					<dt>App. Start Date</dt><dd><?php echo $r->getFormattedAppStartDate(); ?></dd>
					</dl>
					
					<dl class="dl-horizontal" >
						<dt>Android Version</dt><dd><?php echo $r->android_version; ?></dd>
					</dl>
					
					<dl class="dl-horizontal" >
  					<dt>Device</dt><dd><?php echo $r->getDeviceDesc(); ?></dd>
  					<dt>Device ID</dt><dd><?php echo empty($r->device_id)?'-':$r->device_id; ?></dd>
  					<dt>Memory</dt><dd><?php echo $r->getFormattedAvailMemSize()." / ".$r->getFormattedTotalMemSize(); ?></dd>
					</dl>
  			</div>
  			<div class="span5" >
  				<dl>
  					<dt>User Comment</dt><dd><blockquote><?php echo $r->user_comment; ?></blockquote></dd>
  					<dt>User Mail</dt><dd><a href="mailto:<?php echo $r->user_email; ?>" ><?php echo $r->user_email; ?></a></dd>
  				</dl>
  			</div>
  		</div>
  		
  		<dl>
  			<dt>Display</dt>
  			<dd><br/><?php echo ReportHelper::displayDeviceDisplayValues($r->display); ?></dd>
  		
  			<dt>Features</dt>
  			<dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($r->device_features); ?></dd>
  		</dl>
  	</div>
  	
  	<div class="tab-pane" id="report-config" >
  		<table class="table table-condensed table-hover" >
  			<thead>
  				<tr>
  					<th>Configuration</th>
  					<th>Initial Value</th>
  					<th>Crash Value</th>
  				</tr>
  			</thead>
  			<tbody>
	  		<?php 
	  			if (is_object($r->initial_configuration)) {
	  		
		  			$initial = get_object_vars($r->initial_configuration); ksort($initial);
		  			$crash = get_object_vars($r->crash_configuration);
		  			
		  			foreach($initial as $key=>$initialValue) {
		  				$crashValue =& $crash[$key];
		  				
		  				$diff = false;
		  				if (strcmp($initialValue, $crashValue)!=0)
		  					$diff = true;
		  				
		  				echo '<tr><td>'.$key.'</td><td style="width:200px;" >'.
		  									str_replace('+', '<br/>', $initialValue).'</td><td style="width:200px;" class="'.($diff?'text-error':'').'" >'.
		  									str_replace('+', '<br/>', $crashValue).'</td></tr>';
		  			}
		  			
	  			} else { ?><tr><td colspan="3" ><p class="muted" ><i>No data collected</i></p></td></tr><?php } 
	  		?>
  			</tbody>
  		</table>
  	</div>
  	
  	<div class="tab-pane" id="report-prefs" >
  		<dl>
  			<dt>Shared Preferences</dt>
  			<dd><br/><?php	ReportHelper::displayPreferences($r->shared_preferences); ?></dd>
  		</dl>
  	</div>
  	
  	<div class="tab-pane" id="report-settings" >
  		<dl>
  			<dt>Settings Global</dt><dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($r->settings_global, FORMAT_SETTINGS_KEY); ?></dd>
  			<dt>Settings System</dt><dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($r->settings_system, FORMAT_SETTINGS_KEY); ?></dd>
  			<dt>Settings Secure</dt><dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($r->settings_secure, FORMAT_SETTINGS_KEY); ?></dd>
  		</dl>
  	</div>
  	
  	<div class="tab-pane" id="report-environments" >
  		<dl>
  			<dt>Environments</dt>
  			<dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($r->environment, FORMAT_ENV_KEY); ?></dd>
  		</dl>
  	</div>
  	
  	<div class="tab-pane" id="report-stacktrace" ><?php echo $r->getFormattedStackTrace(); ?></div>
  	<div class="tab-pane" id="report-logcat" ><?php echo $r->getFormattedLogCat(); ?></div>
  	<div class="tab-pane" id="report-eventslog" ><?php echo $r->getFormattedEventsLog(); ?></div>
  	<div class="tab-pane" id="report-meminfo" ><?php echo $r->getFormattedMemInfo(); ?></div>
  	<div class="tab-pane" id="report-radiolog" ><?php echo $r->getFormattedRadioLog(); ?></div>
  	
  	<?php if ($r->hasCustomData()) { ?>
  		<div class="tab-pane" id="report-custom" ><?php echo $r->getFormattedCustomData(); ?></div>
  	<?php } ?>
	</div>
 
	<script>
	  $(function () { $('#reportTabs a:first').tab('show'); });
	</script>
</div>
<div class="modal-footer">
  <a href="javascript:hideReportDetailsDialog()" class="btn" >Close</a>
 	
  <a href="#" class="btn btn-danger" onclick="delReports('<?php echo $r->report_id; ?>');" >
  	<i class="icon-trash icon-white" ></i>&nbsp;Delete</a>
</div>