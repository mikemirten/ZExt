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
namespace ZExt\Components;

use ZExt\Config\ConfigInterface;
use ZExt\Components\Exceptions\InvalidOption;

use Traversable, ReflectionObject, ReflectionMethod;

/**
 * Inject the options throught a "set" methods and collect throught a "get" methods
 * 
 * @category   ZExt
 * @package    Components
 * @subpackage Options
 * @author     Mike.Mirten
 * @version    1.1
 */
trait OptionsTrait {
	
	/**
	 * Set the options
	 * 
	 * Converts the "underscore_options" to the "camelcaseOptions"
	 * 
	 * @param  array | Traversable $options
	 * @param  bool                $ignoreUnknown
	 * @param  bool                $arraysAsManyOfArgs
	 * @return object
	 * @throws InvalidOption
	 */
	public function setOptions($options, $ignoreUnknown = false, $arraysAsManyOfArgs = true) {
		if (! is_array($options) && ! $options instanceof Traversable) {
			throw new InvalidOption('Options must be represented as an array or a traversable implementation');
		}

		foreach ($options as $option => $value) {
			if (strpos($option, '_') !== false) {
				$option = ucwords(str_replace('_', ' ', $option));
				$option = lcfirst(str_replace(' ', '', $option));
			}

			if ($option === 'options') {
				continue;
			}
			
			$method = 'set' . $option;
			
			if (! method_exists($this, $method)) {
				if (! $ignoreUnknown) {
					$this->onUnknownOptionSet($option, $value);
				}
				
				continue;
			}

			if ($value instanceof ConfigInterface) {
				$value = $value->toArray();
			}

			if ($arraysAsManyOfArgs && is_array($value)) {
				call_user_func_array([$this, $method], $value);
			} else {
				$this->$method($value);
			}
		}

		return $this;
	}
	
	/**
	 * On unknown option set callback
	 * Can be overriden by user
	 * 
	 * @param string $option
	 * @param mixed  $value
	 */
	protected function onUnknownOptionSet($option, $value) {
		throw new InvalidOption('Unknown option "' . $option . '"');
	}
	
	/**
	 * Get the options
	 * 
	 * @return array
	 */
	public function getOptions() {
		$reflection = new ReflectionObject($this);
		$methods    = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
		
		$options = [];
		
		foreach ($methods as $method) {
			if ($method->getNumberOfRequiredParameters() > 0) {
				continue;
			}
			
			$name = $method->name;
			
			if (isset($name[3]) && $name !== 'getOptions' && substr($name, 0, 3) === 'get') {
				$optionName = lcfirst(substr($name, 3));
				$options[$optionName] = $method->invoke($this);
			}
		}
		
		return $options;
	}
	
}