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

$cfg = CfgHelper::getInstance();
?>

<div class="row" style="margin-top:0px;" >
	<div class="span12" >
		<h4 class="with-icon" ><img src="assets/images/ic_bug.png" />New reports</h4>
		<div id="newreports" class="well" ><?php include('pages/new_issues_box.php'); ?></div>
	</div> 
</div>

<div class="row" style="margin-top:0px;" >
	<div class="span4" >
		<h4>Issues States</h4>
		<div id="chart2-2" ><?php include('pages/issues_states_box.php'); ?></div>
	</div>

	<div class="span8" >
		<h4>Evolution</h4>
		<div id="chart2-1" class="home-chart-row2-height" ></div>
		
		<ul class="inline" style="margin-top:20px;" >
		<?php 
			$currentYear = date('Y');
			$res = DBHelper::selectRows(TBL_REPORTS, null, null, 
																	'(SELECT count(*) FROM `'.DBHelper::getTblName(TBL_REPORTS).'` WHERE DATE_FORMAT('.REPORT_CRASH_DATE.',"%Y")=\''.$currentYear.'\') current, '.
																	'(SELECT count(*) FROM `'.DBHelper::getTblName(TBL_REPORTS).'` WHERE DATE_FORMAT('.REPORT_CRASH_DATE.',"%Y")=\''.($currentYear-1).'\') past, '.
																	'(SELECT count(*) FROM `'.DBHelper::getTblName(TBL_REPORTS).'` WHERE DATE('.REPORT_CRASH_DATE.')=CURDATE()) today',
																	null, '1', false);
			$res = $res[0];


			$current = round($res[0]/365, 2);
			$past = round($res[1]/365, 2);
			$today = $res[2];
		?>
			<li>New reports average</li><li><i class="<?php if ($current>$past) echo 'icon-arrow-up'; else if ($current<$past) echo 'icon-arrow_down'; else echo 'icon-minus'; ?>" ></i>&nbsp;<?php echo $current; ?>/day</li>
			<li style="padding-left:30px;" >Last year</li><li><?php echo $past; ?>/day</li>
			<li style="padding-left:30px;" >Today</li><li><?php echo $today; ?></li>
		</ul>
		
		<ul class="inline" style="margin-top:10px;" >
		<?php 
			$res = DBHelper::selectRows(TBL_REPORTS, null, null, 'count(*)', REPORT_INSTALLATION_ID, null, false);
						
			$max = 0; $sum = 0;
			foreach ($res as $row) {
				$count =& $row[0];

				if ($count>$max)
					$max = $count;
				
				$sum += $count;
			}
		?>
			<li>Reports per installation</li>
			<li>Avg <?php echo round($sum/sizeOf($res), 2); ?> / Max <?php echo $max; ?></li>
<!-- 			<li>Distinct reports per installation</li> -->
<!-- 			<li>Avg 1,2 / Max 2,1</li> -->
		</ul>
	</div>
</div>

<div class="row" style="margin-top:20px;" >
	<div class="span4" >
		<h4>Reports per Application</h4>
		<div id="chart1-2" class="home-chart-row1-height" ></div>
	</div> 
	<div class="span4" >
		<h4>Reports per Android version</h4>
		<div id="chart1-1" class="home-chart-row1-height" ></div>
	</div> 
	<div class="span4" >
		<h4>Most Affected Devices</h4>
		<div id="chart1-3" class="home-chart-row1-height" ><?php include('pages/most_affected_devices_box.php'); ?></div>
	</div> 
</div>

<div id="reportDialog" class="modal hide fade" style="width:1000px; margin-left:-500px; height:700px;" ></div>

<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="libs/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.pie.min.js"></script>
<script type="text/javascript" >
	var REPORTS_PER_ANDROID_VERSION_PIE_CHART_ID = <?php echo REPORTS_PER_ANDROID_VERSION_PIE_CHART_ID; ?>;
	var REPORTS_PER_APPLICATION_PIE_CHART_ID = <?php echo REPORTS_PER_APPLICATION_PIE_CHART_ID; ?>;
	var REPORTS_EVOLUTION_LINE_CHART_ID = <?php echo REPORTS_EVOLUTION_LINE_CHART_ID; ?>;

	var REFRESH_INTERVAL = <?php echo $cfg->getDashboardRefreshIntervalInMillis(); ?>;
	var HOME_PAGE = true;
</script>
<script type="text/javascript" src="assets/functions-chart.js" ></script>
<script type="text/javascript" src="assets/functions-issues.js" ></script>
<script type="text/javascript" src="assets/functions-home.js" ></script>