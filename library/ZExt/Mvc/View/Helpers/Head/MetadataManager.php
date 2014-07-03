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

use stdClass;

/**
 * Interface of metadata manager
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
class MetadataManager implements MetadataManagerInterface {
	
	/**
	 * Files' metadata
	 *
	 * @var array
	 */
	protected $metadata;
	
	/**
	 * Path to the metadata file
	 *
	 * @var string
	 */
	protected $metadataPath;
	
	/**
	 * Metadata was changed
	 *
	 * @var bool
	 */
	protected $metadataChanged = false;
	
	/**
	 * Constructor
	 * 
	 * @param string $path Path to the metadata file
	 */
	public function __construct($path) {
		$this->setStoragePath($path);
	}
	
	/**
	 * Set the metadata storage path
	 * 
	 * @param string $path
	 */
	public function setStoragePath($path) {
		$dir  = pathinfo($path, PATHINFO_DIRNAME);
		$name = pathinfo($path, PATHINFO_BASENAME);
		
		$dir = realpath($dir);
		
		if ($dir === false) {
			throw new Exceptions\MetadataError('Invalid path: "' . $path . '"');
		}
		
		$this->metadataPath = $dir . DIRECTORY_SEPARATOR . $name;
	}
	
	/**
	 * Set metadata parameter for the resource
	 * 
	 * @param Resource $resource
	 * @param string   $name
	 * @param mixed    $value
	 */
	public function setMetaParam(Resource $resource, $name, $value) {
		// Init the exact metadata by the resource
		if (! $this->getMetadata($resource)) {
			return;
		}
		
		$this->metadata[$resource->getPath()]->$name = $value;
		$this->metadataChanged = true;
	}
	
	/**
	 * Get metadata for the resource
	 * 
	 * @param  Resource $resource
	 * @param  bool     $forceUpdate
	 * @return stdObject | bool
	 */
	public function getMetadata(Resource $resource, $forceUpdate = false) {
		if ($this->metadata === null) {
			$this->initMetadata();
		}
		
		$path = $resource->getPath();
		
		if (! is_file($path)) {
			return false;
		}
		
		// Already exists for the resource
		if (! $forceUpdate && isset($this->metadata[$path])) {
			return $this->metadata[$path];
		}
		
		if ($resource->isLocal()) {
			$fileMtime = filemtime($path);
			
			if (isset($this->metadata[$path]) && $fileMtime === $this->metadata[$path]->mtime) {
				return $this->metadata[$path];
			}
		}
		
		$fileMeta = new stdClass();
		
		$fileMeta->mtime = isset($fileMtime) ? $fileMtime : false;
		$fileMeta->hash  = substr(md5_file($path), 24);
		
		$this->metadata[$path] = $fileMeta;
		$this->metadataChanged = true;
		
		return $fileMeta;
	}
	
	/**
	 * Get the files metadata
	 * 
	 * @return array
	 * @throws Exceptions\MetadataError
	 */
	protected function initMetadata() {
		if (! is_file($this->metadataPath)) {
			$this->metadata = [];
			
			return $this->metadata;
		}
		
		$metaRaw = file_get_contents($this->metadataPath);

		if ($metaRaw === false) {
			throw new Exceptions\MetadataError('Unable to read the metadata');
		}

		$meta = json_decode($metaRaw);

		if ($meta === null) {
			throw new Exceptions\MetadataError('Error in the metadata; Errorcode: ' . json_last_error());
		}

		$this->metadata = (array) $meta;

		return $this->metadata;
	}
	
	/**
	 * Destructor
	 * 
	 * Writes the metadata at the end of script
	 */
	public function __destruct() {
		if (! $this->metadataChanged) {
			return;
		}
		
		$meta   = json_encode($this->metadata);
		$result = file_put_contents($this->metadataPath, $meta);
		
		if ($result === false) {
			throw new Exceptions\MetadataError('Unable to write the metadata');
		}
	}
	
}