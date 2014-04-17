<?php define('DIRECT_ACCESS_CHECK', true); 
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

define('BASE_PATH', '../');

require_once(BASE_PATH.'includes/define.php');
require_once(BASE_PATH.'includes/config.php');
require_once(BASE_PATH.'includes/confighelper.class.php');
require_once(BASE_PATH.'includes/helper.class.php');
require_once(BASE_PATH.'includes/debug.class.php');
require_once(BASE_PATH.'includes/dbhelper.class.php');
require_once(BASE_PATH.'includes/issue.class.php');
require_once(BASE_PATH.'includes/reporthelper.class.php');
require_once(BASE_PATH.'includes/mailhelper.class.php');

Debug::logi('New report requested !', 'REPORT');

$cfg = CfgHelper::getInstance();

// check if HTTP basic auth is required and PHP method is used
if ($cfg->isReportBasicAuthEnabled() && $cfg->isBasicAuthPHPMethodEnabled()) {
	Debug::logi('Authentication required ...', 'REPORT');	
	
	if (!$cfg->isReportBasicAuthGranted($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
		if (!array_key_exists('PHP_AUTH_USER', $_SERVER) || !array_key_exists('PHP_AUTH_PW', $_SERVER)) {
			Debug::loge('HTTP authentication USER/PW does not exist in $_SERVER !!! Check your application acra parameters. If the problem still remain you should try the htaccess/htpasswd method.', 'REPORT');
		}
		
		Debug::loge('Somebody try to access report script without a correct login/password !!!', 'REPORT');
		
		if ($cfg->sendMailOnReportReceived()) {
			MailHelper::sendMail($cfg->getReportMailRecipients(),
														'On report received auth failure !',
														'Somebody try to access report script without a correct login/password !!!');
		}
		
		// exit if access not granted
		exit;
		
	} else { Debug::logi('  |_ access granted !', 'REPORT'); }
}

// Get HTTP PUT data
$data = file_get_contents("php://input");

// if empty then get data from GET or POST request
if (empty($data)) {
	foreach($_REQUEST as $key=>$value) {
		//Check if json key
		if (strtolower($key)=='json') {
			$data = $value;
			break;
		}
	}
}


// if data not empty then store into DB
if (!empty($data)) {
	Debug::logi("Seems to be a valid request... Try to decode JSON and save to DB.", 'REPORT');	
	
	$json = json_decode($data, true);
	
	// check if device is in exception list
	if (isset($json['device_id']) && !empty($json['device_id']) && $cfg->isInReportExceptionDevices($json['device_id'])) {
		Debug::logi("Report ignored, device ".$json['device_id']." is in exception list.");
		
		exit;
	}
	
	// open db connection
	DBHelper::open();
	
	$values = ReportHelper::buildMySQLValuesArr($json);
	
	$result = DBHelper::insertReport($values);
	if (!$result) {
		Debug::loge('Inserting report data failed ! '.DBHelper::getLastError(), 'REPORT');
		Debug::loge('Report content '.print_r($values, true), 'REPORT');
		
	} else { Debug::logi('Report inserted with success !', 'REPORT'); }

	if ($cfg->sendMailOnReportReceived()) {
		$package = explode('.', $json['PACKAGE_NAME']);
		
		MailHelper::sendMail($cfg->getReportMailRecipients(),
													'[MABL] New '.($json['IS_SILENT']>0?'SILENT ':'').'report received for '.$package[count($package)-1].' !',
													ReportHelper::createMailContent(!$result, $package, $json));
	}
	
	DBHelper::close();

} else { Debug::loge('Invalid report request data ! '.print_r($_REQUEST, true), 'REPORT'); }
?>