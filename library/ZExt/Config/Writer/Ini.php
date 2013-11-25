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
namespace ZExt\Config\Writer;

use Traversable;

/**
 * Ini config writer
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage Writer
 * @author     Mike.Mirten
 * @version    1.0
 */
class Ini implements WriterInterface {
	
	const OPTION_SECTIONS  = 'sections';
	const OPTION_DELIMITER = 'delimiter';
	const OPTION_ALIGMENT  = 'aligment';
	
	const DEFAULT_DELIMITER = '.';
	
	/**
	 * Assemble the config
	 * 
	 * Options:
	 * "sections"  => Use the first config's layer as a sections
	 * "delimiter" => Specify the delimiter in the parameters names
	 * "aligment"  => Align a key/value pairs by the keys length
	 * 
	 * @param  Traversable $config
	 * @param  array $options
	 * @return string
	 */
	public function assemble(Traversable $config, array $options = []) {
		// Sections resolve
		$sections = isset($options[self::OPTION_SECTIONS])
			? (bool) $options[self::OPTION_SECTIONS]
			: false;
		
		// Delimiter resolve
		$delimiter = isset($options[self::OPTION_DELIMITER])
			? (string) $options[self::OPTION_DELIMITER]
			: self::DEFAULT_DELIMITER;
		
		// Aligment resolve
		$aligment = isset($options[self::OPTION_ALIGMENT])
			? (string) $options[self::OPTION_ALIGMENT]
			: false;
		
		return $this->assembleConfig($config, $delimiter, $aligment, $sections);
	}
	
	/**
	 * Assemble the config
	 * 
	 * @param  Traversable $config
	 * @param  string      $delimiter
	 * @param  bool        $aligment
	 * @param  bool        $sections
	 * @return string
	 */
	protected function assembleConfig(Traversable $config, $delimiter, $aligment, $sections = false) {
		$rows = [];
		
		if ($sections) {
			foreach ($config as $section => $data) {
				if ($data instanceof Traversable) {
					$rows[] = '[' . $section . ']';
					$rows[] = $this->assembleConfig($data, $delimiter, $aligment) . PHP_EOL;
				}
			}
		} else {
			$rawConfig = $this->iteratorToArray($config, $delimiter);
			
			if ($aligment) {
				$maxLength = 0;
				
				foreach (array_keys($rawConfig) as $key) {
					$keyLength = strlen($key);
					
					if ($maxLength < $keyLength) {
						$maxLength = $keyLength;
					}
				}
				
				foreach ($rawConfig as $key => $value) {
					$keyLength = strlen($key);
					
					if ($keyLength < $maxLength) {
						$key .= str_repeat(' ', $maxLength - $keyLength);
					}
					
					$rows[] = $key . ' = ' . $value;
				}
			} else {
				foreach ($rawConfig as $key => $value) {
					$rows[] = $key . ' = ' . $value;
				}
			}
		}
		
		return implode(PHP_EOL, $rows);
	}
	
	/**
	 * Assemble an one-dimension array from an recursive traversable config
	 * 
	 * @param  Traversable $config
	 * @return array
	 */
	protected function iteratorToArray(Traversable $config, $delimiter) {
		$rows = [];
		
		foreach ($config as $key => $value) {
			if ($value instanceof Traversable) {
				foreach ($this->iteratorToArray($value, $delimiter) as $subkey => $subvalue) {
					$rows[$key . $delimiter . $subkey] = $subvalue;
				}
			} else {
				$rows[$key] = $value;
			}
		}
		
		return $rows;
	}
	
}