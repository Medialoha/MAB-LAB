<?php
$mGlobalCfg = array(
	'db.host'=>'localhost',
	'db.port'=>3306,
	'db.name'=>'mabl',
	'db.user'=>'mabluser',
	'db.pwd'=>'password',
		
	'tbl.prefix'=>'mabl_',
		
	'date.format'=>'Y-m-d H:i',
	'date.timezone'=>'Europe/Berlin',
	
	'report.packagename.shrink'=>true,
	'report.sendmail'=>false,
	'report.sendmail.recipients'=>'',
		
	'report.basicauth'=>false,
	'report.basicauth.method'=>0,
	// we use an array of accounts to be able to support multiple accounts in future
	'report.basicauth.accounts'=>array(),
		
	'mail.from.addr'=>'',
	'mail.from.name'=>'MAB-LAB',
	
	'dashboard.refresh.interval'=>60000,
	'dashboard.issues.nb'=>5,
		
	// http://twitter.github.io/bootstrap/components.html#labels-badges
	'report.tags'=>array()
);