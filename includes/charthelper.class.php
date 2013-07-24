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


define('REPORTS_PER_ANDROID_VERSION_PIE_CHART_ID', '0');
define('REPORTS_PER_APPLICATION_PIE_CHART_ID', '1');
define('REPORTS_EVOLUTION_LINE_CHART_ID', '2');

define('PIE_CHART_TYPE_ID', 0);
define('BAR_CHART_TYPE_ID', 1);


class ChartHelper {
	
	public static function convertMySQLArrToPieChartJSON($arr) {
		$json = '['; $sep = '';
		foreach($arr as $row) {
			$json .= $sep.'{"label":"'.$row[0].'","data":'.$row[1].'}'; $sep = ',';
		}
		
		return $json.']';
	}

	public static function convertMySQLArrToBarChartJSON($arr) {
		$data = ''; $ticks = ''; $sep = ''; $i = 0;
		foreach($arr as $row) {
			$ticks .= $sep.'['.$i.',"'.$row[0].'"]';
			$data .= $sep.'['.$i.','.$row[1].']';
			
			$sep = ','; ++$i;
		}
	
		return '{"data":['.$data.'], "ticks":['.$ticks.']}';
	}
}