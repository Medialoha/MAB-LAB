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
require_once('includes/dbhelper.class.php');
require_once('includes/reporthelper.class.php');
require_once('includes/report.class.php');
require_once('includes/issue.class.php');
require_once('includes/issuehelper.php');


// get page to display
$pageId = isset($_GET['p'])?$_GET['p']:PAGE_ID_HOME;
$page = Helper::getPage($pageId);

// open db connection
DBHelper::open();

if (isset($_GET['reloadcfg']))
	CfgHelper::init(true);

// if user not logged in the nshow login form
if (!isset($_SESSION['LOGGEDIN'])) {
	require_once('pages/login.php');
	$page = null;
}

// if action requested (user must be logged in)
if (isset($_REQUEST['a'])) {
	require_once('controller.php');
	$page = null;
}

if ($page!=null) { // start content 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo APP_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" >
    <meta name="description" content="My ACRA Backend Laboratory" >
    <meta name="author" content="Medialoha" >

    <!-- Le styles -->
    <link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet" />
    <link href="libs/bootstrap/css/bootstrap-responsive.css" rel="stylesheet" />
   
    <link href="assets/css/layout.css" rel="stylesheet" />
    <link href="assets/css/dashboard.css" rel="stylesheet" />
    <link href="assets/css/issues.css" rel="stylesheet" />
    <link href="assets/css/reports.css" rel="stylesheet" />

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
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#">MAB-LAB</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li <?php echo $pageId==PAGE_ID_HOME?'class="active"':''; ?> >
              	<a href="index.php"><i class="icon-th-large icon-white" ></i>&nbsp;Dashboard</a>
              </li>
              <li <?php echo $pageId==PAGE_ID_ISSUES?'class="active"':''; ?> >
              	<a href="index.php?p=<?php echo PAGE_ID_ISSUES; ?>"><i class="icon-tags icon-white" ></i>&nbsp;Issues</a>
              </li>
              
              <li class="dropdown" >
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="<?php echo $pageId==PAGE_ID_USERS||$pageId==PAGE_ID_CONFIG||$pageId==PAGE_ID_LOGS?'color:#e5e5e5':'';?>" ><i class="icon-cog icon-white" ></i>&nbsp;Admin tools&nbsp;<b class="caret"></b></a>
                <ul class="dropdown-menu">
              		<li <?php echo $pageId==PAGE_ID_USERS?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_USERS; ?>"><i class="icon-user" ></i>&nbsp;Manage Users</a>
              		</li>
              		<li <?php echo $pageId==PAGE_ID_LOGS?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_LOGS; ?>"><i class="icon-list-alt" ></i>&nbsp;View Logs</a>
              		</li>
              		<li class="divider" ></li>
                	<li <?php echo $pageId==PAGE_ID_CONFIG?'class="active"':''; ?> >
              			<a href="index.php?p=<?php echo PAGE_ID_CONFIG; ?>"><i class="icon-wrench" ></i>&nbsp;Edit Settings</a>
              		</li>
                </ul>
              </li>

              <li <?php echo $pageId==PAGE_ID_CONTACT?'class="active"':''; ?> >
              	<a href="http://www.medialoha.net/" target="_blank" ><i class="icon-envelope icon-white" ></i>&nbsp;Contact</a>
              </li>
              <li <?php echo $pageId==PAGE_ID_ABOUT?'class="active"':''; ?> >
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
      			?><div class="alert <?php echo $alert->type; ?>">
      					<button type="button" class="close" data-dismiss="alert">&times;</button>
      					<?php if ($alert->type!=ALERT_SUCCESS) { ?><strong>Warning!</strong>&nbsp;&nbsp;<?php } echo $alert->message; ?>
      				</div><?php 
      		}
      	
    			require_once('pages/'.$page); 
    		?>
    	</div>
    </div>
    
    <div id="footer">
      <div class="container"></div>
    </div>

    <script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js" ></script>
    <script type="text/javascript" >
    	$('#loader').hide();
    </script>
  </body>
</html>
<?php } // end content

// close db connection
DBHelper::close(); 
?>