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

namespace ZExt\Cache\Backend\Decorators;

/**
 * Tags supporting ability decorator
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Decorators
 * @author     Mike.Mirten
 * @version    1.1
 */
class SerializerJson extends DecoratorAbstract {
	
	/**
	 * Cache topology title
	 *
	 * @var string
	 */
	protected $topologyTitle = 'Serializer Json';
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		$result = $this->getBackend()->get($id);
		
		if ($result !== null) {
			return json_decode($result);
		}
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $ids
	 * @return array
	 */
	public function getMany(array $ids) {
		$result = $this->getBackend()->getMany($ids);
		
		if (! empty($result)) {
			return array_map('json_decode', $result);
		}
		
		return $result;
	}
	
	/**
	 * Store the data into the cache
	 * 
	 * @param  string $id       ID of the stored data
	 * @param  mixed  $data     Stored data
	 * @param  int    $lifetime Lifetime in seconds
	 * @return bool
	 */
	public function set($id, $data, $lifetime = 0) {
		return $this->getBackend()->set($id, json_encode($data), $lifetime);
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array $data
	 * @param  int   $lifetime
	 * @return bool
	 */
	public function setMany(array $data, $lifetime = 0) {
		return $this->getBackend()->setMany(array_map('json_encode', $data), $lifetime);
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function has($id) {
		return $this->getBackend()->has($id);
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		return $this->getBackend()->remove($id);
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $ids
	 * @return bool
	 */
	public function removeMany(array $ids) {
		return $this->getBackend()->removeMany($ids);
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function inc($id, $value = 1) {
		return $this->getBackend()->inc($id, $value);
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function dec($id, $value = 1) {
		return $this->getBackend()->dec($id, $value);
	}
	
}