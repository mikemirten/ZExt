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

use ZExt\Config\Config;
use Traversable;

/**
 * Definition abstract
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class DefinitionAbstract implements DefinitionInterface {
	
	/**
	 * Arguments for constructor of service
	 *
	 * @var mixed 
	 */
	protected $arguments;
	
	/**
	 * Factory mode
	 *
	 * @var bool
	 */
	protected $factoryMode = false;
	
	/**
	 * Instance of service
	 *
	 * @var mixed
	 */
	protected $service;
	
	/**
	 * Instances of services initialized with arguments
	 *
	 * @var array
	 */
	protected $servicesByArgs = [];
	
	/**
	 * Set arguments for constructor of service
	 * 
	 * @param  mixed $args
	 * @return DefinitionInterface
	 */
	public function setArguments($args) {
		$this->arguments = $args;
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get arguments for constructor of service
	 * 
	 * @return mixed
	 */
	public function getArguments() {
		return $this->arguments;
	}
	
	/**
	 * Set factory mode
	 * 
	 * @param  bool $factory
	 * @return DefinitionInterface
	 */
	public function setFactoryMode($factory = true) {
		$this->factoryMode = (bool) $factory;
		
		if ($this->factoryMode) {
			$this->reset();
		}
		
		return $this;
	}
	
	/**
	 * Is factory mode on ?
	 * 
	 * @return bool
	 */
	public function isFactory() {
		return $this->factoryMode;
	}
	
	/**
	 * Get service
	 * 
	 * @param  mixed $args Arguments for service constructor
	 * @return mixed
	 */
	public function getService($args = null) {
		if ($this->isFactory()) {
			return $this->getServiceByFactory();
		}
		
		if ($args === null) {
			return $this->getCommonService();
		}
		
		return $this->getServiceByArgs($args);
	}
	
	/**
	 * Get service by factory
	 * 
	 * @return mixed
	 */
	protected function getServiceByFactory() {
		if ($this->arguments === null) {
			return $this->initService();
		}

		$args = $this->normalizeArgs($this->arguments);
		$args = $this->processArgs($args);
		
		return $this->initService($args);
	}
	
	/**
	 * Get service with common arguments
	 * 
	 * @return mixed
	 */
	protected function getCommonService() {
		if ($this->service === null) {
			if ($this->arguments === null) {
				$this->service = $this->initService();
			} else {
				$args = $this->normalizeArgs($this->arguments);
				$args = $this->processArgs($args);
				
				$this->service = $this->initService($args);
			}
		}
		
		return $this->service;
	}
	
	/**
	 * Get service by arguments
	 * 
	 * @param  mixed $args Arguments for service constructor
	 * @return mixed
	 */
	protected function getServiceByArgs($args) {
		$args = $this->normalizeArgs($args);
		$id   = $this->getIdByArgs($args);
		$args = $this->processArgs($args);
			
		if (! isset($this->servicesByArgs[$id])) {
			$this->servicesByArgs[$id] = $this->initService($args);
		}

		return $this->servicesByArgs[$id];
	}
	
	/**
	 * Initialize service
	 * 
	 * @param  array $args
	 * @return mixed
	 */
	abstract protected function initService(array $args = null);
	
	/**
	 * Has service initialized ?
	 * 
	 * @param  mixed $args Arguments which was service initialized
	 * @return bool
	 */
	public function hasInitialized($args = null) {
		if ($args === null) {
			return $this->service !== null;
		}
		
		$args = $this->normalizeArgs($args);
		$id   = $this->getIdByArgs($args);
		
		return isset($this->servicesByArgs[$id]);
	}
	
	/**
	 * Reset instance of service
	 * 
	 * @param mixed $args Arguments which was service initialized
	 */
	public function reset($args = null) {
		if ($args === null) {
			$this->service = null;
			return;
		}
		
		$args = $this->normalizeArgs($args);
		$id   = $this->getIdByArgs($args);
		
		unset($this->servicesByArgs[$id]);
	}
	
	/**
	 * Calculate ID by arguments
	 * 
	 * @param  array $args
	 * @return string
	 */
	protected function getIdByArgs(array $args) {
		return json_encode($args);
	}
	
	/**
	 * Normalize arguments
	 * 
	 * @param  mixed $args
	 * @return array
	 */
	protected function normalizeArgs($args) {
		if (is_array($args)) {
			return $args;
		}
		
		if ($args instanceof Config) {
			return $args->toArray();
		}
		
		if ($args instanceof Traversable) {
			return iterator_to_array($args);
		}
		
		return [$args];
	}
	
	/**
	 * Process arguments
	 * 
	 * @param  array $args
	 * @return array
	 */
	protected function processArgs(array $args) {
		foreach ($args as &$arg) {
			if ($arg instanceof Argument\ArgumentInterface) {
				$arg = $arg->getValue();
			}
		}
		unset($arg);
		
		return $args;
	}
	
}