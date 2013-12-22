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

namespace ZExt\Html;

use Countable, ArrayAccess, IteratorAggregate, ArrayIterator;

/**
 * Html multi elements tags' abstraction
 * 
 * @package    Html
 * @subpackage MultiElements
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class MultiElementsAbstract extends Tag implements Countable, ArrayAccess, IteratorAggregate {
	
	const SPECIAL_CLASS = '_class_';
	const SPECIAL_ATTRS = '_attrs_';
	const SPECIAL_STYLE = '_style_';
	
	/**
	 * Elements of the multielements structure
	 * 
	 * @var array
	 */
	protected $_elements = array();
	
	/**
	 * Constructor
	 * 
	 * @param array $elements
	 * @param array $attrs
	 */
	public function __construct(array $elements = null, $attrs = null) {
		parent::__construct(null, null, $attrs);
		
		if ($elements !== null) {
			// Class
			if (isset($elements[self::SPECIAL_CLASS])) {
				is_array($elements[self::SPECIAL_CLASS])
					? $this->addClasses($elements[self::SPECIAL_CLASS])
					: $this->addClass($elements[self::SPECIAL_CLASS]);
				
				unset($elements[self::SPECIAL_CLASS]);
			}
			
			// Attributes
			if (isset($elements[self::SPECIAL_ATTRS])) {
				$this->setAttrs($elements[self::SPECIAL_ATTRS]);
				unset($elements[self::SPECIAL_ATTRS]);
			}
			
			// Styles
			if (isset($elements[self::SPECIAL_STYLE])) {
				$this->addStyles($elements[self::SPECIAL_STYLE]);
				unset($elements[self::SPECIAL_STYLE]);
			}
			
			$this->addElements($elements);
		}
	}
	
	/**
	 * Add the elements
	 * 
	 * @param  array $elements
	 * @return MultiElementsAbstract
	 */
	public function addElements(array $elements) {
		foreach ($elements as $name => $element) {
			if (is_string($name)) {
				$this->addElement($element, $name);
			} else {
				$this->addElement($element);
			}
		}
		
		return $this;
	}
	
	/**
	 * Add the element
	 * 
	 * @param  mixed  $element
	 * @param  string $name
	 * @return MultiElementsAbstract
	 */
	public function addElement($element, $name = null) {
		if ($name === null) {
			$this->_elements[] = $element;
		} else {
			$this->_elements[$name] = $element;
		}
		
		return $this;
	}
	
	/**
	 * Get the elements
	 * 
	 * @return Tag[]
	 */
	public function getElements() {
		return $this->_elements;
	}
	
	/**
	 * Get the element
	 * 
	 * @param  string | int $name
	 * @return Tag | null
	 */
	public function getElement($name) {		
		if (isset($this->_elements[$name])) {
			return $this->_elements[$name];
		}
	}
	
	/**
	 * Remove the element
	 * 
	 * @param  string | int $name
	 * @return MultiElementsAbstract
	 */
	public function removeElement($name) {
		unset($this->_elements[$name]);
		
		return $this;
	}
	
	/**
	 * Has the element
	 * 
	 * @param  string | int $name
	 * @return bool
	 */
	public function hasElement($name) {
		return isset($this->_elements[$name]);
	}
	
	/**
	 * Render
	 * 
	 * @return string
	 */
	public function render($html = null) {
		$separator = $this->getSeparator();
		$result    = '';
		
		foreach ($this->getElements() as $element) {
			$result .= $separator . $element->render();
		}
		
		return parent::render($result . $separator);
	}
	
	/**
	 * Set the attribute for each element
	 * 
	 * @param  string $attr
	 * @param  string $value
	 * @return MultiElementsAbstract
	 */
	public function setElementsAttr($attr, $value) {
		foreach ($this->getElements() as $element) {
			if ($element instanceof Tag) {
				$element->setAttr($attr, $value);
			}
		}
		
		return $this;
	}
	
	/**
	 * Remove the attribute from each element
	 * 
	 * @param  string $attr
	 * @return MultiElementsAbstract
	 */
	public function removeElementsAttr($attr) {
		foreach ($this->getElements() as $element) {
			if ($element instanceof Tag) {
				$element->removeAttr($attr);
			}
		}
		
		return $this;
	}
	
	/**
	 * Add the class for each element
	 * 
	 * @param  string $class
	 * @return MultiElementsAbstract
	 */
	public function addElementsClass($class) {
		foreach ($this->getElements() as $element) {
			if ($element instanceof Tag) {
				$element->addClass($class);
			}
		}
		
		return $this;
	}
	
	/**
	 * Remove the class from each element
	 * 
	 * @param  string $class
	 * @return MultiElementsAbstract
	 */
	public function removeElementsClass($class) {
		foreach ($this->getElements() as $element) {
			if ($element instanceof Tag) {
				$element->removeClass($class);
			}
		}
		
		return $this;
	}
	
	/**
	 * Add the style for each element
	 * 
	 * @param  string $param
	 * @param  string $value
	 * @return MultiElementsAbstract
	 */
	public function addElementsStyle($param, $value) {
		foreach ($this->getElements() as $element) {
			if ($element instanceof Tag) {
				$element->addStyle($param, $value);
			}
		}
		
		return $this;
	}
	
	/**
	 * Remove a style from each element
	 * 
	 * @param  string $param
	 * @return MultiElementsAbstract
	 */
	public function removeElementsStyle($param) {
		foreach ($this->getElements() as $element) {
			if ($element instanceof Tag) {
				$element->removeStyle($param);
			}
		}
		
		return $this;
	}
	
	/**
	 * Replace the existing element with the new element with the new name, preserving the order
	 * 
	 * @param  string | int $targetName
	 * @param  mixed $element
	 * @param  string | int $name
	 * @return MultiElementsAbstract
	 */
	public function setElementInsteadOf($targetName, $element, $name = null) {
		if (! isset($this->_elements[$targetName])) {
			return $this;
		}
		
		$elements = [];
		
		foreach ($this->_elements as $existsName => $existsElement) {
			
			if ($targetName === $existsName) {
				if ($name === null) {
					$elements[] = $element;
				} else {
					$elements[$name] = $element;
				}
			} else {
				$elements[$existsName] = $existsElement;
			}
		}
		
		$this->_elements = $elements;
		
		return $this;
	}
	
	/**
	 * Remove all the elements
	 * 
	 * @return MultiElementsAbstract
	 */
	public function removeElements() {
		$this->_elements = [];
		
		return $this;
	}
	
	/**
	 * The list is empty
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->_elements);
	}
	
	/**
	 * Count the elements
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->_elements);
	}
	
	/**
	 * Get the iterator of the elements list
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator($this->getElements());
	}
	
	/**
	 * Is the element exists ?
	 * 
	 * @param  string | int $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return $this->hasElement($offset);
	}
	
	/**
	 * Get the element
	 * 
	 * @param  string | int $offset
	 * @return Tag | null
	 */
	public function offsetGet($offset) {
		return $this->getElement($offset);
	}
	
	/**
	 * Set the element
	 * 
	 * @param string | int $offset
	 * @param mixed        $value
	 */
	public function offsetSet($offset, $value) {
		$this->addElement($value, $offset);
	}
	
	/**
	 * Remove the element
	 * 
	 * @param string | int $offset
	 */
	public function offsetUnset($offset) {
		$this->removeElement($offset);
	}
	
}