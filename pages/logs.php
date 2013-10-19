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
$mDebug = new Debug();
?>
<div id="logTabs" class="tabbable tabs-left">
  <ul class="nav nav-tabs">
		<li class="active" ><a href="#tabFile" data-toggle="tab" ><i class="icon-file" ></i>&nbsp;Log file</a></li>
		<li><a href="#tabDB" data-toggle="tab" ><i class="icon-th-list" ></i>&nbsp;Database logs</a></li>
  </ul>
  
  <div class="tab-content" >
		<div class="btn-toolbar" style="margin-bottom:10px; text-align:right;" >
			<button class="btn" onclick="reloadActiveTab()" ><i class="icon-refresh" ></i>&nbsp;Refresh</button>
			<button class="btn btn btn-danger" onclick="clearActiveTabLogs()" ><i class="icon-remove-circle icon-white" ></i>&nbsp;Clear</button>
		</div>
  
  	<div id="tabFile" class="tab-pane active" >	
  		<?php echo $mDebug->getFormattedLogs(); ?>
  	</div>
		<div id="tabDB" class="tab-pane" >
			<?php echo $mDebug->getFormattedDBLogs(); ?>
		</div>
  </div>
</div>

<script type="text/javascript" src="assets/functions-logs.js" ></script>