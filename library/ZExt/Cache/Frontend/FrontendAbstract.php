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

use ZExt\Cache\Topology\TopologyInterface;
use ZExt\Topology\Descriptor;

abstract class FrontendAbstract implements TopologyInterface {
	
	/**
	 * Cache backend instance
	 *
	 * @var BackendInterface
	 */
	private $_backend;
	
	/**
	 * Default lifetime in seconds
	 *
	 * @var int
	 */
	protected $_defaultLifetime = 0;
	
	/**
	 * ID's namespace
	 *
	 * @var string
	 */
	protected $_namespace;
	
	/**
	 * Set the backend
	 * 
	 * @param BackendInterface $backend
	 */
	public function setBackend(BackendInterface $backend) {
		$this->_backend = $backend;
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
	 * Set the namespace
	 * 
	 * @param string $namespace
	 */
	public function setNamespace($namespace) {
		$this->_namespace = (string) $namespace;
	}
	
	/**
	 * Get the namespace
	 * 
	 * @return string
	 */
	public function getNamespace() {
		return $this->_namespace;
	}
	
	/**
	 * Set the default lifetime in seconds
	 * 
	 * @param int $lifetime
	 */
	public function setDefaultLifetime($lifetime) {
		$this->_defaultLifetime = (int) $lifetime;
	}
	
	/**
	 * Get the default lifetime in seconds
	 * 
	 * @return int
	 */
	public function getDefaultLifetime() {
		return $this->_defaultLifetime;
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor = new Descriptor('Frontend', self::TOPOLOGY_FRONTEND);
		
		if ($this->_namespace !== null) {
			$descriptor->namespace = $this->_namespace;
		}
		
		$descriptor->lifetime  = $this->_defaultLifetime;
		
		$backend = $this->getBackend();
		
		if ($backend instanceof TopologyInterface) {
			$descriptor[] = $backend->getTopology();
		}
		
		return $descriptor;
	}
	
	/**
	 * Get the unique ID of the Topology element
	 * 
	 * @return string Hexadecimal ID
	 */
	public function getTopologyId() {
		return $this->_namespace;
	}
	
}