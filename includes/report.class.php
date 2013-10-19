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

define('REPORT_STATE_NEW', 1);
define('REPORT_STATE_VIEWED', 2);
define('REPORT_STATE_CLOSED', 3);
define('REPORT_STATE_ARCHIVED', 0);


class Report {
	
	public $report_id;  // primary key
	
	public $report_key; // unique
	
	public $app_version_code;
	public $app_version_name;
	public $package_name;
	
	public $brand;
	public $phone_model;
	public $product;
		
	public $android_version;
	public $build;
	public $total_mem_size;
	public $available_mem_size;
	public $display;
	public $device_features;
	
	public $device_id;
	public $installation_id;
	
	public $initial_configuration;
	public $crash_configuration;
	
	public $custom_data;
	
	public $user_comment;
	public $user_email;
	public $user_app_start_date;
	public $user_crash_date;
	
	public $stack_trace;
	public $logcat;
	public $eventslog;
	public $radiolog;
	public $dumpsys_meminfo;
	
	public $shared_preferences;
	public $settings_system;
	public $settings_secure;
	public $settings_global;
	
	public $file_path;
	public $dropbox;
	public $is_silent;
	public $environment;
	
	private $report_state;
	
	
	// CONSTRUCTOR
	public function Report() { 
		$this->report_id = 0;
	}
	
	public static function createFromArray($values) {
		$obj = new Report();
		
		if (!empty($values))
			foreach($values as $k=>$v) {
				if (!is_string($k)) continue;
				
				if (in_array(strtoupper($k), ReportHelper::$mSerializedFields) && !empty($v)) {
					$tmp_arr = unserialize(base64_decode($v));
					
					if (sizeof($tmp_arr)==0) {
						$obj->{$k} = null;
						
					} else {
						if ($k=='display' && !empty($v)) {
							$obj->{$k} = array();
							
							foreach ($tmp_arr as $values)
								$obj->{$k}[] = (object)$values;
							
						} else if ($k=='shared_preferences' && !empty($v)) {
							$obj->{$k} = array();
	
							foreach ($tmp_arr as $idx=>$values) {							
								if (empty($idx)) {
									$obj->{$k}['count'] = intval($values);
									
								} else { $obj->{$k}[$idx] = (object)$values; }
							}
							
						} else { $obj->{$k} = (object)$tmp_arr; }
					}
									
				} else { $obj->{$k} = $v; }
			}
			
		return $obj;
	}
	
	
	public function getApplicationDesc() {
		return ReportHelper::formatPackageName($this->package_name, true)." ".$this->app_version_name." #".$this->app_version_code;
	}
	
	public function getDeviceDesc() {
		return ucfirst($this->brand).' '.$this->phone_model.' <span class="muted" >'.$this->product.'</span>';
	}
	
	public function getFormattedDate() {
		return Helper::formatDate($this->user_crash_date, CfgHelper::getInstance()->getDateFormat());
	}
	
	public function getFormattedAppStartDate() {
		return Helper::formatDate($this->user_app_start_date, CfgHelper::getInstance()->getDateFormat());
	}
	
	// Format logcat (supported format is time) 
	public function getFormattedLogCat() {
		if (empty($this->logcat))
			return '<p class="nodata" >No data collected...</p>';
		
		$lines = explode("\n", $this->logcat);
		$s = "";
		
		foreach ($lines as $line) {
			if (empty($line)) continue;
			
			if (substr_compare($line, '-', 0, 1)==0) {
				$s .= '<tr><td colspan="6" class="logcat-filename" >'.
								substr_replace($line, '<i class="icon-file" ></i>&nbsp;', 0, 9).
							'</td></tr>';
				
			} else {
				list($details, $message) = explode('): ', $line);
				list($datetime_priority, $tag_pid) = explode('/', $details);
				
				$html = "";
				$priority = 'D';
								
				// format date time and priority
				$cols = explode(' ', $datetime_priority);
				foreach ($cols as $idx=>$value) {
					switch ($idx) {
						case 0 : // DATE
								$html .= '<td class="datetime" >'.$value;
							break;
						case 1 : // TIME
								$time = explode(':', $value);
								
								$html .= ' '.$time[0].':'.$time[1].'</td><td>'.$time[2].'</td>';
							break;
						case 2 : // PRIORITY
								$html .= '<td><span class="'.$this->getPriorityLabelClass($value).'" >'.$value.'</span></td>';
								$priority = $value;
							break;
					}
				}
				
				// format tag and pid
				$cols = explode('(', $tag_pid);
				foreach ($cols as $idx=>$value) {
					switch ($idx) {
						case 0 : // TAG
								$html .= '<td>'.$value.'</td>';
							break;
						case 1 : // PID
								$html.= '<td>'.$value.'</td>';
							break;
					}
				}
								
				$s .= '<tr class="'.$priority.'" >'.$html.'<td>'.$message.'</td></tr>';
			}
		}
		
		return '<table class="logcat-tbl" >'.$s.'</table>';
	}
	
