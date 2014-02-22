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

$mLogFile = BASE_PATH.'logs/mablab.log';
$mMaxLogFileSize = 3145728;
$mSupportMailAddr = '';
$mMailSubject = 'Website log report: %s';

define('MAX_DB_LOGS_DISPLAYED', 50);

define('DEBUG', 0);
define('INFO', 1);
define('WARNING', 2);
define('ERROR', 3);


class Debug {
	
	// CONSTRUCTOR
	public function Debug() {}
	
	public static function isDebugEnabled() { return (LOG_SEVERITY==DEBUG); }
	
	public static function sendMail($message, $appendLogFile=false) {
		global $mSupportMailAddr, $mMailSubject, $mLogFile;
		
		if (empty($mSupportMailAddr)) return;
				
		if ($appendLogFile && is_readable($mLogFile)) {
			$message .= "\n\nLog file content :\n".file_get_contents($mLogFile);
		}
		
		if (!@mail($mSupportMailAddr, sprintf($mMailSubject, $_SERVER['SERVER_NAME']), $message, $header)) {
			Debug::log(ERROR, "Sending mail failed !", "Debug.class", false);
		}
	}
	
	public static function logd($message, $tag=null) { Debug::log(DEBUG, $message, $tag, false); }
	
	public static function logi($message, $tag=null) {Debug::log(INFO, $message, $tag, false); }
	
	public static function logw($message, $tag=null) { Debug::log(WARNING, $message, $tag, false); }
	
	public static function loge($message, $tag=null) { Debug::log(ERROR, $message, $tag, true); }
	
	public static function log($severity, $message, $tag=null, $sendmail=false) {
		global $mLogFile, $mMaxLogFileSize;

		if ($severity<LOG_SEVERITY) return;
		
		switch ($severity) {
			case DEBUG : $severityStr = 'DEBUG';
				break;
			case INFO : $severityStr = 'INFO';
				break;
			case WARNING : $severityStr = 'WARNING';
				break;
			case ERROR : $severityStr = 'ERROR';
				break;
				
			default : $severityStr = ' - ';
		}
		
		if (!is_string($message))
			$message = print_r($message, true);
		
		if(@file_exists($mLogFile)) {
			if(@filesize($mLogFile)>$mMaxLogFileSize)
				@unlink($mLogFile);
		}
		
		// Ecriture du texte
		$inF = @fopen($mLogFile, "a");
		@chmod($mLogFile, 0777);
		
		$message = "[".date("d-m-y H:i.s")."]\t".$severityStr."\t".($tag!=null?$tag:" ")."\t:\t".$message."\n";
		@fwrite($inF, $message);
		@fclose($inF);
		
		if ($sendmail)
			Debug::sendMail($message, true);
	}
	
	
	public function clearLogFile() {
		global $mLogFile;
		
		if(@file_exists($mLogFile)) {
				@unlink($mLogFile);
		}
	}
	
	public function getFormattedLogs() {
		global $mLogFile;
		
		$html = ''; $fileExists = true;
		
		if (!file_exists($mLogFile)) {
			$html = '<p><i class="icon-thumbs-up" ></i>&nbsp;Log file is empty.</p>';
			$fileExists = false;
			
		} else if (!is_writable($mLogFile)) {
			$html = '<p class="text-error" ><i class="icon-warning-sign" ></i>&nbsp;Log file <i>'.$mLogFile.'</i> is not writeable !</p>';
		}
		
		if(is_readable($mLogFile)) {
			$size = filesize($mLogFile);
			
			if ($size==0) {
				$html .= '<p><i class="icon-thumbs-up" ></i>&nbsp;Log file is empty.</p>';
				
			} else {
				$html .= '<p><b>Total file size '.$this->formatFileSize($size);
				
				if ($size>10240) {
					$content = file_get_contents($mLogFile, false, null, $size-10240, 10240);
					$html .= ', only last '.$this->formatFileSize(10240).' displayed ...';
					
				} else { $content = file_get_contents($mLogFile); }
				
				$html .= '</b></p><table class="table table-condensed table-hover logcat-tbl" >';
				
				$content = explode("\n", $content); $TROpened = false;
				foreach ($content as $row) {
					$cols = explode("\t", $row);
					
					// if one col then sub row
					if (sizeOf($cols)==1) {
						if ($TROpened==false) {
							$html .= '<td colspan="4" >';
							$TROpened = true;
						}
						
						$html .= str_replace(" ", "&nbsp;", $row).'<br/>';
						
					} else {
						if ($TROpened) {
							$html .= '</td></tr>';
							$TROpened = false; 
						}
						
						switch ($cols[1]) {
							case 'INFO' : $severity = 'I'; $label = '<span class="label label-priority-info" >I</span>';
								break;
							case 'WARNING' : $severity = 'W'; $label = '<span class="label label-priority-warning" >W</span>';
								break;
							case 'ERROR' : $severity = 'E'; $label = '<span class="label label-priority-error" >E</span>';
								break;
								

							default : $severity = 'D'; $label = '<span class="label label-priority-debug" >D</span>';
						}

						$html .= '<tr class="'.$severity.'" >';
						foreach ($cols as $colIdx=>$value) {
							if ($colIdx==1) {
								$html .= '<td>'.$label.'</td>';
								
							} else if ($colIdx==3) {
								continue; 
							
							} else { $html .= '<td>'.$value.'</td>'; }
						}
						$html .= "</tr>";
					}
				}
				
				$html .= "</table>";
			}
			
		} else { if ($fileExists) $html .= '<p class="text-error" ><i class="icon-warning-sign" ></i>&nbsp;Log file <i>'.$mLogFile.'</i> is not readable !</p>'; }
				
		return $html;
	}
	
	public function getFormattedDBLogs() {
		$logs = DBHelper::fetchLastLogs($total, MAX_DB_LOGS_DISPLAYED);
		
		$html = '<p><b>Total logs '.$total.($total>MAX_DB_LOGS_DISPLAYED?', only last '.MAX_DB_LOGS_DISPLAYED.' rows displayed ...':'').'</b></p>'.
						'<table class="table table-condensed table-hover logcat-tbl" >';
		
		if (!empty($logs)) {
			foreach ($logs as $log) {
				switch ($log[2]) {
					case 'D' : $label = '<span class="label label-priority-debug" >D</span>';
						break;
					case 'I' : $label = '<span class="label label-priority-info" >I</span>';
						break;
					case 'W' : $label = '<span class="label label-priority-warning" >W</span>';
						break;
					case 'E' : $label = '<span class="label label-priority-error" >E</span>';
						break;
				}
				
				$html .= '<tr class="'.$log[2].'" ><td>'.$log[1].'</td><td>'.$label.'</td><td>'.$log[3].'</td><td>'.$log[4].'</td></tr>';				
			}
			
		} else { $html .= '<p><i class="icon-thumbs-up" ></i>&nbsp;No logs found.</p>'; }
			
		return $html.'</table>';
	} 
	
	public function formatFileSize($bytes) {
		$bytes = floatval($bytes);
		$units = array(array("TB", 1099511627776), array("GB", 1073741824), array("MB", 1048576), array("KB", 1024), array("B", 1));
	
		foreach($units as $u) {
			if($bytes>=$u[1]) {
				return str_replace(".", "," , strval(round($bytes/$u[1], 2)))." ".$u[0];
			}
		}
		
		return $bytes;
	}
}
?>