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

use ZExt\Config\Exceptions\UnableToRead,
    ZExt\Config\Exceptions\InvalidIniSection,
    ZExt\Config\Exceptions\InvalidIniKey;

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
	
	/**
	 * Create config from json file
	 * 
	 * @param  string $path
	 * @param  bool  $readOnly
	 * @return ConfigInterface | Bare config instance with chained properties
	 */
	public static function createFromJsonFile($path, $readOnly = true) {
		$json   = static::readFile($path);
		$config = static::parseJson($json);
		
		return static::create($config, $readOnly);
	}
	
	/**
	 * Create config from ini file
	 * 
	 * @param  string         $path
	 * @param  string | array $section
	 * @param  bool           $readOnly
	 * @return ConfigInterface | Bare config instance with chained properties
	 */
	public static function createFromIniFile($path, $section = null, $readOnly = true) {
		$source = static::readFile($path);
		$config = static::parseIni($source, $section);
		
		return static::create($config, $readOnly);
	}
	
	/**
	 * Create config from json string
	 * 
	 * @param  string $source
	 * @param  bool  $readOnly
	 * @return ConfigInterface | Bare config instance with chained properties
	 */
	public static function createFromJson($json, $readOnly = true) {
		$config = static::parseJson($json);
		
		return static::create($config, $readOnly);
	}
	
	/**
	 * Create config from ini string
	 * 
	 * @param  string         $source
	 * @param  string | array $section
	 * @param  bool           $readOnly
	 * @return ConfigInterface | Bare config instance with chained properties
	 */
	public static function createFromIni($source, $section = null, $readOnly = true) {
		$config = static::parseIni($source, $section);
		
		return static::create($config, $readOnly);
	}
	
	/**
	 * Create a config's instance
	 * 
	 * @param  array $source
	 * @param  bool  $readOnly
	 * @return ConfigInterface | Bare config instance with chained properties
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
	protected static function readFile($path) {
		$path = realpath($path);
		
		if ($path === false) {
			throw new UnableToRead('File "' . $path . '" wasn\'t found or inaccessible');
		}
		
		$content = file_get_contents($path);
		
		if ($content === false) {
			throw new UnableToRead('Unable to read the file "' . $path . '"');
		}
		
		return $content;
	}
	
	/**
	 * Parse json source
	 * 
	 * @param  string $source
	 * @return array
	 */
	public static function parseJson($source) {
		return json_decode($source);
	}
	
	/**
	 * Parse ini source
	 * 
	 * @param  string         $source
	 * @param  string | array $section
	 * @return array
	 */
	public static function parseIni($source, $section = null) {
		// No section(s) was specified
		if ($section === null) {
			$dataRaw = parse_ini_string($source);
		} else {
			$sections = parse_ini_string($source, true);
			
			// Many of sections
			if (is_array($section)) {
				$data = [];
				
				foreach ($section as $part) {
					$dataRaw = static::getIniSection($sections, $part);
					$data[]  = static::parseIniData($dataRaw);
				}
				
				return call_user_func_array('array_replace_recursive', $data);
			}
			// Single section
			else {
				$dataRaw = static::getIniSection($sections, $section);
			}
		}
		
		return static::parseIniData($dataRaw);
	}
	
	/**
	 * Get the section of a section's set
	 * 
	 * @param  array  $sections
	 * @param  string $section
	 * @return array
	 * @throws InvalidIniSection
	 */
	protected static function getIniSection($sections, $section) {
		if (isset($sections[$section])) {
			return $sections[$section];
		}
		
		foreach (array_keys($sections) as $part) {
			$colon = strpos($part, ':');

			if ($colon === false) continue;

			$successor = trim(substr($part, 0, $colon));
			$parent    = trim(substr($part, $colon + 1));

			if (! isset($successor[0], $parent[0])) {
				throw new InvalidIniSection('Invalid definition of the section "' . $part . '"');
			}
			
			if ($successor !== $section) continue;
			
			return array_replace(self::getIniSection($sections, $parent), $sections[$part]);
		}
		
		throw new InvalidIniSection('Section "' . $section . '" wasn\'t found');
	}
	
	/**
	 * Parse a raw ini data
	 * 
	 * @param  array $dataRaw
	 * @return array
	 */
	protected static function parseIniData($dataRaw) {
		$data = [];
		
		foreach ($dataRaw as $key => $value) {
			$data = array_replace_recursive($data, static::parseIniKey($key, $value));
		}
		
		return $data;
	}
	
	/**
	 * Parse a key of an ini key-value pair
	 * 
	 * @param  string $key
	 * @param  string $value
	 * @return mixed
	 * @throws InvalidIniKey
	 */
	protected static function parseIniKey($key, $value) {
		$point = strpos($key, '.');
		
		if ($point === false) {
			if (is_numeric($value)) {
				$valueOrigin = trim($value);
				
				if (strpos($value, '.') === false) {
					$value = (int) $value;
				} else {
					$value = (float) $value;
				}
				
				// Overflow checking
				if ($valueOrigin !== (string) $value) {
					$value = $valueOrigin;
				}
			}
			
			return [$key => $value];
		} else {
			$keyCurrent = trim(substr($key, 0, $point));
			$keyRemains = trim(substr($key, $point + 1));
			
			if (! isset($keyCurrent[0], $keyRemains[0])) {
				throw new InvalidIniKey('Invalid definition of the key "' . $key . '"');
			}
			
			return [$keyCurrent => self::parseIniKey($keyRemains, $value)];
		}
	}
	
}