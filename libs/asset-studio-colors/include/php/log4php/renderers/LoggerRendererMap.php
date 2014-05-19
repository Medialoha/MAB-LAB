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
 * Log objects using customized renderers that implement {@link LoggerRendererObject}.
 *
 * Example:
 * {@example ../../examples/php/renderer_map.php 19}<br>
 * {@example ../../examples/resources/renderer_map.properties 18}<br>
 * <pre>
 * DEBUG - Now comes the current MyClass object:
 * DEBUG - Doe, John
 * </pre>
 * 
 * @version $Revision: 1166187 $
 * @package log4php
 * @subpackage renderers
 * @since 0.3
 */
class LoggerRendererMap {

	/**
	 * @var array
	 */
	private $map;

	/**
	 * @var LoggerDefaultRenderer
	 */
	private $defaultRenderer;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->map = array();
		$this->defaultRenderer = new LoggerRendererDefault();
	}

	/**
	 * Add a renderer to a hierarchy passed as parameter.
	 * Note that hierarchy must implement getRendererMap() and setRenderer() methods.
	 *
	 * @param LoggerHierarchy $repository a logger repository.
	 * @param string $renderedClassName
	 * @param string $renderingClassName
	 */
	public function addRenderer($renderedClassName, $renderingClassName) {
		$renderer = LoggerReflectionUtils::createObject($renderingClassName);
		if($renderer == null) {
			return;
		} else {
			$this->put($renderedClassName, $renderer);
		}
	}


	/**
	 * Find the appropriate renderer for the class type of the
	 * <var>o</var> parameter. 
	 *
	 * This is accomplished by calling the {@link getByObject()} 
	 * method if <var>o</var> is object or using {@link LoggerRendererDefault}. 
	 * Once a renderer is found, it is applied on the object <var>o</var> and 
	 * the result is returned as a string.
	 *
	 * @param mixed $o
	 * @return string 
	 */
	public function findAndRender($o) {
		if($o == null) {
			return null;
		} else {
			if(is_object($o)) {
				$renderer = $this->getByObject($o);
				if($renderer !== null) {
					return $renderer->render($o);
				} else {
					return null;
				}
			} else {
				$renderer = $this->defaultRenderer;
				return $renderer->render($o);
			}
		}
	}

	/**
	 * Syntactic sugar method that calls {@link PHP_MANUAL#get_class} with the
	 * class of the object parameter.
	 * 
	 * @param mixed $o
	 * @return string
	 */
	public function getByObject($o) {
		return ($o == null) ? null : $this->getByClassName(get_class($o));
	}


	/**
	 * Search the parents of <var>clazz</var> for a renderer. 
	 *
	 * The renderer closest in the hierarchy will be returned. If no
	 * renderers could be found, then the default renderer is returned.
	 *
	 * @param string $class
	 * @return LoggerRendererObject
	 */
	public function getByClassName($class) {
		$r = null;
		for($c = $class; !empty($c); $c = get_parent_class($c)) {
			$c = strtolower($c);
			if(isset($this->map[$c])) {
				return $this->map[$c];
			}
		}
		return $this->defaultRenderer;
	}

	public function clear() {
		$this->map = array();
	}

	/**
	 * Register a {@link LoggerRendererObject} for <var>clazz</var>.
	 * @param string $class
	 * @param LoggerRendererObject $or
	 */
	private function put($class, $or) {
		$this->map[strtolower($class)] = $or;
	}
}
