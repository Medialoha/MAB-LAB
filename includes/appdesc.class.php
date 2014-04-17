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

define('APP_DESC_DIR', 'pubfiles/');
define('APP_DESC_TEMPLATES_DIR', 'pubfiles/_templates/');

define('XML_ATTR_LANGUAGE', 'lang');
define('XML_ATTR_LANGUAGE_CODE', 'code');
define('XML_ATTR_DEFAULT', 'default');


class ApplicationDesc {

	private $mAppPackage;								// application package, if empty then templates dir is used
	
	private $mAvailableTranslations;		// available translations for application package
	private $mAvailableTemplates;				// available translations in templates directory
	
	private $mLoadedTranslationIsTmpl;
	private $mLoadedTranslationFile;		// file path without extension
	private $mLoadedTranslationHTML; 		// template
	private $mLoadedTranslationXML;			// properties
	
	
	// CONSTRUCTOR
	// if $appPackage is empty then translation will be loaded from templates directory
	public function ApplicationDesc($appPackage) {
		$this->mAppPackage = $appPackage;
		$this->mAvailableTranslations = null;
		$this->mAvailableTemplates = null;
		
		$this->mLoadedTranslationFile = null;
		$this->mLoadedTranslationHTML = null;
		$this->mLoadedTranslationXML = null;

		$this->getAvailableTemplates();
		$this->getAvailableTranslations();
	}


	public function loadTranslation($code) {
		$this->mLoadedTranslationIsTmpl = empty($this->mAppPackage);
		
		$dir = $this->checkTranslationExists($code, $this->mLoadedTranslationIsTmpl);
			
		if ($dir!=null) {
			$this->mLoadedTranslationFile = $dir.'/'.str_replace('.html', '', ($this->mLoadedTranslationIsTmpl?$this->mAvailableTemplates[$code]['file']:$this->mAvailableTranslations[$code]['file']));
			
			$this->mLoadedTranslationHTML = file($this->mLoadedTranslationFile.'.html', FILE_IGNORE_NEW_LINES);
			$this->mLoadedTranslationXML = new SimpleXMLElement(file_get_contents($this->mLoadedTranslationFile.'.xml'));
			
		//	Debug::logd($this->mLoadedTranslationXML);
			
			return true;
		}
		
		return false;
	}
	
	public function reloadTranslation() {
		if (!empty($this->mLoadedTranslationFile)) {
			$this->mLoadedTranslationHTML = file($this->mLoadedTranslationFile.'.html');
			$this->mLoadedTranslationXML = new SimpleXMLElement(file_get_contents($this->mLoadedTranslationFile.'.xml'));			
		}
	}
	
	public function delTranslation($code) {
		$dir = $this->ensureAppTranslationsDir();
		if ($dir==null) {
			Debug::loge("Unable to access app directory ".$dir, "APP DESC");
			return false;
		}
		
		if ($dh=opendir($dir)) {
			while (($file=readdir($dh)) !== false) {
				if (in_array($file, array('.', '..')))
					continue;
				
				if (strpos($file, $code)!==false)
					unlink($dir.'/'.$file);
				
			}
			
			closedir($dh);
		}
		
		return true;
	}
	
	/**
	 * Add a new translation from templates to application dir (package)
	 * 
	 * @param string $code
	 * @return boolean
	 */
	public function addTranslation($code) {
		$mTmpls = $this->getAvailableTemplates();
		
		if (isset($mTmpls[$code])) {
			$dir = $this->ensureAppTranslationsDir();
			if ($dir==null) {
				Debug::loge("Unable to access app directory ".$dir, "APP DESC");
				return false;
			}
				
			if (!copy(APP_DESC_TEMPLATES_DIR.$mTmpls[$code]['file'], $dir.'/'.$mTmpls[$code]['file']))
				return false;
			
			if (!copy(str_replace('.html', '.xml', APP_DESC_TEMPLATES_DIR.$mTmpls[$code]['file']), str_replace('.html', '.xml', $dir.'/'.$mTmpls[$code]['file']))) {
				// remove html file
				unlink($dir.'/'.$mTmpls[$code]['file']);
				
				return false;
			}
		}
		
		return true;
	}
	
