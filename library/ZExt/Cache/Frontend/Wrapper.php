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

namespace ZExt\Cache\Frontend;

use ZExt\Cache\Backend\BackendInterface;
use ZExt\Cache\Backend\TaggableInterface;
use ZExt\Cache\Frontend\Exceptions\NoTagsSupported;

/**
 * Namespaced cache frontend
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Frontend
 * @author     Mike.Mirten
 * @version    1.0
 */
class Wrapper extends FrontendAbstract {
	
	/**
	 * Constructor
	 * 
	 * @param BackendInterface $backend
	 * @param string           $namespace
	 */
	public function __construct(BackendInterface $backend = null, $namespace = null) {
		if ($backend !== null) {
			$this->setBackend($backend);
		}
		
		if ($namespace !== null) {
			$this->setNamespace($namespace);
		}
	}
	
	/**
	 * Store the data
	 * 
	 * @param  string         $id
	 * @param  mixed          $data
	 * @param  int            $lifetime seconds
	 * @param  string | array $tags
	 * @return bool
	 * @throws NoTagsSupported
	 */
	public function set($id, $data, $lifetime = null, $tags = null) {
		$backend = $this->getBackend();
		
		if ($tags !== null) {
			if (! $backend instanceof TaggableInterface) {
				throw new NoTagsSupported('Unable to set the data with the tag(s) due to a tags does not supported by the backend "' . get_class($backend) . '"');
			}
			
			if (is_array($tags)) {
				$tags = array_map([$this, 'prepareId'], $tags);
			} else {
				$tags = $this->prepareId($tags);
			}
		}
		
		$id = $this->prepareId($id);
		
		if ($lifetime === null) {
			$lifetime = $this->_defaultLifetime;
		}
		
		return $backend->set($id, $data, $lifetime, $tags);
	}
	
	/**
	 * Set the many of the data
	 * 
	 * @param  array          $data     [id => data]
	 * @param  int            $lifetime seconds
	 * @param  string | array $tags
	 * @return Wrapper
	 * @throws NoTagsSupported
	 */
	public function setMany(array $data, $lifetime = null, $tags = null) {
		$backend = $this->getBackend();
		
		if ($tags !== null) {
			if (! $backend instanceof TaggableInterface) {
				throw new NoTagsSupported('Unable to set the data with the tag(s) due to the tags does not supported by the backend "' . get_class($backend) . '"');
			}
			
			if (is_array($tags)) {
				$tags = array_map([$this, 'prepareId'], $tags);
			} else {
				$tags = $this->prepareId($tags);
			}
		}
		
		$ids  = array_map([$this, 'prepareId'], array_keys($data));
		$data = array_combine($ids, array_values($data));
		
		if ($lifetime === null) {
			$lifetime = $this->_defaultLifetime;
		}
		
		return $backend->setMany($data, $lifetime, $tags);
	}
	
	/**
	 * Fetch the data
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		return $this->getBackend()->get($this->prepareId($id));
	}
	
	/**
	 * Fetch the many of the data
	 * 
	 * @param  array $id
	 * @return array
	 */
	public function getMany(array $id) {
		$preparedIds = array_map([$this, 'prepareId'], $id);
		
		$result = $this->getBackend()->getMany($preparedIds);
		
		if (empty($result)) {
			return $result;
		}
		
		if ($this->_namespace !== null) {
			$ids = array_intersect_key(array_combine($preparedIds, $id), $result);
			return array_combine(array_values($ids), array_values($result));
		}
		
		return $result;
	}
	
	/**
	 * Fech the many of a data by the tag(s)
	 * 
	 * @param  string | array $tags
	 * @param  bool           $byIntersect
	 * @return array
	 * @throws NoTagsSupported
	 */
	public function getByTag($tags, $byIntersect = false) {
		$backend = $this->getBackend();
		
		if (! $backend instanceof TaggableInterface) {
			throw new NoTagsSupported('Unable to fetch a data by the tag(s) due to the tags does not supported by the backend "' . get_class($backend) . '"');
		}
		
		if (is_array($tags)) {
			$tags = array_map([$this, 'prepareId'], $tags);
		} else {
			$tags = $this->prepareId($tags);
		}
		
		$result = $backend->getByTag($tags, $byIntersect);
		
		if (empty($result) || $this->_namespace === null) {
			return $result;
		}
		
		$processedIds = [];
		$namespaceLen = strlen($this->_namespace) + 1;
		
		foreach (array_keys($result) as $id) {
			$processedIds[] = substr($id, $namespaceLen);
		}
		
		return array_combine($processedIds, array_values($result));
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function has($id) {
		return $this->getBackend()->has($this->prepareId($id));
	}
	
	/**
	 * Remove the data
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		return $this->getBackend()->remove($this->prepareId($id));
	}
	
	/**
	 * Remove the many of the data
	 * 
	 * @param  array $ids
	 * @return bool
	 */
	public function removeMany(array $ids) {
		$ids = array_map([$this, 'prepareId'], $ids);
		
		return $this->getBackend()->removeMany($ids);
	}
	
	/**
	 * Remove the many of a data by the tag(s)
	 * 
	 * @param  string | array $tags
	 * @param  bool           $byIntersect
	 * @return bool
	 */
	public function removeByTag($tags, $byIntersect = false) {
		$backend = $this->getBackend();
		
		if (! $backend instanceof TaggableInterface) {
			throw new NoTagsSupported('Unable to remove a data by the tag(s) due to the tags does not supported by the backend "' . get_class($backend) . '"');
		}
		
		if (is_array($tags)) {
			$tags = array_map([$this, 'prepareId'], $tags);
		} else {
			$tags = $this->prepareId($tags);
		}
		
		return $backend->removeByTag($tags, $byIntersect);
	}
	
	/**
	 * Increment the data
	 * 
	 * @param  string $id
	 * @param  int $value
	 * @return bool
	 */
	public function inc($id, $value = 1) {
		$id = $this->prepareId($id);
		
		return $this->getBackend()->inc($id, $value);
	}
	
	/**
	 * Decrement the data
	 * 
	 * @param  string $id
	 * @param  int $value
	 * @return bool
	 */
	public function dec($id, $value = 1) {
		$id = $this->prepareId($id);
		
		return $this->getBackend()->dec($id, $value);
	}
	
	/**
	 * Prepare the ID
	 * 
	 * @param  string $id
	 * @return string
	 */
	protected function prepareId($id) {
		if (! is_scalar($id)) {
			$id = json_encode($id);
		}
		
		if ($this->_namespace === null) {
			return $id;
		}
		
		return $this->_namespace . '_' . $id;
	}
	
	public function __set($name, $value) {
		$this->set($name, $value);
	}
	
	public function __get($name) {
		return $this->get($name);
	}
	
	public function __isset($name) {
		return $this->has($name);
	}
	
	public function __unset($name) {
		$this->remove($name);
	}
	
}