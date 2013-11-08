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
 * String length validator
 * 
 * @category   ZExt
 * @package    Validator
 * @subpackage StringLength
 * @author     Mike.Mirten
 * @version    1.0
 */
class StringLength extends ValidatorAbstract {
	
	/**
	 * Max and min length
	 *
	 * @var int
	 */
	protected $lengthMin, $lengthMax;
	
	/**
	 * String encoding
	 *
	 * @var string
	 */
	protected $encoding;
	
	/**
	 * Set the minimum length
	 * 
	 * @param int $value
	 */
	public function setMin($value) {
		$this->lengthMin = (int) $value;
	}
	
	/**
	 * Set the maximum length
	 * 
	 * @param int $value
	 */
	public function setMax($value) {
		$this->lengthMax = (int) $value;
	}
	
	/**
	 * Set the encoding
	 * 
	 * @param string $encoding
	 */
	public function setEncoding($encoding) {
		$this->encoding = (string) $encoding;
	}
	
	/**
	 * Is the value valid
	 * 
	 * @param  mixed $value
	 * @return bool
	 */
	public function isValid($value) {
		if (! is_string($value)) {
			$this->addMessage('Value must be a string');
		
			return false;
		}
		
		if ($this->lengthMin !== null && $this->lengthMax !== null && $this->lengthMin >= $this->lengthMax) {
			throw new ValidationFailure('Min length cannot be equal or greater than max length');
		}
		
		if ($this->encoding === null) {
			$length = mb_strlen($value);
		} else {
			$length = mb_strlen($value, $this->encoding);
		}
		
		if ($this->lengthMin !== null && $length < $this->lengthMin) {
			$this->addMessage('Must be not less than %s symbols', $this->lengthMin);
		
			return false;
		}
		
		if ($this->lengthMax !== null && $length > $this->lengthMax) {
			$this->addMessage('Must be not more than %s symbols', $this->lengthMax);
		
			return false;
		}
		
		return true;
	}
	
}