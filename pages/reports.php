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

// get reports preferences
$cfg = CfgHelper::getInstance();

// restore from session
if (isset($_SESSION['reportListOpts'])) {
	$opts = $_SESSION['reportListOpts'];
	
} else { $opts = null; }

// overload if passed by request
if (isset($_GET['showArchived']) || $opts==null) {
	$opts = array(
					'showArchived'=>Helper::getHTTPGetBooleanValue('showArchived', true),
					'package'=>Helper::getHTTPGetStringValue('package', null),
					'version'=>Helper::getHTTPGetStringValue('version', null),
					'android'=>Helper::getHTTPGetStringValue('android', null),
					'sortCol'=>Helper::getHTTPGetStringValue('sortCol', REPORT_CRASH_DATE),
					'sortOrder'=>Helper::getHTTPGetStringValue('sortOrder', 'DESC'),
					'limit'=>intval(Helper::getHTTPGetStringValue('limit', '20')),
					'start'=>intval(Helper::getHTTPGetStringValue('start', '0'))
				);
	
	// update session
	$_SESSION['reportListOpts'] = $opts;
}

// build where clauses
$where = ''; $and = '';

if (!$opts['showArchived']) {
	$where = REPORT_STATE.'<>'.REPORT_STATE_ARCHIVED; $and = ' AND ';
}

if ($opts['package']!=null) {
	$where .= $and.REPORT_PACKAGE_NAME.'="'.$opts['package'].'"'; $and = ' AND ';
}

if ($opts['version']!=null) {
	$tmp = explode('|', $opts['version']);
	$where .= $and.REPORT_VERSION_NAME.'="'.$tmp[0].'" AND '.REPORT_VERSION_CODE.'="'.$tmp[1].'"'; $and = ' AND ';
}

if ($opts['android']!=null) {
	$where .= $and.REPORT_ANDROID_VERSION.'="'.$opts['android'].'"'; $and = ' AND ';
}

$orderBy = $opts['sortCol'].' '.$opts['sortOrder'];
$limit = $opts['start'].', '.$opts['limit'];

$reports = DBHelper::selectRows(TBL_REPORTS, $where, $orderBy, "*", null, $limit, false);
$tmp = DBHelper::selectRow(TBL_REPORTS, $where, "COUNT(*) count");
$totalRows = $tmp[0];

// define select options array
$sortCols = array(
		REPORT_CRASH_DATE=>'Crash date',
		REPORT_PACKAGE_NAME=>'Package name',
		REPORT_VERSION_NAME=>'Version name',
		REPORT_VERSION_CODE=>'Version code',
		REPORT_ANDROID_VERSION=>'Android version'
	);

$limits = array(5, 10, 15, 20, 50, 100, 200);

