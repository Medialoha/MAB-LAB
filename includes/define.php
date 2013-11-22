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

define('APP_NAME', 'MAB-LAB');
define('VERSION_NAME', '1.2.3-Lester');
define('VERSION_CODE', 5);

define('LOG_SEVERITY', 0); // could be 0=>DEBUG, 1=>INFO, 2=>WARNING, 3=>ERROR

define('PAGE_ID_HOME', 'h');
define('PAGE_ID_CONTACT', 'c');
define('PAGE_ID_ABOUT', 'a');
define('PAGE_ID_ISSUES', 'i');
define('PAGE_ID_CONFIG', 'cfg');
define('PAGE_ID_USERS', 'u');
define('PAGE_ID_LOGS', 'l');

define('ALERT_SUCCESS', 'alert-success');
define('ALERT_ERROR', 'alert-error');

if (!defined('BASE_PATH'))
	define('BASE_PATH', '');