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

use ZExt\Cache\Backend\TaggableInterface;
use ZExt\Cache\Backend\BackendInterface;

use ZExt\Cache\Topology\TopologyInterface;
use ZExt\Topology\Descriptor;

/**
 * Tags supporting ability decorator
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Decorators
 * @author     Mike.Mirten
 * @version    1.1
 */
class Taggable extends DecoratorAbstract implements TaggableInterface {
	
	/**
	 * Tags holding backend
	 *
	 * @var BackendInterface 
	 */
	protected $tagsBackend;
	
	/**
	 * Constructor
	 * 
	 * @param BackendInterface $backend
	 */
	public function __construct(BackendInterface $backend = null, BackendInterface $tagHolderBackend = null) {
		if ($backend !== null) {
			$this->setBackend($backend);
		}
		
		if ($tagHolderBackend !== null) {
			$this->setTagHolderBackend($tagHolderBackend);
		}
	}
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		return $this->getBackend()->get($id);
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $ids
	 * @return array
	 */
	public function getMany(array $ids) {
		return $this->getBackend()->getMany($ids);
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
		if ($tags === null) {
			return $this->getBackend()->set($id, $data, $lifetime);
		}
		
		$tagBackend = $this->getTagHolderBackend();
		
		if (is_array($tags)) {
			$tags      = array_map([$this, 'prepareTag'], array_values($tags));
			$tagsData  = $tagBackend->getMany($tags);
			$tagsCount = count($tagsData, COUNT_RECURSIVE);
			
			if (empty($tagsData)) {
				$tagsData = array_fill_keys($tags, [$id]);
			} else {
				foreach ($tags as $tag) {
					if (empty($tagsData[$tag])) {
						$tagsData[$tag] = [$id];
					} else if (! in_array($id, $tagsData[$tag], true)) {
						$tagsData[$tag][] = $id;
					}
				}
			}
			
			if (count($tagsData, COUNT_RECURSIVE) !== $tagsCount) {
				$tagBackend->setMany($tagsData, $lifetime);
			}
		} else {
			$tag      = $this->prepareTag($tags);
			$tagData  = $tagBackend->get($tag);
			$tagCount = count($tagData);
			
			if (empty($tagData)) {
				$tagData = [$id];
			} else if (! in_array($id, $tagData, true)) {
				$tagData[] = $id;
			}
			
			if (count($tagData) !== $tagCount) {
				$tagBackend->set($tag, $tagData, 0);
			}
		}
		
		return $this->getBackend()->set($id, $data, $lifetime);
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array          $data
	 * @param  int            $lifetime
	 * @param  string | array $tags
	 * @return bool
	 */
	public function setMany(array $data, $lifetime = 0, $tags = null) {
		if ($tags === null) {
			return $this->getBackend()->setMany($data, $lifetime);
		}
		
		$tagBackend = $this->getTagHolderBackend();
		
		if (is_array($tags)) {
			$tags      = array_map([$this, 'prepareTag'], array_values($tags));
			$tagsData  = $tagBackend->getMany($tags);
			$tagsCount = count($tagsData, COUNT_RECURSIVE);
			
			if (empty($tagsData)) {
				$tagsData = array_fill_keys($tags, array_keys($data));
			} else {
				foreach ($tags as $tag) {
					if (empty($tagsData[$tag])) {
						$tagsData[$tag] = array_keys($data);
					} else {
						$tagsData[$tag] = array_merge(
							array_diff(array_keys($data), $tagsData[$tag]),
							$tagsData[$tag]
						);
					}
				}
			}
			
			if (count($tagsData, COUNT_RECURSIVE) !== $tagsCount) {
				$tagBackend->setMany($tagsData, $lifetime);
			}
		} else {
			$tag      = $this->prepareTag($tags);
			$tagData  = $tagBackend->get($tag);
			$tagCount = count($tagData);
			
			if (empty($tagData)) {
				$tagData = array_keys($data);
			} else {
				$tagData = array_merge(
					array_diff(array_keys($data), $tagData),
					$tagData
				);
			}
			
			if (count($tagData) !== $tagCount) {
				$tagBackend->set($tag, $tagData, 0);
			}
		}
		
		return $this->getBackend()->setMany($data, $lifetime);
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
	
	/**
	 * Fetch a data from the cache by the tag(s)
	 * 
	 * @param  string | array $tags
	 * @param  bool           $byIntersect
	 * @return array
	 */
	public function getByTag($tags, $byIntersect = false) {
		if (is_array($tags)) {
			$tags = array_map([$this, 'prepareTag'], array_values($tags));
		} else {
			$tags = $this->prepareTag($tags);
		}
		
		$tagData = $this->getTagData($tags, $byIntersect);
		
		if ($tagData === null) {
			return [];
		}
		
		return $this->getBackend()->getMany($tagData);
	}
	
	/**
	 * Remove a data from the cache by the tag(s)
	 * 
	 * @param  string | array $tags
	 * @param  bool           $byIntersect
	 * @return bool
	 */
	public function removeByTag($tags, $byIntersect = false) {
		if (is_array($tags)) {
			$tags = array_map([$this, 'prepareTag'], array_values($tags));
		} else {
			$tags = $this->prepareTag($tags);
		}
		
		$tagData = $this->getTagData($tags, $byIntersect);
		
		if ($tagData === null) {
			return true;
		}
		
		$result = $this->getBackend()->removeMany($tagData);
		
		if ($result === true && $byIntersect === false) {
			if (is_array($tags)) {
				$this->getTagHolderBackend()->removeMany($tags);
			} else {
				$this->getTagHolderBackend()->remove($tags);
			}
		}
		
		return $result;
	}
	
	/**
	 * Get the tag's data by a tag's names
	 * 
	 * @param  string | array $tags
	 * @param  bool           $byIntersect
	 * @return array
	 */
	protected function getTagData($tags, $byIntersect = false) {
		if (is_array($tags)) {
			$tagsData = $this->getTagHolderBackend()->getMany($tags);
			
			if (empty($tagsData)) {
				return;
			}
			
			if ($byIntersect) {
				$tagData = call_user_func_array('array_intersect', $tagsData);
				
				if (empty($tagData)) {
					return;
				}
			} else {
				$tagData = array_unique(call_user_func_array('array_merge', $tagsData));
			}
		} else {
			$tagData = $this->getTagHolderBackend()->get($tags);

			if (empty($tagData)) {
				return;
			}
		}
		
		return $tagData;
	}
	
	/**
	 * Prepare the Tag
	 * 
	 * @param  string | array $tag
	 * @return string
	 */
	protected function prepareTag($tag) {
		if (! is_scalar($tag)) {
			$tag = json_encode($tag);
		}
		
		return 'TAG_' . $tag;
	}
	
	/**
	 * Set the tags holding backend
	 * 
	 * @param BackendInterface $backend
	 */
	public function setTagHolderBackend(BackendInterface $backend) {
		$this->tagsBackend = $backend;
	}
	
	/**
	 * Get the tags holding backend
	 * 
	 * @return BackendInterface
	 */
	public function getTagHolderBackend() {
		if ($this->tagsBackend === null) {
			$this->tagsBackend = $this->getBackend();
		}
		
		return $this->tagsBackend;
	}
	
	/**
	 * Flush all the cache data
	 * 
	 * @return bool
	 */
	public function flush() {
		$result1 = $this->getBackend()->flush();
		$result2 = $this->getTagHolderBackend()->flush();
		
		return $result1 && $result2;
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor  = new Descriptor('Taggable', Descriptor::TYPE_SUCCESS);
		$backend     = $this->getBackend();
		$tagsBackend = $this->getTagHolderBackend();
		
		$descriptor->id = $this->getTopologyId();
		
		if ($backend instanceof TopologyInterface) {
			if ($tagsBackend instanceof TopologyInterface) {
				if ($backend->getTopologyId() === $tagsBackend->getTopologyId()) {
					$descriptor[] = $backend->getTopology();
					
					return $descriptor;
				}
				
				$descriptor['data'] = $backend->getTopology();
				$descriptor['tags'] = $tagsBackend->getTopology();
				
				return $descriptor;
			}
			
			$descriptor[] = $backend->getTopology();
		}
		
		return $descriptor;
	}
	
}