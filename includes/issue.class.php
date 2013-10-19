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

define('ISSUE_STATE_NEW', 1);
define('ISSUE_STATE_VIEWED', 2);
define('ISSUE_STATE_CLOSED', 3);
define('ISSUE_STATE_ARCHIVED', 0);

define('REPORT_PROJECTION', REPORT_ID.','.REPORT_KEY.','.REPORT_STATE.','.REPORT_PACKAGE_NAME.','.REPORT_VERSION_NAME.','.REPORT_VERSION_CODE.','.REPORT_ANDROID_VERSION.','.REPORT_PHONE_MODEL.','.REPORT_CRASH_DATE.','.REPORT_USER_COMMENT);


class Issue {
	
	public $issue_id;
	public $issue_key;
	public $issue_cause;
	public $issue_datetime;
	
	private $issue_state;
	private $issue_priority;

	private $package_name;
	private $app_version_name;
	private $app_version_code;
	
	private $issue_reports;
	
	
	// CONSTRUCTOR
	public function Issue() {
		$this->issue_id = 0;
		$this->issue_state = ISSUE_STATE_NEW;
		$this->issue_priority = new IssuePriority(IssuePriority::NORMAL);		
		$this->issue_reports = null;
	}


	public static function createFromArray($values) {
		$obj = new Issue();
		
		foreach($values as $k=>$v) {
			if (!is_string($k)) continue;

			if (strcmp($k, 'issue_priority')==0) { 
				$obj->setPriority($v);
				
			} else { $obj->{$k} = $v; }
		}
		
		return $obj;
	}
	
	
	public function getReports() {
		if ($this->issue_reports==null) {
			$arr = DBHelper::selectRows(TBL_REPORTS, REPORT_ISSUE.'='.$this->issue_id, REPORT_CRASH_DATE.' DESC', REPORT_PROJECTION, null, null, false);
			$this->issue_reports = array();
			
			if (!empty($arr))
				foreach ($arr as $values)
					$this->issue_reports[] = Report::createFromArray($values);
		}
		
		return $this->issue_reports;
	}
	
	public function getLastReport() {
		$reports = $this->getReports();
		if (!empty($reports))
			return $reports[0];
		
		return new Report();
	}
	
	public function getLastReportId() {
		$reports = $this->getReports();
		if (!empty($reports))
			return $reports[0]->report_id;
		
		return 0;
	}
	
	public function getApplicationDesc() {		
		if (!empty($this->package_name))
			return ReportHelper::formatPackageName($this->package_name, true)." ".$this->app_version_name." #".$this->app_version_code;
		
		$r = self::getLastReport();
		if ($r!=null)
			return $r->getApplicationDesc();
		
		return null;
	}
	
	public function getPackageName() {
		if (!empty($this->package_name))
			return $this->package_name;
		
		$reports = $this->getReports();
		if (!empty($reports))
			return $reports[0]->package_name;
		
		return ' - ';
	}
	
	public function getReportsCount() {
		return sizeOf($this->getReports());
	}
	
	public function getState() {
		return $this->issue_state;
	}
	
	public function getPriority() {
		return $this->issue_priority;
	}
	
	public function setPriority($priorityId) { 
		$this->issue_priority->setPriority($priorityId);
	}
	
	public function isNew() { return ($this->issue_state==REPORT_STATE_NEW); }
	
	public function isArchived() { return ($this->issue_state==REPORT_STATE_ARCHIVED); }
	
	public function isOpen() { return ($this->issue_state==REPORT_STATE_NEW || $this->issue_state==REPORT_STATE_VIEWED); }
	
}

class IssuePriority {
	
	const LOW 				= 0;
	const NORMAL 			= 1;
	const CRITICAL		= 9;
	
	private $id = self::NORMAL;
	
	
	// CONSTRUCTOR
	public function IssuePriority($priorityId) {
		$this->setPriority($priorityId);
	} 
	
	
	public function getId() {
		return $this->id;
	}
	
	public function setPriority($priorityId) {
		switch ($this->id) {
			case self::LOW : 
			case self::NORMAL :
			case self::CRITICAL : 
				break;
		
			default : $priorityId = self::NORMAL;
		}
		
		$this->id = $priorityId;
	}
	
	public function getName() {
		switch ($this->id) {
			case self::LOW :
				return 'Low';
			case self::NORMAL :
				return 'Normal';
			case self::CRITICAL :
				return 'Critical';
		}
	}
	
	public function getLabel($fullname=true) {
		$name = $this->getName();
		
		return '<span class="label label-priority-'.strtolower($name).'">'.($fullname?$name:substr($name, 0, 1)).'</span>';
	}
	
	public function getTextColorClass() {
		return 'priority-'.strtolower($this->getName()).'-text-color';
	}
	
	public function isNormal() {
		return ($this->id==self::NORMAL);
	}
}