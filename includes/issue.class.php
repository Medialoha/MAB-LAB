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
define('ISSUE_STATE_TESTING', 3);
define('ISSUE_STATE_CLOSED', 4);
define('ISSUE_STATE_ARCHIVED', 0);

define('ISSUE_PRIORITY_LOW', 0);
define('ISSUE_PRIORITY_NORMAL', 1);
define('ISSUE_PRIORITY_CRITICAL', 9);

define('REPORT_PROJECTION', REPORT_ID.','.REPORT_KEY.','.REPORT_STATE.','.REPORT_PACKAGE_NAME.','.REPORT_VERSION_NAME.','.REPORT_VERSION_CODE.','.REPORT_ANDROID_VERSION.','.REPORT_PHONE_MODEL.','.REPORT_CRASH_DATE.','.REPORT_USER_COMMENT.','.REPORT_INSTALLATION_ID.','.REPORT_DEVICE_ID.','.REPORT_IS_SILENT);


class Issue {
	
	public $issue_id;
	public $issue_key;
	public $issue_cause;
	public $issue_datetime;
	
	private $issue_state;
	private $issue_priority;
	
	public $issue_comment;

	public $issue_app_id;
	
	public $app_package;
	public $app_name;
	
	private $issue_reports;
	
	
	// CONSTRUCTOR
	public function Issue() {
		$this->issue_id = 0;
		$this->issue_state = new IssueState(IssueState::STATE_NEW);
		$this->issue_priority = new IssuePriority(IssuePriority::NORMAL);		
		$this->issue_reports = null;
	}


	public static function createFromArray($values) {
		$obj = new Issue();
		
		foreach($values as $k=>$v) {
			if (!is_string($k)) continue;

			if (strcmp($k, 'issue_priority')==0) { 
				$obj->setPriority($v);
				
			} else if (strcmp($k, 'issue_state')==0) { 
				$obj->setState($v);
				
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
	
	public function getReportIds() {
		$s = ''; $sep = '';
		
		$reports = $this->getReports();
		if (!empty($reports)) {
			foreach ($reports as $r) {
				$s .= $sep.$r->report_id; $sep = ','; 
			}
		}

		return $s;
	}
	
	public function getReportsCount() {
		return count($this->getReports());
	}
	
	public function getState() {
		return $this->issue_state;
	}
	
	public function setState($stateId) { 
		$this->issue_state->setState($stateId);
	}
	
	public function getPriority() {
		return $this->issue_priority;
	}
	
	public function setPriority($priorityId) { 
		$this->issue_priority->setPriority($priorityId);
	}
	
}


class IssueState {

	const STATE_NEW 				= ISSUE_STATE_NEW;
	const STATE_VIEWED 			= ISSUE_STATE_VIEWED;
	const STATE_CLOSED			= ISSUE_STATE_CLOSED;
	const STATE_TESTING			= ISSUE_STATE_TESTING;
	const STATE_ARCHIVED		= ISSUE_STATE_ARCHIVED;

	private $id = self::STATE_NEW;


	// CONSTRUCTOR
	public function IssueState($stateId) {
		$this->setState($stateId);
	}


	public function getId() {
		return $this->id;
	}
	
	public function setState($stateId) {
		switch ($stateId) {
			case self::STATE_VIEWED :
			case self::STATE_CLOSED : 
			case self::STATE_TESTING : 
			case self::STATE_ARCHIVED : 
				break;
		
			default : $stateId = self::STATE_NEW;
		}
		
		$this->id = $stateId;
	}
	
	public function isNew() { 
		return ($this->id==self::STATE_NEW);
	}
	
	public function isViewed() { 
		return ($this->id==self::STATE_VIEWED);
	}
	
	public function isTesting() { 
		return ($this->id==self::STATE_TESTING);
	}
	
	public function isArchived() { 
		return ($this->id==self::STATE_ARCHIVED); 
	}
	
	public function isOpen() { 
		return ($this->id==self::STATE_NEW || $this->id==self::STATE_VIEWED || $this->id==self::STATE_TESTING); 
	}
	
	public function getName() {
		switch ($this->id) {
			case self::STATE_NEW :
				return 'New';
			case self::STATE_VIEWED :
				return 'Open';
			case self::STATE_CLOSED :
				return 'Closed';
			case self::STATE_TESTING :
				return 'Testing';
			case self::STATE_ARCHIVED :
				return 'Archived';
				
			default : return 'Unkown';
		}
	}
	
	public function getLabel($fullname=true) {
		$name = 'Open';
		$icon = 'icon-hand-right';
		
		if ($this->id==self::STATE_CLOSED) {
			$name = 'Closed';
			$icon = 'icon-thumbs-up';
			
		} else if ($this->id==self::STATE_ARCHIVED) {
			$name = 'Archived';
			$icon = 'icon-folder-close';
			
		} else if ($this->id==self::STATE_TESTING) {
			$name = 'Testing';
			$icon = 'icon-cog';
		}
		
		return '<span class="label label-state-'.strtolower($name).'"><i class="'.$icon.' icon-white" ></i>&nbsp;&nbsp;'.
							($fullname?$name:substr($name, 0, 1)).
						'&nbsp;&nbsp;</span>';
	}
	
}


class IssuePriority {
	
	const LOW 				= ISSUE_PRIORITY_LOW;
	const NORMAL 			= ISSUE_PRIORITY_NORMAL;
	const CRITICAL		= ISSUE_PRIORITY_CRITICAL;
	
	private $id = self::NORMAL;
	
	
	// CONSTRUCTOR
	public function IssuePriority($priorityId=self::NORMAL) {
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
				
			default : return 'Unkown';
		}
	}
	
	public function getLabel($showFullname=true, $showIcon=false) {
		$name = $this->getName();
		$text = $showFullname?$name:substr($name, 0, 1);
				
		return '<span class="label label-priority-'.strtolower($name).'">'.
							($showIcon?'<i class="icon-bullhorn icon-white" ></i>&nbsp;&nbsp;'.$text.'&nbsp;&nbsp;':$text).
						'</span>';
	}
	
	public function getTextColorClass() {
		return 'priority-'.strtolower($this->getName()).'-text-color';
	}
	
	public function isNormal() {
		return ($this->id==self::NORMAL);
	}
}