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

$mAppArr = DbHelper::selectRows(TBL_APPLICATIONS, null, APP_NAME.' ASC', '*', null, null, false);
$mSelectedAppId = $mNavCtl->getParam('app', '-1');
$mSelectedAppName = "All Applications";

// build applications dropdown items array
$mDropdownItems = array(
		'<li><a href="'.$mNavCtl->getURL().'" >All Applications</a></li>'
);

foreach ($mAppArr as $app) {
	$mDropdownItems[] = '<li><a href="'.$mNavCtl->getURL('&app='.$app[APP_ID]).'" >'.$app[APP_NAME].'</a></li>';
	
	if ($mSelectedAppId==$app[APP_ID])
		$mSelectedAppName = $app[APP_NAME];
}

$urlApp = '';
if ($mSelectedAppId>0) {
	$urlApp = 'app='.$mSelectedAppId;
}
?>
<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="#" >Sales</a>
    <ul class="nav">
    	<li>
    		<a href="#" class="dropdown-toggle" data-toggle="dropdown" ><?php echo $mSelectedAppName; ?>&nbsp;&nbsp;<span class="caret"></span></a>
    		<ul class="dropdown-menu">
    			<?php echo implode('', $mDropdownItems); ?>
    		</ul>
    	</li>
      
      <li <?php echo $mNavCtl->isNav('')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL(null, $urlApp); ?>" >Statistics</a></li>
      <li <?php echo $mNavCtl->isNav('data')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL('data', $urlApp); ?>">Data</a></li>
      <li <?php echo $mNavCtl->isNav('import')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL('import', $urlApp); ?>">Import CSV</a></li>
    </ul>
  </div>
</div>
<?php 
if (!$mNavCtl->isNav('')) {
  require_once(BASE_PATH.'pages/apps_stats_'.$mNavCtl->getNav().'.inc.php');
  
} else { require_once(BASE_PATH.'pages/apps_stats.inc.php'); } 
?>