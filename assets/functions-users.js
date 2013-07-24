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

function delUser(id) {
	if ($('#count').val()==1) {  
		alert('You can not delete all users !');
		return;
	}
	
	if (!confirm('Are you sure you want to delete this user ?')) return;
	
	$('#loader').show();
	
	$('#action').val('deluser');
	$('#userId').val(id);
	
	document.userForm.submit();
}

function editUser(id, name, mail) {	
	$('#user_id').val(id);
	$('#user_name').val(name);
	$('#user_password').val('');
	$('#user_email').val(mail);
}