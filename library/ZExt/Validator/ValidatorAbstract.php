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

use ZExt\Di\LocatorAwareInterface;
use ZExt\Di\LocatorAwareTrait;
use ZExt\Components\OptionsTrait;
use Traversable;

/**
 * Validator abstract
 * 
 * @category   ZExt
 * @package    Validator
 * @subpackage Abstract
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class ValidatorAbstract implements ValidatorInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	use OptionsTrait;
	
	/**
	 * Validation messages
	 *
	 * @var array
	 */
	private $_messages = [];
	
	/**
	 * Constructor
	 * 
	 * @param array | Traversable $options
	 */
	public function __construct($options = null) {
		if (is_array($options) || $options instanceof Traversable) {
			$this->setOptions($options);
		}
	}
	
	/**
	 * Add a message about the validation fail
	 * 
	 * @param string $message
	 * @param mixed  $arg1
	 * @param mixed  $arg2
	 * @param mixed  $argN
	 */
	protected function addMessage($message) {
		$hash = crc32(json_encode(func_get_args()));
		
		if (isset($this->_messages[$hash])) {
			return;
		}
		
		if (func_num_args() > 1) {
			$this->_messages[$hash] = call_user_func_array('sprintf', func_get_args());
		} else {
			$this->_messages[$hash] = (string) $message;
		}
	}
	
	/**
	 * Get validation messages
	 * 
	 * @return array
	 */
	public function getMessages() {
		return array_values($this->_messages);
	}
	
	/**
	 * Get the service
	 * 
	 * @param  string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->getLocator()->get($name);
	}
	
}