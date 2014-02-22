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

namespace ZExt\Topology;

use IteratorAggregate, ArrayObject, ArrayAccess, Countable;

/**
 * Topology descriptor
 * 
 * @category   ZExt
 * @package    Topology
 * @subpackage Descriptor
 * @author     Mike.Mirten
 * @version    1.0
 */
class Descriptor implements IteratorAggregate, ArrayAccess, Countable {
	
	const TYPE_DEFAULT = 'default';
	const TYPE_PRIMARY = 'primary';
	const TYPE_SUCCESS = 'success';
	const TYPE_WARNING = 'warning';
	const TYPE_ALERT   = 'alert';
	
	/**
	 * Type
	 *
	 * @var string 
	 */
	protected $_type = self::TYPE_DEFAULT;
	
	/**
	 * Title
	 *
	 * @var string 
	 */
	protected $_title = 'Element';
	
	/**
	 * Label
	 *
	 * @var string
	 */
	protected $_label;
	
	/**
	 * Badge
	 *
	 * @var string
	 */
	protected $_badge;
	
	/**
	 * Children list
	 *
	 * @var ArrayObject 
	 */
	protected $_children;
	
	/**
	 * Propertities list
	 *
	 * @var ArrayObject 
	 */
	protected $_propertities;
	
	/**
	 * Constructor
	 * 
	 * @param string $title
	 * @param int    $type
	 */
	public function __construct($title = null, $type = null) {
		$this->_children     = new ArrayObject();
		$this->_propertities = new ArrayObject();
		
		if ($title !== null) {
			$this->setTitle($title);
		}
		
		if ($type !== null) {
			$this->setType($type);
		}
	}
	
	/**
	 * Set the title
	 * 
	 * @param  string $title
	 * @return Descriptor
	 */
	public function setTitle($title) {
		$this->_title = (string) $title;
		
		return $this;
	}
	
	/**
	 * Get the title
	 * 
	 * @return string
	 */
	public function getTitle() {
		return $this->_title;
	}
	
	/**
	 * Set the descriptor type
	 * 
	 * @param  string $type
	 * @return Descriptor
	 */
	public function setType($type) {
		$this->_type = (string) $type;
		
		return $this;
	}
	
	/**
	 * Set the descriptor type
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->_type;
	}
	
	/**
	 * Set the descriptor label
	 * 
	 * @param string $label
	 * @return Descriptor
	 */
	public function setLabel($label) {
		$this->_label = (string) $label;
		
		return $this;
	}
	
	/**
	 * Set the descriptor badge
	 * 
	 * @param  string $badge
	 * @return Descriptor
	 */
	public function setBadge($badge) {
		$this->_badge = $badge;
		
		return $this;
	}
	
	/**
	 * Set the property
	 * 
	 * @param  string $property
	 * @param  mixed  $value
	 * @return Descriptor
	 */
	public function setProperty($property, $value) {
		$this->_propertities->offsetSet($property, $value);
		
		return $this;
	}
	
	/**
	 * Get the property
	 * 
	 * @param  string $property
	 * @return mixed
	 */
	public function getProperty($property) {
		return $this->_propertities->offsetGet($property);
	}
	
	/**
	 * Is the property exsists ?
	 * 
	 * @param  string $property
	 * @return bool
	 */
	public function hasProperty($property) {
		return $this->_propertities->offsetExists($property);
	}
	
	/**
	 * Remove the property
	 * 
	 * @param  string $property
	 * @return Descriptor
	 */
	public function removeProperty($property) {
		$this->_propertities->offsetUnset($property);
		
		return $this;
	}
	
	public function hasPropertities() {
		return $this->_propertities->count() > 0;
	}
	
	/**
	 * Get the propertities list
	 * 
	 * @return SplStack
	 */
	public function getProperties() {
		return $this->_propertities;
	}
	
	/**
	 * Add the children
	 * 
	 * @param  array  $children
	 * @param  string $type
	 * @return Descriptor
	 */
	public function addChildren(array $children, $type = self::TYPE_DEFAULT) {
		foreach ($children as $name => $child) {
			if (is_string($name)) {
				$this->addChild($child, $name, $type);
				continue;
			}
			
			$this->addChild($child, null, $type);
		}
		
		return $this;
	}
	
	/**
	 * Add the child
	 * 
	 * @param  Descriptor | string $child
	 * @param  string              $name
	 * @return Descriptor
	 */
	public function addChild($child, $name = null, $type = self::TYPE_DEFAULT) {
		if (! $child instanceof Descriptor) {
			$child = new static($child, $type);
		}
		
		if ($name === null) {
			$this->_children[] = $child;
			return $child;
		}
		
		$this->_children[$name] = $child;
		return $child;
	}
	
	/**
	 * Get the children list
	 * 
	 * @return SplStack
	 */
	public function getChildren() {
		return $this->_children;
	}
	
	/**
	 * Has the element a children ?
	 * 
	 * @return bool
	 */
	public function hasChildren() {
		return $this->_children->count() > 0;
	}
	
	/**
	 * Get the element's total children number
	 * 
	 * @return int
	 */
	public function getChildrenNumber() {
		return $this->_children->count();
	}
	
	public function __set($name, $value) {
		$this->setProperty($name, $value);
	}
	
	public function __get($name) {
		return $this->getProperty($name);
	}
	
	public function __isset($name) {
		return $this->isProperty($name);
	}
	
	public function __unset($name) {
		$this->removeProperty($name);
	}
	
	public function count() {
		return $this->_propertities->count();
	}
	
	/**
	 * Add the child or the array of children
	 * 
	 * @param  string              $offset
	 * @param  Descriptor | string $value
	 * @return Descriptor
	 */
	public function offsetSet($offset, $value) {
		if (is_array($value)) {
			$this->addChildren($value, is_string($offset) ? $offset : null);
			return;
		}
		
		$this->addChild($value, $offset);
	}
	
	public function offsetGet($offset) {
		
	}
	
	public function offsetExists($offset) {
		
	}
	
	public function offsetUnset($offset) {
		
	}
	
	public function getIterator() {
		return $this->_children;
	}
	
}