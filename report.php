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

require_once('includes/define.php');
require_once('includes/config.php');
require_once('includes/confighelper.class.php');
require_once('includes/helper.class.php');
require_once('includes/debug.class.php');
require_once('includes/dbhelper.class.php');
require_once('includes/reporthelper.class.php');
require_once('includes/mailhelper.class.php');

Debug::logi('New report requested !', 'REPORT');

$cfg = CfgHelper::getInstance();

// check if HTTP basic auth is required
if ($cfg->isReportBasicAuthEnabled() && !$cfg->isReportBasicAuthGranted($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
	Debug::loge('Somebody try to access report script without a correct login/password !!!', 'REPORT');
	
	if ($cfg->sendMailOnReportReceived()) {
		MailHelper::sendMail($cfg->getReportMailRecipients(),
													'On report received auth failure !',
													'Somebody try to access report script without a correct login/password !!!');
	}
	
	// exit if access not granted
	exit;
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
	
	// open db connection
	DBHelper::open();
	
	$values = ReportHelper::buildMySQLValuesArr($json);
	
	$result = DBHelper::insertReport($values);
	if (!$result) {
		Debug::loge('Inserting report data failed ! '.DBHelper::getLastError(), 'REPORT');
		Debug::loge('Report content '.print_r($values, r), 'REPORT');
		
	} else { Debug::logi('Report inserted with success !', 'REPORT'); }

	if ($cfg->sendMailOnReportReceived()) {
		MailHelper::sendMail($cfg->getReportMailRecipients(),
													'New report received !',
													($result?
															'New report received !':
															'New report received but <b>not inserted</b> due to unhandled error !!!').' <br/> '.$json['USER_COMMENT'].'<br/>'.$json['STACK_TRACE']);
	}
	
	DBHelper::close();

} else { Debug::loge('Invalid report request data ! '.print_r($_REQUEST, true), 'REPORT'); }

?>