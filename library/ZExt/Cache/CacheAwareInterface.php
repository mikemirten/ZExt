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

namespace ZExt\Cache;

use ZExt\Cache\Frontend\FactoryInterface;
use ZExt\Cache\Frontend\Wrapper;

/**
 * Cache aware interface
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Aware
 * @author     Mike.Mirten
 * @version    1.0
 */
interface CacheAwareInterface {
	
	const TIME_MINUTE = 60;
	const TIME_HOUR   = 3600;
	const TIME_DAY    = 86400;
	const TIME_WEEK   = 604800;
	
	/**
	 * Set the wrapper cache frontend
	 * 
	 * @param Wrapper $frontend
	 */
	public function setCacheFrontend(Wrapper $frontend);
	
	/**
	 * Get the wrapper cache frontend
	 * 
	 * @return Wrapper
	 */
	public function getCacheFrontend();
	
	/**
	 * Has the wrapper cache frontend
	 * 
	 * @return bool
	 */
	public function hasCacheFrontend();
	
	/**
	 * Set the cache frontends' factory
	 * 
	 * @param FactoryInterface $factory
	 */
	public function setCacheFrontendFactory(FactoryInterface $factory);
	
	/**
	 * Get the cache frontends' factory
	 * 
	 * @return FactoryInterface
	 */
	public function getCacheFrontendFactory();
	
	/**
	 * Has the cache frontends' factory
	 * 
	 * @return bool
	 */
	public function hasCacheFrontendFactory();
	
}