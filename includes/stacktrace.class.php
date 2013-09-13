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
 *     MAREK MASLANKA - Logintar - added review reports by the same errors and formatting logcat
 */


class StackTrace {
	
	public $report_id;
	
	public $app_version_code;
	public $app_version_name;
	public $package_name;
	
	public $count;
	public $last_crash_date;
	
	public $stack_trace;
	
	// CONSTRUCTOR
	public static function createFromArray($values) {
		$obj = new StackTrace();
		foreach($values as $k=>$v) {
			if (empty($v) || !is_string($k)) continue;
			
			if (in_array(strtoupper($k), ReportHelper::$mSerializedFields)) {
				$tmp_arr = unserialize(base64_decode($v));
				
				if (sizeof($tmp_arr)==0) {
					$obj->{$k} = null;
					
				} else {
					if ($k=='display') {
					} else if ($k=='shared_preferences') {
					} else { $obj->{$k} = (object)$tmp_arr; }
				}
								
			} else { $obj->{$k} = $v; }
		}
			
		return $obj;
	}
	
	public function getApplicationDesc() {
		return ReportHelper::formatPackageName($this->package_name, true)." ".$this->app_version_name." #".$this->app_version_code;
	}
	
	public function getTitle() {
		return StackTraceHelper::getTitleFromStackTrace($this->stack_trace);
	}
	
	public function getFormatedSystrace() {
		return ReportHelper::translateQuoted($this->stack_trace);
	}
}