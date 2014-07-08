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
define('FORMAT_ENV_KEY', 1);
define('FORMAT_SETTINGS_KEY', 2);


class ReportHelper {
	
	public static $mSerializedFields = array('SETTINGS_GLOBAL', 'SETTINGS_SECURE', 'SETTINGS_SYSTEM', 'DEVICE_FEATURES', 'SHARED_PREFERENCES', 'CRASH_CONFIGURATION', 'BUILD', 'DISPLAY', 'CUSTOM_DATA', 'INITIAL_CONFIGURATION', 'ENVIRONMENT' );
	
	
	public static function checkState($state) {
		switch ($state) {
			case REPORT_STATE_NEW : 
			case REPORT_STATE_VIEWED :
			case REPORT_STATE_CLOSED :
			case REPORT_STATE_TESTING :
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
			case REPORT_STATE_TESTING :
				return 'testing';
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
		$json['USER_APP_START_DATE'] = self::convertRFCDateToMySQLTimestamp($json['USER_APP_START_DATE']);
		
		$values = array();
		foreach ($json as $k=>$v) {
			$k = strtolower($k);
			
			if (in_array(strtoupper($k), self::$mSerializedFields)) {
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
	
	public static function displayObjectValuesToHTMLArray($obj, $formatKeyFunction=0) {
		if ($obj==null) { ?><p class="muted" ><i>No data collected</i></p><?php return; }
		
		$values = get_object_vars($obj);
		
		// order on key
		ksort($values);
		
		?><table class="table table-condensed table-hover" ><tr><th style="min-width:370px; border:0 none;" ></th><th></th></tr><?php
		foreach($values as $key=>$value) {
			if (is_array($value)) {
				$value = print_r($value, true);
				
			} else if (is_bool($value)) {
				$value = '<input type="checkbox" '.($value?'checked="checked"':'').' />';
			}
			
			switch ($formatKeyFunction) {
				case FORMAT_ENV_KEY :
						// split on upper case char
						$key = preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY);
						// remove is or get prefix
						unset($key[0]);
						$key = implode(' ', $key);
					break;
				
				case FORMAT_SETTINGS_KEY : 
						$key = str_replace('_', ' ', $key);
						$key = ucwords(strtolower($key));
					break;
			}
			
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
				if (is_array($value)) {
					$value = print_r($value, true);
				
				} else if(is_bool($value)) {
					$value = '<input type="checkbox" '.($value ? 'checked="checked"' : '').'>';
					
				} 
				
				echo '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
				
			} 
		}
		
		?></table><?php
	}
	
	public static function displayDeviceDisplayValues($dispArr) {
		if (!is_array($dispArr)) { ?><p class="muted" ><i>Nothing recorded</i></p><?php return; }
		
		foreach($dispArr as $obj) {
	//		$values = get_object_vars($obj);
						
			?><table class="table table-condensed table-hover" >
				<tr>
					<td>Orientation</td>
					<td>
					<?php 
						if (isset($obj->orientation) && isset($obj->rotation))
							echo ($obj->orientation==0?'Portrait':'Landscape'), '&nbsp;#', $obj->orientation, '&nbsp;&nbsp;', $obj->rotation; 
						else
							echo '<i class="muted text-i" >Not set</i>';
					?>
					</td>
				</tr>
				<tr>
					<td>Current Size Range</td>
					<td>
						<?php if (isset($obj->currentSizeRange)) { ?>
							<span class="muted" >Largest</span>&nbsp;<?php echo $obj->currentSizeRange['largest']; ?>&nbsp;-&nbsp;<span class="muted" >Smallest</span>&nbsp;<?php echo $obj->currentSizeRange['smallest']; ?>
						<?php } else { ?><i class="muted text-i" >Not set</i><?php } ?>
					</td>
				</tr>
				<tr>
					<td>Size&nbsp;/&nbsp;Real Size</td>
					<td><?php echo $obj->getSize; ?>&nbsp;/&nbsp;<?php echo $obj->getRealSize; ?></td>
				</tr>
				<tr>
					<td>Pixel Format</td><td><?php echo isset($obj->pixelFormat)?$obj->pixelFormat:'<i class="muted text-i" >Not set</i>'; ?></td>
				</tr>
				<tr>
					<td>Refresh Rate</td><td><?php echo isset($obj->refreshRate)?$obj->refreshRate:'<i class="muted text-i" >Not set</i>'; ?></td>
				</tr>
			</table><?php 
		}
	}
	
	/**
	 * 
	 * @param $error true if report not inserted in DB
	 * @param $package application package as array
	 * @param $JSONArr
	 * @return HTML content
	 */
	public static function createMailContent($error, $package, $JSONArr) {
		$html = '<center><table border="0" >';
		
		if ($error)
			$html .= '<tr><td colspan="2" style="color:red" ><br/><center>New report received but <b>not inserted</b> due to unhandled error !!!</center><br/></td></tr>';
		
		$html .= '<tr><td width="200" >Application </td><td>'.ucfirst($package[count($package)-1]).' '.$JSONArr['APP_VERSION_NAME'].' #'.$JSONArr['APP_VERSION_CODE'].'</td></tr>';

		$html .= '<tr><td>User comment </td><td><em>'.(empty($JSONArr['USER_COMMENT'])?'<em style="color:grey" >No comment</em>':$JSONArr['USER_COMMENT']).'</em></td></tr>';

		$html .= '<tr><td colspan="2" ><br/><br/><hr/>'.self::formatMailStackTrace($JSONArr['STACK_TRACE']).'</td></tr>';

		return $html.'</table></center>';
	}
	

	private static function formatMailStackTrace($stack) {
		if (empty($stack))
			return '<p><i>No data collected...</i></p>';

		$groups = explode('Caused by: ', $stack);
		$s = "<dl>";

		foreach ($groups as $groupIdx=>$group) {
			$lines = explode("\tat", $group);
			$lastLine = sizeOf($lines)-1;
		
			foreach ($lines as $lineIdx=>$line) {

				// first line of each group is a cause
				if ($lineIdx==0) {
				
					// split line if too long
					self::formatStackTraceCausedByLine($line);
				
					if ($groupIdx==0) {
						$s .= '<dt>'.$line.'</dt>';

					} else { $s .= '<dt><strong>Caused by&nbsp;</strong>:&nbsp;'.$line.'</dt>'; }
				
				// stack line
				} else {
					$more = false;
					$className = false;
				
					// check more line, two cases :
					//   - last line of a group : android.view.LayoutInflater.createView(LayoutInflater.java:586) ... 28 more
					//   - inside a group : android.widget.TextView.(TextView.java:571) ... 31 more java.lang.reflect.InvocationTargetException
					$tmpArr = explode('...', $line);
						
					if (isset($tmpArr[1])) {
						if ($lineIdx==$lastLine) {
							if (isset($tmpArr[1])) {
								$more = '<dd>...&nbsp;'.$tmpArr[1].'</dd>';
							}
		
						} else {
							list($line, $tmpStr) = explode('...', $line);
							list($count, $className) = explode('more', $tmpStr);
								
							$more = '<dd>...&nbsp;'.$count.' more</dd>';
						}
					}
		
					// process 'at' line : android.view.LayoutInflater.createView(LayoutInflater.java:586)
					list($method, $class_and_line_number) = explode('(', $line);
					$class_and_line_number = substr($class_and_line_number, 0, strlen($class_and_line_number)-2);
						
					$s .= '<dd>'.
									'<i style="color:grey" >at&nbsp;</i>'.$method;
		
					// class and line number
					if (!empty($class_and_line_number)) {
						$class_and_line_number = explode(':', $class_and_line_number);
		
						if (sizeOf($class_and_line_number)>1) {
							$s .= '&nbsp;(<strong>'.$class_and_line_number[0].'</strong> : <span style="color:orange" >'.$class_and_line_number[1].'</span>)';
								
						} else { $s .= $class_and_line_number[0]; }
		
					} // else { $class_and_line_number = ''; }
						
					$s .= '</dd>';
						
					if ($more!==false) {
						$s .= $more;
					}
						
					if ($className!==false) {
						$s .= '<dt>'.self::formatStackTraceCausedByLine($className).'</dt>';
					}
				}
			}
		}
		
		return $s.'</dl>';
	}
	
	private static function hiliteStackTraceException(&$s) {
		$pos = stripos($s, 'Exception');
	
		if ($pos!==false) {
			$dotPos = strripos($s, '.', 0);
			$length = $pos-$dotPos+8;
				
			$s = substr_replace($s, '<strong>'.substr($s, $dotPos+1, $length).'</strong>', $dotPos+1, $length);
		}
	}
	
	private static function formatStackTraceCausedByLine(&$line) {
		$strlen = strlen($line);
		if ($strlen>120) {
			$line = str_replace(': ', '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;</span>', $line);
		}
			
		self::hiliteStackTraceException($line);
	
		return $line;
	}
}
