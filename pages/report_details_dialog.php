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

$r = DBHelper::fetchReport($reportId);

if (empty($r->report_key)) {
	?><b class="color:red;" >Report with id <?php echo $reportId; ?> not found !!!</b><?php
	exit();
}

$priority = new IssuePriority($r->issue_priority);
$issueFormat = false;
?>
<div class="modal-header">
	<span style="float:right; width:60px;" >&nbsp;
		<img id="dlgloader" src="assets/images/loader.gif" style="float:right; height:25px;" />
	</span>
	<script >$('#dlgloader').hide();</script>
	
  <h3>ID <?php echo $r->report_key; ?>  
  	<?php if ($r->isArchived()) { 
  					?><span class="label" style="float:right; margin:5px 0px 0px 20px;" >ARCHIVED</span>
  	<?php } else if (!$r->isOpen()) { 
  					?><span class="label label-success" style="float:right; margin:5px 0px 0px 20px;" >RESOLVED</span>
  	<?php } ?>

		<span style="float:right; margin:-5px 0px 0px 0px;" ><?php echo $priority->getLabel();	?></span>
  </h3>
</div>
<div class="modal-body" style="clear:both; height:800px; max-height:620px;" >
	<?php require_once(BASE_PATH.'pages/report_details.inc.php'); ?>	
</div>
<div class="modal-footer">
  <a href="javascript:closeDialog()" class="btn" >Close</a>
 	
  <a href="#" class="btn btn-danger" onclick="delReports('<?php echo $r->report_id; ?>');" >
  	<i class="icon-trash icon-white" ></i>&nbsp;Delete</a>
</div>