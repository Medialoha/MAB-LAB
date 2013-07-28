<?php define ('CONFIG_OBJ_SESSION_NAME', 'CONFIG_HELPER');
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

define('AUTH_METHOD_PHP', 0);
define('AUTH_METHOD_HTACCESS', 1);


class CfgHelper {
	
	// database options
	private $mTablePrefix;
	
	// report display options
	private $mDateFormat;
	private $mDateTimezone;
	private $mShrinkPackageName;
	
	private $mBasicAuth;
	private $mBasicAuthMethod;
	private $mBasicAuthAccounts;
	
	private $mSendMailOnReportReceived;
	private $mReportMailRecipients;
	
	private $mMailSender;
	
	
	// CONSTRUCTOR
	public static function getInstance() {
		self::init();
		
		return $_SESSION[CONFIG_OBJ_SESSION_NAME];
	}
	
	public static function init($recreate=false) {
		if (!array_key_exists(CONFIG_OBJ_SESSION_NAME, $_SESSION) || !$_SESSION[CONFIG_OBJ_SESSION_NAME] instanceof CfgHelper || $recreate==true) {
			$_SESSION[CONFIG_OBJ_SESSION_NAME] = CfgHelper::createHelperObject();
		}
	}
	
	private static function createHelperObject() {
		global $mGlobalCfg;
		
		$obj = new CfgHelper();
		
		$obj->mTablePrefix = $mGlobalCfg['tbl.prefix'];		
		
		$obj->mDateFormat = $mGlobalCfg['date.format'];
		$obj->mDateTimezone = $mGlobalCfg['date.timezone'];
		if (is_null($obj->mDateTimezone) || strlen($obj->mDateTimezone)==0) {
			$obj->mDateTimezone = date_default_timezone_get();
			
		} else { date_default_timezone_set($obj->mDateTimezone); }
		
		$obj->mShrinkPackageName = $mGlobalCfg['report.packagename.shrink'];
		
		$obj->mBasicAuth = $mGlobalCfg['report.basicauth'];
		$obj->mBasicAuthMethod = $mGlobalCfg['report.basicauth.method'];
		$obj->mBasicAuthAccounts = $mGlobalCfg['report.basicauth.accounts'];
		
		$obj->mSendMailOnReportReceived = is_bool($mGlobalCfg['report.sendmail'])?$mGlobalCfg['report.sendmail']:true;
		$obj->mReportMailRecipients = is_string($mGlobalCfg['report.sendmail.recipients'])?$mGlobalCfg['report.sendmail.recipients']:'';
		
		$obj->mMailSender = array($mGlobalCfg['mail.from.addr'], $mGlobalCfg['mail.from.name']);
		
		return $obj;
	}
	
	public static function checkConfigArr($arr) {
		$error = null;
		
		if (!is_bool($arr['report.packagename.shrink'])) { echo 'not a bool';
			$error = 'Shrink package name must be set to TRUE or FALSE !';
			
		} else {
			if (!is_string($arr['db.host']) || empty($arr['db.host']))
				$error = 'DB Host must be set !';
			else if (!is_string($arr['db.user']) || empty($arr['db.user']))
				$error = 'DB user must be set !';
			else if (!is_string($arr['db.pwd']) || empty($arr['db.pwd']))
				$error = 'DB user password must be set !';
			else if (!is_string($arr['db.name']) || empty($arr['db.name']))
				$error = 'DB name must be set !';
			
			else if (!is_string($arr['date.format']) || empty($arr['date.format']))
				$error = 'Date format must be set !';
		}
		
		return $error;
	} 
	
	// Write config to file includes/config.php
	public static function writeConfig($arr, $path="") {
		$error = self::checkConfigArr($arr);
		
		if ($error!=null) return $error;

		$configDir = $path.'includes';
		$configFile = $configDir.'/config.php';
		$configTmplFile = $configFile.'.tmpl';
			
		// if config file does not exist then check if dir is writeable
		if (!file_exists($configFile)) {
			if (!is_writeable($configDir))
				$error = 'Config directory '.$configDir.' is not writeable !';
			
		// else if config file exists then check if file is writeable
		} else if (!is_writeable($configFile)) {
			$error = 'File '.$configFile.' is not writeable !';

		} 
		
		// if no error found then check if config template file is readable
		if ($error==null && !is_readable($configTmplFile)) {
			$error = 'File '.$configTmplFile.' is not readable !';
			
		}
		
		// if all files checks passed
		if ($error==null) {
			$accounts = ''; $sep = ''; 
			foreach ($arr['report.basicauth.accounts'] as $account) {
				$accounts .= $sep.'array(\'login\'=>"'.$account['login'].'", \'password\'=>"'.$account['password'].'", \'clear\'=>'.($account['clear']?'true':'false').')';
				$sep = ',';
			}

			$tmpl = file_get_contents($configTmplFile);

			if (!file_put_contents($configFile,
															sprintf($tmpl,
																				$arr['db.host'], $arr['db.name'], $arr['db.user'], $arr['db.pwd'],
																				$arr['tbl.prefix'],
																				$arr['date.format'], $arr['date.timezone'],
																	
																				$arr['report.packagename.shrink']?'true':'false',
																				$arr['report.sendmail']?'true':'false',
																				$arr['report.sendmail.recipients'],
																	
																				$arr['report.basicauth']?'true':'false',
																				$arr['report.basicauth.method'],
																				$accounts,
																	
																				$arr['mail.from.addr'], $arr['mail.from.name'],
																				$arr['report.tags']
																			))) {
					
				$error = 'An error occured while writing configuration file !';
			}
		}
	
		return $error;
	}	
	
	
	// GETTERS
	public function isReportBasicAuthEnabled() { return $this->mBasicAuth; }
	
	public function isBasicAuthPHPMethodEnabled() { return $this->mBasicAuthMethod==AUTH_METHOD_PHP; }
	
	public function isReportBasicAuthGranted($login, $password) {
		Debug::logd('Check access for '.$login.' / '.$password, 'CONFIG');
		
		foreach ($this->mBasicAuthAccounts as $account) {
			if (strcmp($login, $account['login'])==0) {
				Debug::logd('  |_ login found !', 'CONFIG');
				
				if (!$account['clear']) {
					$requiredPassword = md5($account['password']);
					Debug::logd('  |_ obfuscated password enabled', 'CONFIG');
					
				} else { $requiredPassword = $account['password']; }
				
				if (strcmp($password, $requiredPassword)==0)
					return true;
			} 
		}
		
		return false;
	}
	
	public function getBasicAuthAccount() {
		if (sizeof($this->mBasicAuthAccounts)>0)
			return (object)$this->mBasicAuthAccounts[0];
		
		return (object)array('login'=>null, 'password'=>null, 'clear'=>false);
	}
	
	public function getBasicAuthMethod() { return $this->mBasicAuthMethod; }
	
	public function getDateFormat() { return $this->mDateFormat; }
	
	public function getDateTimezone() { return $this->mDateTimezone; }
	
	public function getMailFromAddr() { return $this->mMailSender[0]; }
	public function getMailFromName() { return $this->mMailSender[1]; }
	
	public function getReportMailRecipients($asArray=true) { 
		return $asArray?explode(',', $this->mReportMailRecipients):$this->mReportMailRecipients; 
	}
	
	public function getTablePrefix() { return $this->mTablePrefix; }
	
	public function shrinkPackageName() { return $this->mShrinkPackageName; }
	
	public function sendMailOnReportReceived() { return $this->mSendMailOnReportReceived; }
}