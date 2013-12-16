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

use ZExt\Profiler\ProfileableInterface,
    ZExt\Profiler\ProfileableTrait;

/**
 * Profiling ability decorator
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Decorators
 * @author     Mike.Mirten
 * @version    1.0
 */
class Profileable extends DecoratorAbstract implements ProfileableInterface {
	
	use ProfileableTrait;
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		$event  = $this->getProfiler()->startRead('Get: ' . $id);
		$result = $this->getBackend()->get($id);
		
		if ($result === null) {
			$event->stopNotice();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $id
	 * @return array
	 */
	public function getMany(array $ids) {
		$event  = $this->getProfiler()->startRead('Get (' . count($ids) . '): ' . implode(', ', $ids));
		$result = $this->getBackend()->getMany($ids);
		
		if (empty($result)) {
			$event->stopNotice();
		} else {
			$event->stopSuccess();
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
		$event  = $this->getProfiler()->startWrite('Set: ' . $id);
		$result = $this->getBackend()->set($id, $data, $lifetime);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array $data
	 * @param  int   $lifetime
	 * @return bool
	 */
	public function setMany(array $data, $lifetime = 0) {
		$ids    = array_keys($data);
		$event  = $this->getProfiler()->startWrite('Set (' . count($ids) . '): ' . implode(', ', $ids));
		$result = $this->getBackend()->setMany($data, $lifetime);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function has($id) {
		$event  = $this->getProfiler()->startRead('Has: ' . $id);
		$result = $this->getBackend()->has($id);
		
		if ($result === false) {
			$event->stopNotice();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		$event  = $this->getProfiler()->startDelete('Remove: ' . $id);
		$result = $this->getBackend()->remove($id);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $id
	 * @return bool
	 */
	public function removeMany(array $ids) {
		$event  = $this->getProfiler()->startRead('Remove (' . count($ids) . '): ' . implode(', ', $ids));
		$result = $this->getBackend()->removeMany($ids);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function inc($id, $value = 1) {
		$event  = $this->getProfiler()->startWrite('Inc: ' . $id);
		$result = $this->getBackend()->inc($id, $value);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function dec($id, $value = 1) {
		$event  = $this->getProfiler()->startWrite('Dec: ' . $id);
		$result = $this->getBackend()->dec($id, $value);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
}