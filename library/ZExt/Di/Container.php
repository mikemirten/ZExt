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

use ZExt\Di\Exception\NoService,
    ZExt\Di\Exception\ServiceExists,
    ZExt\Di\Exception\ServiceType,
    ZExt\Di\Exception\InitializerExists,
    ZExt\Di\Exception\LocatorExists;

use Closure;

/**
 * Dependency injection service container
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Container
 * @author     Mike.Mirten
 * @version    1.1.1
 */
class Container implements ContainerInterface {
	
	/**
	 * Classes of a services
	 *
	 * @var string[]
	 */
	protected $_classes = [];
	
	/**
	 * Callbacks
	 *
	 * @var Closure[] 
	 */
	protected $_callbacks = [];
	
	/**
	 * Initializers
	 *
	 * @var InitializerInterface[] 
	 */
	protected $_initializers = [];
	
	/**
	 * Initialized services
	 *
	 * @var array
	 */
	protected $_services = [];
	
	/**
	 * Aliases of a services' id's
	 *
	 * @var string[]
	 */
	protected $_aliases = [];
	
	/**
	 * Services locators
	 *
	 * @var LocatorInterface[]
	 */
	protected $_locators = [];
	
	/**
	 * On class init callback, calls after a class instantiation
	 *
	 * @var Closure 
	 */
	protected $_onClassInit;
	
	/**
	 * Register a resources' initializer
	 * 
	 * @param  InitializerInterface $initializer
	 * @param  string $id
	 * @param  int    $existsBehaviour
	 * @return Container
	 * @throws InitializerExists
	 */
	public function registerInitializer(InitializerInterface $initializer, $id = null, $existsBehaviour = self::BEHAVIOUR_EXISTS_EXCEPTION) {
		if ($id === null) {
			$id = $this->_createIdByObject($initializer);
		}
		
		if (isset($this->_initializers[$id]) && $existsBehaviour === self::BEHAVIOUR_EXISTS_EXCEPTION) {
			throw new InitializerExists('Initializer with id: "' . $id . '" already registered');
		}
		
		if ($initializer instanceof LocatorAwareInterface && ! $initializer->hasLocator()) {
			$initializer->setLocator($this);
		}
		
		$this->_initializers[$id] = $initializer;
		
		return $this;
	}
	
	/**
	 * Register a chained services' locator
	 * 
	 * @param  LocatorAwareInterface $locator
	 * @param  string $id
	 * @param  int    $existsBehaviour
	 * @return Container
	 * @throws LocatorExists
	 */
	public function registerLocator(LocatorInterface $locator, $id = null, $existsBehaviour = self::BEHAVIOUR_EXISTS_EXCEPTION) {
		if ($id === null) {
			$id = $this->_createIdByObject($locator);
		}
		
		if (isset($this->_locators[$id]) && $existsBehaviour === self::BEHAVIOUR_EXISTS_EXCEPTION) {
			throw new LocatorExists();
		}
		
		$this->_locators[$id] = $locator;
		
		return $this;
	}
	
	/**
	 * Create string id by passed object
	 * 
	 * @param  object $object
	 * @return string
	 */
	protected function _createIdByObject($object) {
		$id = preg_replace('/[^0-9a-z]+/i', ' ', get_class($object));
		return str_replace(' ', '', ucwords($id)) . '_' . spl_object_hash($object);
	}
	
	/**
	 * Set callback on a class init
	 * 
	 * @param  Closure $callback
	 * @return Container
	 */
	public function setOnClassInit(Closure $callback) {
		$this->_onClassInit = $callback;
		
		return $this;
	}
	
	/**
	 * Set an alias to a service
	 * 
	 * @param  string $alias
	 * @param  string $id
	 * @return Container
	 */
	public function setAlias($alias, $id) {
		$this->_aliases[$alias] = $id;
		
		return $this;
	}
	
	/**
	 * Set an aliases to a services (Overwrites the existing aliases !)
	 * 
	 * @param  array $aliases
	 * @return Container
	 */
	public function setAliases(array $aliases) {
		$this->_aliases = $aliases;
		
		return $this;
	}
	
	/**
	 * Set a service
	 * 
	 * @param  string $id
	 * @param  mixed  $service
	 * @param  int    $existsBehaviour
	 * @return Container
	 * @throws ServiceExists
	 * @throws ServiceType
	 */
	public function set($id, $service, $existsBehaviour = self::BEHAVIOUR_EXISTS_EXCEPTION) {
		if ($existsBehaviour === self::BEHAVIOUR_EXISTS_EXCEPTION && $this->has($id)) {
			throw new ServiceExists('Service "' . $id . '" already registred');
		}
		
		if (is_string($service)) {
			$this->_classes[$id] = $service;
		} else if (is_callable($service)) {
			$this->_callbacks[$id] = $service;
		} else if (is_object($service) || is_array($service)) {
			$this->_services[$id] = $service;
		} else {
			throw new ServiceType('Uncnown the type "' . gettype($service) . '" of the service: "' . $id);
		}
		
		return $this;
	}
	
