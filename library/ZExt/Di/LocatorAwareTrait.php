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

namespace ZExt\Di;

use ZExt\Di\Exception\NoLocator;

trait LocatorAwareTrait {
	
	/**
	 * Services locator
	 *
	 * @var LocatorInterface
	 */
	private $_locator;
	
	/**
	 * Set a services locator
	 * 
	 * @param LocatorInterface $locator
	 */
	public function setLocator(LocatorInterface $locator) {
		$this->_locator = $locator;
	}
	
	/**
	 * Get a services' locator
	 * 
	 * @return LocatorInterface
	 */
	public function getLocator() {
		if ($this->_locator === null) {
			throw new NoLocator('Hasn\'t been locator provided');
		}
		
		return $this->_locator;
	}
	
	/**
	 * Has a services' locator
	 * 
	 * @return boolean
	 */
	public function hasLocator() {
		return $this->_locator !== null;
	}
	
}