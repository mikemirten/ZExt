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

use SplQueue, IteratorAggregate, Countable;

/**
 * Element of "head" section
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class ElementAbstract implements ElementInterface, IteratorAggregate, Countable {
	
	use ResourcesListTrait;

	/**
	 * Packages list
	 *
	 * @var Package[]
	 */
	protected $packages;
	
	/**
	 * Base path to the element
	 *
	 * @var string
	 */
	protected $basePath;
	
	/**
	 * Base URL of the resource
	 *
	 * @var string
	 */
	protected $baseUrl;
	
	/**
	 * Manager of resources' metadata
	 *
	 * @var MetadataManagerInterface
	 */
	protected $metadataManager;
	
	/**
	 * Append hash-tage to the resource
	 *
	 * @var bool
	 */
	protected $hashAppend = false;
	
	/**
	 * Constructor
	 * 
	 * @param string                   $basePath
	 * @param string                   $baseUrl
	 * @param MetadataManagerInterface $metadataManager
	 */
	public function __construct($basePath, $baseUrl, MetadataManagerInterface $metadataManager = null) {
		$this->resources = new SplQueue();
		$this->packages  = new SplQueue();
		
		$this->setBasePath($basePath);
		$this->setBaseUrl($baseUrl);
		
		if ($metadataManager !== null) {
			$this->metadataManager = $metadataManager;
		}
	}
	
	/**
	 * Set the base path
	 * 
	 * @param  string $path
	 * @return ElementAbstract
	 */
	public function setBasePath($path) {
		$path = rtrim($path, '\/');
		
		if (preg_match('~^[a-z]+:~', $path)) {
			$this->basePath = $path;
			return;
		}
		
		$this->basePath = realpath($path);
		
		return $this;
	}
	
	/**
	 * Set the base url
	 * 
	 * @param  string $url
	 * @return ElementAbstract
	 */
	public function setBaseUrl($url) {
		if ($url === '/' || $url === '//') {
			$this->baseUrl = $url;
			return;
		}
		
		$this->baseUrl = rtrim($url, '/');
		
		return $this;
	}
	
	/**
	 * Append hash-tag to URL of the resource
	 * 
	 * @param bool $enable
	 */
	public function setHashAppend($enable = true) {
		$this->hashAppend = (bool) $enable;
	}
	
	/**
	 * Is hash append to the package url need ?
	 * 
	 * @return bool
	 */
	public function isHashAppend() {
		return $this->hashAppend;
	}
	
	/**
	 * Prepend the package
	 * 
	 * @param  Package | string $package
	 * @return Package
	 */
	public function prependPackage($package) {
		$package = $this->handlePackage($package);
		
		$this->packages->unshift($package);
		return $this;
	}
	
	/**
	 * Append the package
	 * 
	 * @param  Package | string $package
	 * @return Package
	 */
	public function appendPackage($package) {
		$package = $this->handlePackage($package);
		
		$this->packages[] = $package;
		return $package;
	}
	
	/**
	 * Remove all packages from the element
	 * 
	 * @return ElementAbstract
	 */
	public function resetPackages() {
		$this->packages = new SplQueue();
		
		return $this;
	}
	
	/**
	 * Set the packages (owerrides the exists)
	 * 
	 * @param  array $packages
	 * @return ElementAbstract
	 */
	public function setPackages(array $packages) {
		$this->resetPackages();
		
		foreach ($packages as $package) {
			$this->appendPackage($package);
		}
		
		return $this;
	}
	
	/**
	 * Handle the package
	 * 
	 * @param  Package | string $package
	 * @return Package
	 */
	protected function handlePackage($package) {
		if ($package instanceof Package) {
			return $package;
		}
		
		$package = new Package($package, $this->basePath, $this->baseUrl, $this->metadataManager);
		
		if ($this->hashAppend) {
			$package->setHashAppend(true);
		}
		
		return $package;
	}
	
	/**
	 * Is the element empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return $this->packages->isEmpty() && $this->resources->isEmpty();
	}
	
	/**
	 * Count the resources number
	 * 
	 * @return int
	 */
	public function count() {
		return $this->packages->count() + $this->resources->count();
	}
	
	/**
	 * Get iterator
	 * 
	 * @return \Traversable
	 */
	public function getIterator() {
		$resources = clone $this->resources;
	
		foreach ($this->packages as $package) {
			$resources[] = $package;
		}
		
		return $resources;
	}

	/**
	 * Render the element
	 * 
	 * @return string
	 */
	public function assemble() {
		$scripts = [];
		
		if (! empty($this->resources)) {
			foreach ($this->resources as $resource) {
				$scripts[] = $this->renderTag($resource);
			}
		}
		
		if (! empty($this->packages)) {
			foreach ($this->packages as $package) {
				$package->assemble();
				$scripts[] = $this->renderTag($package);
			}
		}
		
		if (empty($scripts)) {
			return;
		}
		
		return implode(PHP_EOL, $scripts);
	}
	
	/**
	 * Default action of the element
	 * 
	 * @param string $value
	 */
	public function defaultAction($value) {
		$this->appendResource($value);
	}
	
	/**
	 * Render a tag by the resource
	 * 
	 * @param  Resource $resource
	 * @return string
	 */
	abstract protected function renderTag(Resource $resource);
	
}