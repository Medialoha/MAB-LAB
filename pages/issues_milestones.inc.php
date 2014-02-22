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

$milesArr = DbHelper::fetchMilestones(($mSelectedAppId>0?MILE_APP_ID.'='.$mSelectedAppId:null));
?>
<style>
.progress { height:30px; }
.bar { padding:6px; }
</style>

<form id="milestoneForm" action="<?php echo $mNavCtl->buildActionURL('issues', 'delMilestone', null); ?>" method="post" >
	<input type="hidden" id="mId" name="mId" value="" />
</form>

<table class="table ">
<?php 
if (empty($milesArr)) {
	?><tr><td colspan="2" class="muted text-i" >No milestone found.</td></tr><?php 

} else {

	foreach ($milesArr as $m) {
		$milestone = Milestone::createFromArr($m);
?>
<tr>
	<td style="width:45%; border-top:0px none;" >
		<h3 style="color:#4183C4; margin:0px 0px;" ><?php echo $m[APP_NAME].' '.$m[MILE_NAME]; ?></h3>
		<span class="muted" >Due in </span><span><?php $milestone->printRemainingTime(); ?></span>
	</td>
	<td style="width:55%; border-top:0px none;" >
		<div class="pull-left" ><?php echo $milestone->count_closed; ?> closed &mdash; <?php echo $milestone->getCountOpen(); ?> open </div>
		<div class="pull-right" >
			<a href="<?php echo $mNavCtl->buildPageURL('nmil', array('mId'=>$milestone->getId())); ?>" >Edit</a>&nbsp;&nbsp;
<!-- 			<a href="#" >Close</a>&nbsp;&nbsp; -->
			<?php if ($milestone->count_all==0) { ?>
				<a href="javascript:delMilestone(<?php echo $milestone->getId(); ?>)" class="text-error" >Delete</a>
			<?php } else { ?>
				<a href="#" class="muted text-i" >Delete</a>
			<?php } ?>&nbsp;&nbsp;
			<a href="<?php echo $mNavCtl->buildPageURL('', array('mId'=>$milestone->getId())); ?>" >Browse issues &rarr;</a>
		</div>
	
		<br class="clearfix" style="margin-top:7px;" />
		<?php echo $milestone->printProgressBar(); ?>
	</td>
</tr>
<tr>
	<td colspan="2" style="border-top:0px none;" >
		<hr style="margin:3px 0px;" />
		<?php echo $m[MILE_DESC]; ?>
	</td>
</tr>
<?php 
	} 
}
?>
</table>
