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

$where = null;
if ($mSelectedAppId>0)
	$where = SALE_APP_ID.'='.$mSelectedAppId;

$sales = DbHelper::selectRows(TBL_SALES.' LEFT JOIN '.TBL_APPLICATIONS.' ON '.APP_ID.'='.SALE_APP_ID, 
																$where, 
																SALE_ORDER_CHARGED_TIMESTAMP.' ASC', 
																TBL_SALES.'.*, '.APP_NAME, 
																null, null, false
															);
?>
<div class="row" >
	<div class="span12" >
		<ul class="nav nav-pills">
		  <li class="active" ><a href="#" >sales</a></li>
		  <li><a href="#" >earnings</a></li>
		</ul>
	</div>
</div>

<div class="row" >
	<div class="span12" >
		<table class="table table-condensed table-striped table-hover" >
		<thead>
			<tr>
				<th>Date</th>
				<th>Application</th>
				<th style="text-align:center;" >SKU</th>
				<th style="text-align:center;" >Buyer country</th>
				<th style="text-align:center;" >Status</th>
				<th style="text-align:center;" colspan="2" >Item price</th>
			</tr>
		</thead>
		<tbody>
		<?php 
			if (!is_array($sales)) {
				?><tr><td colspan="6" class="muted" >No sale found...</td></tr><?php 

			} else {
		
				foreach ($sales as $s) { ?>
				<tr>
					<td><?php echo $s[SALE_ORDER_CHARGED_DATE]; ?></td>
					<td><?php echo $s[APP_NAME]; ?></td>	
					<td style="text-align:center;" ><?php echo $s[SALE_SKU_ID]; ?></td>	
					<td style="text-align:center;" ><?php echo $s[SALE_BUYER_COUNTRY]; ?></td>		
					<td style="text-align:center;" ><?php echo $s[SALE_FINANCIAL_STATUS]; ?></td>
					<td style="text-align:center;" ><?php echo $s[SALE_CURRENCY_CODE]; ?></td>
					<td style="text-align:center;" ><?php echo $s[SALE_ITEM_PRICE]; ?></td>		
				</tr>
		<?php }} ?>
		</tbody>
		</table>
	</div>
</div>