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
require_once('includes/charthelper.class.php');

$isAllAppSelected = ($mSelectedAppId==-1);

$whereFilterApp = SALE_APP_ID;
if (!$isAllAppSelected) {
	$whereFilterApp .= '='.$mSelectedAppId;
	
} else { $whereFilterApp .= '!='.$mSelectedAppId; }

$currentYear = date('Y'); 
$pastYear = $currentYear-1;

$currentMonth = date('m');
$pastMonth = $currentMonth-1;

$currentMonthName = date('F');
$pastMonthName = date('F', mktime(0, 0, 0, $pastMonth, 1, $currentYear));

$daysCurrentMonth = date("t");
?>
<div class="row" >
	<!-- ////// SALES PER APPLICATION ////// -->
	<div class="span6 app-stat-box" >
		<h4>Sales per Application</h4>
		<div id="salesPerApp" style="width:400px; height:200px;" ></div>
		<?php 
			$salesPerApp = DbHelper::selectRows(TBL_SALES.' LEFT JOIN '.TBL_APPLICATIONS.' ON '.APP_ID.'='.SALE_APP_ID,
																						null,
																						SALE_ORDER_CHARGED_TIMESTAMP.' ASC',
																						APP_NAME.', COUNT(*) count',
																						SALE_APP_ID,
																						null, false
																					);
		
		?>
		<script type="text/javascript" >$(function() { drawPieChart('#salesPerApp', <?php echo ChartHelper::convertMySQLArrToPieChartJSON($salesPerApp, true); ?>, true); });</script>
	</div>
	
	<!-- ////// SALES PER MONTH ////// -->
	<div class="span6 app-stat-box" >
		<h4>Sales per Month<em class="appname muted" ><?php echo $mSelectedAppName; ?></em></h4>
		<div id="salesPerMonth" style="width:470px; height:200px;" ></div>
		
		<div class="show-tbl-row" style="" >
			<button type="button" class="btn btn-link" data-toggle="collapse" data-target="#salesPerMonthTbl" >
	  		<i class="icon-list" ></i>&nbsp;Show/hide table&nbsp;</button>		
		</div>

		<div id="salesPerMonthTbl" class="collapse" >
			<table class="table table-condensed" >
			<thead>
				<th>Month</th>
				<th style="text-align:center" ><?php echo $pastYear; ?></th>
				<th style="text-align:center" ><?php echo $currentYear; ?></th>
				<th style="width:35px;" ></th>
			</thead>
			<tbody>
			<?php
				$arr = DbHelper::selectRows(TBL_INCREMENTS,
																			null,
																			// order
																			'inc ASC LIMIT 12',
																			// projection
																			INC_VALUE.'+1,'.
																			'(SELECT COUNT(*) FROM '.TBL_SALES.' WHERE '.$whereFilterApp.' AND MONTH(DATE('.SALE_ORDER_CHARGED_DATE.'))=1+'.INC_VALUE.' AND YEAR(DATE('.SALE_ORDER_CHARGED_DATE.'))='.$currentYear.') current,'.
																			'(SELECT COUNT(*) FROM '.TBL_SALES.' WHERE '.$whereFilterApp.' AND MONTH(DATE('.SALE_ORDER_CHARGED_DATE.'))=1+'.INC_VALUE.' AND YEAR(DATE('.SALE_ORDER_CHARGED_DATE.'))='.$pastYear.') past',
																			// group by
																			null, null, false	);
			
			
				$dataCurrent = '['; $dataPast = '['; $ticks = '['; $sep = ''; $i = 0;
				
				foreach ($arr as $idx=>$s) {
					$ignore = $s[0]>$currentMonth; 
	
					$prev = $idx>0?$arr[$idx-1][1]:-1;
					
					echo '<tr class="'.($ignore?'muted':'').'" ><td>', date("F", mktime(0, 0, 0, $s[0], 1, date('Y'))), '<td style="text-align:center" >'.($s[2]>$s[1]?'<strong>'.$s[2].'</strong>':$s[2]), '</td>';
					
					if ($ignore) {
						echo '<td style="text-align:center;" >0</td><td></td></tr>';

					} else {
						if ($prev!=-1) {
							$badge = '<span class="badge" style="background-color:'.($s[1]>$prev?'#99cc00':($s[1]==$prev?'':'#ff4444')).'" ><i class="'.($s[1]>$prev?'icon-arrow-up':($s[1]==$prev?'icon-minus':'icon-arrow-down')).' icon-white" ></i></span>';

						} else {
							$badge = '';
						}

						echo '<td style="text-align:center;" >', ($s[1]>$s[2]?'<strong>'.$s[1].'</strong>':$s[1]),'</td><td>', $badge, '</td></tr>';
					}	
									'<td><i class="'.($ignore?'':($s[1]>$s[2]?'icon-arrow-up':($s[1]==$s[2]?'':'icon-arrow-down'))).'" ></i>'.
									'</td><td style="text-align:center; color:'.($ignore?'':($s[1]>$s[2]?'#99cc00':($s[1]==$s[2]?'':'#ff4444'))).'" >'.$s[1].'</td></tr>';
					
					$ticks .= $sep.'['.$i.',"'.date("M", mktime(0, 0, 0, $s[0], 1, date('Y'))).'"]';
					
					$dataCurrent .= $sep.'['.$i.','.$s[1].']';
					$dataPast .= $sep.'['.$i.','.$s[2].']';
					
					$sep = ','; ++$i;
				}
	
				$dataCurrent .= ']'; $dataPast .= ']'; $ticks .= ']';
			?>
			</tbody>
			</table>
		</div>

		<script type="text/javascript" >		
			$(function() {
					$.plot('#salesPerMonth',	[ { label:<?php echo $currentYear; ?>, data:<?php echo $dataCurrent; ?>, color:"#33b5e5", bars:{ align:"left", barWidth:0.4, numbers:{ xAlign:function (x) {return x+0.2;}, font:{ weight:"bold", color:"#111111" }} } },
				                     		  		{ label:<?php echo $pastYear; ?>, data: <?php echo $dataPast; ?>, color:"#cdcdcd", bars:{ align:"right", barWidth:0.4, numbers:{ xAlign:function (x) {return x-0.2;}, font:{ weight:"normal", color:"#777777" }}} } ],
				                     		   		{ series: { bars:{ show:true, lineWidth:1, align:"center", numbers:{ show:true, processing:function(val) { return val>0?val:""; } } } },
   		   																xaxis: { ticks:<?php echo $ticks; ?> },
				                     						grid: { show:true, color:"#666666", backgroundColor:"#ffffff", borderColor:"#666666", borderWidth:{top:0, right:0, bottom:1, left:1} } });	
				});
		</script>
	</div>
