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

use ZExt\Cache\Backend\BackendInterface;
use ZExt\Cache\Backend\Exceptions\NoBackend;

use ZExt\Cache\Topology\TopologyInterface;
use ZExt\Topology\Descriptor;

/**
 * Decorator abstract
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Decorators
 * @author     Mike.Mirten
 * @version    1.1
 */
abstract class DecoratorAbstract implements DecoratorInterface, TopologyInterface {
	
	/**
	 * Unique IDs counter
	 *
	 * @var type 
	 */
	static private $_idCounter = 0;
	
	/**
	 * Cache topology title
	 *
	 * @var string
	 */
	protected $topologyTitle = 'Decorator';
	
	/**
	 * Backend
	 *
	 * @var BackendInterface 
	 */
	protected $backend;
	
	/**
	 * Unique backend ID
	 *
	 * @var int
	 */
	protected $backendId;
	
	/**
	 * Create the freash unique ID
	 * 
	 * @return int
	 */
	static protected function createId() {
		return self::$_idCounter ++;
	}
	
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
	 * Set the backend
	 * 
	 * @param BackendInterface $backend
	 */
	public function setBackend(BackendInterface $backend) {
		$this->backend = $backend;
	}
	
	/**
	 * Get the backend
	 * 
	 * @return BackendInterface
	 */
	public function getBackend() {
		if ($this->backend === null) {
			throw new NoBackend('Backend wasn\'t been supplied');
		}
		
		return $this->backend;
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor = new Descriptor($this->topologyTitle, self::TOPOLOGY_DECORATOR);
		
		$backend = $this->getBackend();
		
		if ($backend instanceof TopologyInterface) {
			$descriptor[] = $backend->getTopology();
		}
		
		return $descriptor;
	}
	
	/**
	 * Get the unique decorator ID
	 * 
	 * @return int
	 */
	public function getId() {
		if ($this->backendId === null) {
			$this->backendId = self::createId();
		}
		
		return $this->backendId;
	}
	
}