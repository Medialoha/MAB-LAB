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

$users = DBHelper::fetchUsers();
?>
<form name="userForm" class="form-horizontal" method="post" >
	<input type="hidden" id="action" name="a" value="" />
	<input type="hidden" id="userId" name="userId" value="" />
	<input type="hidden" id="count" value="<?php echo sizeof($users); ?>" />
		
  <fieldset><legend>Users List</legend>
  	<table class="table table-condensed table-striped" >
			<thead><tr><th style="width:160px;" >Username</th><th>Email</th><th style="width:65px;" ></th></tr></thead>
			<tbody>
			<?php foreach ($users as $u) { ?>
				<tr>
					<td><?php echo $u->user_name; ?></td>
					<td><?php echo $u->user_email; ?></td>
					<td>
						<a href="#" onclick="editUser(<?php echo $u->user_id.", '".$u->user_name."', '".$u->user_email."'"; ?>)" ><i class="icon-edit" style="padding:0px 5px;" ></i></a>
						<a href="#" onclick="delUser(<?php echo $u->user_id; ?>)" ><i class="icon-trash" style="padding:0px 5px;" ></i></a>
					</td>
			<?php } ?>
  		</tbody>
  	</table>
	</fieldset>
</form>

<br/>

<form name="userEditForm" class="form-horizontal" method="post" onsubmit="$('#loader').show();" >
	<input type="hidden" name="a" value="edituser" />
	<input type="hidden" id="user_id" name="user_id" value="" />
	
	<fieldset><legend>User Editor</legend>
		<div class="control-group">
	    <label class="control-label" for="user_name">Username</label>
	    <div class="controls">
	      <input type="text" id="user_name" name="user_name" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="user_password">Password</label>
	    <div class="controls">
	      <input type="password" id="user_password" name="user_password" >
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="user_email">Email address</label>
	    <div class="controls">
	      <input type="text" id="user_email" name="user_email" value="" >
	    </div>
	  </div>
	  
	  <div class="row" style="margin-top:50px;" >
			<div class="control-group span6 offset5" >
	    	<div class="controls" >
		      <button type="submit" class="btn btn-primary" >Save</button>
		      <button type="button" class="btn" onclick="editUser('', '', '');" >Clear</button>
		    </div>
		  </div>
		</div>
	</fieldset>
</form>

<script type="text/javascript" src="assets/functions-users.js" ></script>