	/**
	 * Get a service
	 * 
	 * @param  string $id
	 * @param  int    $failBehaviour
	 * @param  bool   $recursively
	 * @return mixed
	 * @throws NoService
	 */
	public function get($id, $failBehaviour = self::BEHAVIOUR_FAIL_EXCEPTION, $recursively = true) {
		if (isset($this->_aliases[$id])) {
			$id = $this->_aliases[$id];
		}
		
		if (isset($this->_services[$id])) {
			return $this->_services[$id];
		}
		
		// Trying the class
		if (isset($this->_classes[$id])) {
			$service = new $this->_classes[$id]();
			
			if ($this->_onClassInit !== null) {
				$this->_onClassInit->__invoke($service);
			}
			
			$this->_services[$id] = $service;
			return $this->_services[$id];
		}
		
		// Trying the callback
		if (isset($this->_callbacks[$id])) {
			$this->_services[$id] = $this->_callbacks[$id]();
			
			return $this->_services[$id];
		}
		
		// Trying the initializers
		if (! empty($this->_initializers)) {
			foreach ($this->_initializers as $initializer) {
				$service = $initializer->initialize($id);

				if ($service !== null) {
					$this->_services[$id] = $service;
					return $service;
				}
			}
		}
		
		// Trying the chained locators
		if ($recursively && ! empty($this->_locators)) {
			foreach ($this->_locators as $locator) {
				$service = $locator->get($id, self::BEHAVIOUR_FAIL_NULL);
				
				if ($service !== null) {
					$this->_services[$id] = $service;
					return $service;
				}
			}
		}
		
		if ($failBehaviour === self::BEHAVIOUR_FAIL_EXCEPTION) {
			throw new NoService('Unable to provide the service: "' . $id . '"');
		}
	}
	
	/**
	 * Has a service
	 * 
	 * @param  string $id
	 * @param  bool   $recursively
	 * @return bool
	 */
	public function has($id, $recursively = true) {
		if (isset($this->_aliases[$id])) {
			$id = $this->_aliases[$id];
		}
		
		if (isset($this->_services[$id])
		 || isset($this->_classes[$id])
		 || isset($this->_callbacks[$id])) {
			return true;
		}
		
		if (! empty($this->_initializers)) {
			foreach ($this->_initializers as $initializer) {
				if ($initializer->isAvailable($id)) {
					return true;
				}
			}
		}
		
		if ($recursively && ! empty($this->_locators)) {
			foreach ($this->_locators as $locator) {
				if ($locator->has($id)) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Remove a service
	 * 
	 * @param  string $id
	 * @param  bool   $recursively
	 * @return Container
	 */
	public function remove($id, $recursively = false) {
		unset(
			$this->_aliases[$id],
			$this->_classes[$id],
			$this->_callbacks[$id],
			$this->_services[$id]
		);
		
		if ($recursively && ! empty($this->_locators)) {
			foreach ($this->_locators as $locator) {
				if ($locator instanceof ContainerInterface) {
					$locator->remove($id);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Check for a service has been initialized
	 * 
	 * @param  string $id
	 * @param  bool   $recursively
	 * @return bool
	 */
	public function hasInitialized($id, $recursively = true) {
		if (isset($this->_aliases[$id])) {
			$id = $this->_aliases[$id];
		}
		
		if (isset($this->_services[$id])) {
			return true;
		}
		
		if ($recursively && ! empty($this->_locators)) {
			foreach ($this->_locators as $locator) {
				if ($locator->hasInitialized($id)) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Get an initialized resources
	 * 
	 * @param  bool $recursively
	 * @return array
	 */
	public function getInitialized($recursively = true) {
		if ($recursively && ! empty($this->_locators)) {
			$services = $this->_services;
			
			foreach ($this->_locators as $locator) {
				if ($locator instanceof ContainerInterface) {
					$services += $locator->getInitialized();
				}
			}
			
			return $services;
		}
		
		return $this->_services;
	}
	
	public function __get($id) {
		return $this->get($id);
	}
	
	public function __set($id, $service) {
		$this->set($id, $service);
	}
	
	public function __isset($id) {
		return $this->has($id);
	}
	
	public function __unset($id) {
		$this->remove($id);
	}
	
}