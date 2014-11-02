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

/**
 * Definition of service or group of services
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
interface DefinitionInterface {
	
	/**
	 * Get service by ID
	 * 
	 * @param  mixed $args Arguments for service constructor
	 * @return mixed
	 */
	public function getService($args = null);
	
	/**
	 * Set arguments for constructor of service
	 * 
	 * @param  mixed $args
	 * @return DefinitionInterface
	 */
	public function setArguments($args);
	
	/**
	 * Get arguments for constructor of service
	 * 
	 * @return mixed
	 */
	public function getArguments();
	
	/**
	 * Has service initialized ?
	 * 
	 * @param  mixed $args Arguments which was service initialized
	 * @return bool
	 */
	public function hasInitialized($args = null);
	
	/**
	 * Reset instance of service
	 * 
	 * @param mixed $args Arguments which was service initialized
	 */
	public function reset($args = null);
	
	/**
	 * Set factory mode
	 * 
	 * @param  bool $factory
	 * @return Callback
	 */
	public function setFactoryMode($factory = true);
	
	/**
	 * Is factory mode on ?
	 * 
	 * @return bool
	 */
	public function isFactory();
	
}