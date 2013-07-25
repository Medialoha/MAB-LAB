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

require_once('includes/define.php');
require_once('includes/config.php');
require_once('includes/confighelper.class.php');
require_once('includes/helper.class.php');
require_once('includes/debug.class.php');
require_once('includes/dbhelper.class.php');
require_once('includes/reporthelper.class.php');
require_once('includes/report.class.php');

session_start();

// get page to display
$pageId = isset($_GET['p'])?$_GET['p']:PAGE_ID_HOME;
$page = Helper::getPage($pageId);

// open db connection
DBHelper::open();
//CfgHelper::init(true);

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

    <script src="libs/jquery/jquery-1.10.2.min.js" ></script>
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
              <li <?php echo $pageId==PAGE_ID_REPORTS?'class="active"':''; ?> >
              	<a href="index.php?p=<?php echo PAGE_ID_REPORTS; ?>"><i class="icon-list-alt icon-white" ></i>&nbsp;Reports list</a>
              </li>
              <li <?php echo $pageId==PAGE_ID_CONFIG?'class="active"':''; ?> >
              	<a href="index.php?p=<?php echo PAGE_ID_CONFIG; ?>"><i class="icon-wrench icon-white" ></i>&nbsp;Settings</a>
              </li>
              <li <?php echo $pageId==PAGE_ID_USERS?'class="active"':''; ?> >
              	<a href="index.php?p=<?php echo PAGE_ID_USERS; ?>"><i class="icon-user icon-white" ></i>&nbsp;Users</a>
              </li>
              <li <?php echo $pageId==PAGE_ID_CONTACT?'class="active"':''; ?> >
              	<a href="http://www.medialoha.net/" target="_blank" ><i class="icon-envelope icon-white" ></i>&nbsp;Contact</a>
              </li>
              <li <?php echo $pageId==PAGE_ID_ABOUT?'class="active"':''; ?> >
              	<a href="index.php?p=<?php echo PAGE_ID_ABOUT; ?>"><i class="icon-info-sign icon-white" ></i>&nbsp;About</a>
              </li>
<!--               <li class="dropdown"> -->
<!--                 <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a> -->
<!--                 <ul class="dropdown-menu"> -->
<!--                   <li><a href="#">Action</a></li> -->
<!--                   <li><a href="#">Another action</a></li> -->
<!--                   <li><a href="#">Something else here</a></li> -->
<!--                   <li class="divider"></li> -->
<!--                   <li class="nav-header">Nav header</li> -->
<!--                   <li><a href="#">Separated link</a></li> -->
<!--                   <li><a href="#">One more separated link</a></li> -->
<!--                 </ul> -->
<!--               </li> -->
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

    <script src="libs/bootstrap/js/bootstrap.min.js" ></script>
    <script src="assets/functions-core.js" ></script>
    <script >$('#loader').hide();</script>
  </body>
</html>
<?php } // end content

// close db connection
DBHelper::close(); 

?>