</div>

<div class="row" >	
	<!-- ////// DAILY SALES AVERAGE PER MONTH ////// -->
	<div class="span6 app-stat-box" >
		<h4>Daily sales average per month<em class="appname muted" ><?php echo $mSelectedAppName; ?></em></h4>	
		<div id="dailySalesAvgPerMonth" style="width:470px; height:200px;" ></div>
		<?php
			$arr = DbHelper::selectRows(TBL_INCREMENTS,
																		null,
																		// order
																		'inc ASC LIMIT 12',
																		// projection
																		INC_VALUE.'+1 month,'.
																		'(SELECT COUNT(*)/DAY(LAST_DAY(DATE(CONCAT("'.$currentYear.'-", '.INC_VALUE.'+1, "-01")))) FROM '.TBL_SALES.' WHERE '.$whereFilterApp.' AND MONTH(DATE('.SALE_ORDER_CHARGED_DATE.'))=1+'.INC_VALUE.' AND YEAR(DATE('.SALE_ORDER_CHARGED_DATE.'))='.$currentYear.') current,'.
																		'(SELECT COUNT(*)/DAY(LAST_DAY(DATE(CONCAT("'.$pastYear.'-", '.INC_VALUE.'+1, "-01")))) FROM '.TBL_SALES.' WHERE '.$whereFilterApp.' AND MONTH(DATE('.SALE_ORDER_CHARGED_DATE.'))=1+'.INC_VALUE.' AND YEAR(DATE('.SALE_ORDER_CHARGED_DATE.'))='.$pastYear.') past',
																		// group by
																		null, null, false); 

			$dataCurrent = '['; $dataPast = '['; $ticks = '['; $sep = ''; $i = 0;
			
			foreach ($arr as $s) {					
				$ticks .= $sep.'['.$i.',"'.date("M", mktime(0, 0, 0, $s[0], 1, date('Y'))).'"]';
					
				$dataCurrent .= $sep.'['.$i.','.$s[1].']';
				$dataPast .= $sep.'['.$i.','.$s[2].']';
					
				$sep = ','; ++$i;
			}
			
			$dataCurrent .= ']'; $dataPast .= ']'; $ticks .= ']';
		?>
		
		<script type="text/javascript" >		
			$(function() {
					$.plot('#dailySalesAvgPerMonth',	[ { label:<?php echo $currentYear; ?>, data:<?php echo $dataCurrent; ?>, color:"#33b5e5", bars:{ numbers:{ font:{ weight:"bold", color:"#111111" }} } },
				                     		  						{ label:<?php echo $pastYear; ?>, data: <?php echo $dataPast; ?>, color:"#cdcdcd", bars:{ numbers:{ font:{ weight:"normal", color:"#777777" }}} } ],
				                     		   						{ series: { bars:{ show:true, barWidth:0.7, lineWidth:1, align:"center", numbers:{ show:true, processing:function(val) { return val>0?Math.round(val*100)/100:""; } } } },
   		   																				xaxis: { ticks:<?php echo $ticks; ?>, minTickSize:1 },
				                     										grid: { show:true, color:"#666666", backgroundColor:"#ffffff", borderColor:"#666666", borderWidth:{top:0, right:0, bottom:1, left:1} } });	
				});
		</script>
	</div>
	
	<div class="span6 app-stat-box" >
		<h4>Daily sales average evolution<em class="appname muted" ><?php echo $mSelectedAppName; ?></em></h4>	
		<div id="currentMonthSales" style="width:470px; height:200px;" ></div>
		
		<div class="show-tbl-row" style="" >
			<button type="button" class="btn btn-link" data-toggle="collapse" data-target="#currentMonthSalesTbl" >
	  		<i class="icon-list" ></i>&nbsp;Show/hide table&nbsp;</button>		
		</div>

		<div id="currentMonthSalesTbl" class="collapse" >
			<table class="table table-condensed tbl-dailysalesavgevo" >
			<thead><tr><th>Day of Month</th><th><?php echo $pastMonthName; ?></th><th><?php echo $currentMonthName; ?></th><th style="width:35px;" ></th></thead>
			<tbody>
			<?php		
				$dataCurrent = array();
				$dataPast = array();
			
				$arrCurrent = DbHelper::selectRows(TBL_INCREMENTS,
																			null,
																			// order
																			'inc ASC LIMIT '.date('d'),
																			// projection
																			'DATE("'.$currentYear.'-'.$currentMonth.'-01") + INTERVAL '.INC_VALUE.' DAY,'.
																			'(SELECT COUNT(*)/DAY(DATE("'.$currentYear.'-'.$currentMonth.'-01") + INTERVAL '.INC_VALUE.' DAY) FROM '.TBL_SALES.' WHERE '.$whereFilterApp.' AND ( DATEDIFF(DATE('.SALE_ORDER_CHARGED_DATE.'), DATE("'.$currentYear.'-'.$currentMonth.'-01"))>=0 AND DATEDIFF(DATE('.SALE_ORDER_CHARGED_DATE.'), DATE("'.$currentYear.'-'.$currentMonth.'-01") + INTERVAL '.INC_VALUE.' DAY)<=0 ) ) current_month',
																			// group by
																			null, null, false);
				$arrPast = DbHelper::selectRows(TBL_INCREMENTS,
																			null,
																			// order
																			'inc ASC LIMIT '.date('t', mktime(0, 0, 0, $pastMonth, 1, $currentYear)),
																			// projection
																			'DATE("'.$currentYear.'-'.$pastMonth.'-01") + INTERVAL '.INC_VALUE.' DAY,'.
																			'(SELECT COUNT(*)/DAY(DATE("'.$currentYear.'-'.$pastMonth.'-01") + INTERVAL '.INC_VALUE.' DAY) FROM '.TBL_SALES.' WHERE '.$whereFilterApp.' AND ( DATEDIFF(DATE('.SALE_ORDER_CHARGED_DATE.'), DATE("'.$currentYear.'-'.$pastMonth.'-01"))>=0 AND DATEDIFF(DATE('.SALE_ORDER_CHARGED_DATE.'), DATE("'.$currentYear.'-'.$pastMonth.'-01") + INTERVAL '.INC_VALUE.' DAY)<=0 ) ) past_month',
																			// group by
																			null, null, false);
				
				$maxCurrent = count($arrCurrent);
				$maxPast = count($arrPast);
				if ($maxPast>$maxCurrent) {
					$max = $maxPast;
					$dateArr =& $arrPast;
					
				} else {
					$max = $maxCurrent;
					$dateArr =& $arrCurrent;
				}
					
				$i = 0; $currentPrev = -1; // current month, previous day value
				for ($i=0; $i<$max; ++$i) { 
					echo '<tr><td>'.date('d', strtotime($dateArr[$i][0])).'</td><td>';
					
					$current = isset($arrCurrent[$i])?round($arrCurrent[$i][1], 2):-1;
					$past = isset($arrPast[$i])?round($arrPast[$i][1], 2):-1;
					
					if ($past>0) {
						$dataPast[] = '['.$i.','.$past.']';

						echo ($past>$current && $current!=-1?'<strong>'.$past.'</strong>':$past).'</td>';
						
					} else { echo ' - </td>'; }

					if ($current>0) {
						$dataCurrent[] = '['.$i.','.$current.']';

						if ($currentPrev>0) {
							echo '<td>', ($current>$past?'<strong>'.$current.'</strong>':$current), '</td>',
										'<td><span class="badge" style="background-color:', ($current>$currentPrev?'#99cc00':($current==$currentPrev?'':'#ff4444')),'" ><i class="', ($current>$currentPrev?'icon-arrow-up':($current==$currentPrev?'icon-minus':'icon-arrow-down')),' icon-white" ></i></span></td></tr>';
							
						} else { echo '<td>', ($current>$past?'<strong>'.$current.'</strong>':$current), '</td><td></td></tr>'; }						
						
					} else { echo '<td> - </td><td></td></tr>'; }
					
					$currentPrev = $current;
				}
			?>
			</tbody>
			</table>
		</div>
		
		<script type="text/javascript" >		
 			$(function() {
 					var graph = $.plot("#currentMonthSales",	
									[ { label:"<?php echo $pastMonthName; ?>", data:[<?php echo implode(',', $dataPast); ?>], color:"#33B5E5", valueLabels:{	show:true, showLastValue:true } },
										{ label:"<?php echo $currentMonthName; ?>", data:[<?php echo implode(',', $dataCurrent); ?>], color:"#9440ED", valueLabels:{	show:true, showLastValue:true } } ],
									{ series:{ lines:{ show:true }, points:{ show:true } },
										 xaxis:{  },
										
										legend: { show:true, position:'se' },
										  grid:{ show:true, hoverable:true, color:"#666666", backgroundColor:"#ffffff", borderColor:"#666666", borderWidth:{top:0, right:0, bottom:1, left:1} }
									});				

 		 	});
		</script>
	</div>
