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

/**
 * Object's methods based initializer
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Initializer
 * @author     Mike.Mirten
 * @version    2.0
 */
abstract class InitializerObject extends InitializerAbstract implements LocatorAwareInterface {
	
	use LocatorAwareTrait;
	
	/**
	 * Template of method name in "sprintf()" format
	 *
	 * @var string
	 */
	protected $methodNameTemplate = '%sInit';

	/**
	 * Constructor
	 * 
	 * @param LocatorInterface $locator
	 */
	public function __construct(LocatorInterface $locator = null) {
		if ($locator !== null) {
			$this->setLocator($locator);
		}
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
		$method = sprintf($this->methodNameTemplate, lcfirst($id));
		
		if (! method_exists($this, $method)) {
			throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
		}
		
		if ($args === null) {
			return $this->$method();
		}
		
		return call_user_func_array([$this, $method], $args);
	}
	
	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	protected function hasService($id) {
		$method = sprintf($this->methodNameTemplate, lcfirst($id));
		
		return method_exists($this, $method);
	}
	
	/**
	 * Initialize a definition for service
	 * 
	 * @param  string $id Service ID
	 * @return Definition\DefinitionInterface
	 */
	protected function initDefinition($id) {
		$initializer = $this; // Superclosure won't works without this trick
		
		$call = function($args = null) use($initializer, $id) {
			return $initializer->get($id, $args);
		};
		
		$args = $this->getArguments();
		
		$definition = new Definition\CallbackDefinition($call, $args);
		
		if ($this->isFactory()) {
			$definition->setFactoryMode();
		}
		
		return $definition;
	}
	
	/**
	 * Get service by ID
	 * 
	 * @param  string $id ID of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	public function __get($id) {
		if ($this->has($id)) {
			return $this->get($id);
		}
		
		if ($this->hasLocator()) {
			$locator = $this->getLocator();
			
			if ($locator->has($id)) {
				return $locator->get($id);
			}
		}
		
		throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
	}
	
	/**
	 * Is service available for obtain ?
	 * 
	 * @access protected
	 * @param  string $id ID of service
	 * @return bool
	 */
	public function __isset($id) {
		if ($this->has($id)) {
			return true;
		}
		
		if ($this->hasLocator()) {
			$locator = $this->getLocator();
			
			if ($locator->has($id)) {
				return true;
			}
		}
		
		return false;
	}
	
}