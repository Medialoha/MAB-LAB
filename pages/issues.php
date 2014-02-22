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

// get reports preferences
$cfg = CfgHelper::getInstance();

$mAppArr = DbHelper::selectRows(TBL_APPLICATIONS, null, APP_NAME.' ASC', '*', null, null, false);

$mSelectedAppId = $mNavCtl->getParam('app', '-1');
$mSelectedAppName = "All Applications";
$mSelectedAppPackage = null;

// build applications dropdown items array
$mDropdownItems = array(
		'<li><a href="#" onclick="setSelectedAppId(this, -1)" >All Applications</a></li>'
);

foreach ($mAppArr as $app) {
	$mDropdownItems[] = '<li><a href="#" onclick="setSelectedAppId(this, '.$app[APP_ID].')" >'.$app[APP_NAME].'</a></li>';

	if ($mSelectedAppId==$app[APP_ID]) {
		$mSelectedAppName = $app[APP_NAME];
		$mSelectedAppPackage = $app[APP_PACKAGE];
	}
}
?>
<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="#" >Issues</a>
    
    <ul class="nav">
    	<li>
    		<a href="#" class="dropdown-toggle" data-toggle="dropdown" >
    			<span id="selectedAppName" ><?php echo $mSelectedAppName; ?></span>&nbsp;&nbsp;<span class="caret"></span>
    		</a>
    		<ul class="dropdown-menu" >
    			<?php echo implode('', $mDropdownItems); ?>
    		</ul>
    		<input type="hidden" id="selectedAppId" value="<?php echo $mSelectedAppId; ?>" />
    	</li>
    	
      <li <?php echo $mNavCtl->isNav('')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL(null, null); ?>" >Browse</a></li>
      <li <?php echo $mNavCtl->isNav('mil')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL('mil', null); ?>">Milestones</a></li>
      <li <?php echo $mNavCtl->isNav('nmil')?'class="active"':''; ?> ><a href="<?php echo $mNavCtl->buildPageURL('nmil', null); ?>">Milestone</a></li>
    </ul>
  </div>
</div>
<div class="row" >
	<div id="issuesContent" class="span12" >
		<?php 
			if ($mNavCtl->isNav('mil'))
				require_once(BASE_PATH.'pages/issues_milestones.inc.php');
			else if ($mNavCtl->isNav('nmil'))
				require_once(BASE_PATH.'pages/issues_new_milestone.inc.php'); 
			else
				require_once(BASE_PATH.'pages/issues_table.inc.php');
		?>
	</div>
</div>

<div id="dialogContainer" class="modal hide fade" style="width:1000px; margin-left:-500px; height:700px;" ></div>

<script type="text/javascript" src="assets/functions-core.js" ></script>
<script type="text/javascript" src="assets/functions-issues.js" ></script>
<script type="text/javascript" >
var HOME_PAGE = false;

$(function(){
	$('a[rel=tooltip]').tooltip();
});
</script>