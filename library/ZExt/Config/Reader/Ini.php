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
namespace ZExt\Config\Reader;

use ZExt\Config\Reader\Exceptions\InvalidIniSection,
    ZExt\Config\Reader\Exceptions\InvalidIniKey,
	ZExt\Config\Reader\Exceptions\OptionsError;

/**
 * Ini config reader
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage Reader
 * @author     Mike.Mirten
 * @version    1.0.1
 */
class Ini implements ReaderInterface {
	
	// Parse options
	const OPTION_MODE        = 'mode';
	const OPTION_SECTION     = 'section';
	const OPTION_INHERITANCE = 'inheritance';
	
	// Sections treat modes
	const SECTIONS_IGNORE = 'ignore';
	const SECTIONS_ROOT   = 'root';
	const SECTIONS_PICK   = 'pick';
	
	/**
	 * Parse the ini source
	 * 
	 * Options:
	 * "mode"        => Sections treat mode
	 * "section"     => Name of a section or an array of names (only for the "pick" mode)
	 * "inheritance" => Sections inheritance over ":"
	 * 
	 * Modes:
	 * "ignore": Sections will be ignored (default)
	 * "root"  : Sections will be used as the root of a config
	 * "pick"  : Only selected section(s) will be used; the "section" option required with the mode
	 * 
	 * @param  string $source
	 * @param  array  $options
	 * @return array
	 */
	public function parse($source, array $options = []) {
		$mode = isset($options[self::OPTION_MODE])
			? (string) $options[self::OPTION_MODE]
			: self::SECTIONS_IGNORE;
		
		// Parse with sections ignore
		if ($mode === self::SECTIONS_IGNORE) {
			return $this->parseData(parse_ini_string($source));
		}
		
		// Parse with sections as the root of an array
		if ($mode === self::SECTIONS_ROOT) {
			return array_map([$this, 'parseData'], parse_ini_string($source, true));
		}
		
		// Parse with section(s) specify
		if ($mode === self::SECTIONS_PICK) {
			if (! isset($options[self::OPTION_SECTION])) {
				throw new OptionsError('Section(s) must be spicified for the "pick" mode; you should use the "section" option');
			}
			
			$sections    = parse_ini_string($source, true);
			$inheritance = isset($options[self::OPTION_INHERITANCE])
				? (bool) $options[self::OPTION_INHERITANCE]
				: false;
			
			// Many of sections
			if (is_array($options[self::OPTION_SECTION])) {
				$data = [];

				foreach ($options[self::OPTION_SECTION] as $part) {
					$dataRaw = $this->getSection($sections, $part, $inheritance);
					$data[]  = $this->parseData($dataRaw);
				}

				return call_user_func_array('array_replace_recursive', $data);
			}
			
			// Single section
			$dataRaw = $this->getSection($sections, $options[self::OPTION_SECTION], $inheritance);
			return $this->parseData($dataRaw);
		}
		
		throw new OptionsError('Unknown mode "' . $mode . '"');
	}
	
	/**
	 * Get the section of a section's set
	 * 
	 * @param  array  $sections
	 * @param  string $section
	 * @param  bool   $inheritance
	 * @return array
	 * @throws InvalidIniSection
	 */
	protected function getSection($sections, $section, $inheritance = false) {
		if (isset($sections[$section])) {
			return $sections[$section];
		}
		
		if ($inheritance) {
			foreach (array_keys($sections) as $part) {
				$colon = strpos($part, ':');

				if ($colon === false) {
					continue;
				}

				$successor = trim(substr($part, 0, $colon));
				$parent    = trim(substr($part, $colon + 1));

				if (! isset($successor[0], $parent[0])) {
					throw new InvalidIniSection('Invalid definition of the section "' . $part . '"');
				}

				if ($successor !== $section) {
					continue;
				}

				return array_replace(
					$this->getSection($sections, $parent),
					$sections[$part]
				);
			}
		}
		
		throw new InvalidIniSection('Section "' . $section . '" wasn\'t found');
	}
	
	/**
	 * Parse a raw ini data
	 * 
	 * @param  array $dataRaw
	 * @return array
	 */
	protected function parseData($dataRaw) {
		$data = [];
		
		foreach ($dataRaw as $key => $value) {
			$data = array_replace_recursive($data, $this->parseKey($key, $value));
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
	protected function parseKey($key, $value) {
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
		}
		
		$keyCurrent = trim(substr($key, 0, $point));
		$keyRemains = trim(substr($key, $point + 1));

		if (! isset($keyCurrent[0], $keyRemains[0])) {
			throw new InvalidIniKey('Invalid definition of the key "' . $key . '"');
		}

		return [$keyCurrent => $this->parseKey($keyRemains, $value)];
	}
	
}