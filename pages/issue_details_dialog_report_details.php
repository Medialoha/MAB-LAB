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

$r = DBHelper::fetchReport($reportId);

if (empty($r->report_key)) {
	?><b class="color:red;" >Report with id <?php echo $reportId; ?> not found !!!</b><?php
	exit();
}

$priority = new IssuePriority($r->issue_priority);
$issueFormat = true;

require_once(BASE_PATH.'pages/report_details.inc.php');	