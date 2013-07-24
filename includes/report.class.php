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
define('REPORT_STATE_ARCHIVED', 0);


class Report {
	
	public $report_id;
	
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
	
	public $report_state;
	public $report_tag;
	
	
	// CONSTRUCTOR
	public static function createFromArray($values) {
		$obj = new Report();
		foreach($values as $k=>$v) {
			if (empty($v) || !is_string($k)) continue;
			
			if (in_array(strtoupper($k), ReportHelper::$mSerializedFields)) {
				$tmp_arr = unserialize(base64_decode($v));
				
				if (sizeof($tmp_arr)==0) {
					$obj->{$k} = null;
					
				} else {
					if ($k=='display') {
						$obj->{$k} = array();
						
						foreach ($tmp_arr as $values)
							$obj->{$k}[] = (object)$values;
						
					} else if ($k=='shared_preferences') {
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
	
	public function getFormatedDate() {
		return ReportHelper::formatDate($this->user_crash_date, CfgHelper::getInstance()->getDateFormat());
	}
	
	public function getFormatedAppStartDate() {
		return ReportHelper::formatDate($this->user_app_start_date, CfgHelper::getInstance()->getDateFormat());
	}
	
	public function getFormatedLogCat() {
		$search = '--------- beginning of ';
		
		return str_replace($search, '<hr/>', substr($this->logcat, strlen($search)));
	}
	
	public function getFormatedMemInfo() {
		return str_replace('Permission Denial', '<span class="text-error" >Permission Denial</span>', $this->dumpsys_meminfo);
	}
	
	public function getFormatedTotalMemSize() { return ReportHelper::formatMemSize($this->total_mem_size); }
	
	public function getFormatedAvailMemSize() { return ReportHelper::formatMemSize($this->available_mem_size); }
	
	public function getFormatedCustomData() { 
		require_once('includes/customdataformatter.class.php');
		
		return CustomDataFormatter::format($this->custom_data); 
	}
	
	public function hasCustomData() { return (empty($this->custom_data)?false:true); }
	
	public function isNew() { return ($this->report_state==REPORT_STATE_NEW); }
	
	public function isArchived() { return ($this->report_state==REPORT_STATE_ARCHIVED); }
}