	public function getAvailableTranslations() {
		if (!is_array($this->mAvailableTranslations)) {
			
			$dir = $this->ensureAppTranslationsDir();
			if ($dir==null) {
				$this->mAvailableTranslations = null;

			} else {
				$this->mAvailableTranslations = array();
		
				if ($dh=opendir($dir)) {
					while (($file=readdir($dh)) !== false) {
						if (in_array($file, array('.', '..')))
							continue;
							
						list($filename, $code, $type) = explode('.', $file);
						if (strcmp($type, 'html')==0) {
							$this->mAvailableTranslations[$code] = array('name'=>'['.$code.'] XML description file not found',	'file'=>$file);

							$xmlFile = $dir.'/'.$filename.'.'.$code.'.xml';
								
							try {
								// check if file is readable
								if (!is_readable($xmlFile)) {
									Debug::loge("File is not readable ".$xmlFile, "APP DESC");
							
									continue;
								}
							
								$name = $this->getTranslationNameFromPropFile($xmlFile);
								if ($name!=null)
									$this->mAvailableTranslations[$code]['name'] = $name;
							
							} catch (Exception $e) { Debug::loge($e->getMessage(), 'APP DESC');	}
						}
					}
						
					closedir($dh);
				}
			}
		}

		return $this->mAvailableTranslations;
	}
	
	public function getAvailableTemplates() {
		if (!is_array($this->mAvailableTemplates)) {

			$dir = $this->ensureTemplatesDir();
			if ($dir==null) {
				$this->mAvailableTemplates = null;
				
			} else {
				$this->mAvailableTemplates = array();
				
				if ($dh=opendir($dir)) {
					while (($file=readdir($dh)) !== false) {
						if (in_array($file, array('.', '..')))
							continue;
						
					//	Debug::logd('Current template file '.$dir.$file, 'APP DESC');
							
						list($filename, $code, $type) = explode('.', $file);
						if (strcmp($type, 'html')==0) {
							$this->mAvailableTemplates[$code] = array('name'=>'['.$code.'] XML description file not found',	'file'=>$file);
							
							$xmlFile = $dir.$filename.'.'.$code.'.xml';
							
							try {					
								// check if file is readable			
								if (!is_readable($xmlFile)) {
									Debug::loge("File is not readable ".$xmlFile, "APP DESC");

									continue;
								}
								
								$name = $this->getTranslationNameFromPropFile($xmlFile);
								if ($name!=null)
									$this->mAvailableTemplates[$code]['name'] = $name;
								
							} catch (Exception $e) { Debug::loge($e->getMessage(), 'APP DESC');	}
						}
					}
				
					closedir($dh);
				}
			}
		}		

		return $this->mAvailableTemplates;
	}
	
	/**
	 * type : text, list, input-mini, input-small, input-medium, input-large, input-xlarge, input-xxlarge
	 * 
	 * return Array of SimpleXMLElement Object : 
	 * Array ( [0] => SimpleXMLElement Object ( [key] => app_name, [type] => text, [desc] => Application name [value] => My Favorite App ),
						 [1] => SimpleXMLElement Object ( ... 
           )
	 */
	public function getProperties() {
		if ($this->mLoadedTranslationXML!=null && isset($this->mLoadedTranslationXML->properties))
			return $this->mLoadedTranslationXML->properties->property;
		
		return array();
	}
	
	public function setProperty($key, $value) {
		if ($this->mLoadedTranslationXML!=null && isset($this->mLoadedTranslationXML->properties)) {			
			$properties =& $this->mLoadedTranslationXML->properties->property;
			$idx = -1; $newValue = "";
			
			foreach ($properties as $i=>$p) {						
				if (strcmp($p->key, $key)==0) {
					switch ($p->type) {
						case 'list' : $newValue = str_replace("\n", "|", $value);
							break;

						default : $newValue = $value;
					}
						
					$p->value = $newValue;
					
					break; 
				}
			}
		}
	}
	
	public function getTranslationName() {
		if ($this->mLoadedTranslationXML!=null) {
			$attrs = $this->mLoadedTranslationXML->attributes();
			if (isset($attrs) && isset($attrs[XML_ATTR_LANGUAGE]))
				return "".$attrs[XML_ATTR_LANGUAGE]; // force string convertion
		}
		
		return null;
	}
	
	public function commit() {
		if ($this->mLoadedTranslationFile!=null) {
			
			if ($this->mLoadedTranslationIsTmpl) {
				$dir = $this->ensureTemplatesDir();
				
			} else { $dir = $this->ensureAppTranslationsDir(); }
			
			if ($dir!=null) {
				file_put_contents($this->mLoadedTranslationFile.'.html', implode(PHP_EOL, $this->mLoadedTranslationHTML));
				
				$this->mLoadedTranslationXML->asXml($this->mLoadedTranslationFile.'.xml');
			}
		}
	}
	
