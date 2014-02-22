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

namespace ZExt\Config;

use ZExt\Di\LocatorAwareInterface;

use ZExt\Config\Exceptions\NoConfigsFactory;
use ZExt\Config\Exceptions\NoConfig;

/**
 * Configuration holder aware trait
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage ConfigAware
 * @author     Mike.Mirten
 * @version    1.0
 */
trait ConfigAwareTrait {
	
	/**
	 * Local config
	 *
	 * @var ConfigInterface
	 */
	private $_configLocal;
	
	/**
	 * Supplied config
	 *
	 * @var ConfigInterface 
	 */
	private $_configSupplied;
	
	/**
	 * Processed config
	 *
	 * @var ConfigInterface
	 */
	private $_config;
	
	/**
	 * Configs' factory's instance
	 *
	 * @var FactoryInterface
	 */
	private $_configsFactory;
	
	/**
	 * Config's id in a services' locator
	 *
	 * @var string
	 */
	private $_configServiceId = 'config';
	
	/**
	 * Set a service id of the config
	 * 
	 * @param string $id
	 */
	public function setConfigServiceId($id) {
		$this->_configServiceId = (string) $id;
	}
	
	/**
	 * Get a service id of the config
	 * 
	 * @return string
	 */
	public function getConfigServiceId() {
		return $this->_configServiceId;
	}
	
	/**
	 * Set a config
	 * 
	 * @param ConfigInterface $config
	 */
	public function setConfig(ConfigInterface $config) {
		$this->_configSupplied = $config;
		$this->_config         = null;
	}
	
	/**
	 * Get a config
	 * 
	 * @return ConfigInterface
	 * @throws NoConfig
	 */
	public function getConfig() {
		if ($this->_config !== null) {
			return $this->_config;
		}
		
		// Local config
		if ($this->_configLocal === null && method_exists($this, 'getLocalConfig') && $this->hasConfigsFactory()) {
			$localConfigSrc = $this->getLocalConfig();
			$configsFactory = $this->getConfigsFactory();

			if (is_array($localConfigSrc)) {
				$this->_configLocal = $configsFactory->create($localConfigSrc);
			}
			else if (is_string($localConfigSrc)) {
				$this->_configLocal = $configsFactory->createFromFile($localConfigSrc);
			}
			else {
				throw new NoConfig('Type of a local config\'s source must be an array or a string');
			}
			
			$this->_config = clone $this->_configLocal;
		}

		// Supplied config
		if ($this->_configSupplied === null && $this instanceof LocatorAwareInterface && $this->hasLocator()) {
			$locator  = $this->getLocator();
			$configId = $this->getConfigServiceId();
			
			if ($locator->has($configId)) {
				$this->_configSupplied = $locator->get($configId);
				
				if (! $this->_configSupplied instanceof ConfigInterface) {
					throw new NoConfig('Config must implement the "ConfigInterface"');
				}
			}
		}

		// Merge supplied config
		if ($this->_configSupplied !== null) {
			if ($this->_config === null) {
				$this->_config = clone $this->_configSupplied;
			} else {
				$this->_config = $this->_config->merge($this->_configSupplied);
			}
		}

		// Still no final config ?
		if ($this->_config === null) {
			throw new NoConfig('Unable to provide the config');
		}
		
		return $this->_config;
	}
	
	/**
	 * Has a config
	 * 
	 * @return bool
	 */
	public function hasConfig() {
		if ($this->_configSupplied !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface && $this->hasLocator()
			&& $this->getLocator()->has($this->getConfigServiceId())) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Set a configs' factory
	 * 
	 * @param FactoryInterface $factory
	 */
	public function setConfigsFactory(FactoryInterface $factory) {
		$this->_configsFactory = $factory;
	}
	
	/**
	 * Get a configs' factory
	 * 
	 * @return FactoryInterface
	 * @throws NoConfigsFactory
	 */
	public function getConfigsFactory() {
		if ($this->_configsFactory === null) {
			if ($this instanceof LocatorAwareInterface && $this->hasLocator()) {
				$this->_configsFactory = $this->getLocator()->get('configsFactory');
				
				if (! $this->_configsFactory instanceof FactoryInterface) {
					throw new NoConfigsFactory('Configs\' factory must implement the "FactoryInterface"');
				}
			} else {
				throw new NoConfigsFactory('No configs\' factory been supplied, also unable to obtain one through locator');
			}
		}
		
		return $this->_configsFactory;
	}
	
	/**
	 * Has a configs' factory
	 * 
	 * @return bool
	 */
	public function hasConfigsFactory() {
		if ($this->_configsFactory !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface && $this->hasLocator()
			&& $this->getLocator()->has('configsFactory')) {
			return true;
		}
		
		return false;
	}
	
}