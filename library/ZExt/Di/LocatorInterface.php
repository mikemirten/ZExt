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

/**
 * A service locator interface
 */
interface LocatorInterface {
	
	const BEHAVIOUR_FAIL_EXCEPTION = 1;
	const BEHAVIOUR_FAIL_NULL      = 2;
	
	/**
	 * Get a service
	 * 
	 * @param  string $id            An id of a service
	 * @param  int    $failBehaviour On a service locate fail behaviour
	 * @return mixed
	 */
	public function get($id, $failBehaviour = self::BEHAVIOUR_FAIL_EXCEPTION);
	
	/**
	 * Has a service
	 * 
	 * @param  string $name An id of a service
	 * @return boolean
	 */
	public function has($id);
	
	/**
	 * Check for a service has been initialized
	 * 
	 * @param  string $name An id of a service
	 * @return boolean
	 */
	public function hasInitialized($id);
	
}