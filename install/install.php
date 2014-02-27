<?php 
define('DIRECT_ACCESS_CHECK', true);

define('BASE_PATH', '../');

require_once('updatehelper.php');

$mUpdateHelper = new UpdateHelper();

$mUpdateHelper->begin();

$mUpdateHelper->applySQLUpdateFile('db-install.sql');

$mUpdateHelper->exitOnError();

$mUpdateHelper->printStartNextStepMsg('Populate increments table');

// populate table increments
for ($i=0; $i<=180; ++$i) {
	$mUpdateHelper->execSQL('INSERT INTO '.TBL_INCREMENTS.'('.INC_VALUE.') VALUES ('.$i.');');
}

$mUpdateHelper->printEndStepMsg(true, null, true);

$mUpdateHelper->end();