<?php 
define('DIRECT_ACCESS_CHECK', true);

define('BASE_PATH', '../../');


require_once('../updatehelper.php');

require_once(BASE_PATH.'includes/reporthelper.class.php');


$mUpdateHelper = new UpdateHelper();

$mUpdateHelper->begin();

$mUpdateHelper->applySQLUpdateFile();

$mUpdateHelper->exitOnError();

$mUpdateHelper->printEndStepMsg(true, null, true);

$mUpdateHelper->end();