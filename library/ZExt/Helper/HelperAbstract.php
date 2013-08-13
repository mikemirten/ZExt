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

namespace ZExt\Helper;

use ZExt\Di\LocatorAwareTrait,
    ZExt\Di\LocatorAwareInterface;

use ZExt\Helper\HelpersBrokerAwareInterface,
    ZExt\Helper\HelpersBrokerAwareTrait;

use ZExt\Session\SessionAwareInterface,
    ZExt\Session\SessionAwareTrait;

use ZExt\Helper\Exceptions\WrongParent;

/**
 * Helper abstract
 * 
 * @category   ZExt
 * @package    Helper
 * @subpackage Broker
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
abstract class HelperAbstract implements HelperInterface, LocatorAwareInterface, HelpersBrokerAwareInterface, SessionAwareInterface {
	
	use LocatorAwareTrait;
	use HelpersBrokerAwareTrait;
	use SessionAwareTrait;
	
	/**
	 * Caller of helper
	 *
	 * @var object
	 */
	private $_parent;
	
	/**
	 * Set a parent
	 * 
	 * @param  object $parent
	 * @return HelperAbstract
	 * @throws WrongParent
	 */
	public function setParent($parent) {
		if (! is_object($parent)) {
			throw new WrongParent('Parent of a helper must be an object, "' . gettype($parent) . '" was given');
		}
		
		$this->_parent = $parent;
		
		return $this;
	}
	
	/**
	 * Get a parent
	 * 
	 * @return object
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * Was a parent specified
	 * 
	 * @return bool
	 */
	public function hasParent() {
		return $this->_parent !== null;
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
	
	/**
	 * Has the service
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->getLocator()->has($name);
	}
	
}