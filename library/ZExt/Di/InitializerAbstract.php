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

namespace ZExt\Di;

/**
 * Abstract services' initializer
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Initializer
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class InitializerAbstract implements InitializerInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	
	const INIT_METHOD_POSTFIX = 'Init';
	
	/**
	 * Local cache of an initialized services
	 *
	 * @var array 
	 */
	private $_services;
	
	/**
	 * Constructor
	 * 
	 * @param LocatorInterface $locator
	 */
	public function __construct(LocatorInterface $locator = null) {
		if ($locator !== null) {
			$this->setLocator($locator);
		}
	}
	
	/**
	 * Get a service
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	protected function get($id) {
		$service = $this->initialize($id);
		
		if ($service !== null) {
			return $service;
		}
		
		if ($this->_locator !== null) {
			return $this->_locator->get($id, LocatorInterface::BEHAVIOUR_FAIL_NULL);
		}
	}
	
	/**
	 * Has a service
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	protected function has($id) {
		if ($this->isAvailable($id)) {
			return true;
		}
		
		if ($this->_locator !== null) {
			return $this->_locator->has($id);
		}
	}
	
	/**
	 * Check for a service has been initialized
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	protected function hasInitialized($id) {
		if (isset($this->_services[$id])) {
			return true;
		}
		
		if ($this->_locator !== null) {
			return $this->_locator->hasInitialized($id);
		}
	}
	
	/**
	 * Initialize a service
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function initialize($id) {
		if (isset($this->_services[$id])) {
			return $this->_services[$id];
		}
		
		$method = lcfirst($id) . self::INIT_METHOD_POSTFIX;
		
		if (method_exists($this, $method)) {
			$service = $this->$method();
			
			$this->_services[$id] = $service;
			return $service;
		}
	}
	
	/**
	 * Is service available
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	public function isAvailable($id) {
		if (isset($this->_services[$id])) {
			return true;
		}
		
		return method_exists($this, lcfirst($id) . self::INIT_METHOD_POSTFIX);
	}
	
	public function __get($name) {
		return $this->get($name);
	}
	
	public function __isset($name) {
		return $this->has($name);
	}
	
}