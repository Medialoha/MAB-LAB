<?php
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

$mLogFile = getcwd().'/logs/mablab.log';
$mMaxLogFileSize = 3145728;
$mSupportMailAddr = '';
$mMailSubject = 'Website log report: %s';


class Debug {
	
	public static function sendMail($message, $appendLogFile=false) {
		global $mSupportMailAddr, $mMailSubject, $mLogFile;
		
		if (empty($mSupportMailAddr)) return;
				
		if ($appendLogFile && is_readable($mLogFile)) {
			$message .= "\n\nLog file content :\n".file_get_contents($mLogFile);
		}
		
		if (!@mail($mSupportMailAddr, sprintf($mMailSubject, $_SERVER['SERVER_NAME']), $message, $header)) {
			Debug::log("ERROR", "Sending mail failed !", "Debug.class", false);
		}
	}
	
	public static function logd($message, $tag=null) { Debug::log("DEBUG", $message, $tag, false); }
	
	public static function logi($message, $tag=null) {Debug::log("INFO", $message, $tag, false); }
	
	public static function logw($message, $tag=null) { Debug::log("WARNING", $message, $tag, false); }
	
	public static function loge($message, $tag=null) { Debug::log("ERROR", $message, $tag, true); }
	
	public static function log($severity, $message, $tag=null, $sendmail=false) {
		global $mLogFile, $mMaxLogFileSize;
				
		if (!is_string($message))
			$message = print_r($message, true);
		
		if(@file_exists($mLogFile)) {
			if(@filesize($mLogFile)>$mMaxLogFileSize)
				@unlink($file);
		}
		
		// Ecriture du texte
		$inF = @fopen($mLogFile, "a");
		@chmod($mLogFile, 0777);
		
		$message = "[".date("d-m-y H:i.s")."]\t".$severity."\t".($tag!=null?$tag."\t:\t":"").$message."\n";
		@fwrite($inF, $message);
		@fclose($inF);
		
		if ($sendmail)
			Debug::sendMail($message, true);
	}
}
?>