<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 *	   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package log4php
 */

/**
 * ConsoleAppender appends log events to STDOUT or STDERR. 
 * 
 * <p><b>Note</b>: Use this Appender with command-line php scripts. 
 * On web scripts this appender has no effects.</p>
 *
 * Configurable parameters of this appender are:
 *
 * - layout     - The layout (required)
 * - target     - "stdout" or "stderr"
 * 
 * An example php file:
 * 
 * {@example ../../examples/php/appender_console.php 19}
 * 
 * An example configuration file:
 * 
 * {@example ../../examples/resources/appender_console.properties 18}
 * 
 * @version $Revision: 1213283 $
 * @package log4php
 * @subpackage appenders
 */
class LoggerAppenderConsole extends LoggerAppender {

	const STDOUT = 'php://stdout';
	const STDERR = 'php://stderr';

	/**
	 * Can be 'php://stdout' or 'php://stderr'. But it's better to use keywords <b>STDOUT</b> and <b>STDERR</b> (case insensitive). 
	 * Default is STDOUT
	 * @var string
	 */
	protected $target = self::STDOUT;
	
	/**
	 * @var mixed the resource used to open stdout/stderr
	 */
	protected $fp = null;

	/**
	 * Set console target.
	 * @param mixed $value a constant or a string
	 */
	public function setTarget($value) {
		$v = trim($value);
		if ($v == self::STDOUT || strtoupper($v) == 'STDOUT') {
			$this->target = self::STDOUT;
		} elseif ($v == self::STDERR || strtoupper($v) == 'STDERR') {
			$this->target = self::STDERR;
		} else {
			$value = var_export($value);
			$this->warn("Invalid value given for 'target' property: [$value]. Property not set.");
		}
	}

	public function getTarget() {
		return $this->target;
	}

	public function activateOptions() {
		$this->fp = fopen($this->target, 'w');
		if(is_resource($this->fp) && $this->layout !== null) {
			fwrite($this->fp, $this->layout->getHeader());
		}
		$this->closed = (bool)is_resource($this->fp) === false;
	}
	
	public function close() {
		if($this->closed != true) {
			if (is_resource($this->fp) && $this->layout !== null) {
				fwrite($this->fp, $this->layout->getFooter());
				fclose($this->fp);
			}
			$this->closed = true;
		}
	}

	public function append(LoggerLoggingEvent $event) {
		if (is_resource($this->fp) && $this->layout !== null) {
			fwrite($this->fp, $this->layout->format($event));
		}
	}
}

