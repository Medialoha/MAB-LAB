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

// force reloading configure to prevent out of sync from config file
CfgHelper::init(true);

$cfg = CfgHelper::getInstance(); 
?>
<form class="form-horizontal" method="post" >
	<input type="hidden" name="a" value="updateconfig" />
	<input type="hidden" name="in-report-tags" value="" />

  <fieldset><legend>Display Options</legend>
	  <div class="control-group">
	    <label class="control-label" for="reportDateFormat">Date format</label>
	    <div class="controls">
	      <input type="text" id="reportDateFormat" name="in-date-format" value="<?php echo $cfg->getDateFormat(); ?>" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="reportDateFormat">Default timezone</label>
	    <div class="controls">
	      <input type="text" id="reportDateFormat" name="in-date-timezone" value="<?php echo $cfg->getDateTimezone(); ?>" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="packageNameShrink">Shrink package name</label>
	    <div class="controls">
	      <select id="packageNameShrink" name="in-report-packagename-shrink" >
	      	<option value="1" <?php echo $cfg->shrinkPackageName()?'selected="selected"':''; ?> >Yes (Myappname)</option>
	      	<option value="0" <?php echo $cfg->shrinkPackageName()?'':'selected="selected"'; ?> >No (com.my.package.myappname)</option>
	      </select>
	    </div>
	  </div>
  </fieldset>

  <?php 
  	$account = $cfg->getBasicAuthAccount(); 
  	$disabled = $cfg->isReportBasicAuthEnabled()?null:'disabled="disabled"'; 
  ?>
  <fieldset><legend>Report Authentication</legend>
	  <div class="control-group">
	    <label class="control-label" for="reportBasicAuthEnabled">Enable HTTP basic auth</label>
	    <div class="controls">
	      <input type="checkbox" id="reportBasicAuthEnabled" name="report-basicauth" <?php if ($cfg->isReportBasicAuthEnabled()) echo 'checked="checked"'; ?> onclick="toggleBasicAuthFields(this);" value="1" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="reportBasicAuthLogin">Login</label>
	    <div class="controls">
	      <input type="text" id="reportBasicAuthLogin" name="report-basicauth-login" value="<?php echo $account->login; ?>" <?php echo $disabled; ?> />
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="reportBasicAuthPassword">Password</label>
	    <div class="controls">
	      <input type="password" id="reportBasicAuthPassword" name="report-basicauth-password" value="<?php echo $account->password; ?>" <?php echo $disabled; ?> onkeyup="clearObfPwd()" />
	      
	      <label class="control-label-inline" style="width:200px;" for="" >Obfuscated with md5</label>
	      <input type="checkbox" id="reportBasicAuthPasswordClear" name="report-basicauth-obfuscate" <?php if (!$account->clear) echo 'checked="checked"'; ?> <?php echo $disabled; ?> value="1" onchange="toggleObfPwd()" />
	    </div>
	  </div>
	  <div class="control-group">
	  	<label class="control-label" style="padding-top:0px;" >Help</label>
	    <div class="controls">
	 			<small class="muted" >
	 				<i>If you use obfuscated password, type your clear password here. 
	 				<br/>And, under your app, use the obfuscated password : </i>
	 				<b id="obfpwd" ><?php if (!$account->clear) echo md5($account->password); else echo ' - '; ?></b>
	 			</small>
	 		</div>
	  </div>
  </fieldset>

  <fieldset><legend>Mail Options</legend>
	  <div class="control-group">
	    <label class="control-label" for="reportSendMail" >On report received</label>
	    <div class="controls">
	      <select id="reportSendMail" name="in-report-sendmail" >
	      	<option value="1" <?php echo $cfg->sendMailOnReportReceived()?'selected="selected"':''; ?> >Send a mail</option>
	      	<option value="0" <?php echo $cfg->sendMailOnReportReceived()?'':'selected="selected"'; ?> >Do nothing</option>
	      </select>
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="reportMailRecipients">
	    	Recipients<br/><small class="muted" >Comma separeted list of mail addresses</small>
	    </label>
	    <div class="controls">
	      <textarea id="reportSendMailRecipients" name="in-report-sendmail-recipients" rows="3" style="width:400px;" ><?php echo $cfg->getReportMailRecipients(false); ?></textarea>
	    </div>
	  </div>
	  
	  <div class="control-group">
	    <label class="control-label" for="mailFromAddr" >Sender mail address</label>
	    <div class="controls">
	      <input type="text" id="mailFromAddr" name="in-mail-from-addr" value="<?php echo $cfg->getMailFromAddr(); ?>" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="mailFromName" >Sender name</label>
	    <div class="controls">
	      <input type="text" id="mailFromName" name="in-mail-from-name" value="<?php echo $cfg->getMailFromName(); ?>" >
	    </div>
	  </div>
  </fieldset>
  
  <fieldset><legend>Database Configuration</legend>
	  <div class="control-group">
	    <label class="control-label" for="reportDbHost">Host</label>
	    <div class="controls">
	      <input type="text" id="reportDbHost" name="in-db-host" value="<?php echo $mGlobalCfg['db.host']; ?>" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="reportDbUser">User</label>
	    <div class="controls">
	      <input type="text" id="reportDbUser" name="in-db-user" value="<?php echo $mGlobalCfg['db.user']; ?>" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="reportDbPwd">Password</label>
	    <div class="controls">
	      <input type="password" id="reportDbPwd" name="in-db-pwd" value="<?php echo $mGlobalCfg['db.pwd']; ?>" placeholder="Password" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="reportDbName">Database name</label>
	    <div class="controls">
	      <input type="text" id="reportDbName" name="in-db-name" value="<?php echo $mGlobalCfg['db.name']; ?>" >
	    </div>
	  </div>
	  
	  <div class="control-group">
	    <label class="control-label" for="reportTblPrefix">Table prefix</label>
	    <div class="controls">
	      <input type="text" id="reportTblPrefix" name="in-tbl-prefix" value="<?php echo $mGlobalCfg['tbl.prefix']; ?>" >
	    </div>
	  </div>
  </fieldset>
  
  
	<div class="row" style="margin-top:50px;" >
		<div class="control-group span6 offset5" >
    	<div class="controls" >
	      <button type="submit" class="btn btn-primary" >Save</button>
	      <button type="submit" class="btn">Cancel</button>
	    </div>
	  </div>
	</div>
</form>

<script type="text/javascript" src="assets/functions-configure.js" ></script>