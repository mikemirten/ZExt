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

/**
 * Resource
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
class Resource {
	
	/**
	 * Name of the resource
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * Base path to the resource
	 *
	 * @var string
	 */
	protected $basePath;
	
	/**
	 * Is local resource
	 *
	 * @var bool
	 */
	protected $isLocal;
	
	/**
	 * Base URL of the resource
	 *
	 * @var string
	 */
	protected $baseUrl;
	
	/**
	 * Append hash-tage to the resource
	 *
	 * @var bool
	 */
	protected $hashAppend = false;
	
	/**
	 * Manager of resources' metadata
	 *
	 * @var MetadataManagerInterface
	 */
	protected $metadataManager;
	
	/**
	 * Constructor
	 * 
	 * @param string                   $name
	 * @param string                   $basePath
	 * @param string                   $baseUrl
	 * @param MetadataManagerInterface $metadataManager
	 */
	public function __construct($name, $basePath, $baseUrl, MetadataManagerInterface $metadataManager = null) {
		$this->name = (string) $name;
		
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
	 * @return Resource
	 */
	protected function setBasePath($path) {
		$path = rtrim($path, '\/');
		
		if (preg_match('~^[a-z]+:~', $path)) {
			$this->basePath = $path;
			$this->isLocal  = false;
			return;
		}
		
		$this->basePath = realpath($path);
		$this->isLocal  = true;
		
		return $this;
	}
	
	/**
	 * Set the base url
	 * 
	 * @param  string $url
	 * @return Resource
	 */
	protected function setBaseUrl($url) {
		if ($url === '/') {
			$this->baseUrl = '';
			return;
		}
		
		if ($url === '//') {
			$this->baseUrl = '/';
			return;
		}
		
		$this->baseUrl = rtrim($url, '/');
		
		return $this;
	}
	
	/**
	 * Append hash-tag to URL of the resource
	 * 
	 * @param  bool $enable
	 * @return Resource
	 */
	public function setHashAppend($enable = true) {
		$this->hashAppend = (bool) $enable;
		
		return $this;
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
	 * Is local resource ?
	 * 
	 * @return bool
	 */
	public function isLocal() {
		return $this->isLocal;
	}
	
	/**
	 * Get the name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get full path to the resource
	 * 
	 * @return string
	 */
	public function getPath() {
		if ($this->isLocal) {
			return $this->basePath . DIRECTORY_SEPARATOR . $this->name;
		}
		
		return $this->basePath . '/' . $this->name;
	}
	
	/**
	 * Get URL of the resource
	 * 
	 * @return string
	 */
	public function getUrl() {
		$url = $this->baseUrl . '/' . $this->name;
		
		if ($this->hashAppend) {
			$meta = $this->getMetadata();
			
			if ($meta !== false) {
				$url .= '?' . $meta->hash;
			}
		}
		
		return $url;
	}
	
	/**
	 * Get metadata for the file resource
	 * 
	 * @param string $path
	 */
	protected function getMetadata() {
		if ($this->metadataManager === null) {
			throw new Exceptions\MetadataError('Metadata manager was not set');
		}
		
		return $this->metadataManager->getMetadata($this);
	}
	
}