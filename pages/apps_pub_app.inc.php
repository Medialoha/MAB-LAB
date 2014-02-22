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

// build available templates and application translations
$translations = $mAppDesc->getAvailableTranslations();
$selectedTranslationName = '<span class="text-i" >No translation found</span>';
$translationList = array();
 
if (count($translations)==0) {

} else {
	if (strlen($mSelectedLang)==0) {
		$codes = array_keys($translations);
		$mSelectedLang = $codes[0];
	}

	foreach ($translations as $code=>$desc) {
		if (strcmp($mSelectedLang, $code)==0) {
			$selectedTranslationName = $desc['name'].' - '.$code;
		}
			
		$extras['code'] = $code;
			
		$translationList[] = '<li><a href="'.$mNavCtl->buildPageURL(null, $extras).'" >'.$desc['name'].'</a></li>';
	}
}

$templates = $mAppDesc->getAvailableTemplates();
$templateList = array();
 
if (count($templates)==0) {
	$templateList[] = '<li><a href="#" class="text-i" >No template available</a></li>';

} else {
	foreach ($templates as $code=>$desc) {
		$extras['code'] = $code;

		$templateList[] = '<li><a href="'.$mNavCtl->buildActionURL('apps', 'addTrans', $extras).'" >'.$desc['name'].'</a></li>';
	}
}

$extras['code'] = $mSelectedLang;
?>
  
<form id="pubForm" action="<?php echo $mNavCtl->buildActionURL('apps', null, null); ?>" method="post" >
	<input type="hidden" name="a" value="" />
	<input type="hidden" name="package" value="<?php echo $mSelectedAppPackage; ?>" />
	<input type="hidden" name="code" value="<?php echo $mSelectedLang; ?>" />
	
	<div class="row" style="margin-top:0px;" >
		<div class="span12" >
			<div class="btn-toolbar" >		  
				<div class="btn-group" >
					<button class="btn dropdown-toggle" data-toggle="dropdown">
				    <?php echo $selectedTranslationName; ?>&nbsp;&nbsp;<span class="caret" ></span>
					</button>				
					<ul class="dropdown-menu" >
						<?php echo implode('', $translationList); ?>
				    <li class="dropdown-submenu" ><a href="#" >Add translation</a>
				    	<ul class="dropdown-menu" >
				    		<?php echo implode('', $templateList); ?>
				    	</ul>
				    </li>
				  </ul>
			  </div>
			  
			  <button class="btn" type="button" onclick="editTranslationTmpl()" ><i class="icon-file"></i>&nbsp;Edit template</button>
			  <button class="btn btn-success" type="button" onclick="saveTranslation()" ><i class="icon-ok icon-white"></i>&nbsp;Save</button>
			  
			  <button class="btn btn-danger" type="button" onclick="deleteTranslation()" style="float:right;" ><i class="icon-trash icon-white"></i>&nbsp;Delete</button>
			</div>
		</div>
	</div>
	
	<div class="row" >
		<div class="span6" ><h4>PROPERTIES</h4></div>
		<div class="span6" ><h4>PREVIEW</h4></div>
	</div>
	
	<div class="row" >
		<div class="span6" >
		<?php 
			$mAppDesc->loadTranslation($mSelectedLang);
		
			$properties = $mAppDesc->getProperties();
		//	Debug::logd($properties);
			
			if ($properties==null) {
				?><span class="text-i muted" >No properties defined.</span><?php 
	
			} else {
				foreach ($properties as $p) {
					echo '<div class="control-group"><label class="control-label" for="'.$p->key.'" >'.$p->desc.'</label><div class="controls">';
		
					switch ($p->type) {
						case 'list' :
								echo '<textarea name="prop-', $p->key, '" rows="10" style="width:400px;" >', implode("\n", explode("|", $p->value)), '</textarea>';
							break;
						case 'text' :
								echo '<textarea name="prop-', $p->key, '" rows="5" style="width:400px;" >', $p->value, '</textarea>'; 
							break;
		
						default : echo '<input type="text" class="', $p->type, '" name="prop-', $p->key, '" value="', $p->value, '" />';
					}
					
					echo '</div></div>';
				}
			}
		?>
		</div>
	
		<div class="span6" style="background-color:#f6f6f6; " >
			<div id="preview" style="height:650px; margin:10px; padding:5px; background:#ffffff; border:1px solid #cdcdcd; overflow-y:scroll;" >
				<?php echo $mAppDesc->buildTranslatedDescription(); ?>
			</div>
		</div>
	</div>
</form>