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

namespace ZExt\Mvc\View\Helpers\Head;

use ZExt\File\Linker;
use SplQueue, IteratorAggregate, Countable;

/**
 * Resource's package
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
class Package extends Resource implements IteratorAggregate, Countable {
	
	use ResourcesListTrait;
	
	/**
	 * Constructor
	 * 
	 * @param string                   $name
	 * @param string                   $basePath
	 * @param string                   $baseUrl
	 * @param MetadataManagerInterface $metadataManager
	 */
	public function __construct($name, $basePath, $baseUrl, MetadataManagerInterface $metadataManager = null) {
		parent::__construct($name, $basePath, $baseUrl, $metadataManager);
		
		$this->resources = new SplQueue();
		
		$this->resetResources();
	}
	
	/**
	 * Assemble the package
	 */
	public function assemble() {
		$hashdata = '';
		$linker   = new Linker();
		$linker->setOutputPath($this->getPath());
		
		foreach ($this->resources as $resource) {
			$path      = $resource->getPath();
			$hashdata .= $path;
			
			$linker->append($path);
		}
		
		$hash = substr(md5($hashdata), 24);
		$meta = $this->getMetadata();
		
		if ($meta !== false && isset($meta->shash) && $meta->shash === $hash) {
			$linker->write();
			return;
		}
		
		// Update metadata
		$this->metadataManager->getMetadata($this, true);
		$this->metadataManager->setMetaParam($this, 'shash', $hash);
		
		// Force (re)assemble
		$linker->write(true);
	}
	
	/**
	 * Is the package empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return $this->resources->isEmpty();
	}
	
	/**
	 * Count the resources number
	 * 
	 * @return int
	 */
	public function count() {
		return $this->resources->count();
	}
	
	/**
	 * Get iterator
	 * 
	 * @return \Traversable
	 */
	public function getIterator() {
		return $this->resources;
	}
	
}