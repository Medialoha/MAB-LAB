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

define('PAGE_ID_ABOUT', 'a');
define('PAGE_ID_APPS', 'apps');
define('PAGE_ID_APPS_PUB', 'apps_p');
define('PAGE_ID_APPS_STATS', 'apps_s');
define('PAGE_ID_CONFIG', 'cfg');
define('PAGE_ID_CONTACT', 'c');
define('PAGE_ID_HOME', 'h');
define('PAGE_ID_ISSUES', 'i');
define('PAGE_ID_LOGS', 'l');
define('PAGE_ID_USERS', 'u');

define('PAGE_ID_ASSET_STUDIO', 'as');




class NavigationController {
	
	public $id; // page id defined above
	private $nav;
	
	
	
	// CONSTRUTOR
	public function NavigationController() {
		$this->id = isset($_GET['p'])?$_GET['p']:PAGE_ID_HOME;
		$this->nav = isset($_GET['n'])?$_GET['n']:null;
	}
	
	
	public function buildActionURL($controller, $action, $extras) {		
		return '?'.(empty($action)?'':'a='.$action).($controller==null?'':'&ctl='.$controller).$this->buildExtras($extras);
	}
		
	public function getParam($key, $default=null) {
		return (isset($_GET[$key]) && !empty($_GET[$key]))?$_GET[$key]:$default;
	}
	
	// get the current page URL with custom args appended
	public function getURL($extras=null) {
		return '?p='.$this->id.($this->nav==null?'':'&n='.$this->nav).$this->buildExtras($extras);
	}
	
	// build URL from the current page
	public function buildPageURL($nav, $extras=null) {
		return self::buildFullPageURL($this->id, $nav, $extras);
	}
	
	// build a new page URL
	public function buildFullPageURL($pageId, $nav=null, $extras=null) {
		return '?p='.$pageId.($nav==null?'':'&n='.$nav).$this->buildExtras($extras);
	}
	
	public function getNav() {
		return ($this->nav==null?'':$this->nav);
	}
	
	public function getPage() {
		switch ($this->id) {
			case PAGE_ID_ABOUT :
				return 'about.php';
			case PAGE_ID_APPS :
				return 'apps.php';
			case PAGE_ID_APPS_PUB :
				return 'apps_pub.php';
			case PAGE_ID_APPS_STATS :
				return 'apps_stats.php';
			case PAGE_ID_CONFIG :
				return 'configure.php';
			case PAGE_ID_CONTACT :
				return 'contact.php';
			case PAGE_ID_ISSUES :
				return 'issues.php';
			case PAGE_ID_LOGS :
				return 'logs.php';
			case PAGE_ID_USERS :
				return 'users.php';
			case PAGE_ID_ASSET_STUDIO :
				return 'dev_asset_studio.php';
	
			default : return 'home.php';
		}
	}
	
	public function isNav($nav) {
		return (strcmp($this->getNav(), $nav)==0);
	}
	
	
	private function buildExtras($extras) {
		$extrasStr = '';
		
		if ($extras!=null) {
			if (is_array($extras)) {
				foreach ($extras as $k=>$v)
					$extrasStr .= '&'.$k.'='.$v;
				
			} else if (is_string($extras)) { $extrasStr .= '&'.$extras; }
		}
		
		return $extrasStr;
	}
}