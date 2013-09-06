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

namespace ZExt\Validator;

use ZExt\Validator\Exceptions\ValidationFailure;

/**
 * In array value validator
 * 
 * @category   ZExt
 * @package    Validator
 * @subpackage InArray
 * @author     Mike.Mirten
 * @version    1.0
 */
class InArray extends ValidatorAbstract {
	
	/**
	 * Reference haystack
	 *
	 * @var array
	 */
	protected $haystack;
	
	/**
	 * Strict comparision
	 *
	 * @var bool
	 */
	protected $strict = false;
	
	/**
	 * Set the reference array
	 * 
	 * @param array $array
	 */
	public function setHaystack(array $haystack) {
		$this->haystack = $haystack;
	}
	
	/**
	 * Set the strict comparision
	 * 
	 * @param bool $strict
	 */
	public function setStrict($strict) {
		$this->strict = (bool) $strict;
	}
	
	/**
	 * Is the value valid
	 * 
	 * @param  mixed $value
	 * @return bool
	 */
	public function isValid($value) {
		if ($this->haystack === null) {
			throw new ValidationFailure('Reference array wasn\'t been suplied');
		}
		
		if (in_array($value, $this->haystack, $this->strict)) {
			return true;
		}
		
		$this->addMessage('Value does not exists in the list');
		
		return false;
	}
	
}