<?php
/**
 * ZExt Framework (http://z-ext.com)
 * Copyright (C) 2012 Mike.Mirten
 * 
 * LICENSE
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * @copyright (c) 2012, Mike.Mirten
 * @license   http://www.gnu.org/licenses/gpl.html GPL License
 * @category  ZExt
 * @version   1.0
 */

namespace ZExt\Di\Definition;

/**
 * Callback type definition
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
class CallbackDefinition extends DefinitionAbstract {
	
	/**
	 * Callback
	 *
	 * @var \Closure 
	 */
	protected $callback;
	
	/**
	 * Constructor
	 * 
	 * @param \Closure $callback Callback of service init
	 * @param mixed   $args     Arguments for constructor of service
	 */
	public function __construct($callback, $args = null) {
		$this->setCallback($callback);
		
		if ($args !== null) {
			$this->setArguments($args);
		}
	}
	
	/**
	 * Set callback
	 * 
	 * @param  \Closure $callback
	 * @return CallbackDefinition
	 */
	public function setCallback($callback) {
		$this->callback = $callback;
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get callback
	 * 
	 * @return \Closure
	 */
	public function getCallback() {
		return $this->callback;
	}
	
	/**
	 * Initialize service
	 * 
	 * @param  array $args
	 * @return mixed
	 */
	protected function initService(array $args = null) {
		if ($args === null) {
			return call_user_func($this->callback);
		}
		
		return call_user_func_array($this->callback, $args);
	}
	
}