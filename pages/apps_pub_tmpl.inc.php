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

$templates = $mAppDesc->getAvailableTemplates();
?>
<form id="tmplForm" action="<?php echo $mNavCtl->buildActionURL('apps', null, null); ?>" method="post" >
	<input type="hidden" name="a" value="" />
	<input type="hidden" name="code" value="" />
	
	<div class="row" style="margin-top:0px;" >
		<div class="span12" >			  
			<div class="btn-toolbar" >	
				<button class="btn" type="button" onclick="editTmpl()" ><i class="icon-file"></i>&nbsp;New template</button>
			</div>
		</div>
	</div>
	
	<div class="row" >
		<div class="span12" >
		<table class="table table-condensed" >
		<thead><tr><th>Language Name</th><th style="text-align:center;" >Language Code</th><th colspan="2" >Template File</th></thead>
		<tbody>
		<?php foreach ($templates as $code=>$desc) { ?> 
			
			<tr>
				<td><?php echo $desc['name']; ?></td>
				<td style="text-align:center;" ><?php echo $code; ?></td>
				<td class="muted text-i" ><em><?php echo $desc['file']; ?></em></td>
				<td><a href="javascript:editTmpl('<?php echo $code; ?>')" class="button" ><i class="icon-edit" ></i></a>
			</tr>
		
		<?php } ?>
		</tbody>		  
		</table>
		</div>
	</div>	
</form>