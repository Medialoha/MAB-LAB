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

/////////// APPS MANAGEMENT METHODS ////////////////
function updateAppName(appId, appName) { 	
	var newName = prompt("New application name :", appName);
	
	if (!newName)
		return;
	
	$('#loader').show();
	doRequest('updateAppName&ctl=apps', {appId:appId, appName:newName}, 
				function(data) {
					$("TR#"+appId+" > TD.app-name > SPAN").html(newName);
					$('#loader').hide();
				});
}

/////////// PUBLICATION TRANSLATIONS METHODS ////////////////
function saveTranslation() {
	$('#loader').show();
	
	$('#pubForm [name="a"]').attr('value', 'saveTranslation');
	
	$.post(	$('#pubForm').attr('action'), 
					$('form#pubForm').serialize(), 
					function(data) {
						$('#preview').html(data);
						
						$('#loader').hide();
			    }
  			);
	
}

function deleteTranslation() {
	if (confirm("Do you really want to delete this translation ?")) {
		$('#pubForm [name="a"]').attr('value', 'delTranslation');
		
		$('#pubForm').submit();
	}
}

function editTranslationTmpl() {
	$('#loader').show();
	
	$('#pubForm [name="a"]').attr('value', 'getTmplEditor');
	
	$.post(	$('#pubForm').attr('action'), 
					$('form#pubForm').serialize(), 
					function(data) {
						try {
							$('#dialogContainer').html(data);							
							$('#dialogContainer').modal('show');
							$('#loader').hide();
							
						} catch (err) { console.error(err); }
			    }
  			);
}

function closeTmplEditor() {
	$('#dialogContainer').html('');							
	$('#dialogContainer').modal('hide');
}

function saveTmpl() {
	if ($('#tmplForm [name="lang-name"]').length>0) {
		if ($('#tmplForm [name="lang-name"]').attr('value').empty() || $('#tmplForm [name="lang-code"]').attr('value').empty()) {
			alert('Language name and code are required !');
			return;
		}
	}
	
	$('#loader').show();
	
	$('#editorForm').submit();
	
	closeTmplEditor();
}

function editTmpl(code) {
	$('#loader').show();
	
	$('#tmplForm [name="a"]').attr('value', 'getTmplEditor');
	$('#tmplForm [name="code"]').attr('value', code?code:'');
	
	$.post(	$('#tmplForm').attr('action'), 
					$('form#tmplForm').serialize(), 
					function(data) {
						try {
							$('#dialogContainer').html(data);							
							$('#dialogContainer').modal('show');
							$('#loader').hide();
							
						} catch (err) { console.error(err); }
			    }
  			);
}
