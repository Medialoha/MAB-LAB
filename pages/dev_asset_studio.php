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

define('ASSET_STUDIO_BASE_PATH', 'libs/asset-studio/');

?>
<script type="text/javascript" src="libs/jquery/jquery-migrate-1.2.1.js" ></script>

<link rel="stylesheet" href="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/cssreset-3.4.1.min.css">
<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:regular,medium,thin,italic,mediumitalic,bold" title="roboto">
<link rel="stylesheet" href="<?php echo ASSET_STUDIO_BASE_PATH; ?>css/studio.css">

<!-- canvg used to work around <img src=SVG> toDataURL security issues -->
<!-- see code.google.com/p/chromium/issues/detail?id=54204 -->
<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/canvg/rgbcolor.js"></script> 
<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/canvg/canvg.js"></script>

<!-- prereq. for asset studio lib -->
<link rel="stylesheet" href="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/spectrum/spectrum-assetstudio.css">
<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/spectrum/spectrum.js"></script>
<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/jszip/jszip.js"></script>

<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>js/asset-studio.pack.js"></script>

<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="#" >Asset Studio</a>
    
    <ul class="nav">
      <li class="dropdown">
      	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Icons <b class="caret"></b></a>
        <ul class="dropdown-menu">
		      <li <?php echo $mNavCtl->isNav('li')?'class="active"':''; ?> >
		      	<a href="<?php echo $mNavCtl->buildPageURL('li', null); ?>" >Launcher icons</a></li>
		      <li <?php echo $mNavCtl->isNav('abti')?'class="active"':''; ?> >
		      	<a href="<?php echo $mNavCtl->buildPageURL('abti', null); ?>">Action bar and tab icons</a></li>
		      <li <?php echo $mNavCtl->isNav('ni')?'class="active"':''; ?> >
		      	<a href="<?php echo $mNavCtl->buildPageURL('ni', null); ?>">Notification icons</a></li>
		      <li <?php echo $mNavCtl->isNav('gi')?'class="active"':''; ?> >
		      	<a href="<?php echo $mNavCtl->buildPageURL('gi', null); ?>">Generic icons</a></li>
		      <li class="divider" ></i>
		      <li <?php echo $mNavCtl->isNav('ndi')?'class="active"':''; ?> >
		      	<a href="<?php echo $mNavCtl->buildPageURL('ndi', null); ?>">Navigation drawer indicator</a></li>
        </ul>
			</li>
      <li <?php echo $mNavCtl->isNav('snpg')?'class="active"':''; ?> >
      	<a href="<?php echo $mNavCtl->buildPageURL('snpg', null); ?>">Simple Nine-patch</a></li>
      <li <?php echo $mNavCtl->isNav('absg')?'class="active"':''; ?> >
      	<a href="<?php echo $mNavCtl->buildPageURL('absg', null); ?>">Action Bar Style</a></li>      	
      <li <?php echo $mNavCtl->isNav('hcg')?'class="active"':''; ?> >
      	<a href="<?php echo $mNavCtl->buildPageURL('hcg', null); ?>">Holo Colors</a></li>      	
    </ul>
  </div>
</div>

<div class="row" >
	<div class="span12" style="background-color:#f0f0f0;" >
		<?php 
			switch ($mNavCtl->getNav()) {
				case 'abti' : require_once(ASSET_STUDIO_BASE_PATH.'icons-actionbar.html');
					break;
				case 'ni' : require_once(ASSET_STUDIO_BASE_PATH.'icons-notification.html');
					break;
				case 'gi' : require_once(ASSET_STUDIO_BASE_PATH.'icons-generic.html');
					break;
				case 'ndi' : require_once(ASSET_STUDIO_BASE_PATH.'icons-nav-drawer-indicator.html');
					break;
				case 'snpg' : require_once(ASSET_STUDIO_BASE_PATH.'nine-patches.html');
					break;
// 				case 'absg' : require_once(ASSET_STUDIO_BASE_PATH.'icons-actionbar.html');
// 					break;
// 				case 'hcg' : require_once(ASSET_STUDIO_BASE_PATH.'icons-actionbar.html');
// 					break;
					
				default : require_once(ASSET_STUDIO_BASE_PATH.'icons-launcher.html');
			}
		?>
	</div>
</div>