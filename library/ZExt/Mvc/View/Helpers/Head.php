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

use Exception;

/**
 * Head elements helper
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    3.0
 * 
 * @property \ZExt\Mvc\View\Helpers\Head\ElementTitle       $title       Title element
 * @property \ZExt\Mvc\View\Helpers\Head\ElementEncoding    $encoding    Encoding element
 * @property \ZExt\Mvc\View\Helpers\Head\ElementDescription $description Description element
 * @property \ZExt\Mvc\View\Helpers\Head\ElementKeywords    $keywords    Keywords element
 * @property \ZExt\Mvc\View\Helpers\Head\ElementStyle       $style       Style element
 * @property \ZExt\Mvc\View\Helpers\Head\ElementScript      $script      Script element
 */
class Head extends HelperAbstract {
	
	use OptionsTrait;
	
	/**
	 * Static files' base URL
	 *
	 * @var string
	 */
	protected $staticBaseUrl = '/';
	
	/**
	 * Static files' base path
	 *
	 * @var string
	 */
	protected $staticBasePath;
	
	/**
	 * Static files' checksum handle
	 *
	 * @var string
	 */
	protected $staticHashAppend = false;
	
	/**
	 * Manager of resources' metadata
	 *
	 * @var Head\MetadataManagerInterface
	 */
	protected $metadataManager;
	
	/**
	 * Elements list
	 *
	 * @var Head\ElementInterface
	 */
	protected $elements = [];
	
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
		$this->staticBaseUrl = rtrim($url, '/');
		
		return $this;
	}
	
	/**
	 * Get the base URL of the static files
	 * 
	 * @return string
	 */
	public function getBaseStaticUrl() {
		return $this->staticBaseUrl;
	}
	
	/**
	 * Set the base path of the static files
	 * 
	 * @param  string $path
	 * @return Head
	 */
	public function setBaseStaticPath($path) {
		$this->staticBasePath = realpath($path);
		
		return $this;
	}
	
	/**
	 * Get the base path of the static files
	 * 
	 * @return string
	 */
	public function getBaseStaticPath() {
		if ($this->staticBasePath === null) {
			if (isset($_SERVER['DOCUMENT_ROOT'])) {
				$this->staticBasePath = $_SERVER['DOCUMENT_ROOT'];
			} else {
				$this->staticBasePath = realpath('.');
			}
		}
		
		return $this->staticBasePath;
	}
	
	/**
	 * Calculate hashes for the static files and append their
	 * 
	 * @param  bool $enable
	 * @return Head
	 */
	public function setStaticHashAppend($enable = true) {
		$this->staticHashAppend = (bool) $enable;
		
		return $this;
	}
	
	/**
	 * Is the hashes for the static files enabled ?
	 * 
	 * @return bool
	 */
	public function isStaticHashAppend() {
		return $this->staticHashAppend;
	}

	/**
	 * Set the metadata manager
	 * 
	 * @param  Head\MetadataManagerInterface $manager
	 * @return Head
	 */
	public function setMetadataManager(Head\MetadataManagerInterface $manager) {
		$this->metadataManager = $manager;
		
		return $this;
	}
	
	/**
	 * Get the metadata manager
	 * 
	 * @return Head\MetadataManagerInterface
	 */
	public function getMetadataManager() {
		if ($this->metadataManager === null) {
			$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . '.zstaticmeta';
			
			$this->metadataManager = new Head\MetadataManager($path);
		}
		
		return $this->metadataManager;
	}
	
	/**
	 * Render the meta tags
	 * 
	 * @return string
	 */
	public function render() {
		$parts = [];
		
		if (empty($this->elements)) {
			return '';
		}
		
		// Elements
		foreach ($this->elements as $element) {
			if (! $element->isEmpty()) {
				$parts[] = $element->assemble();
			}
		}
		
		return implode(PHP_EOL, $parts);
	}
	
	/**
	 * Get the element
	 * 
	 * @return Head\ElementInterface
	 */
	public function getElement($name) {
		if (isset($this->elements[$name])) {
			return $this->elements[$name];
		}
		
		$class = 'ZExt\Mvc\View\Helpers\Head\Element' . ucfirst($name);

		$element = new $class(
			$this->getBaseStaticPath(),
			$this->getBaseStaticUrl(),
			$this->getMetadataManager()
		);

		if ($this->isStaticHashAppend() && method_exists($element, 'setHashAppend')) {
			$element->setHashAppend();
		}

		$this->elements[$name] = $element;
		return $element;
	}
	
	/**
	 * Easy access to the elements
	 * 
	 * @param  string $method
	 * @param  array  $args
	 * @return object
	 */
	public function __get($name) {
		return $this->getElement($name);
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
	
}