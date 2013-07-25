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

var LOADER_HTML = '<div class="boxloader" ><img src="assets/images/loader.gif" /></div>';

loadAll();

function loadAll() {	
	showLoaderIfEmpty('#chart1-1');
	loadChart('#chart1-1', REPORTS_PER_ANDROID_VERSION_PIE_CHART_ID, PIE_CHART_TYPE_ID);

	showLoaderIfEmpty('#chart1-2');
	loadChart('#chart1-2', REPORTS_PER_APPLICATION_PIE_CHART_ID, PIE_CHART_TYPE_ID);

	loadMostAffectedDevices();
	
	showLoaderIfEmpty('#chart2-1');
	loadChart('#chart2-1', REPORTS_EVOLUTION_LINE_CHART_ID, LINE_CHART_TYPE_ID);

	loadLastReports();
	
	// Refresh chart each 10min
	setTimeout(function() { loadAll(); }, 60000 );
}

function loadLastReports() {
	showLoaderIfEmpty('#lastreports');
	
	$.ajax({ url:"?a=getlastreports", type:"get" })
	 .done(function(data) {
		 $('#lastreports').html(data);
	 });	
}

function loadMostAffectedDevices() {
	showLoaderIfEmpty('#chart1-3');
	
	$.ajax({ url:"?a=getmostaffecteddev", type:"get" })
	 .done(function(data) {
		 $('#chart1-3').html(data);
	 });	
}

function showLoaderIfEmpty(eltId) {
	if (!$(eltId).html()) {
		$(eltId).html(LOADER_HTML);	
	}
}