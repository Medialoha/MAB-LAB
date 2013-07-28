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

function toggleBasicAuthFields(elt) {
	var disabled = elt.checked?false:true;
	
	$('#reportBasicAuthLogin').prop('disabled', disabled);
	$('#reportBasicAuthPassword').prop('disabled', disabled);
	$('#reportBasicAuthPasswordObfuscate').prop('disabled', disabled);
	$('#reportBasicAuthMethod').prop('disabled', disabled);
}

function clearObfPwd() {
	$('#obfpwd').html(' - ');
}

function toggleObfPwd() {
	if ($('#reportBasicAuthPasswordClear').prop('checked'))
		$('#obfpwd').show();
	else
		$('#obfpwd').hide();
}

function createHtFiles() {
	$('#action').val('createHtFiles');
	document.configForm.submit();
}

function deleteHtFiles()  {
	$('#action').val('deleteHtFiles');
	document.configForm.submit();
}

var error = "";
function submitForm() {
	cleanErrors();
	
	var check;
	
	if ($('#reportBasicAuthEnabled').prop('checked')) {
		check = $('#reportBasicAuthLogin');
		if (check.val().length==0) {
			displayCheckError(check, 'Login is required to use HTTP auth !');				
		} 
		check = $('#reportBasicAuthPassword');
		if (check.val().length==0) {
			displayCheckError(check, 'Password is required to use HTTP auth !');				
		} 
		
		if ($('#reportBasicAuthMethod :selected').index()==1) {

			if ($('#reportBasicAuthPasswordObfuscate').prop('checked')) {
				displayCheckError($('#reportBasicAuthMethod'), 'Password obfuscation can not be used with htaccess auth method !');
			}
		}

	}
	
	
	if (error=="") {
		$('#action').val('updateconfig');
		document.configForm.submit();
		
	} else { alert(error); }
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

function cancelForm() {
	location.reload();
}