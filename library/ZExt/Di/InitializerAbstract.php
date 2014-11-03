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

use ZExt\Di\Definition\ArgumentsTrait;

/**
 * Abstract initializer
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Initializer
 * @author     Mike.Mirten
 * @version    2.0
 */
abstract class InitializerAbstract implements LocatorInterface {
	
	use ArgumentsTrait;
	
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
	 * Instances of services
	 *
	 * @var mixed
	 */
	protected $services;
	
	/**
	 * IDs of services which checked on availability
	 *
	 * @var array 
	 */
	protected $checkedIds = [];
	
	/**
	 * Set arguments for constructor of service
	 * 
	 * @param  mixed $args
	 * @return InitializerNamespace
	 */
	public function setArguments($args) {
		$this->arguments = $args;
		
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
	 * Get service by ID
	 * 
	 * @param  string $id   ID of service
	 * @param  mixed  $args Arguments for constructor of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	public function get($id, $args = null) {
		if (isset($this->checkedIds[$id]) && ! $this->checkedIds[$id]) {
			throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
		}
		
		$args = $this->resolveArgs(func_get_args());
		
		try {
			$service = ($args === null)
				? $this->getService($id)
				: $this->getServiceByArgs($id, $args);
		}
		catch (Exceptions\ServiceNotFound $exception) {
			$this->checkedIds[$id] = false;
			throw $exception;
		}
		
		$this->checkedIds[$id] = true;
		return $service;
	}
	
	/**
	 * Get service without arguments
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function getService($id) {
		if ($this->factoryMode) {
			return $this->initService($id);
		}
		
		$fullId = $id . ':' . $this->getIdByArgs([]);
		
		if (! isset($this->services[$fullId])) {
			$this->services[$fullId] = $this->initService($id);
		}
			
		return $this->services[$fullId];
	}
	
	/**
	 * Get service by arguments
	 * 
	 * @param  string $id
	 * @param  mixed  $args
	 * @return mixed
	 */
	public function getServiceByArgs($id, $args) {
		$processedArgs = $this->processArgs($args);
		
		if ($this->factoryMode) {
			return $this->initService($id, $processedArgs);
		}
		
		$fullId = $id . ':' . $this->getIdByArgs($args);
		
		if (! isset($this->services[$fullId])) {
			$this->services[$fullId] = $this->initService($id, $processedArgs);
		}
		
		return $this->services[$fullId];
	}
	
	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	public function has($id) {
		if (! isset($this->checkedIds[$id])) {
			$this->checkedIds[$id] = $this->hasService($id);
		}
		
		return $this->checkedIds[$id];
	}
	
	/**
	 * Has service initialized ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 * @throws Exceptions\ServiceNotFound
	 */
	public function hasInitialized($id, $args = null) {
		if (! $this->has($id)) {
			throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
		}
		
		$args   = $this->resolveArgs(func_get_args());
		$fullId = $id . ':' . $this->getIdByArgs($args);
		
		return isset($this->services[$fullId]);
	}
	
	/**
	 * Resolve a method's call arguments
	 * Input must be a result of "func_get_args()"
	 * 
	 * @param  array $methodCallArgs
	 * @return array
	 */
	protected function resolveArgs($methodCallArgs) {
		if (! isset($methodCallArgs[1])) {
			if ($this->arguments === null) {
				return $this->normalizeArgs([]);
			}
			
			return $this->normalizeArgs($this->arguments);
		}
		
		if (isset($methodCallArgs[2])) {
			array_shift($methodCallArgs);
			return $this->normalizeArgs($methodCallArgs);
		}
		
		return $this->normalizeArgs($methodCallArgs[1]);
	}
	
	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	abstract protected function hasService($id);
	
	/**
	 * Initialize service by ID
	 * 
	 * @param  string $id   ID of service
	 * @param  array  $args Arguments for constructor of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	abstract protected function initService($id, array $args = null);
	
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
	 * @access protected
	 * @param  string $id ID of service
	 * @return bool
	 */
	public function __isset($id) {
		return $this->has($id);
	}
	
	/**
	 * Get service by a method name as an ID
	 * 
	 * @param  string $id   ID of service
	 * @param  array  $args Arguments for constructor of service
	 * @throws Exceptions\ServiceNotFound
	 */
	public function __call($id, $args) {
		return $this->get($id, $args);
	}
	
}