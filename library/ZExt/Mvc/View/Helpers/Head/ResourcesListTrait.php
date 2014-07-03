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

use SplQueue;

/**
 * Resources list trait
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
trait ResourcesListTrait {
	
	/**
	 * Resources list
	 *
	 * @var Resource[]
	 */
	protected $resources;
	
	/**
	 * Prepend the source
	 * 
	 * @param  Resource | string $resource
	 * @return Resource
	 */
	public function prependResource($resource) {
		$resource = $this->handleResource($resource);
		
		$this->resources->unshift($resource);
		return $resource;
	}
	
	/**
	 * Append the source
	 * 
	 * @param  Resource | string $resource
	 * @return Resource
	 */
	public function appendResource($resource) {
		$resource = $this->handleResource($resource);
		
		$this->resources[] = $resource;
		return $resource;
	}
	
	/**
	 * Remove all sources from the package
	 * 
	 * @return Package
	 */
	public function resetResources() {
		$this->resources = new SplQueue();
		
		return $this;
	}
	
	/**
	 * Set the resources (owerrides the exists)
	 * 
	 * @param  array $resources
	 * @return Package
	 */
	public function setResources(array $resources) {
		$this->resetResources();
		
		foreach ($resources as $resource) {
			$this->appendResource($resource);
		}
		
		return $this;
	}
	
	/**
	 * Handle the resource
	 * 
	 * @param  Resource | string $resource
	 * @return Resource
	 */
	protected function handleResource($resource) {
		if ($resource instanceof Resource) {
			return $resource;
		}
		
		// Is not a local resource
		if (preg_match('~^[a-z]+:~', $resource)) {
			$slashPos = strrpos($resource, '/');
			
			$base = substr($resource, 0, $slashPos);
			$name = substr($resource, $slashPos + 1);
			
			$resource = new Resource($name, $base, $base, $this->metadataManager);
		} else {
			$resource = new Resource($resource, $this->basePath, $this->baseUrl, $this->metadataManager);
		}
		
		if ($this->hashAppend) {
			$resource->setHashAppend(true);
		}
		
		return $resource;
	}
	
}