	public function getFormattedMemInfo() {
		if (empty($this->dumpsys_meminfo))
			return '<p class="nodata" >No data collected...</p>';
		
		return str_replace('Permission Denial', '<span class="text-error" >Permission Denial</span>', $this->dumpsys_meminfo);
	}
	
	public function getFormattedTotalMemSize() { return ReportHelper::formatMemSize($this->total_mem_size); }
	
	public function getFormattedAvailMemSize() { return ReportHelper::formatMemSize($this->available_mem_size); }
	
	public function getFormattedCustomData() { 
		require_once(BASE_PATH.'includes/customdataformatter.class.php');
		
		return CustomDataFormatter::format($this->custom_data); 
	}
	
	public function getFormattedRadioLog() {
		if (empty($this->radiolog))
			return '<p class="nodata" >No data collected...</p>';
		
		return $this->radiolog;
	}
	
	public function getFormattedEventsLog() {
		if (empty($this->eventslog))
			return '<p class="nodata" >No data collected...</p>';
		
		return $this->eventslog;
	}
	
	public function getFormattedStackTrace() {
		if (empty($this->stack_trace))
			return '<p class="nodata" >No data collected...</p>';
		
		$groups = explode('Caused by: ', $this->stack_trace);
		$s = "";
		
		foreach ($groups as $groupIdx=>$group) {
			$lines = explode("\tat", $group);
			$lastLine = sizeOf($lines)-1;
			
			foreach ($lines as $lineIdx=>$line) { 
				
				// first line of each group is a cause
				if ($lineIdx==0) {
					
					// split line if too long
					$this->formatStackTraceCausedByLine($line);
					
					if ($groupIdx==0) {
						$s .= '<div class="stacktrace-message" ><i class="icon-arrow-down"></i>&nbsp;&nbsp;'.$line.'</div>';
						
					} else { $s .= '<div class="stacktrace-causedby" ><strong>Caused by&nbsp;</strong>:&nbsp;'.$line.'</div>'; }
					
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
								$more = '<div class="stacktrace-more" >...&nbsp;'.$tmpArr[1].'</div>';	
							}
						
						} else {
							list($line, $tmpStr) = explode('...', $line);
							list($count, $className) = explode('more', $tmpStr);
							
							$more = '<div class="stacktrace-more" >...&nbsp;'.$count.' more</div>';
						}
					}

					// process 'at' line : android.view.LayoutInflater.createView(LayoutInflater.java:586)
					list($method, $class_and_line_number) = explode('(', $line);
					$class_and_line_number = substr($class_and_line_number, 0, strlen($class_and_line_number)-2);
					
					$s .= '<div class="stacktrace-at" >'.
									'<i class="muted" >at&nbsp;</i>'.$method;

					// class and line number
					if (!empty($class_and_line_number)) {
						$class_and_line_number = explode(':', $class_and_line_number);
						
						if (sizeOf($class_and_line_number)>1) {
							$s .= '&nbsp;(<strong>'.$class_and_line_number[0].'</strong> : <span class="line-number" >'.$class_and_line_number[1].'</span>)';
							
						} else { $s .= $class_and_line_number[0]; }
						
					} // else { $class_and_line_number = ''; }
					
					$s .= '</div>';					
					
					if ($more!==false) {
						$s .= $more;
					}
					
					if ($className!==false) {
						$s .= '<div class="stacktrace-causedby" style="padding-left:10px;" >'.$this->formatStackTraceCausedByLine($className).'</div>';
					}
				}
			}

		}
			
		return $s;
	}
	
	public function hasCustomData() { return (empty($this->custom_data)?false:true); }
	
	public function isNew() { return ($this->report_state==REPORT_STATE_NEW); }
	
	public function isArchived() { return ($this->report_state==REPORT_STATE_ARCHIVED); }
	
	public function isOpen() { return ($this->report_state==REPORT_STATE_NEW || $this->report_state==REPORT_STATE_VIEWED); }
	
	
	private function hiliteStackTraceException(&$s) {
		$pos = stripos($s, 'Exception');
		
		if ($pos!==false) {
			$dotPos = strripos($s, '.', 0);
			$length = $pos-$dotPos+8;
			
			$s = substr_replace($s, '<span class="stacktrace-exception" >'.substr($s, $dotPos+1, $length).'</span>', $dotPos+1, $length);
		}
	}
	
	private function formatStackTraceCausedByLine(&$line) {
		$strlen = strlen($line);
		if ($strlen>120) {
			$line = str_replace(': ', '<br/><span style="padding:0px 5px 0px 40px;" >:</span>', $line);
		}
			
		$this->hiliteStackTraceException($line);
		
		return $line;
	}
	
	private function getPriorityLabelClass($priority_char) {		
		switch ($priority_char) {
			case 'V' : return 'label label-priority-verbose';
			case 'D' : return 'label label-priority-debug';
			case 'I' : return 'label label-priority-info';
			case 'W' : return 'label label-priority-warning';
			case 'E' : return 'label label-priority-error';
		}
	}
}