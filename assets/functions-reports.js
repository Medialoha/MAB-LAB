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

function showReportDetails(id) {
	$('#loader').show();
	
	$.ajax({ url:"?a=getreport", type:"post", data:{reportId:id} })
		.done(function(data) {
			try {
				$('#reportDialog').html(data);
								
			} catch (err) { console.error(err); }
			
			$('#reportDialog').modal('show');
			$('#loader').hide();
		});	
}

function delReports() {
	var arr = $('input[name="itemChecked"]');
	var ids = ''; var sep = '';
	
	for (var i=0; i<arr.length; i++) {
		if (arr[i].checked) {
			ids += sep+arr[i].value;
			sep = ',';
		}
	}
	
	delReport(ids, '#loader');
}

function archiveReports() {
	var arr = $('input[name="itemChecked"]');
	var ids = ''; var sep = '';
	
	for (var i=0; i<arr.length; i++) {
		if (arr[i].checked) {
			ids += sep+arr[i].value;
			sep = ',';
		}
	}
	
	archiveReport(ids, '#loader');
}

function delReport(ids, loaderId) {
	if (!confirm('Do you really want to delete this report ?')) return;
	
	$(loaderId).show();

	$.ajax({ url:"?a=delreports", type:"post", data:{reportIds:ids} })
	.done(function(data) {
		alert(data);
		$('#reportDialog').modal('hide');
		$('#reportDialog').html('');

		$(loaderId).hide();
		
		window.location.reload();
	});
}

function archiveReport(ids, loaderId) {
	$(loaderId).show();

	$.ajax({ url:"?a=archreports", type:"post", data:{reportIds:ids} })
	.done(function(data) {
		alert(data);
		$('#reportDialog').modal('hide');
		$('#reportDialog').html('');

		$(loaderId).hide();
		
		window.location.reload();
	});
}

function gotoPage(start) {
	$('#start').val(start);
	document.filterForm.submit();
}

function toggleCheckboxes(elt) {
	var arr = $('input[name="itemChecked"]');
	for (var i=0; i<arr.length; i++)
		arr[i].checked = elt.checked;
}