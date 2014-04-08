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

use Traversable;

/**
 * Standart functions library
 * 
 * @category   ZExt
 * @package    Components
 * @subpackage Options
 * @author     Mike.Mirten
 * @version    1.0
 */
class Std {
	
	/**
	 * Recursively iterator to array converter
	 * 
	 * @param  Traversable $iterator
	 * @return array
	 */
	static function iteratorToArray(Traversable $iterator) {
		$array = [];
		
		foreach ($iterator as $key => $value) {
			if ($value instanceof Traversable) {
				$value = self::iteratorToArray($value);
			}
			
			$array[$key] = $value;
		}
		
		return $array;
	}
	
}