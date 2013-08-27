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
    ZExt\Di\Exception\NoNamespaces;

use ZExt\Log\LoggerAwareInterface,
    ZExt\Log\LoggerAwareTrait;

use ZExt\Config\ConfigAwareInterface,
    ZExt\Config\ConfigAwareTrait;

use Closure, ReflectionClass;

/**
 * Initializer based on the namespace(s)
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Initializer
 * @author     Mike.Mirten
 * @version    1.1
 */
class InitializerNamespace

	implements InitializerInterface,
	           LocatorInterface,
	           LocatorAwareInterface,
	           LoggerAwareInterface,
	           ConfigAwareInterface {
	
	use LocatorAwareTrait;
	use LoggerAwareTrait;
	use ConfigAwareTrait;
	
	/**
	 * Services' namespaces
	 *
	 * @var string[]
	 */
	protected $_namespaces = [];
	
	/**
	 * Initialized services
	 *
	 * @var array
	 */
	protected $_services = [];
	
	/**
	 * Initialized with args services
	 *
	 * @var array
	 */
	protected $_servicesArgs = [];
	
	/**
	 * Arguments for the constructor of a service
	 *
	 * @var array
	 */
	protected $_arguments;
	
	/**
	 * Service's class prefix
	 *
	 * @var string
	 */
	protected $_classPrefix = '';
	
	/**
	 * Service's class postfix
	 *
	 * @var string
	 */
	protected $_classPostfix = '';
	
	/**
	 * Use separate directory for an each service
	 *
	 * @var bool
	 */
	protected $_dirForEachService = false;
	
	/**
	 * On init callback
	 * 
	 * @var Closure
	 */
	protected $_onInit;
	
	/**
	 * Misses while class loading
	 *
	 * @var int
	 */
	protected $_loadMisses = 0;
	
	/**
	 * Prefix for the config's service id
	 *
	 * @var string
	 */
	protected $_configIdPrefix = '';
	
	/**
	 * Postfix for the config's service id
	 * 
	 * @var string 
	 */
	protected $_configIdPostfix = 'Config';
	
	/**
	 * Set a prefix for the config's service id
	 * 
	 * @param  string $prefix
	 * @return InitializerNamespace
	 */
	public function setConfigIdPrefix($prefix) {
		$this->_configIdPrefix = (string) $prefix;
		
		return $this;
	}

	/**
	 * Set a postfix for the config's service id
	 * 
	 * @param  string $postfix
	 * @return InitializerNamespace
	 */
	public function setConfigIdPostfix($postfix) {
		$this->_configIdPostfix= (string) $postfix;
		
		return $this;
	}

	/**
	 * Set prefix for a services' classes
	 * 
	 * @param string $prefix
	 * @return Initializer
	 */
	public function setClassPrefix($prefix) {
		$this->_classPrefix = (string) $prefix;
		
		return $this;
	}
	
	/**
	 * Set postfix for a services' classes
	 * 
	 * @param string $postfix
	 * @return Initializer
	 */
	public function setClassPostfix($postfix) {
		$this->_classPostfix = (string) $postfix;
		
		return $this;
	}
	
	/**
	 * Set the arguments for a constructor
	 * 
	 * @param  array $args
	 * @return InitializerNamespace
	 */
	public function setArguments(array $args) {
		$this->_arguments = array_values($args);
		
		return $this;
	}
	
	/**
	 *  Get the arguments for a constructor
	 * 
	 * @return array | null
	 */
	public function getArguments() {
		return $this->_arguments;
	}
	
	/**
	 * Calls right after an object instantiation
	 * 
	 * @param  Closure $callback
	 * @return Initializer
	 */
	public function setOnInit(Closure $callback) {
		$this->_onInit = $callback;
		
		return $this;
	}
	
	/**
	 * Set using a separate directory for an each service
	 * 
	 * @param  bool $option
	 * @return Initializer
	 */
	public function setDirectoryForEachService($option = true) {
		$this->_dirForEachService = (bool) $option;
		
		return $this;
	}
	
	/**
	 * Register the services' namespace
	 * 
	 * @param  string $namespace
	 * @return Initializer
	 */
	public function registerNamespace($namespace) {
		if (! in_array($namespace, $this->_namespaces, true)) {
			$this->_namespaces[] = (string) $namespace;
		}
		
		return $this;
	}
	
	/**
	 * Initialize the service
	 * 
	 * @param  string $id
	 * @param  array  $args
	 * @return mixed
	 */
	public function initialize($id, $arguments = null) {
		if ($arguments === null) {
			if (isset($this->_services[$id])) {
				return $this->_services[$id];
			}
		} else {
			$arguments = array_values((array) $arguments);
			$argsId    = $this->argumentsHashFunction($arguments);
			
			if (isset($this->_services[$id][$argsId])) {
				return $this->_services[$id][$argsId];
			}
		}
		
		$service = $this->loadService($id, false, $arguments);
		
		if ($this->_onInit !== null) {
			$this->_onInit->__invoke($service);
		}
		
		// Supplying a service's locator
		if ($service instanceof LocatorAwareInterface && $this->hasLocator() && ! $service->hasLocator()) {
			$service->setLocator($this->getLocator());
		}
		
		// Supplying the personal config
		if ($service instanceof ConfigAwareInterface) {
			$configPart = strtolower($id);
			
			$service->setConfigServiceId($this->_configIdPrefix . $configPart . $this->_configIdPostfix);
			
			if ($this->hasConfig() && ! $service->hasConfig()) {
				$config = $this->getConfig();

				if (isset($config->$configPart)) {
					$service->setConfig($config->$configPart);
				}
			}
		}
		
		if ($arguments === null) {
			$this->_services[$id] = $service;
		} else {
			$this->_services[$id][$argsId] = $service;
		}
		
		return $service;
	}
	
	/**
	 * Is the service available
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	public function isAvailable($id) {
		if (isset($this->_services[$id]) || isset($this->_servicesArgs[$id])) {
			return true;
		}
		
		if ($this->loadService($id, true)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Load the service
	 * 
	 * @param  string $id
	 * @param  bool   $onlyCheck
	 * @param  array  $arguments
	 * @return object | bool
	 * @throws NoNamespaces
	 * @throws NoHelper
	 */
	protected function loadService($id, $onlyCheck = false, $arguments = null) {
		if (empty($this->_namespaces)) {
			throw new NoNamespaces('Wasn\'t a namespaces registered');
		}
		
		set_error_handler(function() {
			++ $this->_loadMisses;
		});

		foreach ($this->_namespaces as $namespace) {
			if ($this->_dirForEachService) {
				$namespace .= '\\' . $id;
			}

			$class = $namespace . '\\' . $this->_classPrefix . ucfirst($id) . $this->_classPostfix;
			
			if (class_exists($class)) {
				restore_error_handler();
				
				// No really instance
				if ($onlyCheck) {
					return true;
				}
				
				if ($arguments === null && $this->_arguments !== null) {
					$arguments = $this->_arguments;
				}
				
				// With no arguments
				if ($arguments === null) {
					return new $class();
				}
				
				// With more than one argument
				if (count($arguments) > 1) {
					$reflection = new ReflectionClass($class);
					return $reflection->newInstanceArgs($this->_arguments);
				}
				
				// With a single argument
				return new $class($arguments[0]);
			}
		}
		
		restore_error_handler();
		
		if ($onlyCheck) {
			return false;
		} else {
			throw new NoService('Unable to load the service "' . $id . '", registered namespaces: "' . implode('", "', $this->_namespaces) . '"');
		}
	}
	
	/**
	 * Get a misses number which were occurred while a class loading
	 * 
	 * @return int
	 */
	public function getMissesNumber() {
		return $this->_loadMisses;
	}
	
	/**
	 * Get the service
	 * 
	 * @param  string $id            An id of a service
	 * @param  int    $failBehaviour On a service locate fail behaviour
	 * @return mixed
	 */
	public function get($id, $failBehaviour = self::BEHAVIOUR_FAIL_EXCEPTION) {
		if ($failBehaviour === self::BEHAVIOUR_FAIL_NULL) {
			try {
				return $this->initialize($id);
			} catch (NoService $e) {
				return;
			}
		} else {
			return $this->initialize($id);
		}
	}
	
	/**
	 * Has the service
	 * 
	 * @param  string $name An id of a service
	 * @return boolean
	 */
	public function has($id) {
		return $this->isAvailable($id);
	}
	
	/**
	 * Check for a service has been initialized
	 * 
	 * @param  string $name An id of a service
	 * @param  array  $arguments
	 * @return boolean
	 */
	public function hasInitialized($id, $arguments = null) {
		if ($arguments === null) {
			return isset($this->_services[$id]);
		} else {
			if (isset($this->_servicesArgs[$id])) {
				$argsId = $this->argumentsHashFunction($arguments);
				return isset($this->_servicesArgs[$id][$argsId]);
			}
			
			return false;
		}
	}
	
	/**
	 * Argument hashing function
	 * 
	 * @param  mixed $arguments
	 * @return string
	 */
	protected function argumentsHashFunction($arguments) {
		return md5(serialize($arguments));
	}

	/**
	 * Is the service available
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	public function __isset($name) {
		return $this->isAvailable($name);
	}
	
	/**
	 * Initialize the service
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function __get($name) {
		return $this->initialize($name);
	}
	
}