</div>

<div class="row" >
	<!-- ////// SALES EVOLUTION ////// -->
	<div class="span8 app-stat-box" >
		<h4>Sales Evolution (last 60 days)</h4>	
		<div id="salesEvolution" style="width:570px; height:200px;" ></div>
		<?php // if "all application" selected then display a stacked chart
			if ($isAllAppSelected) {
				$series = array();

				$mAppArr = DbHelper::selectRows(TBL_APPLICATIONS, null, APP_NAME.' ASC', '*', null, null, false);
				foreach ($mAppArr as $app) {
					$arr = DbHelper::selectRows(TBL_INCREMENTS,
																				null,
																				// order
																				'inc DESC LIMIT 120,61',
																				// projection
																				'CURDATE() - INTERVAL '.INC_VALUE.' DAY,'.
																				'(SELECT COUNT(*) FROM '.TBL_SALES.' WHERE '.SALE_APP_ID.'='.$app[APP_ID].' AND DATEDIFF('.SALE_ORDER_CHARGED_DATE.', CURDATE() - INTERVAL '.INC_VALUE.' DAY)<=0 ) count',
																				// group by
																				null, null, false);

					// skip application without any sales (maybe free apps)
					if ($arr[count($arr)-1][1]==0) 
						continue;
	
					$data = array();
					foreach ($arr as $row) {
						$data[] = '[gd("'.$row[0].'"),'.$row[1].']';
					}
					
					$series[$app[APP_NAME]] = $data;
				}
		?>
		<script type="text/javascript" >		
 			$(function() {
					$.plot("#salesEvolution",	
									[ <?php $sep = ''; foreach ($series as $label=>$data) { echo $sep, '{ label:"', $label, '", data:[', implode(',', $data), '], valueLabels:{	show:true, showLastValue:true }}'; $sep = ','; } ?> ],
									{ series:{ stack:true, lines:{ show:true, fill:true } },
										 xaxis:{ mode:"time", timeformat:"%b %d" },
										legend: { show:true, container:'#salesEvolutionLegend'  },
										  grid:{ show:true, color:"#666666", backgroundColor:"#ffffff", borderColor:"#666666", borderWidth:{top:0, right:0, bottom:1, left:1} }
									});				
	 			});
		</script>
		<?php // else if an application is selected then display "all application" and selected application sales
			} else {
				$arr = DbHelper::selectRows(TBL_INCREMENTS,
																			null,
																			// order
																			'inc DESC LIMIT 120,61',
																			// projection
																			'CURDATE() - INTERVAL '.INC_VALUE.' DAY,'.
																			'(SELECT COUNT(*) FROM '.TBL_SALES.' WHERE DATEDIFF('.SALE_ORDER_CHARGED_DATE.', CURDATE() - INTERVAL '.INC_VALUE.' DAY)<=0 ) all_count,'.
																			'(SELECT COUNT(*) FROM '.TBL_SALES.' WHERE '.$whereFilterApp.' AND DATEDIFF('.SALE_ORDER_CHARGED_DATE.', CURDATE() - INTERVAL '.INC_VALUE.' DAY)<=0 ) app_count',
																			// group by
																			null, null, false);

				$dataAll = array();
				$dataApp = array();

				foreach ($arr as $row) {
					$dataAll[] = '[gd("'.$row[0].'"),'.$row[1].']';
					$dataApp[] = '[gd("'.$row[0].'"),'.$row[2].']';
				}

				$idx = count($arr)-1;
				$allLastValue = $arr[$idx][1];
				$appLastValue = $arr[$idx][2];
		?>
		<script type="text/javascript" >		
 			$(function() {
					$.plot("#salesEvolution",	
									[ { label:"All Application (<?php echo $allLastValue; ?>)", data:[<?php echo implode(',', $dataAll); ?>], color:"#33B5E5" }, 
										{ label:"<?php echo $mSelectedAppName, ' (', $appLastValue, ')'; ?>", data:[<?php echo implode(',', $dataApp); ?>], color:"#9440ED" } ],
									{ series:{  },
										 xaxis:{ mode:"time", timeformat:"%b %d" },
										legend: { show:true, container:'#salesEvolutionLegend' },
										  grid:{ show:true, color:"#666666", backgroundColor:"#ffffff", borderColor:"#666666", borderWidth:{top:0, right:0, bottom:1, left:1} }
									});				
	 			});
		</script>
		<?php } ?>
	</div>
	
	<div id="salesEvolutionLegend" class="span4" style="padding-top:30px;" ></div>
</div>



<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="libs/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.pie.min.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.stack.min.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.canvas.min.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot-barnumbers/jquery.flot.barnumbers.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot-valuelabels/jquery.flot.valuelabels.js"></script>
<script type="text/javascript" src="assets/functions-chart.js" ></script>