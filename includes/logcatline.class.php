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
 *     MAREK MASLANKA - Logintar - added review reports by the same errors and formatting logcat
 */

class LogCatLine
{
	public $time;
	public $date;
	public $type;
	public $tag;
	public $pid;
	public $message;
	private $valid;
	
	public function parse($string)
	{
		$this->valid = preg_match('/(?P<time>[\d\\-]+) (?P<date>[\d.:]+) (?P<type>\w)\/(?P<tag>.+)[ ]*\([ ]*(?P<pid>[\d]+)\): (?P<msg>.*)/', $string, $matches);
		if($this->valid)
		{
			$this->time = $matches['time'];
			$this->date = $matches['date'];
			$this->type = $matches['type'];
			$this->tag = $matches['tag'];
			$this->pid = $matches['pid'];
			$this->message = $matches['msg'];
		}
		else
		{
			$this->message = $string;
		}
	}
	
	public function isValid()
	{
		return $this->valid;
	}
}

?>