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

$message = '';

// try to login
if (isset($_POST['login'])) {
	$res = DBHelper::selectRow(TBL_USERS, USER_NAME.'="'.$_POST['login'].'" AND '.USER_PASSWORD.'="'.md5($_POST['password']).'"');

	if ($res!=null) {
		$_SESSION['LOGGEDIN'] = true;
		$_SESSION[USER_ID] = $res[USER_ID];
		$_SESSION[USER_NAME] = $res[USER_NAME];
		$_SESSION[USER_EMAIL] = $res[USER_EMAIL];
		
		header('Location:index.php');
		
	} else { $message = 'Wrong password or username !'; }
}
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
    <link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet" >
    <link href="libs/bootstrap/css/bootstrap-responsive.css" rel="stylesheet" >
    
    <link href="assets/css/tags.css" rel="stylesheet" >
    <link href="assets/css/page-login.css" rel="stylesheet" >
    <link href="assets/css/page-login.css" rel="stylesheet" >

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
  </head>
  <body>
    <div class="container row" >
    	<div class="span3 offset3" >
      <form class="form-signin" action="index.php" method="post" >
        <h3 class="form-signin-heading">
        	<?php echo _APPLICATION_NAME_; ?>&nbsp;&nbsp;<small class="muted" >Please sign in</small>
        </h3>
        <input name="login" type="text" class="input-block-level" placeholder="Username" >
        <input name="password" type="password" class="input-block-level" placeholder="Password" >

        <p><?php echo $message; ?></p>
        
        <button class="btn btn-primary" type="submit">Sign in</button>
      </form>
      </div>
    </div> <!-- /container -->

    <script src="libs/jquery/jquery-1.10.2.min.js" ></script>
    <script src="libs/bootstrap/js/bootstrap.min.js" ></script>
  </body>
</html>