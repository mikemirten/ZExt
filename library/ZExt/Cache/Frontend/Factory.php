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

use ZExt\Datagate\DatagateInterface;
use ZExt\Cache\Backend\BackendInterface;
use ZExt\Cache\Backend\Decorators\DecoratorInterface;

use ZExt\Cache\Frontend\Exceptions\NoBackend;

use ZExt\Profiler\ProfileableInterface;
use ZExt\Profiler\ProfilerInterface;

use ZExt\Cache\Topology\TopologyInterface;
use ZExt\Topology\Descriptor;

/**
 * Cache frontend's factory
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Frontend
 * @author     Mike.Mirten
 * @version    1.1
 */
class Factory implements FactoryInterface, ProfileableInterface, TopologyInterface {
	
	/**
	 * Backend
	 *
	 * @var BackendInterface 
	 */
	protected $_backend;
	
	/**
	 * Default data lifetime
	 *
	 * @var int
	 */
	protected $_defaultLifetime;
	
	/**
	 * Instanced wrappers
	 *
	 * @var Wrapper[]
	 */
	protected $_wrappers = [];
	
	/**
	 * Instanced collections' handlers
	 *
	 * @var CollectionHandler[]
	 */
	protected $_collectionHandlers = [];
	
	/**
	 * Cache queries profiler
	 *
	 * @var ProfilerInterface
	 */
	protected $_profiler;
	
	/**
	 * Constructor
	 * 
	 * @param BackendInterface $backend
	 */
	public function __construct(BackendInterface $backend = null) {
		if ($backend !== null) {
			$this->setBackend($backend);
		}
	}
	
	/**
	 * Set the default data lifetime
	 * 
	 * @param int $lifetime
	 */
	public function setDefaultLifetime($lifetime) {
		$this->_defaultLifetime = (int) $lifetime;
	}
	
	/**
	 * Get the default data lifetime
	 * 
	 * @return int $lifetime
	 */
	public function getDefaultLifetime() {
		return $this->_defaultLifetime;
	}

	/**
	 * Create the namespaced wrapper
	 * 
	 * @param  string $namespace
	 * @return Wrapper
	 */
	public function createWrapper($namespace = null) {
		if (! isset($this->_wrappers[$namespace])) {
			$wrapper  = new Wrapper($this->getBackend(), $namespace);
			$lifetime = $this->getDefaultLifetime();
			
			if ($lifetime !== null) {
				$wrapper->setDefaultLifetime($lifetime);
			}
			
			$this->_wrappers[$namespace] = $wrapper;
		}
		
		return $this->_wrappers[$namespace];
	}
	
	/**
	 * Create the collection handler for the datagate
	 * 
	 * @param  DatagateInterface $datagate
	 * @return CollectionHandler
	 */
	public function createCollectionHandler(DatagateInterface $datagate) {
		$namespace = md5($datagate->getModelClass());
		
		if (! isset($this->_collectionHandlers[$namespace])) {
			$handler  = new CollectionHandler($this->getBackend(), $datagate, $namespace);
			$lifetime = $this->getDefaultLifetime();
			
			if ($lifetime !== null) {
				$handler->setDefaultLifetime($lifetime);
			}
			
			$this->_collectionHandlers[$namespace] = $handler;
		}
		
		return $this->_collectionHandlers[$namespace];
	}
	
	/**
	 * Get the initilized wrappers
	 * 
	 * @return Wrapper[]
	 */
	public function getInitializedWrappers() {
		return $this->_wrappers;
	}
	
	/**
	 * Set the backend
	 * 
	 * @param  BackendInterface $backend
	 * @return Factory
	 */
	public function setBackend(BackendInterface $backend) {
		$this->_backend = $backend;
		
		if ($this->_profiler === null) {
			do {
				if ($backend instanceof ProfileableInterface) {
					if ($backend->isProfilerEnabled()) {
						$this->setProfiler($backend->getProfiler());
					}
					
					break;
				}
			} while (
				$backend instanceof DecoratorInterface
			&& ($backend = $backend->getBackend()) !== null
			);
		}
		
		return $this;
	}
	
	/**
	 * Get the backend
	 * 
	 * @return BackendInterface
	 * @throws NoBackend
	 */
	public function getBackend() {
		if ($this->_backend === null) {
			throw new NoBackend('Backend wasn\'t been supplied');
		}
		
		return $this->_backend;
	}
	
	/**
	 * Set the profiler
	 * 
	 * @var ProfilerInterface
	 */
	public function setProfiler(ProfilerInterface $profiler) {
		$this->_profiler = $profiler;
	}
	
	/**
	 * Get the profiler
	 * 
	 * @return ProfilerInterface
	 */
	public function getProfiler() {
		$this->_profiler->addAdditionalInfo(
			'__TOPOLOGY__',
			$this->getTopology()
		);
		
		return $this->_profiler;
	}
	
	/**
	 * Switch the profiler on/off
	 * 
	 * @param bool $switch
	 */
	public function setProfilerStatus($enabled = true) {
		if (! $enabled) {
			trigger_error('Unable to disable a profiler, due to profiler is always active if one used');
		}
	}
	
	/**
	 * Is enabled the profiler
	 * 
	 * @return bool
	 */
	public function isProfilerEnabled() {
		return $this->_profiler !== null;
	}

	/**
	 * Create the namespaced wrapper
	 * 
	 * @param  string $namespace
	 * @return Wrapper
	 */
	public function __get($name) {
		return $this->createWrapper($name);
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor = new Descriptor('Factory', self::TOPOLOGY_FRONTEND);
		
		if ($this->_defaultLifetime === null) {
			$descriptor->lifetime = 'n/a';
		} else {
			$descriptor->lifetime = $this->_defaultLifetime;
		}
		
		$descriptor->created = count($this->_wrappers);
		
		$backend = $this->getBackend();
		
		if ($backend instanceof TopologyInterface) {
			$descriptor[] = $backend->getTopology();
		}
		
		return $descriptor;
	}
	
	public function getTopologyId() {
		return 'frontend_factory';
	}
	
}