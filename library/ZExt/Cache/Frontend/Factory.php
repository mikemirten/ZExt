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
use ZExt\Cache\Frontend\Exceptions\NoBackend;

/**
 * Cache frontend's factory
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Frontend
 * @author     Mike.Mirten
 * @version    1.0
 */
class Factory implements FactoryInterface {
	
	/**
	 * Backend
	 *
	 * @var BackendInterface 
	 */
	protected $backend;
	
	/**
	 * Default data lifetime
	 *
	 * @var int
	 */
	protected $defaultLifetime;
	
	/**
	 * Instanced wrappers
	 *
	 * @var Wrapper[]
	 */
	protected $wrappers = [];
	
	/**
	 * Instanced collections' handlers
	 *
	 * @var CollectionHandler[]
	 */
	protected $collectionHandlers = [];
	
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
		$this->defaultLifetime = (int) $lifetime;
	}
	
	/**
	 * Get the default data lifetime
	 * 
	 * @return int $lifetime
	 */
	public function getDefaultLifetime() {
		return $this->defaultLifetime;
	}

	/**
	 * Create the namespaced wrapper
	 * 
	 * @param  string $namespace
	 * @return Wrapper
	 */
	public function createWrapper($namespace = null) {
		if (! isset($this->wrappers[$namespace])) {
			$wrapper  = new Wrapper($this->getBackend(), $namespace);
			$lifetime = $this->getDefaultLifetime();
			
			if ($lifetime !== null) {
				$wrapper->setDefaultLifetime($lifetime);
			}
			
			$this->wrappers[$namespace] = $wrapper;
		}
		
		return $this->wrappers[$namespace];
	}
	
	/**
	 * Get the initilized wrappers
	 * 
	 * @return Wrapper[]
	 */
	public function getInitializedWrappers() {
		return $this->wrappers;
	}
	
	/**
	 * Set the backend
	 * 
	 * @param  BackendInterface $backend
	 * @return Factory
	 */
	public function setBackend(BackendInterface $backend) {
		$this->backend = $backend;
		
		return $this;
	}
	
	/**
	 * Get the backend
	 * 
	 * @return BackendInterface
	 * @throws NoBackend
	 */
	public function getBackend() {
		if ($this->backend === null) {
			throw new NoBackend('Backend wasn\'t been supplied');
		}
		
		return $this->backend;
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
	
}