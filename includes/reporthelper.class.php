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


class ReportHelper {
	
	public static $mSerializedFields = array('SETTINGS_GLOBAL', 'SETTINGS_SECURE', 'SETTINGS_SYSTEM', 'DEVICE_FEATURES', 'SHARED_PREFERENCES', 'CRASH_CONFIGURATION', 'BUILD', 'DISPLAY', 'CUSTOM_DATA', 'INITIAL_CONFIGURATION', 'ENVIRONMENT' );
	
	
	public static function checkState($state) {
		switch ($state) {
			case REPORT_STATE_NEW : 
			case REPORT_STATE_VIEWED :
			case REPORT_STATE_CLOSED :
			case REPORT_STATE_ARCHIVED :
				break;
			
			default : return false;
		}	
		
		return true;
	}
	
	public static function getStateTitle($state) {
		switch ($state) {
			case REPORT_STATE_NEW :
				return 'new';
			case REPORT_STATE_VIEWED :
				return 'open';
			case REPORT_STATE_CLOSED :
				return 'resolved';
			case REPORT_STATE_ARCHIVED :
				return 'archived';
					
			default : return 'unknown';
		}
	}
	
	public static function formatPackageName($name, $shrink) {
		if ($shrink && !empty($name)) {
			$arr = explode('.', $name);
			$name = $arr[sizeof($arr)-1];
		}
	
		return ucfirst($name);
	}
	
	public static function formatMemSize($value) {
		$value = $value/(1048576);
		$unit = "MB";
		
		if ($value>=1024) {
			$value = $value/1024;
			$unit = "GB";
		}
		
		return number_format($value, 2).$unit;
	}
	
	public static function convertRFCDateToMySQLTimestamp($date) {
		if (preg_match('/([0-9]{2,4})-([0-9][0-9])-([0-9][0-9])T([0-9][0-9]):([0-9][0-9]):([0-9][0-9])(\.[0-9][0-9][0-9])?(\+|-)([0-9][0-9]):([0-9][0-9])/i', $date, $matches)) {
			
			return date("Y-m-d H:i:s", strtotime($matches[1].'-'.$matches[2].'-'.$matches[3].' '.$matches[4].':'.$matches[5].':'.$matches[6].' '.$matches[8].$matches[9].$matches[10]));
		}
		
		return $date;
	}
	
	public static function buildMySQLValuesArr($json) {
		$json['USER_CRASH_DATE'] = self::convertRFCDateToMySQLTimestamp($json['USER_CRASH_DATE']);
		
		$values = array();
		foreach ($json as $k=>$v) {
			$k = strtolower($k);
			
			if (in_array($k, self::$mSerializedFields)) {
				 $values[$k] = base64_encode(serialize($v));
				
			} else {
				// report_id is translate to report_key
				if (strcmp($k, 'report_id')==0) {
					$values['report_key'] = DBHelper::escapeString($v);
					
				} else { $values[$k] = DBHelper::escapeString($v); } 
			}
		}
		
		return $values;
	}
	
	public static function displayObjectValuesToHTMLArray($obj) {
		if ($obj==null) { ?><p class="muted" ><i>No data collected</i></p><?php return; }
		
		$values = get_object_vars($obj);
		?><table class="table table-condensed table-hover" ><?php
		foreach($values as $key=>$value) {
			if (is_array($value))
				$value = print_r($value, true);
			
			echo '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
		}
		?></table><?php 
	}
	
	public static function displayPreferences($prefs) { 
		if (!is_array($prefs)) { ?><p class="muted" ><i>No data collected</i></p><?php return; }
		
		?><table class="table table-condensed table-hover" ><?php
		
		foreach ($prefs as $name=>$prefObj) {
			?><tr><td colspan="2" class="muted" style="text-transform:uppercase;" ><?php echo $name; ?></td></tr><?php
			if ($name=='count') {
				?><tr><td colspan="2" ><?php echo $prefObj; ?></td></tr><?php 
				continue;
			}
			
			$values = get_object_vars($prefObj);
			foreach($values as $key=>$value) {
				if (is_array($value))
					$value = print_r($value, true);
				
				echo '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
			} 
		}
		
		?></table><?php
	}
	
	public static function displayDeviceDisplayValues($dispArr) {
		if (!is_array($dispArr)) { ?><p class="muted" ><i>Nothing recorded</i></p><?php return; }
		
		foreach($dispArr as $obj) {
			$values = get_object_vars($obj);
						
			?><table class="table table-condensed table-hover" >
				<tr>
					<td>Orientation</td>
					<td><?php echo ($obj->orientation==0?'Portrait':'Landscape').'&nbsp;#'.$obj->orientation; ?>&nbsp;&nbsp;<?php echo $obj->rotation; ?></td>
				</tr>
				<tr>
					<td>Current Size Range</td>
					<td>
						<?php if (isset($obj->currentSizeRange)) { ?>
							<span class="muted" >Largest</span>&nbsp;<?php echo $obj->currentSizeRange['largest']; ?>&nbsp;-&nbsp;<span class="muted" >Smallest</span>&nbsp;<?php echo $obj->currentSizeRange['smallest']; ?>
						<?php } else { ?><i class="muted" >Not set</i><?php } ?>
					</td>
				</tr>
				<tr><td>Size&nbsp;/&nbsp;Real Size</td><td><?php echo $obj->getSize; ?>&nbsp;/&nbsp;<?php echo $obj->getRealSize; ?></td></tr>
				<tr><td>Pixel Format</td><td><?php echo $obj->pixelFormat; ?></td></tr>
				<tr><td>Refresh Rate</td><td><?php echo $obj->refreshRate; ?></td></tr>
			</table><?php 
		}
	}
}