$packages = DBHelper::selectRows(TBL_REPORTS, null, REPORT_PACKAGE_NAME.' ASC', REPORT_PACKAGE_NAME, REPORT_PACKAGE_NAME, null, false);
$versions = DBHelper::selectRows(TBL_REPORTS, null, REPORT_VERSION_CODE.' DESC', REPORT_VERSION_NAME.','.REPORT_VERSION_CODE, REPORT_VERSION_NAME.','.REPORT_VERSION_CODE, null, false);
$androidVersions = DBHelper::selectRows(TBL_REPORTS, null, REPORT_ANDROID_VERSION.' ASC', REPORT_ANDROID_VERSION, REPORT_ANDROID_VERSION, null, false);
?>
<div class="accordion" id="accordion2" >
	<div class="accordion-group">
		<div class="accordion-heading">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
				<i class="icon-filter" ></i>&nbsp;&nbsp;Filter & Sort Options</a>
		</div>
		<div id="collapseOne" class="accordion-body collapse">
			<div class="accordion-inner">
				<form name="filterForm" action="index.php" method="get" class="form-horizontal" >
					<input type="hidden" name="p" value="r" />
					<input type="hidden" id="start" name="start" value="<?php echo $opts['start']; ?>" />
								
					<div class="control-group">
						<label class="control-label" for="showArchived" >Archived reports</label>
						<div class="controls" >
						  <select id="showArchived" name="showArchived" style="width:80px;" >
				    		<option value="1" <?php if ($opts['showArchived']) echo 'selected="selected"'; ?> >Show</option>
				    		<option value="0" <?php if (!$opts['showArchived']) echo 'selected="selected"'; ?> >Hide</option>
				    	</select>
						</div>
					</div>
					
					<div class="control-group" >
						<label class="control-label" for="package" >Package</label>
						<div class="controls" >
							<select id="package" name="package" style="width:160px;" >
								<option value="" >----------------</option>
							<?php foreach ($packages as $v) { ?>
								<option value="<?php echo $v[0]; ?>" <?php if ($v[0]==$opts['package']) echo 'selected="selected"'; ?> >
									<?php echo ReportHelper::formatPackageName($v[0], $cfg->shrinkPackageName()); ?></option>
							<?php } ?>
							</select>
						
							<label class="control-label-inline" for="version" >Version</label>
							<select id="version" name="version" style="width:160px;" >
								<option value="" >----------------</option>
							<?php foreach ($versions as $v) { ?>
								<option value="<?php echo $v[0].'|'.$v[1]; ?>" <?php if ($v[0].'|'.$v[1]==$opts['version']) echo 'selected="selected"'; ?> ><?php echo $v[0].' #'.$v[1]; ?></option>
							<?php } ?>
							</select>
						
							<label class="control-label-inline" for="android" >Android</label>
							<select id="android" name="android" style="width:80px;" >
								<option value="" >-----</option>
							<?php foreach ($androidVersions as $v) { ?>
								<option value="<?php echo $v[0]; ?>" <?php if ($v[0]==$opts['android']) echo 'selected="selected"'; ?> ><?php echo $v[0]; ?></option>
							<?php } ?>
							</select>
						</div>
					</div>
					
					<div class="control-group">
						<label class="control-label" for="sortBy" >Sort by</label>
						<div class="controls" >
						  <select id="sortCol" name="sortCol" style="width:160px;" >
							<?php foreach ($sortCols as $value=>$text) { ?>
								<option value="<?php echo $value; ?>" <?php if ($value==$opts['sortCol']) echo 'selected="selected"'; ?> ><?php echo $text; ?></option>
							<?php } ?>
				    	</select>
				    	<select id="sortOrder" name="sortOrder" style="width:140px;" >
				    		<option value="ASC" <?php if ($opts['sortOrder']=='ASC') echo 'selected="selected"'; ?> >Ascending</option>
				    		<option value="DESC" <?php if ($opts['sortOrder']=='DESC') echo 'selected="selected"'; ?> >Descending</option>
				    	</select>

				    	<label class="control-label-inline" for="limit" >Page rows</label>
							<select id="limit" name="limit" onchange="$('#start').val(0);"  style="width:110px;" >
								<?php foreach ($limits as $value) { ?>
									<option value="<?php echo $value; ?>" <?php if ($value==$opts['limit']) echo 'selected="selected"'; ?> ><?php echo $value; ?>&nbsp;rows</option>
								<?php } ?>
							</select>
						</div>
						<br/>
						<div class="control-group" style="text-align:right;" >
				    	<button type="submit" class="btn" ><i class="icon-ok" ></i>&nbsp;&nbsp;Submit</button>
				    </div>
				  </fieldset>
				</form>
			</div>
		</div>
	</div>
</div>

<br/>

