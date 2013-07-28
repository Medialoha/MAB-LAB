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

require_once(BASE_PATH.'libs/PHPMailer/class.phpmailer.php');


class MailHelper {
	
	public static function sendMail($recipients, $subject, $content) {
		$cfg = CfgHelper::getInstance();
		$mail = new PHPMailer;
		
		// recipients
		if (is_array($recipients)) {
			foreach($recipients as $r)
				$mail->AddAddress($r);
			
		} else if (is_string($recipients)) {
			
		} else {
			Debug::loge('Wrong recipients parameter ! Should be an array or a string...', 'MailHelper'); 
			return; 
		}
				
		// sender
		$mail->From = $cfg->getMailFromAddr();
		$mail->FromName = $cfg->getMailFromName();
		
		$mail->IsHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = $subject;
		$mail->Body = $content;
		
		if(!$mail->Send()) {
			Debug::loge('Message could not be sent. Mailer Error: '.$mail->ErrorInfo, 'MailHelper');
		}
	}

}