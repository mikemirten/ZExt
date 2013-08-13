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

/**
 * Helper interface
 * 
 * @category   ZExt
 * @package    Helper
 * @subpackage Broker
 * @author     Mike.Mirten
 * @version    1.0
 */
interface HelperInterface {
	
	/**
	 * Set a parent
	 * 
	 * @param object $parent
	 */
	public function setParent($parent);
	
	/**
	 * Get a parent
	 * 
	 * @return object
	 */
	public function getParent();
	
	/**
	 * Was a parent specified
	 * 
	 * @return bool
	 */
	public function hasParent();
	
}