<table class="table table-condensed table-striped table-bordered reports-tbl" >
<thead>
	<tr>
		<td style="width:12px;" ><input type="checkbox" onclick="toggleCheckboxes(this);" /></td>
		<th>Date</th>
		<th>Package Name</th>
		<th>Version Name</th>
		<th>Version Code</th>
		<th>Android</th>
		<th>Phone Model</th>
		<th style="width:65px;" >
			<div class="btn-group">
			  <a class="btn btn-inverse btn-small dropdown-toggle" data-toggle="dropdown" href="#" >
			    <i class="icon-tasks icon-white" ></i>&nbsp;&nbsp;<span class="caret"></span>
			  </a>
			  <ul class="dropdown-menu" >
    			<li class="text-left" ><a href="javascript:archiveReports()" ><i class="icon-folder-open" ></i>&nbsp;Archive selected</a></li>
    			<li class="text-left" ><a href="javascript:delReports()" ><i class="icon-trash" ></i>&nbsp;Delete selected</a></li>
			  </ul>
			</div>
		</th>
	</tr>		
</thead>
<tbody>
<?php 
	if (empty($reports)) { 
		?><tr><td colspan="8" class="muted" >No repords recorded yet...</td></tr><?php
		 
	} else {
		foreach ($reports as $values) { 
			$r = Report::createFromArray($values); ?>
	<tr class="<?php echo $r->isArchived()?'muted':''; ?>" >
		<td><input type="checkbox" name="itemChecked" value="<?php echo $r->report_id; ?>" /></td>
		<td class="date" >
		<?php
			echo ReportHelper::getBadge($r->isNew());
			echo "&nbsp;";
			echo ReportHelper::formatDate($r->user_crash_date, $cfg->getDateFormat()); 
		?>
		</td>
		<td class="package-name" ><?php echo ReportHelper::formatPackageName($r->package_name, $cfg->shrinkPackageName()); ?></td>
		<td class="version-name" ><?php echo $r->app_version_name; ?></td>
		<td class="version-code" ><?php echo $r->app_version_code; ?></td>
		<td class="android-version" ><?php echo $r->android_version; ?></td>
		<td class="phone-model" ><?php echo $r->phone_model; ?></td>
		<td>
			<div class="btn-group">
  			<button class="btn btn-small" onclick="showReportDetails('<?php echo $r->report_id; ?>')" ><i class="icon-eye-open" ></i></button>
  			<button class="btn btn-small dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
  			<ul class="dropdown-menu">
    			<li><a href="javascript:showReportDetails('<?php echo $r->report_id; ?>')" ><i class="icon-eye-open" ></i>&nbsp;Details</a></li>
    			<li><a href="javascript:archiveReport('<?php echo $r->report_id; ?>', '#loader')" ><i class="icon-folder-open" ></i>&nbsp;Archive</a></li>
    			<li><a href="javascript:delReport('<?php echo $r->report_id; ?>', '#loader')" ><i class="icon-trash" ></i>&nbsp;Delete</a></li>
  			</ul>
			</div>
		</td>
	</tr>
<?php } } ?>
</tbody>
</table>

<?php // create pagination
$nbPage = $totalRows/$opts['limit'];
$currentPage = ($opts['start']/$opts['limit'])+1;
?>
<div class="pagination pagination-right" >
  <ul>
    <li <?php if ($currentPage==1) echo 'class="disabled"'; ?> >
    	<a href="#" onclick="<?php if ($currentPage>1) echo 'gotoPage('.(($currentPage-2)*$opts['limit']).')'; ?>" >Prev</a>
    </li>
    
    <?php for ($page=0; $page<$nbPage; ++$page) { ?>
    	<li <?php if (($page+1)==$currentPage) echo 'class="active"'; ?> >
    		<a href="#" onclick="gotoPage(<?php echo ($page*$opts['limit']); ?>);" ><?php echo $page+1; ?></a>
    	</li>
    <?php } ?>
    
    <li <?php if ($currentPage>=$nbPage) echo 'class="disabled"'; ?> >
    	<a href="#" onclick="<?php if ($currentPage<$nbPage) echo 'gotoPage('.($currentPage*$opts['limit']).')'; ?>" >Next</a>
    </li>
  </ul>
</div>

<div id="reportDialog" class="modal hide fade" style="width:1000px; margin-left:-500px; height:700px;" ></div>

<script type="text/javascript" src="assets/functions-reports.js" ></script>