	public function buildTranslatedDescription($edit=false) {
		$html = '<p class="muted text-i" style="padding:10px;" >Translated description is not available...</p>';
		
		if ($this->mLoadedTranslationXML!=null && $this->mLoadedTranslationHTML!=null) {
			$html = $this->mLoadedTranslationHTML; 			
			$html = implode($edit?PHP_EOL:'<br/>', $html);
						
			$properties = $this->getProperties();
			
			foreach ($properties as $i=>$p) {
				$key = '{'.strtoupper($p->key).'}'; 
				$s = '';
				
				if ($edit) {
					$html = str_replace($key, '{'.strtoupper($p->key).':'.$p->type.':'.$p->desc.'}', $html);
										
				} else {
					switch ($p->type) {
						case 'list' :
								$items = explode("|", $p->value);
								foreach ($items as $text) {
									if ($text=="") continue;
									
									$s .= ' - '.$text."<br/>"; 
								}
							
							break;
						case 'text' :
								if (!$edit) {
									$s = str_replace(PHP_EOL, '<br/>', $p->value);
									
								} else { $s = $p->value; }
							break;
				
						default : $s = $p->value;
					}
						
					$html = str_replace($key, '<span class="text-info">'.$s.'</span>', $html);
				}
			}
		}
		
		return $html;
	}
	
	public function setTranslationTemplate($html) {
		if ($this->mLoadedTranslationFile!=null) {
			$this->mLoadedTranslationHTML = !is_array($html)?explode(PHP_EOL, $html):$html;
			
		} else { Debug::loge('No translation loaded to upadte...', 'APP DESC'); }
	}
	
	public function setTranslationProperties($xml) {
		if ($this->mLoadedTranslationFile!=null) {
			$this->mLoadedTranslationXML = $xml;
			
		} else { Debug::loge('No translation loaded to upadte...', 'APP DESC'); }
	}
	
	public function createEmptyTranslation($code, $name) {
		if (!empty($code)) {
			$this->mLoadedTranslationIsTmpl = empty($this->mAppPackage);
			
			$this->mLoadedTranslationFile = ($this->mLoadedTranslationIsTmpl?APP_DESC_TEMPLATES_DIR:APP_DESC_DIR.$this->mPackage).'/description.'.$code;
			$this->mLoadedTranslationHTML = array();
			$this->mLoadedTranslationXML = new SimpleXMLElement('<description></description>');
			$this->mLoadedTranslationXML->addAttribute(XML_ATTR_LANGUAGE, $name);
			$this->mLoadedTranslationXML->addAttribute(XML_ATTR_LANGUAGE_CODE, $code);
			$this->mLoadedTranslationXML->addAttribute(XML_ATTR_DEFAULT, "false");
		
			$this->commit();
			
			return true;
		}
		
		return false;
	}
	
	
	private function getTranslationNameFromPropFile($xmlFile) {
		$content = file_get_contents($xmlFile);
		// Debug::logd(htmlspecialchars($content), 'CONTENT');
		
		$xml = new SimpleXMLElement($content);
		$attrs = $xml->attributes();
		if (isset($attrs) && isset($attrs[XML_ATTR_LANGUAGE]))
			return "".$attrs[XML_ATTR_LANGUAGE]; // force string convertion
		
		return null;
	}
	
	private function ensureAppTranslationsDir() {
		if (empty($this->mAppPackage)) {
			Debug::loge("Package name is empty !", "APP DESC");
			return null;
		}
		
		return self::ensureDir(APP_DESC_DIR.$this->mAppPackage);
	}
	
	private function ensureTemplatesDir() {
		
		return self::ensureDir(APP_DESC_TEMPLATES_DIR);
	}
	
	private function ensureDir($dir) {
		// if dir does not extists then create it first
		if (!file_exists($dir)) {
			if (!mkdir($dir, 0770)) {
				Debug::loge("Unable to create directory ".$dir, "APP DESC");
				return null;
			}
		}
		
		if (!is_dir($dir)) {
			Debug::loge($dir.' is not a directory !', "APP DESC");
			return null;
		}
		
		// check directory access
		if (!is_writeable($dir)) {
			Debug::logw($dir.' is not writeable, try changing perms...', "APP DESC");
			if (!chmod($dir, 0770)) {
				Debug::loge('chmod failed on '.$dir, "APP DESC");
				return null;
			}
		}
		
		return $dir;
	}
	
	private function checkTranslationExists($code, $tmplDir=false) {
		$dir = null;
		
		if ($tmplDir) {
			if (isset($this->mAvailableTemplates[$code]))
				$dir = APP_DESC_TEMPLATES_DIR;
		
		} else {
			if (isset($this->mAvailableTranslations[$code]))
				$dir = APP_DESC_DIR.$this->mAppPackage;
		}
		
		return $dir;
	}
	
}