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

$arr = DBHelper::selectRows(TBL_ISSUES.' LEFT JOIN '.TBL_REPORTS.' ON '.REPORT_ISSUE.'='.ISSUE_ID,
														// where 
														null, 
														// order 
														'distinct_count DESC, count DESC',
														// projection
														REPORT_PHONE_MODEL.', '.REPORT_BRAND.', '.REPORT_PRODUCT.', COUNT(DISTINCT issue_id) distinct_count, COUNT(*) count',
														// group by 
														'CONCAT('.REPORT_PHONE_MODEL.', '.REPORT_BRAND.', '.REPORT_PRODUCT.')',
														// limit 
														'5', 
														true);
?>
<table class="table table-condensed most-affected" >
<thead>
	<tr>
		<th>Devices</th>
		<th style="text-align:center;" >Issues</th>
		<th>Reports</th>
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
			<span class="device <?php echo $class; ?>" ><?php echo $row->phone_model; ?></span><br/>
			<small>
				<span class="brand" ><?php echo ucfirst($row->brand); ?></span>&nbsp;&nbsp;<span class="muted" ><?php echo ucfirst($row->product); ?></span>
			</small>
		</td>
	 	<td class="count <?php echo $class; ?>" ><?php echo $row->distinct_count; ?></td>
	 	<td class="count <?php echo $class; ?>" ><?php echo $row->count; ?></td>
	</tr>
<?php } ?>

	<tr class="actions" ><td colspan="3" ><a href="javascript:showMostAffectedDevicesFullList()" ><i class="icon-eye-open" ></i>&nbsp;View all</a></td></tr>

<?php } else { ?><tr><td colspan="3" class="muted" >No reports recorded yet...</td></tr><?php } ?>
</tbody>
</table>

<div id="reportDialog" class="modal hide fade" style="width:900px; margin-left:-450px;" ></div>