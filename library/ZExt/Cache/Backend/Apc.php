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

use ZExt\Cache\Backend\Exceptions\NoPhpExtension;

use ZExt\Topology\Descriptor;
use ZExt\Formatter\Memory;

/**
 * APC backend adapter
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Backend
 * @author     Mike.Mirten
 * @version    1.1
 */
class Apc extends BackendAbstract {
	
	public function __construct() {
		if (! extension_loaded('apc')) {
			throw new NoPhpExtension('The apc php extension required for the backend');
		}
	}

	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		$result = apc_fetch($id);
		
		if ($result !== false) {
			return $result;
		}
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $id
	 * @return array
	 */
	public function getMany(array $id) {
		return apc_fetch($id);
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
		return apc_store($id, $data, $lifetime);
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array $data
	 * @param  int   $lifetime
	 * @return bool
	 */
	public function setMany(array $data, $lifetime = 0) {
		return apc_store($data, null, $lifetime);
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		return apc_delete($id);
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function removeMany(array $ids) {
		$result = true;
		
		foreach ($ids as $id) {
			if (! apc_delete($id)) {
				$result = false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $id
	 * @return bool
	 */
	public function has($id) {
		return apc_exists($id);
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function inc($id, $value = 1) {
		return apc_inc($id, $value);
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function dec($id, $value = 1) {
		return apc_dec($id, $value);
	}
	
	/**
	 * Flush all the cache data
	 * 
	 * @return bool
	 */
	public function flush() {
		return apc_clear_cache('user');
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor      = new Descriptor('APC', self::TOPOLOGY_BACKEND);
		$memoryFormatter = new Memory();
				
		$info = apc_sma_info(true);
		
		$limit  = $info['seg_size'] * $info['num_seg'];
		$used   = $limit - $info['avail_mem'];
		$filled = $used / $limit * 100;
		
		$descriptor->id     = $this->getTopologyId();
		$descriptor->used   = $memoryFormatter->format($used);
		$descriptor->limit  = $memoryFormatter->format($limit);
		$descriptor->filled = round($filled, 2) . '%';
		
		return $descriptor;
	}
	
}