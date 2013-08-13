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

namespace ZExt\Session\Phalcon;

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait,
    ZExt\Di\LocatorInterface;

use ZExt\Session\NamespaceFactoryInterface;
use Phalcon\Session\Bag;

/**
 * Phalcon Bag based namespaces' factory
 * 
 * @category   ZExt
 * @package    Session
 * @subpackage Factory
 * @author     Mike.Mirten
 * @version    1.0
 */
class NamespaceFactory implements NamespaceFactoryInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	
	/**
	 * Instanced namespaces
	 *
	 * @var Bag[]
	 */
	protected $_namespaces = [];
	
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
	 * Create the session's namespace
	 * 
	 * @param  string $name
	 * @return Bag
	 */
	public function createNamespace($name) {
		if (isset($this->_namespaces[$name])) {
			return $this->_namespaces[$name];
		}
		
		$namespace = new Bag($name);
		
		if ($this->hasLocator()) {
			$namespace->setDi($this->getLocator());
		}
		
		$this->_namespaces[$name] = $namespace;
		return $namespace;
	}
	
	/**
	 * Create the session's namespace over an undefined property
	 * 
	 * @param  string $name
	 * @return Bag
	 */
	public function __get($name) {
		return $this->createNamespace($name);
	}
	
	/**
	 * Create the session's namespace over factory treat as a function
	 * 
	 * @param  string $name
	 * @return Bag
	 */
	public function __invoke($name) {
		return $this->createNamespace($name);
	}

}