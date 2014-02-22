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
require_once(BASE_PATH.'includes/googlecheckoutcsvfileimporter.class.php');


?>
<form method="POST" enctype="multipart/form-data" action="?a=import&ctl=apps" class="form-horizontal" >
	<fieldset><legend>Import Google Checkout earnings report</legend>
		
		<div class="row" >
			<div class="span6" >
		
				<div class="control-group">
					<label class="control-label" for="inputEmail">Choose a CSV file</label>
				  <div class="controls">
				  	<input class="input-medium search-query" id="csvFile" name="csvFile" type="file" value="" />
				  </div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputPassword">Report type</label>
				  <div class="controls">
				  	<select name="fileType" > 
			  			<option value="<?php echo CHECKOUT_FILE_TYPE_SALES; ?>" >Sales</option>
			  			<option value="<?php echo CHECKOUT_FILE_TYPE_EARNINGS; ?>" >Earnings</option>
						</select>
				  </div>
				</div>
				<div class="control-group">
					<div class="controls" style="text-align:right;" >
				  	<button class="btn" type="submit" style="marginLeft:50px;" ><i class="icon-upload" ></i>&nbsp;Upload&nbsp;</button>		
					</div>
				</div>
					
			</div>
			
			<div class="span1" ></div>
			
			<div class="span5" >
				<span class="help-block" >
					<b>Import Info.</b>
					<p style="font-style:italic" >Each rows with the same sale order number will be automaticcaly replaced. This way you can import the daily updated sales report several times during the current month.</p>
					<p style="font-style:italic" >The file will not be saved after the import finished.</p>
				</span>
			</div>
		</div>
				
	</fieldset>
</form>
	
