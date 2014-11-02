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

use RuntimeException;

/**
 * Prototype type definition
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
class PrototypeDefinition extends DefinitionAbstract {
	
	/**
	 * Prototype
	 *
	 * @var mixed
	 */
	protected $prototype;
	
	/**
	 * Constructor
	 * 
	 * @param mixed   $prototype Prototype of service
	 */
	public function __construct($prototype) {
		$this->setCallback($prototype);
	}
	
	/**
	 * Set prototype
	 * 
	 * @param  mixed $prototype
	 * @return PrototypeDefinition
	 */
	public function setCallback($prototype) {
		$this->prototype = $prototype;
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get callback
	 * 
	 * @return Closure
	 */
	public function getCallback() {
		return $this->callback;
	}
	
	/**
	 * Set factory mode
	 * 
	 * @param  bool $factory
	 * @return DefinitionInterface
	 */
	public function setFactoryMode($factory = true) {
		throw new RuntimeException('Prototype is always factory');
	}
	
	/**
	 * Initialize service
	 * 
	 * @param  array $args
	 * @return mixed
	 */
	protected function initService(array $args = null) {
		if (is_object($this->prototype)) {
			return clone $this->prototype;
		}
		
		$copy = $this->prototype;
		return $copy;
	}
	
}