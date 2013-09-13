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

$arr = DBHelper::selectRows(TBL_REPORTS, null, 'count DESC', REPORT_PHONE_MODEL.', '.REPORT_BRAND.', '.REPORT_PRODUCT.', COUNT(*) count', REPORT_PHONE_MODEL, '5', false);
?>
<table class="table table-condensed most-affected" >
<thead>
	<tr>
		<th>Devices</th>
		<th style="text-align:center;" >Reports</th>
	</tr>		
</thead>
<tbody>
<?php 
	if (is_array($arr) && sizeof($arr)>0) {
		$i = 1;

		foreach ($arr as $row) {
			$class = 'most-affected-'.($i++);
?>
	<tr>
		<td>
			<span class="device <?php echo $class; ?>" ><?php echo $row[0]; ?></span><br/>
			<small>
				<span class="brand" ><?php echo ucfirst($row[1]); ?></span>&nbsp;&nbsp;<span class="muted" ><?php echo ucfirst($row[2]); ?></span>
			</small>
		</td>
	 	<td class="count <?php echo $class; ?>" ><?php echo $row[3]; ?></td>
	</tr>
<?php } 
	} else { ?><tr><td colspan="2" class="muted" >No reports recorded yet...</td></tr><?php } ?>
</tbody>
</table>
