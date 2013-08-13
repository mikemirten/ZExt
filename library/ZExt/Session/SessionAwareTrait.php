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

namespace ZExt\Session;

use ZExt\Di\LocatorAwareInterface;
use ZExt\Session\Exceptions\NoNamespacesFactory;

/**
 * Namespaces' aware trait
 * 
 * @category   ZExt
 * @package    Session
 * @subpackage Session
 * @author     Mike.Mirten
 * @version    1.0
 */
trait SessionAwareTrait {
	
	/**
	 * Namespaces' factory
	 * 
	 * @var NamespaceFactoryInterface 
	 */
	private $_namespacesFactory;
	
	/**
	 * Instanced namespaces
	 *
	 * @var array
	 */
	private $_namespacesInstances = [];
	
	/**
	 * Get the namespace of the session
	 * 
	 * @param  string $name
	 * @return object
	 */
	protected function getSessionNamespace($name = null) {
		if ($name === null) {
			$name = str_replace('\\', '_', get_class($this));
		}
		
		if (isset($this->_namespacesInstances[$name])) {
			return $this->_namespacesInstances[$name];
		}
		
		$namespace = $this->getNamespaceFactory()->createNamespace($name);
		
		$this->_namespacesInstances[$name] = $namespace;
		return $namespace;
	}
	
	/**
	 * Get the name of the factory service id
	 * 
	 * @return string
	 */
	protected function getSessionNamespaceFactoryServiceId() {
		return 'sessionNamespaceFactory';
	}
	
	/**
	 * Set the namespaces' factory
	 * 
	 * @param NamespaceFactoryInterface $factory
	 */
	public function setNamespaceFactory(NamespaceFactoryInterface $factory) {
		$this->_namespacesFactory = $factory;
	}
	
	/**
	 * Get the namespaces factory
	 * 
	 * @return NamespaceFactoryInterface
	 */
	public function getNamespaceFactory() {
		if ($this->_namespacesFactory === null) {
			if ($this instanceof LocatorAwareInterface && $this->hasLocator()) {
				$this->_namespacesFactory = $this->getLocator()->get($this->getSessionNamespaceFactoryServiceId());
				
				if (! $this->_namespacesFactory instanceof NamespaceFactoryInterface) {
					throw new NoNamespacesFactory('Namespace\'s factory must implement the "HelpersLocatorInterface"');
				}
			} else {
				throw new NoNamespacesFactory('No namespace\'s factory been supplied, also unable to obtain one through locator');
			}
		}
		
		return $this->_namespacesFactory;
	}
	
	/**
	 * Has the namespaces factory
	 * 
	 * @return bool
	 */
	public function hasNamespaceFactory() {
		if ($this->_namespacesFactory !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface && $this->hasLocator()
			&& $this->getLocator()->has($this->getSessionNamespaceFactoryServiceId())) {
			return true;
		}
		
		return false;
	}
	
}