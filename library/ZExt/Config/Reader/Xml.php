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

use ZExt\Config\Reader\Exceptions\InvalidContent;
use SimpleXMLElement;

/**
 * XML config reader
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage Reader
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
class XMl implements ReaderInterface {

	/**
	 * Parse the source config into array
	 * 
	 * @param  string $source
	 * @param  array  $options
	 * @return array
	 */
	public function parse($source, array $options = null) {
		$result = simplexml_load_string($source);
		
		if ($result === false) {
			throw new InvalidContent('Unable to parse the XML content');
		}
		
		return $this->simpleXmlToArray($result);
	}
	
	/**
	 * COnvert the SimpleXMLElement instance into an array recursively
	 * 
	 * @param  SimpleXMLElement $xml
	 * @return array
	 */
	protected function simpleXmlToArray(SimpleXMLElement $xml) {
		$result = [];
		
		foreach ($xml as $element) {
			$name    = $element->getName();
			$content = get_object_vars($element);
			
			if (isset($content['@attributes'])){
				if (isset($content['@attributes']['value'])) {
					$result[$name] = $this->parseValue($content['@attributes']['value']);
					continue;
				}
				
				unset($content['@attributes']);
			}
			
			if (empty($content)) {
				$result[$name] = $this->parseValue($element);
				continue;
			}
			
			$result[$name] = $this->simpleXmlToArray($element);
		}
		
		return $result;
	}
	
	/**
	 * Parse a value
	 * 
	 * @param  string $value
	 * @return mixed
	 */
	public function parseValue($value) {
		$value = trim($value);
		
		if (is_numeric($value)) {
			$valueOrigin = $value;

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
		
		return $value;
	}
	
}