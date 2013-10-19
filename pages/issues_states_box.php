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

// count total number of issues
$res = DBHelper::selectRows(TBL_ISSUES, null, null, 'COUNT(*) count', null, null, false);
$total = $res[0][0];

// count issues closed
$res = DBHelper::selectRows(TBL_ISSUES, 
														ISSUE_STATE.'='.ISSUE_STATE_CLOSED,
		 												null, 'COUNT(*) count', null, null, false);
$nbResolved = $res[0][0];

// count issues archived
$res = DBHelper::selectRows(TBL_ISSUES, 
														ISSUE_STATE.'='.ISSUE_STATE_ARCHIVED,
		 												null, 'COUNT(*) count', null, null, false);
$nbArchived = $res[0][0];

// count reports not fixed
$res = DBHelper::selectRows(TBL_ISSUES, 
														// where
														ISSUE_STATE.'<>'.ISSUE_STATE_ARCHIVED,
														// order
		 												null,
														// projection
														ISSUE_PRIORITY.', COUNT('.ISSUE_ID.') count', 
														// group by
														ISSUE_PRIORITY, null, true);
$nbNotFixed = 0;
$nbNotFixedCritical = 0;
$nbNotFixedNormal = 0;
$nbNotFixedLow = 0;

if (is_array($res))
foreach ($res as $row) {
	$nbNotFixed += $row->count;
	
	switch ($row->issue_priority) {
		case IssuePriority::CRITICAL : $nbNotFixedCritical = $row->count;
			break; 
		case IssuePriority::NORMAL : $nbNotFixedNormal = $row->count;
			break; 
		case IssuePriority::LOW : $nbNotFixedLow = $row->count;
			break; 
	}
}

$p = new IssuePriority(IssuePriority::CRITICAL);
?>
<div style="padding-right:25px;" >
<table class="table table-condensed states-tbl" >
<thead>
	<tr>
		<th>Total issues</th>
		<th style="width:60px" ></th>
		<th style="width:20px; text-align:center;" ><?php echo $total; ?></th>
	</tr>		
</thead>
<tbody>
	<tr>
		<td class="name" >Resolved</td>
	 	<td class="issues-resolved percent" ><?php  echo $total>0?round($nbResolved/$total*100, 2):0;  ?>&#37;</td>
	 	<td class="count" ><?php echo $nbResolved; ?></td>
	</tr>
	<tr>
		<td class="name" >Needs to be fixed</td>
	 	<td class="issues-notfixed percent" ><b><?php echo $total>0?round($nbNotFixed/$total*100, 2):0; ?>&#37;</b></td>
	 	<td class="count" ><b><?php echo $nbNotFixed; ?></b></td>
	</tr>
	<tr class="notfixed-tag" >
		<td class="name" ><?php //echo $p->getLabel(); ?>Critical</td>
	 	<td class="percent" ><?php echo $nbNotFixed>0?round($nbNotFixedCritical/$nbNotFixed*100, 2):0; ?>&#37;</td>
	 	<td class="count" ><?php echo $nbNotFixedCritical; ?></td>
	</tr>
	<tr class="notfixed-tag" >
		<td class="name" ><?php //$p->setPriority(IssuePriority::NORMAL); echo $p->getLabel(); ?>Normal</td>
	 	<td class="percent" ><?php echo $nbNotFixed>0?round($nbNotFixedNormal/$nbNotFixed*100, 2):0; ?>&#37;</td>
	 	<td class="count" ><?php echo $nbNotFixedNormal; ?></td>
	</tr>
	<tr class="notfixed-tag" >
		<td class="name" ><?php //$p->setPriority(IssuePriority::LOW); echo $p->getLabel(); ?>Low</td>
	 	<td class="percent" ><?php echo $nbNotFixed>0?round($nbNotFixedLow/$nbNotFixed*100, 2):0; ?>&#37;</td>
	 	<td class="count" ><?php echo $nbNotFixedLow; ?></td>
	</tr>
	<tr class="archived" >
		<td class="name" >Archived</td>
	 	<td class="reports-archived percent" ><?php echo $total>0?round($nbArchived/$total*100, 2):0; ?>&#37;</td>
	 	<td class="count" ><?php echo $nbArchived; ?></td>
	</tr>
</tbody>
</table>
</div>