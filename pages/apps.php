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


$apps = DbHelper::selectRows(TBL_APPLICATIONS, null, APP_NAME.' ASC', 
															'*, (SELECT COUNT(*) FROM '.TBL_ISSUES.' WHERE '.ISSUE_APP_ID.'='.APP_ID.') issues', 
															null, null, false);
?>
<fieldset><legend><img src="assets/images/ic_application.png" class="fieldset-icon" />Applications</legend>
	<table class="table table-condensed apps-tbl" >
	<thead>
		<tr>
			<th class="app-id" >ID</th>
			<th style="width:300px;" >Application Name</th>
			<th>Package</th>
			<th class="app-issues" style="width:30px;" ><i class="icon-tag" /></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($apps as $app) { ?> 
		<tr id="<?php echo $app[APP_ID]; ?>" >
			<td class="app-id" ><?php echo $app[APP_ID]; ?></td>
			<td class="app-name" >
				<span><?php echo $app[APP_NAME]; ?></span>
				<a href="javascript:updateAppName(<?php echo $app[APP_ID]; ?>, '<?php echo $app[APP_NAME]; ?>');" style="float:right;" title="Edit application name" >
					<i class="icon-edit" ></i>
				</a>
			</td>
			<td class="app-package text-i" ><?php echo $app[APP_PACKAGE]; ?></td>
			<td class="app-issues" ><?php echo $app['issues']; ?></td>
		</tr>
	<?php } ?>
	</tbody>
	</table>
</fieldset>

<script type="text/javascript" src="assets/functions-core.js" ></script>
<script type="text/javascript" src="assets/functions-applications.js" ></script>