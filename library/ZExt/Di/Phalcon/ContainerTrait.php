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

namespace ZExt\Di\Phalcon;

use ZExt\Di\LocatorInterface,
    ZExt\Di\ContainerInterface,
    ZExt\Di\InitializerInterface,
    ZExt\Di\LocatorAwareInterface;

use ZExt\Di\Exception\NoService,
    ZExt\Di\Exception\InitializerExists,
    ZExt\Di\Exception\LocatorExists;

use Phalcon\Di\Exception as PhalconDiException;

use Closure;

/**
 * Dependency injection service container trait based on the Phalcon DI
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Container
 * @author     Mike.Mirten
 * @version    1.0
 */
trait ContainerTrait {
	
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
	protected $_initializedServices = [];
	
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
	 * Get a service
	 * 
	 * @param  string      $id
	 * @param  int | array $failBehaviour Fail behaviour or service init params
	 * @param  bool        $recursively
	 * @return mixed
	 * @throws NoService
	 */
	public function get($id, $failBehaviour = self::BEHAVIOUR_FAIL_EXCEPTION, $recursively = true) {
		if (isset($this->_aliases[$id])) {
			$id = $this->_aliases[$id];
		}
		
		if (isset($this->_initializedServices[$id])) {
			return $this->_initializedServices[$id];
		}
		
		// Trying Phalcon DI
		try {
			if (is_array($failBehaviour)) {
				$service = parent::get($id, $failBehaviour);
			} else {
				$service = parent::get($id);
			}
		} catch (PhalconDiException $exception) {
			// Trying the initializers
			if (! empty($this->_initializers)) {
				foreach ($this->_initializers as $initializer) {
					$service = $initializer->initialize($id);

					if ($service !== null) {
						$this->_initializedServices[$id] = $service;
						return $service;
					}
				}
			}

			// Trying the chained locators
			if ($recursively && ! empty($this->_locators)) {
				foreach ($this->_locators as $locator) {
					$service = $locator->get($id, self::BEHAVIOUR_FAIL_NULL);

					if ($service !== null) {
						$this->_initializedServices[$id] = $service;
						return $service;
					}
				}
			}
			
			if ($failBehaviour === self::BEHAVIOUR_FAIL_EXCEPTION) {
				throw new NoService('Unable to provide the service: "' . $id . '"', 0, $exception);
			}
		}
		
		$this->_initializedServices[$id] = $service;
		return $service;
	}
	
	/**
	 * Get an initialized resources
	 * 
	 * @param  bool $recursively
	 * @return array
	 */
	public function getInitialized($recursively = true) {
		if ($recursively && ! empty($this->_locators)) {
			$services = $this->_initializedServices;
			
			foreach ($this->_locators as $locator) {
				if ($locator instanceof ContainerInterface) {
					$services += $locator->getInitialized();
				}
			}
			
			return $services;
		}
		
		return $this->_initializedServices;
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
		
		if (parent::has($id)) {
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
		
		if (isset($this->_initializedServices[$id])) {
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
	 * Remove a service
	 * 
	 * @param  string $id
	 * @param  bool   $recursively
	 * @return Container
	 */
	public function remove($id, $recursively = false) {
		unset($this->_initializedServices[$id], $this->_aliases[$id]);
		
		parent::remove($id);
		
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
	 * Set a service
	 * 
	 * @param  string $id
	 * @param  mixed  $service
	 * @param  int    $existsBehaviour
	 * @return Container
	 * @throws PhalconDiException
	 */
	public function set($id, $service, $existsBehaviour = self::BEHAVIOUR_EXISTS_EXCEPTION) {
		if ($existsBehaviour === self::BEHAVIOUR_EXISTS_EXCEPTION) {
			parent::set($id, $service);
		} else {
			try {
				parent::set($id, $service);
			} catch (PhalconDiException $exception) {
				// Do nothing
			}
		}
		
		if (is_object($service) && ! $service instanceof Closure) {
			$this->_initializedServices[$id] = $service;
		}
		
		return $this;
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