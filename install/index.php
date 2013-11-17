<?php
define('DIRECT_ACCESS_CHECK', true); 

define('DB_INSTALL_SCRIPT', 'db-install.sql');
define('HTACCESS_TEMPLATE', 'htaccess.tmpl');

session_start();

require_once('../includes/define.php');
require_once('../includes/confighelper.class.php');
require_once('../includes/dbhelper.class.php');

$installation_done = false;

if (isset($_POST['in-db-host'])) {
	$error = null;

	// try db connection
	$dbo = @new mysqli($_POST['in-db-host'], $_POST['in-db-user'], $_POST['in-db-pwd'], $_POST['in-db-name']);
	if ($dbo->connect_error) {
		$error = 'CONNECT ERROR #'.$dbo->connect_errno.' : '.$dbo->connect_error;
	}
	if (mysqli_connect_error()) {
		$error = 'CONNECT ERROR #'.mysqli_connect_errno().' : '.mysqli_connect_error();
	}
	@mysqli_close($dbo);
	
	if ($error==null) {
		// prepare config array 			
		$tmpCfg = array(	
									'date.format'=>'Y-m-d H:i', 'date.timezone'=>null,
									'report.sendmail'=>false, 'report.sendmail.recipients'=>null, 
									'report.packagename.shrink'=>true, 'report.tags'=>null,
									'mail.from.addr'=>null, 'mail.from.name'=>'MAB-LAB'
								);
		
		foreach ($_POST as $k=>$v) {
			if (strpos('in-', $k, 0)==0) {
				$key = str_replace('-', '.', substr($k, 3));
				if (!empty($key)) {
					if (strcmp($key, 'report.packagename.shrink')==0) {
						$tmpCfg[$key] = $v=='1'?true:false;
		
					} else { $tmpCfg[$key] = $v;
					}
				}
			}
		}
		
		// write config file
		$error = CfgHelper::writeConfig($tmpCfg, '../');
		if ($error==null) {
			require_once('../includes/config.php');
			
			CfgHelper::init(true);
			$cfg = CfgHelper::getInstance();
		
			// insert user
			DBHelper::open();
			
			// create database
			if (is_readable(DB_INSTALL_SCRIPT)) {
				$sql = str_replace('%PREFIX%', $cfg->getTablePrefix(), file_get_contents(DB_INSTALL_SCRIPT));
				$error = DBHelper::exec($sql, true);
								
				// clear results to prevent mysql out of sync
				DBHelper::clearStoredResults();
								
				if ($error==null) {
					if (!DBHelper::updateUser(null, $_POST['user_name'], $_POST['user_password'], $_POST['user_email']))
						$error = 'Unable to insert user account ! <br/>'.DBHelper::getLastError();
											
				} else { $error = 'Unable to create database from installation script ! '.$error; }
				
			} else { $error = 'DB install file (install/'.DB_INSTALL_SCRIPT.') not readable !'; }
			
			DBHelper::close();
		}
		
		// write htaccess
		if ($error==null) {
			$ips = $_POST['allowedIps'];
			
			if ($ips!=null && !empty($ips)) {
				$ipsArr = array();
					
				if (strpos($ips, ',')>0) {
					$ipsArr = explode(',', $ips);
				
				} else if (strpos($ips, "\n")>0) {
					$ipsArr = explode("\n", $ips);
				
				} else { $ipsArr[] = $ips; }
				
				$ipsStr = ''; $sep = '';
				foreach ($ipsArr as $ip) {
					if (empty($ip)) continue;
					
					$ipsStr .= $sep.trim($ip); $sep = ', ';
				}
				
				// check if template is readable
				if (is_readable(HTACCESS_TEMPLATE)) {
					
					// check if mab-lab root dir is writeable
					if (is_writeable('../')) {
						$tmpl = file_get_contents(HTACCESS_TEMPLATE);
					
						file_put_contents('../.htaccess', str_replace('%ADDRESSES%', $ipsStr, $tmpl));
					
					} else { $error = 'MAB-LAB root directory '.$configDir.' is not writeable !'; }
					
				} else { $error = 'htaccess template file (install/'.HTACCESS_TEMPLATE.') is not readable !'; }
			}
		}
	}
	
	$installation_done = true;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo APP_NAME; ?>&nbsp; Install Script</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" >
    <meta name="description" content="My ACRA Backend Laboratory" >
    <meta name="author" content="Medialoha" >

    <!-- Le styles -->
    <link href="../libs/bootstrap/css/bootstrap.css" rel="stylesheet" >
    <link href="../libs/bootstrap/css/bootstrap-responsive.css" rel="stylesheet" >
    
    <link href="../assets/css/tags.css" rel="stylesheet" >

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
  	<div class="row" >
    	<div class="span6 offset3" >
  			<h2 class="muted" ><?php echo APP_NAME; ?>&nbsp; Install Script</h2>
		  	<?php if (isset($error)) { ?><div class="alert alert-error" ><button type="button" class="close" data-dismiss="alert">&times;</button><?php echo $error; ?></div><?php	}	?>
		  	
		  	<?php if ($installation_done) { ?>
		  		<div class="alert alert-success" >
		  			<button type="button" class="close" data-dismiss="alert">&times;</button>
		  			<p>Installation succeeded ! Click on the link below to access MAB-LAB. 
		  			Don't forget to delete the <em>install</em> directory.</p>
		  			<p>Under MAB-LAB you should go to the settings page and configure more options...</p>
		  			<p><a href="../index.php?p=<?php echo PAGE_ID_CONFIG; ?>" >Go to MAB-LAB</a></p>
		  		</div>
		  	<?php } ?>
  		</div>
  	</div>
  	
  	<br style="clear:both;" />
  	
		<form name="installform" class="form-horizontal" method="post" >		
			<div class="row" >
    		<div class="span6 offset3" >
    		  <fieldset><legend>Database Configuration</legend>
					  <div class="control-group">
					    <label class="control-label" for="dbhost">Host</label>
					    <div class="controls">
					      <input type="text" id="dbhost" name="in-db-host" value="localhost" >
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="dbuser">User</label>
					    <div class="controls">
					      <input type="text" id="dbuser" name="in-db-user" value="" >
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="dbpassword" >Password</label>
					    <div class="controls">
					      <input type="password" id="dbpassword" name="in-db-pwd" value="" placeholder="Password" >
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="dbname">Database name</label>
					    <div class="controls">
					      <input type="text" id="dbname" name="in-db-name" value="" >
					    </div>
					  </div>
					  
					  <div class="control-group">
					    <label class="control-label" for="tblprefix">Table prefix</label>
					    <div class="controls">
					      <input type="text" id="tblprefix" name="in-tbl-prefix" value="mabl_" >
					    </div>
					  </div>
				  </fieldset>
    		
					<fieldset><legend>Account</legend>
		  			<div class="control-group">
		    			<label class="control-label" for="login">Login</label>
		    			<div class="controls">
		      			<input type="text" id="login" name="user_name" value="" >
		    			</div>
		  			</div>
					  <div class="control-group">
					    <label class="control-label" for="password">Password</label>
					    <div class="controls">
					      <input type="password" id="password" name="user_password" value="" >
					    </div>
					  </div>
					  <div class="control-group" >
					  	<label class="control-label" for="password-control" >Re-type Password</label>
					    <div class="controls" >
					    	<input type="password" id="password-control" value="" />
					    </div>
					  </div>
					  <div class="control-group" >
					  	<label class="control-label" for="email" >Email</label>
					    <div class="controls" >
					    	<input type="text" id="email" name="user_email" value="" />
					    </div>
					  </div>
					</fieldset>
    		
					<fieldset><legend>Limit Access</legend>
					  <div class="control-group" >
					  	<p class="muted" >Type the IP addresses allowed to access MAB-LAB in the field below. 
					  	This will create a <em>.htaccess</em> file. Write one per line or use a comma to separate them.</p>
					  	<p class="muted" >Leave it empty if you don't want it to be created.</p>
					  	<p class="muted" >You can check the <em>.htaccess</em> file template <a href="htaccess.tmpl" target="_blank" >here</a>.</p>
					  </div>
		  			<div class="control-group">
		    			<label class="control-label" for="login">Allow this IP addresses</label>
		    			<div class="controls">
		      			<textarea name="allowedIps" row="6" style="height:100px;" ></textarea>
		    			</div>
		  			</div>
					</fieldset>
				</div>
			</div>
			
			<div class="row span1 offset6" style="margin-top:50px;" >
				<div class="control-group" >
			   	<div class="controls" >
				    <button type="button" class="btn" onclick="submitForm();" >Install</button>
				  </div>
				</div>
			</div>	
		</form>

    <script src="../libs/jquery/jquery-1.10.2.min.js" ></script>
    <script src="../libs/bootstrap/js/bootstrap.min.js" ></script>
    
    <script type="text/javascript" >
			var error = "";
    
			function submitForm() {
				cleanErrors();

				var check = $('#dbhost');
				if (check.val().length==0) {
					displayCheckError(check, 'You must sepcify a DB host !');				
				} 
				
				check = $('#dbuser');
				if (check.val().length==0) {
					displayCheckError(check, 'You must sepcify a DB username !');				
				} 
				
				check = $('#dbname');
				if (check.val().length==0) {
					displayCheckError(check, 'You must sepcify a DB name !');				
				} 

				check = $('#login');
				if (check.val().length==0) {
					displayCheckError(check, 'You must sepcify a login name !');				
				} 

				check = $('#password');
				if (check.val().length==0) {
					displayCheckError(check, 'You must sepcify a login password !');
					
				} else if (check.val()!=$('#password-control').val()) {
					displayCheckError($('#password-control'), 'You made a mistake while typing your password !'); 

				}

				if (error=="")
					document.installform.submit();
				else
					alert(error);
			}

			function displayCheckError(field, message) {
				field.focus();
				field.parent().parent().addClass('error');

				error += (error==""?"":"\n")+message;
			}

			function cleanErrors() {
				error = "";
				
				var arr = $('.control-group');

				for (i=0; i<arr.length; i++)
					$(arr[i]).removeClass('error');
			}
    </script>
  </body>
</html>