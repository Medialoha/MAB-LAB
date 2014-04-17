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

switch ($action) {

	//////// UPDATE APP NAME
	case 'updateAppName' :
			DbHelper::updateApplicationName($_POST['appId'], $_POST['appName']);
		
		break;

	//////// IMPORT CSV
	case 'import' :
			require_once(BASE_PATH.'includes/googlecheckoutcsvfileimporter.class.php');
			
			$error = null;
			
			if ($_FILES['csvFile']['error']>0) {
				$error = "Upload file error: ".$_FILES['csvFile']['error'];
				
			} else {
				$error = GoogleCheckoutCSVFileImporter::checkUploadedFile($_FILES['csvFile']);
				if ($error==null) {
					
					$error = GoogleCheckoutCSVFileImporter::import($_FILES['csvFile'], $_POST['fileType']);
					if (count($error)>0) {
						foreach($error as $msg)
							Helper::pushAlert(ALERT_ERROR, $msg);
						
						$error = 'Error(s) occured while importing file !';
						
					} else { $error = null; }
				}
			}
		
			if ($error==null) {
				Helper::pushAlert(ALERT_SUCCESS, 'File imported successfully !');
					
			} else { Helper::pushAlert(ALERT_ERROR, $error); }


			header('Location:index.php?p='.PAGE_ID_APPS_STATS.'&n=data');
		break;
		
	//////// ADD DESCRIPTION TRANSLATION FROM TEMPLATE
	case 'addTrans' :
			require_once(BASE_PATH.'includes/appdesc.class.php');
			
			$appId = Helper::getHTTPGetStringValue('app', 0);
			$code = Helper::getHTTPGetStringValue('code', null);
		
			$app = DbHelper::selectRow(TBL_APPLICATIONS, APP_ID.'='.$appId, APP_PACKAGE);
			$mAppDesc = new ApplicationDesc($app[0]);

			if ($mAppDesc->addTranslation($code)) {
				Helper::pushAlert(ALERT_SUCCESS, 'Translation added with success !');
					
			} else { Helper::pushAlert(ALERT_ERROR, 'Something goes wrong ! Check log file in admin section.'); }
		
			header('Location:index.php?p='.PAGE_ID_APPS_PUB.'&app='.$appId."&code=".$code);
		break;

	//////// DELETE TRANSLATION
	case 'delTranslation' :
			require_once(BASE_PATH.'includes/appdesc.class.php');
						
			$mAppDesc = new ApplicationDesc($_POST['package']);
			
			if ($mAppDesc->delTranslation($_POST['code'])) {
				Helper::pushAlert(ALERT_SUCCESS, 'Translation deleted with success !');
					
			} else { Helper::pushAlert(ALERT_ERROR, 'Something goes wrong ! Check log file in admin section.'); }
			
			header('Location:index.php?p='.PAGE_ID_APPS_PUB.'&app='.Helper::getHTTPGetStringValue('app', 0));
		break;

	//////// SAVE TRANSLATION
	case 'saveTranslation' :
			require_once(BASE_PATH.'includes/appdesc.class.php');
						
			$mAppDesc = new ApplicationDesc($_POST['package']);
			if ($mAppDesc->loadTranslation($_POST['code'])) {
				
				foreach ($_POST as $k=>$value) {
					$tmp = explode('-', $k);
					
					if (count($tmp)==2 && strcmp($tmp[0], "prop")==0)
						$mAppDesc->setProperty($tmp[1], $value);
				}
				
				$mAppDesc->commit();
				
				echo $mAppDesc->buildTranslatedDescription();
				
			} else { echo 'Invalid language code !'; }
		break;

	//////// GET TMPL EDITOR
	case 'getTmplEditor' :
			require_once(BASE_PATH.'pages/apps_pub_tmpl_editor_dialog.php');
		
		break;

	//////// UPDATE TEMPLATE
	case 'updateTemplate' :
			require_once(BASE_PATH.'includes/appdesc.class.php');
			
			$error = null;
			
			$package = $_POST['package'];
			$tmpl = $_POST['template'];
			$code = $_POST['code'];
		
			$isTmpl = empty($package)?true:false;

			$mAppDesc = new ApplicationDesc($package);
			
			// create a new translation OR load translation
			if ((empty($code) &&	$mAppDesc->createEmptyTranslation($_POST['lang-code'], $_POST['lang-name'])) || $mAppDesc->loadTranslation($code, $isTmpl)) {
				$tmpArr = explode('{', $tmpl);
				$properties = array();
				$html = '';
				
				foreach($tmpArr as $r) {
					if (empty($r)) continue;
					
					$tmp = explode('}', $r);
					$properties[] = $tmp[0];
				}
								
				$xml = new SimpleXMLElement('<description></description>');
				$xml->addAttribute(XML_ATTR_LANGUAGE, $mAppDesc->getTranslationName());
				$xml->addAttribute(XML_ATTR_LANGUAGE_CODE, $code);
				$xml->addAttribute(XML_ATTR_DEFAULT, "false");
				
				$xmlProps = $xml->addChild('properties');
				
				foreach ($properties as $p) {
					$pArr = explode(':', $p);
					
					// skip invalid property
					if (count($pArr)!=3) continue;
					
					// replace full description property {key:type:desc} to {key}
					$html = str_replace('{'.$p.'}', '{'.strtoupper($pArr[0]).'}', $html);
					
					$xmlProp = $xmlProps->addChild('property');
					$xmlProp->addChild('key', $pArr[0]);
					$xmlProp->addChild('type', $pArr[1]);
					$xmlProp->addChild('desc', $pArr[2]);
					$xmlProp->addChild('value', '');
				}			
				
				$mAppDesc->setTranslationTemplate($tmpl);
				$mAppDesc->setTranslationProperties($xml);
				
				$mAppDesc->commit();
									
			} else { $error = 'Unable to load translation '.$code.' for package '.$package; }
			
			if (empty($error)) {
				Helper::pushAlert(ALERT_SUCCESS, 'Template updated with success !');
					
			} else { Helper::pushAlert(ALERT_ERROR, 'Something goes wrong ! Check log file in admin section.'); }
		
			if (empty($package)) {
				header('Location:index.php?p='.PAGE_ID_APPS_PUB.'&n=tmpl&code='.$code);
				
			} else { header('Location:index.php?p='.PAGE_ID_APPS_PUB.'&app='.Helper::getHTTPGetStringValue('app', 0).'&code='.$code); }
		break;
		
		
	default : die("UNHANDLED ACTION REQUESTED !");
}