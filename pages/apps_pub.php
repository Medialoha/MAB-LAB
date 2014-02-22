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

require_once(BASE_PATH.'includes/appdesc.class.php');


$mAppArr = DbHelper::selectRows(TBL_APPLICATIONS, null, APP_NAME.' ASC', '*', null, null, false);
$mSelectedAppId = $mNavCtl->getParam('app', isset($mAppArr[0][APP_ID])?$mAppArr[0][APP_ID]:-1);
$mSelectedAppName = "No application found";
$mSelectedAppPackage = null;

$mSelectedLang = $mNavCtl->getParam('code', null); 

// build applications dropdown items array
$mDropdownItems = array();

foreach ($mAppArr as $app) {
	$mDropdownItems[] = '<li><a href="'.$mNavCtl->getURL('&app='.$app[APP_ID]).'" >'.$app[APP_NAME].'</a></li>';

	if ($mSelectedAppId==$app[APP_ID]) {
		$mSelectedAppName = $app[APP_NAME];
		$mSelectedAppPackage = $app[APP_PACKAGE];
	}
}

$extras = array('app'=>$mSelectedAppId, 'code'=>$mSelectedLang);

$mAppDesc = new ApplicationDesc($mSelectedAppPackage);
?>
<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="#" >Play Store Publication</a>
    
    <ul class="nav">
    	<?php if (!$mNavCtl->isNav('tmpl')) { ?>
    	<li>
    		<a href="#" class="dropdown-toggle" data-toggle="dropdown" ><?php echo $mSelectedAppName; ?>&nbsp;&nbsp;<span class="caret"></span></a>
    		<ul class="dropdown-menu">
    			<?php echo implode('', $mDropdownItems); ?>
    		</ul>
    	</li>
    	<?php } ?>
    	
      <li <?php echo $mNavCtl->isNav('')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL(null, $extras); ?>" >Description</a></li>
      <li <?php echo $mNavCtl->isNav('scr')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL('scr', $extras); ?>">Screenshots</a></li>
      <li <?php echo $mNavCtl->isNav('tmpl')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL('tmpl', null); ?>">Manage Templates</a></li>
    </ul>
  </div>
</div>
<?php 
if (!$mNavCtl->isNav('')) {
  require_once(BASE_PATH.'pages/apps_pub_'.$mNavCtl->getNav().'.inc.php');
  
} else { require_once(BASE_PATH.'pages/apps_pub_app.inc.php'); } 
?>

<div id="dialogContainer" class="modal hide fade" style="width:800px; margin-left:-400px; height:700px;" ></div>

<script type="text/javascript" src="assets/functions-core.js" ></script>
<script type="text/javascript" src="assets/functions-applications.js" ></script>