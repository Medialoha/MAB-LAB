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
require_once('includes/currency.class.php');


$apps = DbHelper::selectRows(TBL_APPLICATIONS, null, APP_NAME.' ASC', 
															'*, (SELECT COUNT(*) FROM '.TBL_ISSUES.' WHERE '.ISSUE_APP_ID.'='.APP_ID.') issues', 
															null, null, false);
?>
<fieldset><legend><img src="assets/images/ic_application.png" class="fieldset-icon" />Applications</legend>
	<table id="appsTbl" class="table table-condensed apps-tbl" >
	<thead>
		<tr>
			<th class="app-id" >ID</th>
			<th style="width:300px;" >Application Name</th>
			<th>Package</th>
			<th class="app-issues" style="width:30px;" ><i class="icon-tag" /></th>
			<th style="" ></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($apps as $app) { 
					$canEditPackage = $app['issues']==0; 	
		
	?> 
		<tr id="app<?php echo $app[APP_ID]; ?>" >
			<td class="app-id" ><?php echo $app[APP_ID]; ?></td>
			<td class="app-name" ><?php echo $app[APP_NAME]; ?></td>
			<td class="app-package text-i" ><?php echo $app[APP_PACKAGE]; ?></td>
			<td class="app-issues" ><?php echo $app['issues']; ?></td>
			<td style="text-align:right;" >
				<a href="javascript:editApplication(<?php echo $app[APP_ID], ", '", $app[APP_NAME], "'", $canEditPackage?", '".$app[APP_PACKAGE]."'":''; ?>);" style="" title="Edit application" >
					<i class="icon-edit" ></i>
				</a>&nbsp;
				<a href="javascript:delApplication(<?php echo $canEditPackage?$app[APP_ID]:'0'; ?>);" style="" title="Delete application" >
					<i class="icon-trash" ></i>
				</a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td>
				<input type="hidden" id="appId" />
			</td>
			<td style="padding:25px 20px 0px 5px;" >
				<input type="text" id="appName" placeholder="Application name" style="width:100%" />
			</td>
			<td style="padding:25px 20px 0px 5px;" >
				<input type="text" id="appPackage" placeholder="Package" style="width:100%" />
			</td>
			<td style="padding:25px 0px 0px 0px; text-align:right;" colspan="2" >
				<button type="button" class="btn" onclick="updateApplication()" ><i class="icon-ok" ></i></button>
				<button type="button" class="btn" onclick="clearEditApplication()" title="clear" ><i class="icon-remove" ></i></button>
			</td>
		</tr>
	</tfoot>
	</table>
</fieldset>

<script type="text/javascript" src="assets/functions-core.js" ></script>
<script type="text/javascript" src="assets/functions-applications.js" ></script>