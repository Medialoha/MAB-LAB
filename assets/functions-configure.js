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
	$('#reportBasicAuthPasswordClear').prop('disabled', disabled);
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