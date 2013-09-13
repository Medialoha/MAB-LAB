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
 *     MAREK MASLANKA - Logintar - added review reports by the same errors and formatting logcat
 */


class StackTraceHelper {
	
	
	public static function getTitleFromStackTrace($trace) {
		return substr($trace, 0, strpos($trace, "\n"));
	}
	
}