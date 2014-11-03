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

use ReflectionClass, ReflectionException;

/**
 * Namespace(s) based initializer
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Initializer
 * @author     Mike.Mirten
 * @version    2.0
 */
class InitializerNamespace extends InitializerAbstract {
	
	/**
	 * Namespaces
	 *
	 * @var array 
	 */
	protected $namespaces = [];
	
	/**
	 * Service's class prefix
	 *
	 * @var string
	 */
	protected $classPrefix = '';
	
	/**
	 * Service's class postfix
	 *
	 * @var string
	 */
	protected $classPostfix = '';
	
	/**
	 * Use separate directory for each service
	 *
	 * @var bool
	 */
	protected $dirForEachService = false;
	
	/**
	 * Reflections of services' classes
	 *
	 * @var ReflectionClass[] 
	 */
	protected $classReflections = [];
	
	/**
	 * Constructor
	 * 
	 * @param string $namespace Namespace of services
	 * @param mixed  $args      Arguments for constructor of service
	 */
	public function __construct($namespace, $args = null) {
		$this->registerNamespace($namespace);
		
		if ($args !== null) {
			$this->setArguments($args);
		}
	}
	
	/**
	 * Register namespace of services
	 * 
	 * @param  string $namespace
	 * @return InitializerNamespace
	 */
	public function registerNamespace($namespace) {
		$namespace = trim($namespace, '\\');
		
		if (! in_array($namespace, $this->namespaces)) {
			$this->namespaces[] = $namespace;
		}
		
		return $this;
	}
	
	/**
	 * Set prefix class
	 * 
	 * @param  string $prefix
	 * @return InitializerNamespace
	 */
	public function setClassPrefix($prefix) {
		$this->classPrefix = (string) $prefix;
		
		return $this;
	}
	
	/**
	 * Set postfix class
	 * 
	 * @param  string $postfix
	 * @return InitializerNamespace
	 */
	public function setClassPostfix($postfix) {
		$this->classPostfix = (string) $postfix;
		
		return $this;
	}
	
	/**
	 * Set using a separate directory for each service
	 * 
	 * @param  bool $option
	 * @return InitializerNamespace
	 */
	public function setDirectoryForEachService($option = true) {
		$this->dirForEachService = (bool) $option;
		
		return $this;
	}
	
	/**
	 * Initialize service by ID
	 * 
	 * @param  string $id   ID of service
	 * @param  array  $args Arguments for constructor of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	protected function initService($id, array $args = null) {
		$reflection = $this->getReflectionById($id);
		
		if ($reflection === null) {
			throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
		}
		
		if ($args === null) {
			return $reflection->newInstance();
		}

		return $reflection->newInstanceArgs($args);
	}
	
	/**
	 * Get class reflection by ID
	 * 
	 * @param string $id
	 */
	protected function getReflectionById($id) {
		if (! isset($this->classReflections[$id])) {
			$this->classReflections[$id] = $this->initReflection($id);
		}
		
		return $this->classReflections[$id];
	}
	
	/**
	 * Initialize class reflection by ID
	 * 
	 * @param string $id
	 */
	protected function initReflection($id) {
		$normalizedId = ucfirst($id);

		$classname  = $this->dirForEachService ? $normalizedId : '';
		$classname .= $this->classPrefix . $normalizedId . $this->classPostfix;

		foreach ($this->namespaces as $namespace) {
			$class = $namespace . '\\' . $classname;

			try {
				return new ReflectionClass($class);
			} catch (ReflectionException $e) {}
		}
	}
	
	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	protected function hasService($id) {
		return $this->getReflectionById($id) !== null;
	}
	
	/**
	 * Initialize a definition for service
	 * 
	 * @param  string $id Service ID
	 * @return Definition\DefinitionInterface
	 */
	public function initDefinition($id) {
		$reflection = $this->getReflectionById($id);
		$args       = $this->getArguments();
		
		$definition = new Definition\ClassDefinition($reflection, $args);
		
		if ($this->isFactory()) {
			$definition->setFactoryMode();
		}
		
		return $definition;
	}
	
}