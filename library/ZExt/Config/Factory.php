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

use ZExt\Config\Reader\ReaderInterface;
use ZExt\Config\Exceptions\UnableToRead;
use ZExt\Config\Exceptions\InvalidReader;

/**
 * Configuration holder's factory
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage ConfigFactory
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
class Factory implements FactoryInterface {
	
	const READERS_NAMESPACE = 'ZExt\Config\Reader';
	
	const OPTION_READONLY = 'readonly';
	const OPTION_TYPE     = 'type';
	
	protected static $readers = [];
	
	/**
	 * Create a config from the file
	 * 
	 * @param string $path    Path of a config
	 * @param bool   $readOnly Created config must be locked to a read only
	 * @param array  $options Options for a reader
	 * 
	 * @return ConfigInterface
	 */
	public static function createFromFile($path, array $options = []) {
		// Config's type resolve
		if (isset($options[self::OPTION_TYPE])) {
			$type = (string) $options[self::OPTION_TYPE];
			unset($options[self::OPTION_TYPE]);
		} else {
			$type = pathinfo($path, PATHINFO_EXTENSION);
		}
		
		return self::createFromSource(self::readFile($path), $type, $options);
	}
	
	/**
	 * Create a config from the source
	 * 
	 * @param string $source   Source of a config
	 * @param string $type     Type of a config
	 * @param bool   $readOnly Created config must be locked to a read only
	 * @param array  $options  Options for a reader
	 * 
	 * @return ConfigInterface
	 */
	public static function createFromSource($source, $type, array $options = []) {
		if (isset(self::$readers[$type])) {
			$reader = self::$readers[$type];
		} else {
			$class  = self::READERS_NAMESPACE . '\\' . ucfirst($type);
			$reader = new $class();

			if (! $reader instanceof ReaderInterface) {
				throw new InvalidReader('Reader must implement the "ReaderInterface"');
			}
			
			self::$readers[$type] = $reader;
		}
		
		// Read only lock resolve
		if (isset($options[self::OPTION_READONLY])) {
			$readOnly = (bool) $options[self::OPTION_READONLY];
			unset($options[self::OPTION_READONLY]);
		} else {
			$readOnly = true;
		}
		
		return self::create($reader->parse($source, $options), $readOnly);
	}
	
	/**
	 * Create a config from an array
	 * 
	 * @param  array $source   Source of a config
	 * @param  bool  $readOnly Lock a created config
	 * 
	 * @return ConfigInterface
	 */
	public static function create(array $source = null, $readOnly = true) {
		return new Config($source, $readOnly);
	}
	
	/**
	 * Read a config file
	 * 
	 * @param  string $path
	 * @return string
	 * @throws UnableToRead
	 */
	protected static function readFile($pathRaw) {
		$path = realpath($pathRaw);
		
		if ($path === false) {
			throw new UnableToRead('File "' . $pathRaw . '" not found or inaccessible');
		}
		
		$content = file_get_contents($path);
		
		if ($content === false) {
			throw new UnableToRead('Unable to read the file "' . $path . '"');
		}
		
		return $content;
	}
	
}