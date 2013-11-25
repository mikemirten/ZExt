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

/**
 * Configuration holder's factory interface
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage ConfigFactory
 * @author     Mike.Mirten
 * @version    1.0
 */
interface FactoryInterface {
	
	/**
	 * Create a config from the file
	 * 
	 * @param string $path    Path of a config
	 * @param array  $options Options for a reader
	 * 
	 * @return ConfigInterface
	 */
	public static function createFromFile($path, array $options = []);
	
	/**
	 * Create a config from the source
	 * 
	 * @param string $source   Source of a config
	 * @param string $type     Type of a config
	 * @param array  $options  Options for a reader
	 * 
	 * @return ConfigInterface
	 */
	public static function createFromSource($source, $type, array $options = []);
	
	/**
	 * Create a config from an array
	 * 
	 * @param  array $source   Source of a config
	 * @param  bool  $readOnly Lock a created config
	 * 
	 * @return ConfigInterface
	 */
	public static function create(array $source = null, $readOnly = true);
	
}