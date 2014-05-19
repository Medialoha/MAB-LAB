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
$cfg = CfgHelper::getInstance();
$currency = new Currency($cfg->getCurrencyCode());

$orderOpts = array( array('Date ASC', SALE_ORDER_CHARGED_TIMESTAMP.' ASC'), 
										array('Date DESC', SALE_ORDER_CHARGED_TIMESTAMP.' DESC'));
$order = isset($_POST['order'])?$_POST['order']:1;

$periodEnd = isset($_POST['periodEnd'])?$_POST['periodEnd']:'';

if (!isset($_POST['periodStart'])) {
	$periodStart = date('Y-m-d', strtotime((empty($periodEnd)?'':$periodEnd).' -90 days'));
	
} else { $periodStart = $_POST['periodStart']; }


$where = array();
if ($mSelectedAppId>0)
	$where[] = SALE_APP_ID.'='.$mSelectedAppId;

$where[] = SALE_ORDER_CHARGED_TIMESTAMP.'>='.strtotime($periodStart);

if (!empty($periodEnd))
	$where[] = SALE_ORDER_CHARGED_TIMESTAMP.'<='.strtotime($periodEnd);

$sales = DbHelper::selectRows(TBL_SALES.' LEFT JOIN '.TBL_APPLICATIONS.' ON '.APP_ID.'='.SALE_APP_ID, 
																implode(' AND ', $where),
																$orderOpts[$order][1], 
																TBL_SALES.'.*, '.APP_NAME, 
																null, null, false
															);
?>
<div class="well" >
	<form class="form-inline" action="<?php echo $mNavCtl->getURL(); ?>" method="post" style="margin-bottom:0px;" >
		<strong style="padding-left:15px; padding-right:15px;" >Period</strong>
	
		<div id="periodStart" class="input-append" >
	  	<input name="periodStart" data-format="yyyy-MM-dd" type="text" class="input-small" value="<?php echo $periodStart; ?>" />
	    <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar" ></i></span>
	  </div>
	
		&nbsp;&nbsp;-&nbsp;&nbsp;
	
		<div id="periodEnd" class="input-append" >
	  	<input name="periodEnd" data-format="yyyy-MM-dd" type="text" class="input-small" value="<?php echo empty($periodEnd)?date('Y-m-d'):$periodEnd; ?>" />
	    <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar" ></i></span>
	  </div>

		<strong style="padding-left:15px; padding-right:15px;" >Order</strong>
	  
	  <select name="order" >
	  <?php 
	  	foreach ($orderOpts as $k=>$v) {
	  		echo '<option value="', $k, '" ', $k==$order?'selected="selected"':'', ' >', $v[0], '</option>';
	  	} 
	 	?>
	  </select>
	  
  	<button type="submit" class="btn" style="float:right;" >Go !</button>
  </form>
</div>

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
				<th style="text-align:center;" colspan="2" >Charged amount</th>
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
					<td class="<?php echo $s[SALE_CURRENCY_CODE]==$s[SALE_MERCHANT_CURRENCY]?'muted':''; ?>" style="text-align:center;" ><?php echo $currency->formatValue($s[SALE_CHARGED_AMOUNT], $s[SALE_CURRENCY_CODE]); ?></td>
					<td style="text-align:center;" ><?php echo $currency->formatValue($s[SALE_CHARGED_AMOUNT_MERCHANT_CURRENCY], $s[SALE_MERCHANT_CURRENCY]); ?></td>		
				</tr>
		<?php }} ?>
		</tbody>
		</table>
	</div>
</div>

<!-- <div class="pagination"> -->
<!--   <ul> -->
<!--     <li><a href="#">Prev</a></li> -->
<!--     <li><a href="#">1</a></li> -->
<!--    	<li><a href="#">Next</a></li> -->
<!-- 	</ul> -->
<!-- </div> -->

<script type="text/javascript" >
  $(function() {
    $('#periodStart').datetimepicker({
      pickTime: false
    });
    $('#periodEnd').datetimepicker({
      pickTime: false
    });
  });
</script>