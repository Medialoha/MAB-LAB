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


class Helper {
	
	public static function pushAlert($type, $message) {
		if (!isset($_SESSION['ALERT']))
			$_SESSION['ALERT'] = array();
		
		$_SESSION['ALERT'][] = array('type'=>$type, 'message'=>$message);
	}
	
	public static function popAlert() {
		$res = null;
		
		if (isset($_SESSION['ALERT']) && is_array($_SESSION['ALERT'])) {
			$res = $_SESSION['ALERT'];
			unset($_SESSION['ALERT']);
		}
		
		return $res;
	}
	
	// TODO move to NavigationController class
	public static function getHTTPGetStringValue($key, $default=null) {
		global $_GET;
		
		if (isset($_GET[$key])) {
			$res = $_GET[$key];
			
			if (is_string($res) && strlen($res)>0)
				return $res;
		}
		
		return $default;
	}

	// TODO move to NavigationController class
	public static function getHTTPGetBooleanValue($key, $default=false) {
		global $_GET;
		
		if (isset($_GET[$key])) {
			$res = $_GET[$key];
			
			if (is_bool($res))
				return $res;
			
			if (is_string($res)) {
				return intval($res)==1?true:false;
			}
		}		
		
		return $default;
	}
	
	public static function shrinkString($string, $maxlen) {		
		if (strlen($string)>$maxlen) {
			return substr($string, 0, $maxlen-4).'...';
		}
		
		return $string;
	}
	
	public static function getBadge($isNew) {
		return $isNew?'<span class="badge badge-important small"><small>NEW</small></span>':'';
	}
	
	public static function formatDate($date, $format, $isNew=false) {
		if ($isNew)
			return date($format, strtotime($date)).'&nbsp;'.self::getBadge(true);
		
		return date($format, strtotime($date));
	}
}