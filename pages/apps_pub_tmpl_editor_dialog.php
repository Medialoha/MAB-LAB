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

require_once(BASE_PATH.'includes/appdesc.class.php');

$package = isset($_REQUEST['package'])?$_REQUEST['package']:'';
$code = $_POST['code'];

$isNew = empty($code);

$isTmpl = empty($package)?true:false;

$mAppDesc = new ApplicationDesc($package);
if (!$isNew)
	$mAppDesc->loadTranslation($code, $isTmpl);
?>
<div class="modal-header">
	<h3><?php echo (empty($package)?"Template":$package).' '.$code; ?></h3>
</div>

<div class="modal-body" style="clear:both; height:800px; max-height:620px;" >
	<div class="row" style="padding:0px 20px 15px 20px;" >
		<p>Property format : <b>{</b> key <b>:</b> type <b>:</b> desc <b>}</b></p>
		<p style="padding-left:10px;" >&bull;&nbsp;&nbsp;<em>type = { text | list | input-small | input-medium | input-large | input-xlarge | input-xxlarge }</em></p>
	</div>	
	
	<form id="editorForm" class="form-horizontal" action="<?php echo $mNavCtl->buildActionURL('apps', null, null); ?>" method="post" >
		<input type="hidden" name="a" value="updateTemplate" />
		<input type="hidden" name="package" value="<?php echo $package; ?>" />
		<input type="hidden" name="code" value="<?php echo $code; ?>" />
		
		<?php if ($isNew) { ?>
		<div class="control-group">
    	<label class="control-label" for="inputEmail">Language name</label>
    	<div class="controls">
      	<input type="text" name="lang-name" class="input-large" placeholder="language name" />
      	<input type="text" name="lang-code" class="input-medium" placeholder="code ie en-US" />
    	</div>
    	
  	</div>
  	<?php } ?>
	
		<center><textarea name="template" style="width:640px; height:<?php echo $isNew?'480':'500'; ?>px;" ><?php if (!$isNew) echo htmlspecialchars($mAppDesc->buildTranslatedDescription(true)); ?></textarea></center>
	</form>
</div>


<div class="modal-footer">
  <a href="javascript:closeTmplEditor()" class="btn" >Close</a>
 	
  <a href="javascript:saveTmpl()" class="btn btn-success" ><i class="icon-ok icon-white" ></i>&nbsp;Save changes</a>
</div>
