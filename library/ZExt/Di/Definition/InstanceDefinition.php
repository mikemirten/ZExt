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

use RuntimeException;

/**
 * Instance type definition
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
class InstanceDefinition extends DefinitionAbstract {
	
	/**
	 * Constructor
	 * 
	 * @param string $id      ID of service
	 * @param mixed  $service Instance of service
	 */
	public function __construct($service) {
		$this->setService($service);
	}
	
	/**
	 * Set instance of service
	 * 
	 * @param mixed $service
	 */
	public function setService($service) {
		$this->service = $service;
	}
	
	/**
	 * Reset instance of service
	 * 
	 * @param  string $id ID of service
	 * @throws RuntimeException
	 */
	public function reset($id = null) {
		throw new RuntimeException('Reset of "Instance" type definition is invalid action');
	}
	
	/**
	 * Set factory mode
	 * 
	 * @param  bool $factory
	 * @return DefinitionInterface
	 * @throws RuntimeException
	 */
	public function setFactoryMode($factory = true) {
		throw new RuntimeException('Factory mode impossible for "Instance" type definition');
	}
	
	/**
	 * Initialize service
	 * 
	 * @param  array $args
	 * @return mixed
	 */
	protected function initService(array $args = null) {}
	
}