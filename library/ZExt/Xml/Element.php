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

namespace ZExt\Xml;

use SimpleXMLElement, IteratorAggregate, ArrayIterator;

/**
 * XML element abstraction
 * 
 * @category   ZExt
 * @package    Xml
 * @subpackage Element
 * @author     Mike.Mirten
 * @version    1.0
 */
class Element implements IteratorAggregate {
	
	/**
	 * Source
	 *
	 * @var SimpleXMLElement 
	 */
	protected $_xml;
	
	/**
	 * Element's attributes
	 *
	 * @var array
	 */
	protected $_attributes;
	
	/**
	 * Element's content
	 *
	 * @var Element[]
	 */
	protected $_content;
	
	/**
	 * Constructor
	 * 
	 * @param SimpleXMLElement $xml
	 */
	public function __construct(SimpleXMLElement $xml) {
		$this->_xml = $xml;
	}
	
	/**
	 * Get name of element
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->_xml->getName();
	}
	
	/**
	 * Get attribute
	 * 
	 * @param  string $name
	 * @return mixed
	 */
	public function getAttribute($name) {
		if ($this->_attributes === null) {
			$this->initAttributes();
		}
		
		if (isset($this->_attributes[$name])) {
			return $this->_attributes[$name];
		}
	}
	
	/**
	 * Set attribute
	 * 
	 * @param  string $name
	 * @param  mixed  $value
	 * @return Element
	 */
	public function setAttribute($name, $value) {
		if ($this->_attributes === null) {
			$this->initAttributes();
		}
		
		$this->_attributes[$name] = $value;
		
		$this->_xml->addAttribute($name, $value);
		
		return $this;
	}
	
	/**
	 * Element has attribute ?
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function hasAttribute($name) {
		if ($this->_attributes === null) {
			$this->initAttributes();
		}
		
		return isset($this->_attributes[$name]);
	}
	
	/**
	 * Remove attribute
	 * 
	 * @param  string $name
	 * @return Element
	 */
	public function removeAttribute($name) {
		if ($this->_attributes === null) {
			$this->initAttributes();
		}
		
		unset($this->_attributes[$name]);
		
		return $this;
	}
	
	/**
	 * Get content of element
	 * 
	 * @return Element[]
	 */
	public function getContent() {
		if ($this->_content === null) {
			$this->_content = [];

			foreach ($this->_xml->children() as $element) {
				$this->_content[] = new static($element);
			}
		}
		
		return $this->_content;
	}
	
	/**
	 * Get value of element
	 * 
	 * @return string
	 */
	public function getValue() {
		return $this->_xml->__toString();
	}

	/**
	 * Get attributes of element
	 * 
	 * @return array
	 */
	public function getAttributes() {
		if ($this->_attributes === null) {
			$this->initAttributes();
		}
		
		return $this->_attributes;
	}
	
	/**
	 * Initialize atributes of element
	 */
	protected function initAttributes() {
		$vars = get_object_vars($this->_xml->attributes());
			
		$this->_attributes = empty($vars) ? [] : $vars['@attributes'];
	}
	
	/**
	 * Get iterator
	 * 
	 * @return \Traversable
	 */
	public function getIterator() {
		return new ArrayIterator($this->getContent());
	}
	
	/**
	 * Set attribute
	 * 
	 * @param  string $name
	 * @param  mixed  $value
	 */
	public function __set($name, $value) {
		$this->setAttribute($name, $value);
	}
	
	/**
	 * Get attribute
	 * 
	 * @param  string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->getAttribute($name);
	}
	
	/**
	 * Element has attribute ?
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->hasAttribute($name);
	}
	
	/**
	 * Remove attribute
	 * 
	 * @param  string $name
	 */
	public function __unset($name) {
		$this->removeAttribute($name);
	}
	
}