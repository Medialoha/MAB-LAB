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

	public static $COLORS = array("#33B5E5", "#9440ED", "#B4EA34", "#FFB239", "#F04158", "#FFF145");
	public static $COLOR_COUNT = 6;
	
	

	// $arr[i] = array(0=>label, 1=>value)
	public static function convertMySQLArrToPieChartJSON($arr, $appendValueToLabel=false) {		
		$json = '['; $sep = ''; $c = 0;
		$arr = $arr===null?array():$arr;
		
		foreach($arr as $row) {
			$json .= $sep.'{"label":"'.$row[0].($appendValueToLabel?' ('.$row[1].')':'').'","data":'.$row[1].', color:"'.self::$COLORS[$c].'"}'; $sep = ',';
			
			$c = (++$c)%self::$COLOR_COUNT;
		}
		
		return $json.']';
	}

	// $arr[i] = array(0=>name, 1=>value)
	public static function convertMySQLArrToBarChartJSON($arr) {
		$data = ''; $ticks = ''; $sep = ''; $i = 0;
		foreach($arr as $row) {
			$ticks .= $sep.'['.$i.',"'.$row[0].'"]';
			$data .= $sep.'['.$i.','.$row[1].']';
			
			$sep = ','; ++$i;
		}
	
		return '{"data":['.$data.'], "ticks":['.$ticks.']}';
	}

	/**
	 * O : DATE
	 * 1 : DATE FORMATTED
	 * 2 : NB REPORTS
	 * 3 : NB ISSUES
	 * 4 : AVG REPORTS PER DAY
	 */
	public static function convertMySQLArrToReportsEvolChartJSON($arr) { 
		$reports = array(); $issues = array(); $avg = array(); $ticks = array(); 
		$i = sizeOf($arr);
		
		foreach($arr as $row) {
			$ticks[] = '['.$i.',"'.$row->formatted_date.'"]';
			$reports[] = '['.$i.','.$row->reports.']';
			$issues[] = '['.$i.','.$row->issues.']';
			$avg[] = '['.$i.','.$row->avg_per_day_current_year.']';
			
			--$i;
		}
	
		return '{"reports":['.implode(',', $reports).'], "issues":['.implode(',', $issues).'], "avg":['.implode(',', $avg).'], "ticks":['.implode(',', $ticks).']}';
	}
}