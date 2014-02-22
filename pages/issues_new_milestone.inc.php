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
require_once('includes/milestone.class.php');

$mMileId = $mNavCtl->getParam('mId', -1);
if ($mMileId>0) {
	$m = DbHelper::selectRow(TBL_MILESTONES, MILE_ID.'='.$mMileId);
	
}

$mAppArr = DbHelper::selectRows(TBL_APPLICATIONS, null, APP_NAME.' ASC', '*', null, null, false);
?>
<form class="form-horizontal" action="<?php echo $mNavCtl->buildActionURL('issues', 'updateMilestone', null); ?>" method="post" >
	<input type="hidden" name="<?php echo MILE_ID; ?>" value="<?php echo isset($m)?$m[MILE_ID]:''; ?>" />

	<fieldset><legend>Milestone Editor</legend>
	  <div class="control-group">
	    <label class="control-label" >Application</label>
	    
	    <div class="controls">
		    <select name="<?php echo MILE_APP_ID; ?>" >
		    <?php foreach ($mAppArr as $a) { ?>
		    	<option value="<?php echo $a[APP_ID]; ?>" ><?php echo $a[APP_NAME]; ?></option>
		    <?php } ?>
		    </select>
		    
	      <input type="text" name="<?php echo MILE_NAME; ?>" class="input-xlarge" placeholder="milestone name (ex: version name)" value="<?php echo isset($m)?$m[MILE_NAME]:''; ?>" />
	    </div>
	    
	  </div>
	  
	  <div class="control-group">
	    <label class="control-label" for="duedate">Due date</label>
	    <div class="controls">
	    	<div id="duedate" class="input-append" >
    			<input name="<?php echo MILE_DUEDATE; ?>" data-format="yyyy-MM-dd" type="text" class="input-small" value="<?php echo isset($m)&&!empty($m[MILE_DUEDATE])?date('Y-m-d', $m[MILE_DUEDATE]):''; ?>" />
    			<span class="add-on">
      			<i data-time-icon="icon-time" data-date-icon="icon-calendar" ></i>
    			</span>
  			</div>
	    </div>
	  </div>
	  
	  <div class="control-group">
	    <label class="control-label" for="description">Description</label>
	    <div class="controls">
	      <textarea <?php echo MILE_DESC; ?> style="width:400px; height:90px;" ><?php echo isset($m)?$m[MILE_DESC]:''; ?></textarea>
	    </div>
	  </div>
	  
	  <div class="control-group">
	    <div class="controls">

	      <button type="submit" class="btn btn-success pull-right">Save changes</button>
	    </div>
	  </div>
	</fieldset>
</form>

<script type="text/javascript" >
  $(function() {
    $('#duedate').datetimepicker({
      pickTime: false
    });
  });
</script>