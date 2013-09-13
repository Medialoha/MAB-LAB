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
?>
<div class="row" >
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
		<div id="chart1-3" class="home-chart-row1-height" ></div>
	</div> 
</div>

<div class="row" style="margin-top:30px;" >
	<div class="span8" >
		<h4>Evolution</h4>
		<div id="chart2-1" class="home-chart-row2-height" ></div>
	</div> 
</div>

<div class="row" style="margin-top:30px;" >
	<div class="span12" >
		<h4>Last reports</h4>
		<div id="lastreports" ></div>
	</div> 
</div>

<div class="row" style="margin-top:30px;" >
	<div class="span12" >
		<h4>Most error reports</h4>
		<div id="mosterrorreports" ></div>
	</div> 
</div>

<div id="stackTraceDialog" class="modal hide fade" style="width:900px; margin-left:-450px;" ></div>
<div id="reportDialog" class="modal hide fade" style="width:1000px; margin-left:-500px; height:700px;" ></div>

<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="libs/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="libs/flot/jquery.flot.pie.min.js"></script>
<script type="text/javascript" >
	var REPORTS_PER_ANDROID_VERSION_PIE_CHART_ID = <?php echo REPORTS_PER_ANDROID_VERSION_PIE_CHART_ID; ?>;
	var REPORTS_PER_APPLICATION_PIE_CHART_ID = <?php echo REPORTS_PER_APPLICATION_PIE_CHART_ID; ?>;
	var REPORTS_EVOLUTION_LINE_CHART_ID = <?php echo REPORTS_EVOLUTION_LINE_CHART_ID; ?>;
</script>
<script type="text/javascript" src="assets/functions-chart.js" ></script>
<script type="text/javascript" src="assets/functions-reports.js" ></script>
<script type="text/javascript" src="assets/functions-home.js" ></script>