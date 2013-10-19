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

function reloadActiveTab() {
	var tab = $('.tab-content .active');
	tab.html(LOADER_HTML);

	doRequest('getlogs', { tab:tab.attr('id') }, function (data) {
			tab.html(data);
		});
}

function clearActiveTabLogs() {
	if (confirm("Do you really want to clear this logs ?")) {
		var tab = $('.tab-content .active');
		tab.html(LOADER_HTML);
		
		doRequest('clearlogs', { tab:tab.attr('id') }, function (data) {
			tab.html(data);
		});
	}
}