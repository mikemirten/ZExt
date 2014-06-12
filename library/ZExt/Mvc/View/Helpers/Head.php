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

namespace ZExt\Mvc\View\Helpers;

use ZExt\Components\OptionsTrait;
use ZExt\Helper\HelperAbstract;

use ZExt\Mvc\View\Helpers\Exceptions\MetadataError;

use Exception, stdClass;

/**
 * Head elements helper
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    2.0
 */
class Head extends HelperAbstract {
	
	use OptionsTrait;
	
	use Head\Description,
	    Head\Keywords,
	    Head\Encoding,
	    Head\Title,
	    Head\Style,
	    Head\Script;
	
	const META_FILENAME = '.zstaticmeta';
	
	/**
	 * Files' metadata
	 *
	 * @var array
	 */
	protected $metadata;
	
	/**
	 * Metadata was changed
	 *
	 * @var bool
	 */
	protected $metadataChanged = false;
	
	/**
	 * Static files' base URL
	 *
	 * @var string
	 */
	protected $staticUrl = '';
	
	/**
	 * Static files' base path
	 *
	 * @var string
	 */
	protected $staticPath;
	
	/**
	 * Static files' checksum handle
	 *
	 * @var string
	 */
	protected $staticHash = false;
	
	/**
	 * The helper "main"
	 * 
	 * @return Head
	 */
	public function head($options = null) {
		if ($options !== null) {
			$this->setOptions($options, false, false);
		}
		
		return $this;
	}
	
	/**
	 * Set the base URL of the static files
	 * 
	 * @param  string $url
	 * @return Head
	 */
	public function setBaseStaticUrl($url) {
		$this->staticUrl = rtrim($url, '/');
		
		return $this;
	}
	
	/**
	 * Get the base URL of the static files
	 * 
	 * @return string
	 */
	public function getBaseStaticUrl() {
		return $this->staticUrl;
	}
	
	/**
	 * Set the base path of the static files
	 * 
	 * @param  string $path
	 * @return Head
	 */
	public function setBaseStaticPath($path) {
		$this->staticPath = realpath($path);
		
		return $this;
	}
	
	/**
	 * Get the base path of the static files
	 * 
	 * @return string
	 */
	public function getBaseStaticPath() {
		if ($this->staticPath === null) {
			if (isset($_SERVER['DOCUMENT_ROOT'])) {
				$this->staticPath = $_SERVER['DOCUMENT_ROOT'];
			} else {
				$this->staticPath = realpath('.');
			}
		}
		
		return $this->staticPath;
	}
	
	/**
	 * Calculate hashes for the static files and append their
	 * 
	 * @param  bool $enable
	 * @return Head
	 */
	public function setStaticHashing($enable = true) {
		$this->staticHash = (bool) $enable;
		
		return $this;
	}
	
	/**
	 * Is the hashes for the static files enabled ?
	 * 
	 * @return bool
	 */
	public function isStaticHashing() {
		return $this->staticHash;
	}
	
	/**
	 * Render the meta tags
	 * 
	 * @return string
	 */
	public function render() {
		$parts = [];
		
		// Encoding
		if ($this->encoding !== null) {
			$parts[] = $this->renderEncoding();
		}
		
		// Title
		if (! empty($this->titleContent)) {
			$parts[] = $this->renderTitle();
		}
		
		// Description
		if ($this->description !== null) {
			$parts[] = $this->renderDescription();
		}
		
		// Keywords
		if (! empty($this->keywords)) {
			$parts[] = $this->renderKeywords();
		}
		
		// Style
		if (! empty($this->styleLinks)) {
			$parts[] = $this->renderStyle();
		}
		
		// Scripts
		if (! empty($this->scriptSources)) {
			$parts[] = $this->renderScripts();
		}
		
		return implode(PHP_EOL, $parts);
	}
	
	/**
	 * Return file's metadata
	 * 
	 * @param  string $path
	 * @return stdObject
	 */
	protected function getFileMeta($path) {
		$meta = $this->getMetadata();
		
		$filePath  = $this->getBaseStaticPath();
		$filePath .= DIRECTORY_SEPARATOR . $path;
		
		// Modification time
		$fileMtime = filemtime($filePath);
		
		if (isset($meta[$path]) && $fileMtime === $meta[$path]->mtime) {
			return $meta[$path];
		}
		
		$fileMeta = new stdClass();
		$fileMeta->mtime = $fileMtime;
		$fileMeta->hash  = substr(md5_file($filePath), 24);
		
		$this->metadata[$path] = $fileMeta;
		$this->metadataChanged = true;
		
		return $fileMeta;
	}
	
	/**
	 * Get the files metadata
	 * 
	 * @return array
	 * @throws MetadataError
	 */
	protected function getMetadata() {
		if ($this->metadata !== null) {
			return $this->metadata;
		}
		
		$metaPath  = $this->getBaseStaticPath();
		$metaPath .= DIRECTORY_SEPARATOR . self::META_FILENAME;
		
		if (is_file($metaPath)) {
			$metaRaw = file_get_contents($metaPath);
			
			if ($metaRaw === false) {
				throw new MetadataError('Unable to read the metadata');
			}
			
			$meta = json_decode($metaRaw);
			
			if ($meta === null) {
				throw new MetadataError('Error in the metadata; Errorcode: ' . json_last_error());
			}
			
			$this->metadata = (array) $meta;
			
			return $this->metadata;
		}
		
		$this->metadata = [];
		
		return $this->metadata;
	}
	
	/**
	 * Render the meta tags
	 * 
	 * @return string
	 */
	public function __toString() {
		try {
			return $this->render();
		} catch (Exception $e) {
			return '<!-- Head helper exception: ' . $e->getMessage() . ' -->';
		}
	}
	
	/**
	 * Destructor
	 * 
	 * Writes the metadata at the end
	 */
	public function __destruct() {
		if (! $this->metadataChanged) {
			return;
		}
		
		$metaPath  = $this->getBaseStaticPath();
		$metaPath .= DIRECTORY_SEPARATOR . self::META_FILENAME;
		
		$meta   = json_encode($this->metadata);
		$result = file_put_contents($metaPath, $meta);
		
		if ($result === false) {
			throw new MetadataError('Unable to write the metadata');
		}
	}
	
}