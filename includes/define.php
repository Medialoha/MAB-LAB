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

define('_APPLICATION_NAME_', 'MAB-LAB');
define('_APPLICATION_VERSION_NAME_', '1.3.1-Helen');
define('_APPLICATION_VERSION_CODE_', 7);

define('LOG_SEVERITY', 0); // could be 0=>DEBUG, 1=>INFO, 2=>WARNING, 3=>ERROR

define('ALERT_SUCCESS', 'alert-success');
define('ALERT_ERROR', 'alert-error');

if (!defined('BASE_PATH'))
	define('BASE_PATH', '');