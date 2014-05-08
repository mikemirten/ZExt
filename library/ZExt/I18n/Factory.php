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

namespace ZExt\I18n;

use ZExt\Components\Std;
use ZExt\Di\LocatorInterface;

use ZExt\I18n\Exceptions\InvalidOptions;
use ZExt\I18n\Plugin\BBCodes;

use Traversable;

/**
 * Translator
 * 
 * @category   ZExt
 * @package    I18n
 * @subpackage Translator
 * @author     Mike.Mirten
 * @version    1.0
 */
class Factory {
	
	const NAMESPACE_RESOURCE = 'ZExt\I18n\Resource';
	const NAMESPACE_CACHE    = 'ZExt\I18n\Cache';
	
	/**
	 * Crteate the translator by the params
	 * 
	 * @param  Traversable | array $options
	 * @param  LocatorInterface    $locator
	 * @return Translator
	 * @throws InvalidOptions
	 */
	static public function create($params, LocatorInterface $locator = null) {
		if (! is_array($params) && ! $params instanceof Traversable) {
			throw new InvalidOptions('Options must be an array or a Traversable implementation');
		}
		
		if ($params instanceof Traversable) {
			$params = Std::iteratorToArray($params);
		}
		
		$translator = new Translator();
		
		foreach ($params as $param => $value) {
			if ($param === 'cacheStrategy') {
				$class = self::NAMESPACE_CACHE . '\Strategy' . ucfirst($value);
				
				$translator->setCache(new $class($locator));
				unset($params[$param]);
				continue;
			}
			
			if ($param === 'resources') {
				if (! is_array($value) && ! $value instanceof Traversable) {
					throw new InvalidOptions('Resources definition must be an array or a Traversable implementation');
				}
				
				$translator->setResources(self::createResources($value));
				unset($params[$param]);
				continue;
			}
			
			if ($param === 'bbcodes') {
				if ($value) {
					$bbcodes = new BBCodes();
					
					if (isset($params['paramsPattern'])) {
						$bbcodes->setParamsPattern($params['paramsPattern']);
					}
					
					$translator->addPlugin($bbcodes, 'bbcodes');
				}
				
				unset($params[$param]);
			}
		}
		
		$translator->setOptions($params, false, false);
		
		return $translator;
	}
	
	/**
	 * Create the resources by the definitions
	 * 
	 * @param  array $definitions
	 * @return Resource\ResourceInterface[]
	 * @throws InvalidOptions
	 */
	static protected function createResources($definitions) {
		$resources = [];
		
		foreach ($definitions as $name => $options) {
			if (! isset($options['type'])) {
				throw new InvalidOptions('Type of resource must be specified');
			}
			
			$class = self::NAMESPACE_RESOURCE . '\\' . ucfirst($options['type']);
			unset($options['type']);
			
			$resources[$name] = new $class($options);
		}
		
		return $resources;
	}
	
}