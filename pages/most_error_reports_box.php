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

?>
<table class="table table-condensed table-hover reports-tbl" >
<thead>
	<tr>
		<th>Package name</th>
		<th>Ver. name</th>
 		<th>Stack trace</th>
 		<th>Count</th>
	</tr>		
</thead>
<tbody>
<?php
	$stacktraces = DBHelper::fetchStackTraces(10);
	if (is_array($stacktraces) && sizeof($stacktraces) > 0)
	{
		foreach($stacktraces as $stacktrace)
		{ ?>
	<tr style="cursor:pointer;" onclick="showStackTraceDetails('<?php echo $stacktrace->report_id; ?>');" >
		<td style="" ><?php echo $stacktrace->package_name; ?></td>
		<td style="" ><?php echo $stacktrace->app_version_name; ?></td>
		<td style="" ><?php echo $stacktrace->stack_trace; ?></td>
	 	<td style="" ><?php echo $stacktrace->count; ?></td>
	</tr>
<?php } 
	} else { ?><tr><td colspan="5" class="muted" >No repords recorded yet...</td></tr><?php } ?>
</tbody>
</table>
