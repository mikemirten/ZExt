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

use ZExt\Di\Definition\DefinitionInterface;

use ZExt\Di\Definition\Argument\ConfigReferenceArgument as ConfigReference,
    ZExt\Di\Definition\Argument\ServiceReferenceArgument as ServiceReference;


use ZExt\Config\ConfigInterface,
    ZExt\Config\Config;

use Closure;

/**
 * Dependency injection service container
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Container
 * @author     Mike.Mirten
 * @version    2.0
 */
class Container implements ContainerInterface {
	
	/**
	 * Definitions of services
	 *
	 * @var DefinitionInterface[]
	 */
	protected $_definitions = [];
	
	/**
	 * Parameters
	 *
	 * @var ConfigInterface
	 */
	protected $_parametersConfig;
	
	/**
	 * PArameters config exchange lock
	 *
	 * @var bool
	 */
	protected $_parametersConfigLock = false;
	
	/**
	 * Locators
	 *
	 * @var LocatorInterface[]
	 */
	protected $_locators = [];
	
	/**
	 * Locators by resolved ID
	 *
	 * @var LocatorInterface[] 
	 */
	protected $_locatorsResolved = [];
	
	/**
	 * Constructor
	 * 
	 * @param ConfigInterface $parametersConfig
	 */
	public function __construct(ConfigInterface $parametersConfig = null) {
		if ($parametersConfig !== null) {
			$this->setParemetersConfig($parametersConfig);
		}
	}
	
	/**
	 * Set service definition
	 * 
	 * @param  string $id         ID of service
	 * @param  mixed  $definition Definition of service
	 * @param  mixed  $args       Arguments for constructor of service
	 * @param  bool   $factory    Factory mode: new instance for each request of service
	 * @return DefinitionInterface
	 * @throws Exceptions\ServiceOverride
	 */
	public function set($id, $definition, $args = null, $factory = false) {
		if (isset($this->_definitions[$id])) {
			throw new Exceptions\ServiceOverride('Service "' . $id . '" already exists');
		}
		
		$definition = $this->normalizeDefinition($definition);
		
		if ($args !== null) {
			$args = $this->processArguments($args);
			$definition->setArguments($args);
		}
		
		if ($factory) {
			$definition->setFactoryMode();
		}
		
		$this->_definitions[$id] = $definition;
		
		return $definition;
	}
	
	/**
	 * Set alias for service
	 * 
	 * @param  string $existsId ID of exists service
	 * @param  string $newId    Alias ID
	 * @throws Exceptions\ServiceOverride
	 */
	public function setAlias($existsId, $newId) {
		if (isset($this->_definitions[$newId])) {
			throw new Exceptions\ServiceOverride('Service "' . $newId . '" already exists');
		}
		
		$this->_definitions[$newId] = $this->getDefinition($existsId);
	}
	
	/**
	 * Set parameter
	 * 
	 * @param  string $name
	 * @param  mixed  $value
	 * @return Container
	 */
	public function setParameter($name, $value) {
		$this->getParametersConfig()->set($name, $value);
		
		return $this;
	}
	
	/**
	 * Get parameter
	 * 
	 * @param  string $name
	 * @return mixed
	 */
	public function getParameter($name) {
		return $this->getParametersConfig()->get($name);
	}
	
	/**
	 * Set config with parameters
	 * 
	 * @param  ConfigInterface $config
	 * @param  bool            $lockExchange Forbid set config in future
	 * @throws Exceptions\ForbiddenAction
	 * @return Container
	 */
	public function setParemetersConfig(ConfigInterface $config, $lockExchange = true) {
		if ($this->_parametersConfigLock) {
			throw new Exceptions\ForbiddenAction('Config is locked and cannot be exchanged');
		}
		
		$this->_parametersConfig     = $config;
		$this->_parametersConfigLock = $lockExchange;
		
		return $this;
	}
	
	/**
	 * Get config with parameters
	 * 
	 * @return ConfigInterface
	 */
	public function getParametersConfig() {
		if ($this->_parametersConfig === null) {
			$this->_parametersConfig     = new Config();
			$this->_parametersConfigLock = true;
		}
		
		return $this->_parametersConfig;
	}
	
	/**
	 * Normalize definition
	 * 
	 * @param  mixed $definition Definition of service
	 * @return DefinitionInterface
	 */
	protected function normalizeDefinition($definition) {
		if ($definition instanceof DefinitionInterface) {
			return $definition;
		}
		
		if ($definition instanceof Closure) {
			return new Definition\CallbackDefinition($definition);
		}
		
		if (is_string($definition)) {
			return new Definition\ClassDefinition($definition);
		}
		
		return new Definition\InstanceDefinition($definition);
	}
	
