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

session_start();

require_once(BASE_PATH.'includes/define.php');
require_once(BASE_PATH.'includes/config.php');
require_once(BASE_PATH.'includes/confighelper.class.php');
require_once(BASE_PATH.'includes/helper.class.php');
require_once(BASE_PATH.'includes/debug.class.php');
require_once(BASE_PATH.'includes/dbhelper.class.php');
require_once(BASE_PATH.'includes/report.class.php');
require_once(BASE_PATH.'includes/issue.class.php');


class UpdateHelper {
	
	public $config;
	
	public $step;
	public $error;
	
	private $step_closed;
	
	
	// CONSTRUCTOR
	public function UpdateHelper() {
		// get config object
		$this->config = CfgHelper::getInstance();
		
		$this->step = 0;
		$this->error = 0;
		
		$this->step_closed = true;
	}
	
	
	public function begin() {
		echo '<p><b>&gt; START UPDATE PROCESS &lt;</b></p>';

		// open database connection
		DBHelper::open();
	}
	
	public function end() {
		DBHelper::close();
		
		$this->printUpdateFinishMsg();
	}
	
	public function applySQLUpdateFile($sqlFile='db-update.sql') {
		$this->printStartNextStepMsg('Read SQL update file');
		
		$success = true;
		$message = null;
		
		if (!file_exists($sqlFile)) {
			$message = 'file <i>'.$sqlFile.'</i> does not exist, nothing to do !';
			
		} else {
			// read sql update file and set the table prefix
			$sql = str_replace(TBL_NAME_PREFIX, $this->config->getTablePrefix(), @file_get_contents($sqlFile));

			$this->printEndStepMsg(true);
			$this->printStartNextStepMsg('Run SQL queries');
			
			$res = DBHelper::exec($sql, true);
			if ($res!=null) {
				$success = false;
				$message = $res;
				
				$this->error++;
			}
			
			DBHelper::clearStoredResults();
		}

		$this->printEndStepMsg($success, $message);
		
		return $success;
	}
	
	public function execSQL($query, $multi=false) {
		$res = DBHelper::exec($query, $multi);
		
		if ($res!=null) {
			$this->error++;
			
			$this->printStepMsg($res, true);
		}
		
		return true;
	}
	
	public function hasError() {
		return ($this->error>0);
	}
	
	public function exitOnError() {
		if ($this->hasError()) {
			$this->end();
			exit;
		}
	}
	
	public function printStartNextStepMsg($message=null) {
		if (!$this->step_closed)
			$this->printEndStepMsg(!$this->hasError());
		
		echo '<b>Step '.(++$this->step).'.</b> '.($message==null?'':$message.' ... ');

		$this->step_closed = false;
	}
	
	public function printStepMsg($message, $error=false, $br=false) {
		if ($error) {
			echo '<br/><b style="padding:5px 0px 5px 30px; color:red;" >'.($message==null?'FAILED':$message).'</b>';
			
			++$this->error;
			
		} else { echo '<br/><i style="padding:5px 0px 5px 30px; color:#666666;" >'.$message.'</i> '; }
		
		if ($br)
			echo '<br/>';
	}
	
	public function printStartSubStepMsg($message) {
		echo '<br/><i style="padding:5px 0px 5px 30px;" >|_&nbsp;&nbsp;'.$message.' ... </i>';
	}
	
	public function printEndSubStepMsg($succeeded, $message=null) {
		if ($succeeded) {
			echo '<i>OK</i>';
				
			if ($message!=null) {
				echo '<br/><i style="padding:5px 0px 5px 30px;" >&nbsp;&nbsp;'.$message.'</i>';
			}
				
		} else { 
			echo '<i style="color:red" >FAILED</i>';
			echo '<br/><i style="padding:5px 0px 5px 30px; color:red;" >&nbsp;&nbsp;'.$message.'</i>';
		}
	}
	
	public function printEndStepMsg($succeeded, $message=null, $hasSubSteps=false) {
		if ($succeeded) {
			if ($message!=null)
				$this->printStepMsg($message, false);
			
			if ($hasSubSteps)
				echo '<br/><b>Step '.$this->step.'.</b> ';
				
			echo 'OK<br/>';
			
		} else { $this->printStepMsg($message==null?'FAILED':$message, true, true); }

		$this->step_closed = true;
	}
	
	public function printUpdateFinishMsg() {
		if ($this->error==0) {
			echo '<p><b style="color:green" >UPDATE COMPLETE !</b></p>';
			echo '<p style="color:#333333; font-style:italic;" >Don\'t forget to remove the install directory !</p>';
			
		} else { echo '<p><b style="color:red" >UPDATE FAILED !</b></p>'; }
	} 
}