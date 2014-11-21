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

namespace ZExt\Di\Config;

/**
 * Configuration reader interface
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Config
 * @author     Mike.Mirten
 * @version    1.0
 */
interface ReaderInterface {
	
	/**
	 * Get configuration of service's definitions
	 * 
	 * [
	 *     "include1.xml",
	 *     "include2.xml",
	 *     "includeN.xml"
	 * ]
	 * 
	 * @return array
	 * @throws \ZExt\Di\Exceptions\InvalidConfig
	 */
	public function getIncludes();
	
	/**
	 * Get parameters
	 * 
	 * {
	 *     "param1": value1,
	 *     "param2": value2,
	 *     "paramN": valueN
	 * }
	 * 
	 * @return object
	 * @throws \ZExt\Di\Exceptions\InvalidConfig
	 */
	public function getParameters();
	
	/**
	 * Get service's definitions
	 * 
	 * {
	 *     "serviceId": {
	 *         "type": "serviceType",
	 *         ...
	 *         "arguments": [
	 *             {
	 *                 "type": "argumentType",
	 *                 ...
	 *             }
	 *         ]
	 *     }
	 * }
	 * 
	 * 
	 * @return object
	 * @throws \ZExt\Di\Exceptions\InvalidConfig
	 */
	public function getServices();
	
	/**
	 * Get initializers's definitions
	 * 
	 * {
	 *     "initializerId": {
	 *         "type": "initializerType",
	 *         ...
	 *     }
	 * }
	 * 
	 * @return object
	 * @throws \ZExt\Di\Exceptions\InvalidConfig
	 */
	public function getInitializers();
	
	/**
	 * Get unique ID of reader
	 * 
	 * @return string
	 */
	public function getId();
	
}