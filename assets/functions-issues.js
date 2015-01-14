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

function showChangeIssueDialog(reportIds) {
	$('#loader').show();
	
	doRequest("getchangeissuedlg", {ctl:'issues', reportIds:reportIds}, 
				function(data) {
					try {
						$('#dialogContainer').height('200px');
						$('#dialogContainer').html(data);
								
					} catch (err) { console.error(err); }
			
					$('#dialogContainer').modal('show');
					$('#loader').hide();
				});	
}

function showIssueDetails(id) {
	$('#loader').show();
	
	doRequest("getissue", {ctl:'issues', issueId:id}, 
				function(data) {
					try {
						$('#dialogContainer').html(data);
								
					} catch (err) { console.error(err); }
			
					$('#dialogContainer').modal('show');
					$('#loader').hide();
				});	
}

function showReportDetails(id) {
	$('#loader').show();
	
	doRequest("getreport", {ctl:'issues', reportId:id}, 
				function(data) {
					try {
						$('#dialogContainer').html(data);
								
					} catch (err) { console.error(err); }
			
					$('#dialogContainer').modal('show');
					$('#loader').hide();
				});	
}

function closeDialog() {
	$('#dialogContainer').modal('hide');
	$('#dialogContainer').html('');
	
	$('#dialogContainer').height('700px');
}

function isReportDetailsDialogOpen() {
	return ($("#reportDialog").is(':visible'));
}

function updateIssuesState(state, issueIds) {
	loader = $('#loader');
	loader.show();
	
	if (!issueIds)
		issueIds = getSelectedIssueIds();

	doRequest("setissuesstate", {ctl:'issues', issueIds:issueIds, state:state },
				function(data) {
					var res = data.split(':');
					
					if (res[0]=='O') {
						updateIssuesTblState(issueIds, res[1]);
						
					} else { alert(res[1]); }
					
					loader.hide();
				});
}

function updateIssuesPriority(priority, issueIds) {
	var loader = $('#loader');
	loader.show();
	
	if (!issueIds) {
		issueIds = getSelectedIssueIds();
	}

	doRequest("setissuespriority", {ctl:'issues', issueIds:issueIds, priority:priority },
				function(data) {
					var res = data.split(':');
					
					if (res[0]=='O') {
						updateIssuesTblPriority(issueIds, res[1]);
						
					} else { alert(res[1]); }
					
					loader.hide();
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
	
	doRequest("delreports", {ctl:'issues', reportIds:reportIds},
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
						
					} else { alert(res[1]); }
					
					if (dialog)
						hideReportDetailsDialog();
					else
						loader.hide();
				});
}

function delIssues(issueIds) { 
	if (!confirm('Do you really want to delete this issue ?This will delete all reports associated with.')) return;

	loader = $('#loader');
	dialog = false;
	
	loader.show();
	
	if (!issueIds)
		issueIds = getSelectedIssueIds();
	
	doRequest("delissues", {ctl:'issues', issueIds:issueIds},
				function(data) {
					var res = data.split(':');
		
					if (res[0]=='O') {
						removeIssuesTblRows(issueIds);
						
					} else { alert(res[1]); }
			
					loader.hide();	
				});
}

function updateIssuesTblState(issueIds, html) {
	try {
		if (typeof stringValue != "string")
			issueIds = ""+issueIds;

		var idsArr = issueIds.split(',');
		var htmlArr = html.split('||');
		
		for (var i=0; i<idsArr.length; i++) {
			var tmp = htmlArr[i].split('|');
			
			$('TR[issue='+idsArr[i]+']').removeClass().removeClass().addClass(tmp[2]);
			
			$('TR[issue='+idsArr[i]+'] > .issue-hilitecol').removeClass().addClass('issue-hilitecol '+tmp[1]);
			$('TR[issue='+idsArr[i]+'] > .state').html(tmp[0]);
		}
					
	} catch(err) { console.log(err); }
}

function updateIssuesTblPriority(issueIds, html) {
	try {
		if (typeof stringValue != "string")
			issueIds = ""+issueIds;
		
		var idsArr = issueIds.split(',');
		var htmlArr = html.split('||');
		
		for (var i=0; i<idsArr.length; i++) {
			var tmp = htmlArr[i].split('|');
			
			$('TR[issue='+idsArr[i]+'] > .issue-hilitecol').removeClass().addClass('issue-hilitecol '+tmp[1]);
			$('TR[issue='+idsArr[i]+'] > .priority').html(tmp[0]);
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

function loadTable(startPage) {	
	$.ajax({ url:"?a=getIssuesTbl&ctl=issues", type:"get", 
						beforeSend: function (xhr) { $('#issuesContent').html(LOADER_HTML); },
						data: {	app:$('#selectedAppId').val(),
										mId:$('#milestone').val(),
										showArchived:$('#showArchived').val(),
										state:$('#state').val(),
										priority:$('#priority').val(),
										order:$('#order').val(),
										versionName:$('#versionName').val(),
										androidVersion:$('#androidVersion').val(),
										deviceName:$('#deviceName').val(),
										issueCause:$('#issueCause').val(),
										start:startPage,
										limit:$('#limit').val()
									}
				}).done(function (data) {
			$('#issuesContent').html(data);
		});
}

function setSelectedAppId(elt, appId) {
	$('#selectedAppId').val(appId);
	$('#selectedAppName').html($(elt).html());
	
	if (onNewAppSelected)
		onNewAppSelected();
}

function editIssueComment() {
	$('form#issueForm #commentEdit').show();
	$('form#issueForm #commentView').hide();
}

function editIssueMilestone() {
	$('form#issueForm #milestoneView').hide();		
	$('form#issueForm #milestoneEdit').show();
}
 
function delMilestone(id) {
	if (confirm('Do you really want to delete this milestone ?')) {
		$('form#milestoneForm #mId').val(id);
		$('form#milestoneForm').submit();
	}
}