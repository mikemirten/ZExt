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

use ZExt\Components\OptionsTrait;

use ZExt\Cache\Topology\TopologyInterface;
use ZExt\Topology\Descriptor;

/**
 * Backend adapter interface
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Backend
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class BackendAbstract implements BackendInterface, TopologyInterface {
	
	use OptionsTrait;
	
	/**
	 * Unique IDs counter
	 *
	 * @var int 
	 */
	static private $_idCounter = 0;
	
	/**
	 * Unique backend ID
	 *
	 * @var string
	 */
	protected $backendId;
	
	/**
	 * Cache topology title
	 *
	 * @var string
	 */
	protected $topologyTitle = 'Backend';
	
	/**
	 * Create the freash unique ID
	 * 
	 * @return int
	 */
	static protected function createId() {
		return 'b' . dechex(self::$_idCounter ++);
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor = new Descriptor($this->topologyTitle, self::TOPOLOGY_BACKEND);
		$descriptor->id = $this->getTopologyId();
		
		return $descriptor;
	}
	
	/**
	 * Get the unique decorator ID
	 * 
	 * @return string
	 */
	public function getTopologyId() {
		if ($this->backendId === null) {
			$this->backendId = self::createId();
		}
		
		return $this->backendId;
	}
	
}