<?php define('DIRECT_ACCESS_CHECK', true);
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

session_start();

require_once('includes/define.php');
require_once('includes/config.php');
require_once('includes/confighelper.class.php');
require_once('includes/helper.class.php');
require_once('includes/debug.class.php');
require_once('includes/navigationcontroller.class.php');
require_once('includes/dbhelper.class.php');
require_once('includes/reporthelper.class.php');
require_once('includes/report.class.php');
require_once('includes/issue.class.php');
require_once('includes/issuehelper.php');

// init page
$mNavCtl = new NavigationController();


// open db connection
DBHelper::open();

if (isset($_GET['reloadcfg']))
	CfgHelper::init(true);

// if user not logged in the nshow login form
if (!isset($_SESSION['LOGGEDIN'])) {
	require_once('pages/login.php');
	$mNavCtl = null;
}

// if action requested (user must be logged in)
if (isset($_REQUEST['a'])) {
	require_once('controller.php');
	$mNavCtl = null;
}

if ($mNavCtl!=null) { // start content 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo _APPLICATION_NAME_; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" >
    <meta name="description" content="My ACRA Backend Laboratory" >
    <meta name="author" content="Medialoha" >

    <!-- Le styles -->
    <link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet" />
    <link href="libs/bootstrap/css/bootstrap-responsive.css" rel="stylesheet" />
    <link href="libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
   
    <link href="assets/css/layout.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/icons.css" rel="stylesheet" />
    <link href="assets/css/dashboard.css" rel="stylesheet" />
    <link href="assets/css/issues.css" rel="stylesheet" />
    <link href="assets/css/reports.css" rel="stylesheet" />
    <link href="assets/css/applications.css" rel="stylesheet" />
    <link href="assets/css/apps_stats.css" rel="stylesheet" />

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png" >
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png" >
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png" >
    <link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png" >
    
		<link rel="shortcut icon" href="assets/images/favicon.png" >
		
    <script type="text/javascript" src="libs/jquery/jquery-1.10.2.min.js" ></script>
    <script type="text/javascript" src="assets/functions-core.js" ></script>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner"> 
        <div class="container">
<!--           <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> -->
<!--             <span class="icon-bar"></span> -->
<!--             <span class="icon-bar"></span> -->
<!--             <span class="icon-bar"></span> -->
<!--           </button> -->
          <img src="assets/images/ic_superandroid.png" style=" float:left; height:40px;" />
          <a class="brand" href="#">&nbsp;MABL</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li <?php echo $mNavCtl->id==PAGE_ID_HOME?'class="active"':''; ?> >
              	<a href="index.php"><i class="icon-th-large icon-white" ></i>&nbsp;Dashboard</a>
              </li>
              <li <?php echo $mNavCtl->id==PAGE_ID_ISSUES?'class="active"':''; ?> >
              	<a href="index.php?p=<?php echo PAGE_ID_ISSUES; ?>"><i class="icon-tags icon-white" ></i>&nbsp;Issues</a>
              </li>
              
              <li class="dropdown" >
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="<?php echo $mNavCtl->id==PAGE_ID_APPS_PUB||$mNavCtl->id==PAGE_ID_ASSET_STUDIO?'color:#e5e5e5':'';?>" ><i class="icon-folder-close icon-white" ></i>&nbsp;Applications&nbsp;<b class="caret"></b></a>
                <ul class="dropdown-menu">
              		<li <?php echo $mNavCtl->id==PAGE_ID_ASSET_STUDIO?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_ASSET_STUDIO; ?>"><i class="icon-pencil" ></i>&nbsp;Asset Studio</a>
              		</li>
              		<li <?php echo $mNavCtl->id==PAGE_ID_APPS_PUB?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_APPS_PUB; ?>"><i class="icon-book" ></i>&nbsp;Publication</a>
              		</li>
              		<li <?php echo $mNavCtl->id==PAGE_ID_APPS_STATS?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_APPS_STATS; ?>"><i class="icon-signal" ></i>&nbsp;Sales Statistics</a>
              		</li>
                </ul>
              </li>
              
              <li class="dropdown" >
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="<?php echo $mNavCtl->id==PAGE_ID_APPS||$mNavCtl->id==PAGE_ID_USERS||$mNavCtl->id==PAGE_ID_CONFIG||$mNavCtl->id==PAGE_ID_LOGS?'color:#e5e5e5':'';?>" ><i class="icon-cog icon-white" ></i>&nbsp;Admin tools&nbsp;<b class="caret"></b></a>
                <ul class="dropdown-menu">
              		<li <?php echo $mNavCtl->id==PAGE_ID_APPS?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_APPS; ?>"><i class="icon-tasks" ></i>&nbsp;Manage Applications</a>
              		</li>
              		<li <?php echo $mNavCtl->id==PAGE_ID_USERS?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_USERS; ?>"><i class="icon-user" ></i>&nbsp;Manage Users</a>
              		</li>
              		<li <?php echo $mNavCtl->id==PAGE_ID_LOGS?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_LOGS; ?>"><i class="icon-list-alt" ></i>&nbsp;View Logs</a>
              		</li>
              		<li class="divider" ></li>
                	<li <?php echo $mNavCtl->id==PAGE_ID_CONFIG?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_CONFIG; ?>"><i class="icon-wrench" ></i>&nbsp;Edit Settings</a>
              		</li>
                </ul>
              </li>

              <li <?php echo $mNavCtl->id==PAGE_ID_CONTACT?'class="active"':''; ?> >
              	<a href="http://www.medialoha.net/" target="_blank" ><i class="icon-envelope icon-white" ></i>&nbsp;Contact</a>
              </li>
              <li <?php echo $mNavCtl->id==PAGE_ID_ABOUT?'class="active"':''; ?> >
              	<a href="index.php?p=<?php echo PAGE_ID_ABOUT; ?>"><i class="icon-info-sign icon-white" ></i>&nbsp;About</a>
              </li>              
              <li>
              	<a href="index.php?a=logout" ><i class="icon-off icon-white" ></i>&nbsp;Logout</a>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div id="wrap" >
      <!-- Begin page content -->
      <div class="container" style="padding-top:70px;" >
      	<div id="loader" class="row" >
      		<div class="span12" style="height:60px;" >
      			<ul class="inline mainloader" >
  						<li><img src="assets/images/loader.gif" class="" /></li><li>LOADING...</li>
						</ul>
      		</div>
      	</div>
      
      	<?php 
      		$alert = Helper::popAlert();
      		if ($alert!=null) {
						foreach ($alert as $a) {
							?><div class="alert <?php echo $a['type']; ?>">
      					<button type="button" class="close" data-dismiss="alert">&times;</button>
      					<?php if ($a['type']!=ALERT_SUCCESS) { ?><strong>Warning!</strong>&nbsp;&nbsp;<?php } echo $a['message']; ?>
      				</div><?php 
						}						
					}
      	
    			require_once('pages/'.$mNavCtl->getPage()); 
    		?>
    	</div>
    </div>
    
    <div id="footer">
      <div class="container"></div>
    </div>

    <script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js" ></script>
    <script type="text/javascript" src="libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" ></script>
    <script type="text/javascript" >
    	$('#loader').hide();
    </script>
  </body>
</html>
<?php } // end content

// close db connection
DBHelper::close(); 
?>