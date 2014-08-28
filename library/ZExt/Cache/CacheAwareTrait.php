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

use ZExt\Di\LocatorAwareInterface;

use ZExt\Cache\Exceptions\NoFrontend;

/**
 * Cache aware trait
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Aware
 * @author     Mike.Mirten
 * @version    1.0
 */
trait CacheAwareTrait {
	
	/**
	 * Cache frontend
	 *
	 * @var Wrapper 
	 */
	private $_cacheFrontendWrapper;
	
	/**
	 * Cache frontend's factory
	 *
	 * @var FactoryInterface 
	 */
	private $_cacheFrontendFactory;

	/**
	 * Store the data in the cache
	 * 
	 * @param  string         $id
	 * @param  mixed          $data
	 * @param  int            $lifetime In seconds
	 * @param  string | array $tags
	 * @return bool
	 */
	protected function cacheSet($id, $data, $lifetime = null, $tags = null) {
		return $this->getCacheFrontend()->set($id, $data, $lifetime, $tags);
	}
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string | array $id
	 * @return mixed
	 */
	protected function cacheGet($id) {
		return $this->getCacheFrontend()->get($id);
	}
	
	/**
	 * Fetch a data from the cache by the tag(s)
	 * 
	 * @param  string | array $tags
	 * @param  bool           $byIntersect
	 * @return array
	 */
	protected function cacheGetByTag($tags, $byIntersect = false) {
		return $this->getCacheFrontend()->getByTag($tags, $byIntersect);
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string | array $id
	 * @return bool
	 */
	protected function cacheRemove($id) {
		return $this->getCacheFrontend()->remove($id);
	}
	
	/**
	 * Remove a data from the cache by the tag(s)
	 * 
	 * @param  string | array $tags
	 * @param  bool           $byIntersect
	 * @return bool
	 */
	protected function cacheRemoveByTag($tags, $byIntersect = false) {
		return $this->getCacheFrontend()->removeByTag($tags, $byIntersect);
	}
	
	/**
	 * Set the wrapper cache frontend
	 * 
	 * @param Wrapper $frontend
	 */
	public function setCacheFrontend(Wrapper $frontend) {
		$this->_cacheFrontendWrapper = $frontend;
	}
	
	/**
	 * Get the wrapper cache frontend
	 * 
	 * @return Wrapper
	 */
	public function getCacheFrontend() {
		if ($this->_cacheFrontendWrapper === null) {
			// Try the personal frontend for the service
			if ($this instanceof LocatorAwareInterface && $this->hasLocator()) {
				$locator   = $this->getLocator();
				$serviceId = $this->getCacheFrontendServiceId();
				
				if ($locator->has($serviceId)) {
					$this->_cacheFrontendWrapper = $locator->get($serviceId);
					return $this->_cacheFrontendWrapper;
				}
			}
			
			// Try to create through a factory
			if ($this->hasCacheFrontendFactory() && method_exists($this, 'getServiceName')) {
				$this->_cacheFrontendWrapper = $this->getCacheFrontendFactory()->createWrapper($this->getServiceName());
				return $this->_cacheFrontendWrapper;
			}
			
			throw new NoFrontend('Unable to provide the cache frontend');
		}
		
		return $this->_cacheFrontendWrapper;
	}
	
	/**
	 * Has the wrapper cache frontend
	 * 
	 * @return bool
	 */
	public function hasCacheFrontend() {
		if ($this->_cacheFrontendWrapper !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface
		 && $this->hasLocator()
		 && $this->getLocator()->has($this->getCacheFrontendServiceId())) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Set the cache frontends' factory
	 * 
	 * @param FactoryInterface $factory
	 */
	public function setCacheFrontendFactory(FactoryInterface $factory) {
		$this->_cacheFrontendFactory = $factory;
	}
	
	/**
	 * Get the cache frontends' factory
	 * 
	 * @return FactoryInterface
	 */
	public function getCacheFrontendFactory() {
		if ($this->_cacheFrontendFactory === null) {
			if ($this instanceof LocatorAwareInterface && $this->hasLocator()) {
				$locator   = $this->getLocator();
				$serviceId = $this->getCacheFrontendFactoryServiceId();
				
				if ($locator->has($serviceId)) {
					$this->_cacheFrontendFactory = $locator->get($serviceId);
					return $this->_cacheFrontendFactory;
				}
			}
			
			throw new NoFrontend('Unable to provide the cache frontend\'s factory');
		}
		
		return $this->_cacheFrontendFactory;
	}
	
	/**
	 * Has the cache frontends' factory
	 * 
	 * @return bool
	 */
	public function hasCacheFrontendFactory() {
		if ($this->_cacheFrontendFactory !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface
		 && $this->hasLocator()
		 && $this->getLocator()->has($this->getCacheFrontendFactoryServiceId())) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get the ID of the service's cache frontend
	 * 
	 * @return string
	 */
	protected function getCacheFrontendServiceId() {
		return 'cache';
	}
	
	/**
	 * Get the ID of the frontend's factory
	 * 
	 * @return string
	 */
	protected function getCacheFrontendFactoryServiceId() {
		return 'cacheFactory';
	}
	
}