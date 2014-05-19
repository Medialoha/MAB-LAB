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
define('ACTIONBAR_STYLE_GENERATOR_PATH', 'libs/asset-studio-actionbar/');
define('HOLO_COLORS_PATH', 'libs/asset-studio-colors/');

// 0: asset studio, 1: actionbar style generator, 2: holo colors
$assets = 0;
$basepath = ASSET_STUDIO_BASE_PATH;
$page = 'icons-launcher.html';


switch ($mNavCtl->getNav()) {
	case 'abti' : $page = 'icons-actionbar.html';
		break;
	case 'ni' : $page = 'icons-notification.html';
		break;
	case 'gi' : $page = 'icons-generic.html';
		break;
	case 'ndi' : $page = 'icons-nav-drawer-indicator.html';
		break;
	case 'snpg' : $page = 'nine-patches.html';
		break;
		
	case 'absg' : 
			$basepath = ACTIONBAR_STYLE_GENERATOR_PATH; 
			$page = 'index.html';
			
			$assets = 1;
		break;
		
	case 'hcg' :
			$basepath = HOLO_COLORS_PATH;  
			$page = 'index.php';
			$assets = 2;
		break; 
}
?>

<script type="text/javascript" >
	var BASE_PATH = '<?php echo $basepath; ?>';
</script>

<script type="text/javascript" src="libs/jquery/jquery-migrate-1.2.1.js" ></script>
	
<?php if ($assets==0) { ?>	
	<!-- <link rel="stylesheet" href="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/cssreset-3.4.1.min.css" /> -->
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:regular,medium,thin,italic,mediumitalic,bold" title="roboto" />
	<link rel="stylesheet" href="<?php echo ASSET_STUDIO_BASE_PATH; ?>css/studio.css" />
	
	<!-- canvg used to work around <img src=SVG> toDataURL security issues -->
	<!-- see code.google.com/p/chromium/issues/detail?id=54204 -->
	<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/canvg/rgbcolor.js"></script> 
	<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/canvg/canvg.js"></script>
	
	<!-- prereq. for asset studio lib -->
	<link rel="stylesheet" href="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/spectrum/spectrum-assetstudio.css">
	<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/spectrum/spectrum.js"></script>
	<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>lib/jszip/jszip.js"></script>
	
	<script src="<?php echo ASSET_STUDIO_BASE_PATH; ?>js/asset-studio.js"></script>

<?php } else if ($assets==1) { ?>
	<!-- <link rel="stylesheet" href="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/cssreset-3.4.1.min.css"> -->
	<link rel="stylesheet" href="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/jquery-ui/css/android/jquery-ui-1.8.16.custom.css">
  <link rel="stylesheet" href="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>css/studio.css">

 <!--  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/jquery-ui/js/jquery-1.6.2.min.js"></script> -->
 <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/jquery-ui/js/jquery-ui-1.8.16.custom.min.js"></script> 

  <!-- canvg used to overcome <img src=SVG> toDataURL security issues -->
  <!-- see code.google.com/p/chromium/issues/detail?id=54204 -->
  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/canvg/rgbcolor.js"></script> 
  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/canvg/canvg.js"></script>

  <!-- prereq. for asset studio lib -->
  <link rel="stylesheet" href="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/colorpicker/css/colorpicker.css">
  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/colorpicker/js/colorpicker.js"></script>
    
  <link rel="stylesheet" href="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/spectrum/spectrum-assetstudio.css">
  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/spectrum/spectrum.js"></script>

  <!-- for .ZIP downloads -->
  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/swfobject-2.2.js"></script>
  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/downloadify/js/downloadify.min.js"></script>
  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>lib/jszip/jszip.js"></script>

  <script src="<?php echo ACTIONBAR_STYLE_GENERATOR_PATH; ?>js/asset-studio.js"></script>
  
<?php } else if ($assets==2) { ?>
  <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:regular,medium,thin,italic,mediumitalic,bold" title="roboto"/>
  <link rel="stylesheet" href="<?php echo HOLO_COLORS_PATH; ?>include/css/studio.css"/>
  <link rel="stylesheet" href="<?php echo HOLO_COLORS_PATH; ?>include/lib/spectrum/spectrum-assetstudio.css"/>

  <script src="<?php echo HOLO_COLORS_PATH; ?>include/lib/spectrum/spectrum.js"></script>
  <script src="<?php echo HOLO_COLORS_PATH; ?>include/js/asset-studio.js"></script>
<?php } ?>

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
	<div class="span12" style="background-color:#f0f0f0; margin-bottom:25px;"  >
		<?php require_once($basepath.$page); ?>
	</div>
</div>