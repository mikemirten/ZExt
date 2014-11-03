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

use ZExt\Di\Exceptions\InitializationFailure;

use ReflectionClass, ReflectionException, RuntimeException;

/**
 * Classname type definition
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
class ClassDefinition extends DefinitionAbstract {
	
	/**
	 * Name of class
	 *
	 * @var string
	 */
	protected $class;
	
	/**
	 * Reflection of class
	 *
	 * @var ReflectionClass
	 */
	protected $reflection;
	
	/**
	 * Constructor
	 * 
	 * @param string $class Name of class
	 * @param mixed  $args  Arguments for constructor of service
	 */
	public function __construct($class, $args = null) {
		if (is_string($class)) {
			$this->setClassname($class);
		}
		else if ($class instanceof ReflectionClass) {
			$this->setReflection($class);
		}
		else {
			throw new RuntimeException('Class must be a string or ReflectionClass instance, "' . gettype($class) . '" given');
		}
		
		if ($args !== null) {
			$this->setArguments($args);
		}
	}
	
	/**
	 * Set name of class
	 * 
	 * @param  string $classname
	 * @return ClassDefinition
	 */
	public function setClassname($classname) {
		$this->class      = trim($classname, '\\');
		$this->reflection = null;
		
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get name of class
	 * 
	 * @return string
	 */
	public function getClassname() {
		return $this->class;
	}
	
	/**
	 * Get reflection of class
	 * 
	 * @param  ReflectionClass $reflection
	 * @return ClassDefinition
	 */
	public function setReflection(ReflectionClass $reflection) {
		$this->reflection = $reflection;
		$this->class      = $reflection->getName();
		
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get class reflection
	 * 
	 * @return ReflectionClass
	 * @throws InitializationFailure
	 */
	protected function getReflection() {
		if ($this->reflection === null) {
			try {
				$this->reflection = new ReflectionClass($this->class);
			} catch (ReflectionException $exception) {
				throw new InitializationFailure('Error occurred while service initialization by class "' . $this->class . '"', 0, $exception);
			}
		}
		
		return $this->reflection;
	}
	
	/**
	 * Initialize service
	 * 
	 * @param  array $args
	 * @return mixed
	 */
	protected function initService(array $args = null) {
		$reflection = $this->getReflection();
		
		if ($args === null) {
			return $reflection->newInstance();
		}
		
		return $reflection->newInstanceArgs($args);
	}
	
	public function __sleep() {
		$properties   = parent::__sleep();
		$properties[] = 'class';
		
		return $properties;
	}
	
}