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

function showIssueReportsTbl(issueId) {	
	var tbl = $("#reportsTbl"+issueId);
	
	if (tbl.is(':visible'))
		tbl.hide();
	else
		tbl.show();
}

function showReportDetails(id) {
	$('#loader').show();
	
	doRequest("getreport", {reportId:id}, 
				function(data) {
					try {
						$('#reportDialog').html(data);
								
					} catch (err) { console.error(err); }
			
					$('#reportDialog').modal('show');
					$('#loader').hide();
				});	
}

function hideReportDetailsDialog() {
	$('#reportDialog').modal('hide');
	$('#reportDialog').html('');
}

function isReportDetailsDialogOpen() {
	return ($("#reportDialog").is(':visible'));
}

function updateIssuesState(state, issueIds) {
	loader = $('#loader');
	loader.show();
	
	if (!issueIds)
		issueIds = getSelectedIssueIds();

	doRequest("setissuesstate", { issueIds:issueIds, state:state },
				function(data) {
					var res = data.split(':');
					
					if (res[0]=='O') {
						updateIssuesTblState(issueIds, state);
					}
					
					loader.hide();
					alert(res[1]);		
				});
}

function updateIssuesPriority(priority, issueIds) {	
	loader = $('#loader');
	loader.show();
	
	if (!issueIds)
		issueIds = getSelectedIssueIds();

	doRequest("setissuespriority", { issueIds:issueIds, priority:priority },
				function(data) {
					var res = data.split(':');
					
					if (res[0]=='O') {
						updateIssuesTblPriority(issueIds, res[2]);
					}
					
					loader.hide();
					alert(res[1]);
				});
}

function delReports(reportIds, issueId) {
	if (!confirm('Do you really want to delete this report ?')) return;

	loader = $('#loader');
	dialog = false;
	
	// dialog
	if (isReportDetailsDialogOpen()) {
		loader = $('#dlgloader');
		dialog = true;
	}
	
	loader.show();
	
	doRequest("delreports", {reportIds:reportIds},
				function(data) {
					var res = data.split(':');
		
					if (res[0]=='O') {
						if (HOME_PAGE) {
							loadNewReports();
							
						} else {
							removeReportsTblRows(reportIds);
							
							// update issue reports count
							if (issueId) {
								var count = $('#reportsTbl'+issueId+' >tbody >tr').length;								
								$('#count'+issueId).html(count);
								
								// no more reports, remove issue
								if (count==0) {
									removeIssuesTblRows(issueId);
								}
							}		
						}							
					}
					
					if (dialog)
						hideReportDetailsDialog();
					else
						loader.hide();
					
					alert(res[1]);
				});
}

function delIssues(issueIds) { 
	if (!confirm('Do you really want to delete this issue ?This will delete all reports associated with.')) return;

	loader = $('#loader');
	dialog = false;
	
	loader.show();
	
	if (!issueIds)
		issueIds = getSelectedIssueIds();
	
	doRequest("delissues", {issueIds:issueIds},
				function(data) {
					var res = data.split(':');
		
					if (res[0]=='O') {
						removeIssuesTblRows(issueIds);
					}
			
					loader.hide();	
					alert(res[1]);
				});
}

function updateIssuesTblState(issueIds, state) {
	try {
		if (typeof stringValue != "string")
			issueIds = ""+issueIds;
		
		var arr = issueIds.split(',');
		
		for (var i=0; i<arr.length; i++) {
			var tr = $('TR[issue='+arr[i]+']');
			tr.removeClass();
			tr.addClass(state==0?'archived':state==3?'closed':'');
		}
					
	} catch(err) { console.log(err); }
}

function updateIssuesTblPriority(issueIds, label) {
	try {
		if (typeof stringValue != "string")
			issueIds = ""+issueIds;
		
		var arr = issueIds.split(',');
		
		for (var i=0; i<arr.length; i++) {
			$('TR[issue='+arr[i]+'] > .priority').html(label);
		}
					
	} catch(err) { console.log(err); }
}

function removeIssuesTblRows(issueIds) {
	try {
		if (typeof stringValue != "string")
			issueIds = ""+issueIds;
		
		var arr = issueIds.split(',');
		
		for (var i=0; i<arr.length; i++) {
			var rows = $('TR[issue='+arr[i]+']');
			rows.remove();
		}
					
	} catch(err) { console.log(err); }
}

function removeReportsTblRows(reportIds) {
	try {
		if (typeof stringValue != "string")
			reportIds = ""+reportIds;
		
		var arr = reportIds.split(',');
		
		for (var i=0; i<arr.length; i++)
			$('#reportRow'+arr[i]).remove();
		
	} catch(err) { console.log(err); }
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

function getSelectedIssueIds() {
	var arr = $('input[name="itemChecked"]');
	var ids = ''; var sep = '';

	for (var i=0; i<arr.length; i++) {
		if (arr[i].checked) {
			ids += sep+arr[i].value;
			sep = ',';
		}
	}

	return ids;
}