	/**
	 * Get service by ID
	 * 
	 * @param  string $id   ID of service
	 * @param  mixed  $args Arguments for constructor of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	public function get($id, $args = null) {
		if (isset($args[2])) {
			$args = func_get_args();
			array_shift($args);
		}
		
		if (isset($this->_definitions[$id])) {
			return $this->_definitions[$id]->getService($args);
		}
		
		$locator = $this->getLocatorByServiceId($id);
		
		if ($locator !== null) {
			return $locator->get($id, $args);
		}
			
		throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
	}

	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	public function has($id) {
		if (isset($this->_definitions[$id])) {
			return true;
		}
		
		return $this->getLocatorByServiceId($id) !== null;
	}
	
	/**
	 * Get resolve locator by service ID
	 * 
	 * @param  string $id Service ID
	 * @return LocatorInterface
	 */
	protected function getLocatorByServiceId($id) {
		if (isset($this->_locatorsResolved[$id])) {
			return $this->_locatorsResolved[$id];
		}
		
		foreach ($this->_locators as $locator) {
			if ($locator->has($id)) {
				$this->_locatorsResolved[$id] = $locator;
				return $locator;
			}
		}
	}
	
	/**
	 * Get definition of service by service ID
	 * 
	 * @param  string $id ID of service
	 * @return DefinitionInterface
	 * @throws Exceptions\ServiceNotFound
	 */
	public function getDefinition($id) {
		if (isset($this->_definitions[$id])) {
			return $this->_definitions[$id];
		}
		
		$locator = $this->getLocatorByServiceId($id);
		
		if ($locator !== null) {
			if ($locator instanceof DefinitionAwareInterface) {
				$definition = $locator->getDefinition($id);
				
				$this->_definitions[$id] = $definition;
				return $definition;
			}
			
			throw new Exceptions\ServiceNotFound('Unable to provide definition for the service "' . $id . '"');
		}
		
		throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
	}
	
	/**
	 * Has service initialized ?
	 * 
	 * @param  string $id   ID of service
	 * @param  mixed  $args Arguments which was service initialized
	 * @return bool
	 * @throws Exceptions\ServiceNotFound
	 */
	public function hasInitialized($id, $args = null) {
		if (isset($args[2])) {
			$args = func_get_args();
			array_shift($args);
		}
		
		return $this->getDefinition($id)->hasInitialized($args);
	}
	
	/**
	 * Remove service
	 * 
	 * @param string $id ID of service
	 */
	public function remove($id) {
		unset($this->_definitions[$id]);
	}
	
	/**
	 * Add fallback locator
	 * 
	 * @param  LocatorInterface $locator Locator instance
	 * @param  string           $id      Locator unique ID
	 * @return Container
	 */
	public function addLocator(LocatorInterface $locator, $id = null) {
		if ($id === null) {
			$this->_locators[] = $locator;
		} else {
			$this->_locators[$id] = $locator;
		}
		
		return $this;
	}
	
	/**
	 * Get locator by ID
	 * 
	 * @param  string | int $id
	 * @return LocatorInterface | null
	 */
	public function getLocator($id) {
		if (isset($this->_locators[$id])) {
			return $this->_locators[$id];
		}
	}
	
	/**
	 * Process arguments
	 * 
	 * @param  mixed $args
	 * @return mixed
	 */
	protected function processArguments($args) {
		if (is_array($args)) {
			foreach ($args as &$arg) {
				$arg = $this->processArguments($arg);
			}
			unset($arg);
			
			return $args;
		}
		
		if (is_string($args)) {
			if (preg_match('~^\[\[([a-z0-9_]+)\]\]$~i', $args, $matches)) {
				return new ServiceReference($this, $matches[1]);
			}
			
			if (preg_match('~^\{\{([a-z0-9_]+)\}\}$~i', $args, $matches)) {
				return new ConfigReference($this->getParametersConfig(), $matches[1]);
			}
		}
		
		return $args;
	}
	
	/**
	 * Set service definition
	 * 
	 * @param string $id ID of service
	 * @param mixed  $definition
	 */
	public function __set($id, $definition) {
		$this->set($id, $definition);
	}
	
	/**
	 * Get service by ID
	 * 
	 * @param  string $id ID of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	public function __get($id) {
		return $this->get($id);
	}
	
	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	public function __isset($id) {
		return $this->has($id);
	}
	
	/**
	 * Remove service
	 * 
	 * @param string $id ID of service
	 */
	public function __unset($id) {
		$this->remove($id);
	}
	
	/**
	 * Get service by a method name as an ID
	 * 
	 * @param  string $id   ID of service
	 * @param  array  $args Arguments for constructor of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	public function __call($id, $args) {
		return $this->get($id, $args);
	}
	
	public function __sleep() {
		return [
			'_definitions',
			'_locators',
			'_parametersConfig'
		];
	}
	
	public function __clone() {
		if ($this->_parametersConfig !== null) {
			$this->_parametersConfig     = clone $this->_parametersConfig;
			$this->_parametersConfigLock = false;
		}
	}
	
}