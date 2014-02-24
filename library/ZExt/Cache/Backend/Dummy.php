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

namespace ZExt\Cache\Backend;

/**
 * Dummy (Null) backend adapter
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Backend
 * @author     Mike.Mirten
 * @version    1.1
 */
class Dummy extends BackendAbstract {
	
	/**
	 * Cache topology title
	 *
	 * @var string
	 */
	protected $topologyTitle = 'Dummy';
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		return null;
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $id
	 * @return array
	 */
	public function getMany(array $id) {
		return [];
	}
	
	/**
	 * Fetch a data from the cache by the tag(s)
	 * 
	 * @param  string | array $tag
	 * @param  bool           $byIntersect
	 * @return array
	 */
	public function getByTag($tags, $byIntersect = false) {
		return [];
	}
	
	/**
	 * Store the data into the cache
	 * 
	 * @param  string         $id       ID of the stored data
	 * @param  mixed          $data     Stored data
	 * @param  int            $lifetime Lifetime in seconds
	 * @param  string | array $tag
	 * @return bool
	 */
	public function set($id, $data, $lifetime = 0, $tags = null) {
		return true;
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array          $data
	 * @param  int            $lifetime
	 * @param  string | array $tag
	 * @return bool
	 */
	public function setMany(array $data, $lifetime = 0, $tags = null) {
		return true;
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		return true;
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function removeMany(array $ids) {
		return true;
	}
	
	/**
	 * Remove a data from the cache by the tag(s)
	 * 
	 * @param  string | array $tag
	 * @param  bool           $byIntersect
	 * @return bool
	 */
	public function removeByTag($tags, $byIntersect = false) {
		return true;
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $id
	 * @return bool
	 */
	public function has($id) {
		return false;
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function inc($id, $value = 1) {
		return 0;
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function dec($id, $value = 1) {
		return 0;
	}
	
	/**
	 * Flush all the cache data
	 * 
	 * @return bool
	 */
	public function flush() {
		return true;
	}
	
}