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
 * Services' container interface
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Di
 * @author     Mike.Mirten
 * @version    1.0
 */
interface ContainerInterface extends LocatorInterface, DefinitionAwareInterface {
	
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
	public function set($id, $definition, $args = null, $factory = false);
	
	/**
	 * Set alias for service
	 * 
	 * @param  string $existsId ID of exists service
	 * @param  string $newId    Alias ID
	 * @throws Exceptions\ServiceOverride
	 */
	public function setAlias($existsId, $newId);
	
	/**
	 * Remove service
	 * 
	 * @param string $id ID of service
	 */
	public function remove($id);
	
}