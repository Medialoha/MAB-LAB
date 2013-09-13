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

?>
<div class="modal-header">
	<img id="dlgloader" src="assets/images/loader.gif" style="float:right; height:25px; margin-right:0px;" />
	<script >$('#dlgloader').hide();</script>
	
  <h3>ID <?php echo $report->report_id; ?>
  	<?php if ($report->isArchived()) { ?><span class="label" style="float:right; margin:5px 370px 0px 0px;" >ARCHIVED</span><?php } ?>
  </h3>
</div>
<div class="modal-body" style="clear:both; height:800px; max-height:620px;" >
	<ul id="reportTabs" class="nav nav-tabs" >
	  <li class="active" ><a href="#report-details" data-toggle="tab" >Details</a></li>
	  <li><a href="#report-config" data-toggle="tab" >Configuration</a></li>
	  <li><a href="#report-prefs" data-toggle="tab" >Preferences</a></li>
	  <li><a href="#report-settings" data-toggle="tab" >Settings</a></li>
	  <li><a href="#report-stacktrace" data-toggle="tab" >Stack Trace</a></li>
	  <li><a href="#report-logcat" data-toggle="tab" >Log</a></li>
	  <li><a href="#report-eventslog" data-toggle="tab" >Events Log</a></li>
	  <li><a href="#report-meminfo" data-toggle="tab" >Mem Info</a></li>
	  <li><a href="#report-radiolog" data-toggle="tab" >Radio Log</a></li>
	  <?php echo $report->hasCustomData()?'<li><a href="#report-custom" data-toggle="tab" >Custom</a></li>':''; ?>
	</ul>
	
	<div class="tab-content">	
  	<div class="tab-pane active" id="report-details" >
  		<dl class="dl-horizontal" >
				<dt>Crash Date</dt><dd><?php echo $report->getFormatedDate(); ?></dd>
			</dl>
  	
  		<div class="row-fluid" >
  			<div class="span7" >  			
  				<dl class="dl-horizontal">
  					<dt>Application</dt><dd><?php echo $report->getApplicationDesc(); ?></dd>
  					<dt>Package</dt><dd><?php echo $report->package_name; ?></dd>
  					<dt>Installation ID</dt><dd><?php echo $report->installation_id; ?></dd>
  					<dt>App. Start Date</dt><dd><?php echo $report->getFormatedAppStartDate(); ?></dd>
					</dl>
					
					<dl class="dl-horizontal" >
						<dt>Android Version</dt><dd><?php echo $report->android_version; ?></dd>
					</dl>
					
					<dl class="dl-horizontal" >
  					<dt>Device</dt><dd><?php echo $report->getDeviceDesc(); ?></dd>
  					<dt>Device ID</dt><dd><?php echo empty($report->device_id)?'-':$report->device_id; ?></dd>
  					<dt>Memory</dt><dd><?php echo $report->getFormatedAvailMemSize()." / ".$report->getFormatedTotalMemSize(); ?></dd>
					</dl>
  			</div>
  			<div class="span5" >
  				<dl>
  					<dt>User Comment</dt><dd><blockquote><?php echo $report->user_comment; ?></blockquote></dd>
  					<dt>User Mail</dt><dd><a href="mailto:<?php echo $report->user_email; ?>" ><?php echo $report->user_email; ?></a></dd>
  				</dl>
  			</div>
  		</div>
  		
  		<dl>
  			<dt>Display</dt>
  			<dd><br/><?php echo ReportHelper::displayDeviceDisplayValues($report->display); ?></dd>
  		
  			<dt>Features</dt>
  			<dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($report->device_features); ?></dd>
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
	  			if (is_object($report->initial_configuration)) {
	  		
		  			$initial = get_object_vars($report->initial_configuration); ksort($initial);
		  			$crash = get_object_vars($report->crash_configuration);
		  			
		  			foreach($initial as $key=>$initialValue) {
		  				$crashValue =& $crash[$key];
		  				
		  				$diff = false;
		  				if (strcmp($initialValue, $crashValue)!=0)
		  					$diff = true;
		  				
		  				echo '<tr><td>'.$key.'</td><td style="width:200px;" >'.
		  									str_replace('+', '<br/>', $initialValue).'</td><td style="width:200px;" class="'.($diff?'text-error':'').'" >'.
		  									str_replace('+', '<br/>', $crashValue).'</td></tr>';
		  			}
		  			
	  			} else { ?><tr><td colspan="3" ><p class="muted" ><i>Nothing recorded</i></p></td></tr><?php } 
	  		?>
  			</tbody>
  		</table>
  	</div>
  	
  	<div class="tab-pane" id="report-prefs" >
  		<dt>Shared Preferences</dt>
  		<dd><br/><?php	ReportHelper::displayPreferences($report->shared_preferences); ?></dd>
  	</div>
  	
  	<div class="tab-pane" id="report-settings" >
  		<dl>
  			<dt>Settings Global</dt><dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($report->settings_global); ?></dd>
  			<dt>Settings System</dt><dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($report->settings_system); ?></dd>
  			<dt>Settings Secure</dt><dd><br/><?php echo ReportHelper::displayObjectValuesToHTMLArray($report->settings_secure); ?></dd>
  		</dl>
  	</div>
	
  	<div class="tab-pane" id="report-stacktrace" style="color:red" ><?php echo $report->getFormatedSystrace(); ?></div>
  	<div class="tab-pane" id="report-logcat" ><?php echo $report->getFormatedLogCat(); ?></div>
  	<div class="tab-pane" id="report-eventslog" ><?php echo $report->eventslog; ?></div>
  	<div class="tab-pane" id="report-meminfo" ><?php echo $report->getFormatedMemInfo(); ?></div>
  	<div class="tab-pane" id="report-radiolog" ><?php echo $report->radiolog; ?></div>
  	
  	<?php if ($report->hasCustomData()) { ?>
  		<div class="tab-pane" id="report-custom" ><?php echo $report->getFormatedCustomData(); ?></div>
  	<?php } ?>
	</div>
 
	<script>
	  $(function () { $('#reportTabs a:first').tab('show'); });
	</script>
</div>
<div class="modal-footer">
  <a href="#" class="btn" data-dismiss="modal" >Close</a>
  
  <a href="#" class="btn" onclick="archiveReport('<?php echo $report->report_id; ?>', '#dlgloader');" >
  	<i class="icon-folder-open" ></i>&nbsp;Archive</a>
  	
  <a href="#" class="btn btn-danger" onclick="delReport('<?php echo $report->report_id; ?>', '#dlgloader');" >
  	<i class="icon-trash icon-white" ></i>&nbsp;Delete</a>
</div>