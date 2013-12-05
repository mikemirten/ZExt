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

/**
 * Html tag's abstraction
 * 
 * @package    Html
 * @subpackage Tag
 * @author     Mike.Mirten
 * @version    1.1.1
 */
class Tag {
	
	// Tag's attributes
	const ATTR_CLASS = 'class';
	const ATTR_STYLE = 'style';
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'div';
	
	/**
	 * Tag is closed
	 * 
	 * @var bool
	 */
	protected $_closed = false;
	
	/**
	 * Tag's inner html
	 * 
	 * @var string
	 */
	protected $_html = '';
	
	/**
	 * Tag's attributes
	 * 
	 * @var string[]
	 */
	protected $_attrs = array();
	
	/**
	 * Tag's classes
	 * 
	 * @var string[]
	 */
	protected $_classes = array();
	
	/**
	 * Tag's parts of the style
	 *
	 * @var string[]
	 */
	protected $_style = array();
	
	/**
	 * Tags' separator
	 * 
	 * @var string
	 */
	protected $_separator = PHP_EOL;
	
	/**
	 * Constructor
	 * 
	 * @param string         $tag
	 * @param string         $html
	 * @param array | string $attrs Attributes of the tag. Or a class if a string is passed
	 */
	public function __construct($tag = null, $html = null, $attrs = null) {
		if ($tag !== null) {
			$this->setTag($tag);
		}
		
		if ($html !== null) {
			$this->setHtml($html);
		}
		
		if (is_array($attrs)) {
			if (isset($attrs[self::ATTR_CLASS])) {
				is_array($attrs[self::ATTR_CLASS]) ?
					$this->addClasses($attrs[self::ATTR_CLASS]) :
					$this->addClass($attrs[self::ATTR_CLASS]);

				unset($attrs[self::ATTR_CLASS]);
			}
			
			if (isset($attrs[self::ATTR_STYLE])) {
				is_array($attrs[self::ATTR_STYLE]) ?
					$this->addStyles($attrs[self::ATTR_STYLE]) :
					$this->addStyle($attrs[self::ATTR_STYLE]);

				unset($attrs[self::ATTR_STYLE]);
			}
			
			$this->setAttrs($attrs);
		}
		else if (is_string($attrs)) {
			$this->addClass($attrs);
		}
		
		$this->init();
	}
	
	/**
	 * For an extensions use
	 */
	protected function init(){}
	
	/**
	 * Set the separator of a tags 
	 * For extensions use
	 * 
	 * @return string
	 */
	public function setSeparator($separator = PHP_EOL) {
		$this->_separator = $separator;
	}
	
	/**
	 * Get the separator of a tags 
	 * For extensions use
	 * 
	 * @return string
	 */
	public function getSeparator() {
		return $this->_separator;
	}
	
	/**
	 * Set the tag's name
	 * 
	 * @param  string $tag
	 * @return Tag
	 */
	public function setTag($tag) {
		$this->_tag = $tag;
		
		return $this;
	}
	
	/**
	 * Get the tag's name
	 * 
	 * @return string
	 */
	public function getTag() {
		return $this->_tag;
	}
	
	/**
	 * Set the tag is closed or not
	 * 
	 * @param  bool $flag
	 * @return Tag
	 */
	public function setClosed($flag = true) {
		$this->_closed = (bool) $flag;
		
		return $this;
	}
	
	/**
	 * Is the tag is closed ?
	 * 
	 * @return bool
	 */
	public function getClosed() {
		return $this->_closed;
	}
	
	/**
	 * Set the tag's inner html
	 * 
	 * @param  mixed $html
	 * @return Tag
	 */
	public function setHtml($html) {
		$this->_html = $html;
		
		return $this;
	}
	
	/**
	 * Get the tag's inner html
	 * 
	 * @return string
	 */
	public function getHtml() {
		return $this->_html;
	}
	
	/**
	 * Set the tag's attributes
	 * 
	 * @param  array $attrs
	 * @return Tag
	 */
	public function setAttrs(array $attrs) {
		foreach ($attrs as $attr => $value) {
			$this->setAttr($attr, $value);
		}
		
		return $this;
	}
	
	/**
	 * Set the tag's attribute
	 * 
	 * @param  string $attr
	 * @param  string $value
	 * @return Tag
	 */
	public function setAttr($attr, $value) {
		$this->_attrs[$attr] = trim($value);
		
		return $this;
	}
	
	/**
	 * Get the attributes of the tag
	 * 
	 * @return array
	 */
	public function getAttrs() {
		return $this->_attrs;
	}
	
	/**
	 * Get the tag's attribute
	 * 
	 * @param  string $attr
	 * @return string | null
	 */
	public function getAttr($attr) {
		if (isset($this->_attrs[$attr])) {
			return $this->_attrs[$attr];
		}
	}
	
	/**
	 * Remove the tag's attribute
	 * 
	 * @param  string $attr
	 * @return Tag
	 */
	public function removeAttr($attr) {
		unset($this->_attrs[$attr]);
		
		return $this;
	}
	
	/**
	 * Has the tag's attr
	 * 
	 * @param  string $attr
	 * @return bool
	 */
	public function hasAttr($attr) {
		return isset($this->_attrs[$attr]);
	}
	
	/**
	 * Add the classes to the tag
	 * 
	 * @param  array $classes
	 * @return Tag
	 */
	public function addClasses(array $classes) {
		foreach ($classes as $class) {
			$this->addClass($class);
		}
		
		return $this;
	}
	
	/**
	 * Get the classes of the tag
	 * 
	 * @return array
	 */
	public function getClasses() {
		return array_unique($this->_classes);
	}
	
	/**
	 * Add the class to the tag 
	 * Overrides the "class" attribute
	 * 
	 * @param  string $class
	 * @return Tag
	 */
	public function addClass($class) {
		$class = trim($class);
		
		if (strpos($class, ' ') !== false) {
			$this->addClasses(explode(' ', $class));
			return $this;
		}
		
		$this->_classes[] = $class;
		
		return $this;
	}
	
	/**
	 * Remove the class from the tag
	 * 
	 * @param  string $class
	 * @return Tag
	 */
	public function removeClass($class) {
		$key = array_search($class, $this->_classes);
		
		if ($key !== false)	{
			unset($this->_classes[$key]);
		}
		
		return $this;
	}
	
	/**
	 * Has the tag's class
	 * 
	 * @param  string $class
	 * @return bool
	 */
	public function hasClass($class) {
		return in_array($class, $this->_classes);
	}
	
	/**
	 * Add the styles to the tag
	 * 
	 * @param  array $styles
	 * @return Tag
	 */
	public function addStyles(array $styles) {
		foreach ($styles as $param => $value) {
			$this->addStyle($param, $value);
		}
		
		return $this;
	}
	
	/**
	 * Get the styles of the tag
	 * 
	 * @return array
	 */
	public function getStyles() {
		return $this->_style;
	}
	
	/**
	 * Add the style element to the tag 
	 * Owerrides the "style" attribute
	 * 
	 * @param  string $param
	 * @param  string $value
	 * @return Tag
	 */
	public function addStyle($param, $value) {
		$this->_style[$param] = $value;
		
		return $this;
	}
	
	/**
	 * Get the tag's style element
	 * 
	 * @param  string $param
	 * @return string | null
	 */
	public function getStyle($param) {
		if (isset($this->_style[$param])) {
			return $this->_style[$param];
		}
	}
	
	/**
	 * Remove the tag's style element
	 * 
	 * @param  string $param
	 * @return Tag
	 */
	public function removeStyle($param) {
		unset($this->_style[$param]);
		
		return $this;
	}
	
	/**
	 * Has the tag's style element
	 * 
	 * @return bool
	 */
	public function hasStyle($param) {
		return isset($this->_style[$param]);
	}
	
	/**
	 * Render the tag
	 * 
	 * @param  string $html
	 * @return string
	 */
	public function render($html = null) {
		$tag = '<' . $this->getTag();
		
		$class = $this->_renderClasses();
		if ($class !== null) {
			$this->setAttr(self::ATTR_CLASS, $class);
		}
		
		$style = $this->_renderStyle();
		if ($style !== null) {
			$this->setAttr(self::ATTR_STYLE, $style);
		}
		
		$attrs = $this->_renderAttrs();
		if ($attrs !== null) {
			$tag.= ' ' . $attrs;
		}
		
		if ($this->getClosed()) {
			$tag.= ' />';
		} else {
			if ($html === null) {
				$html = $this->getHtml();
			}
			
			$tag.= '>' . $html . '</' . $this->getTag() . '>';
		}
		
		return $tag;
	}
	
	/**
	 * Render the attributes
	 * 
	 * @return string
	 */
	protected function _renderAttrs() {
		$attrsRaw = $this->getAttrs();
		if (empty($attrsRaw)) return;
		
		$attrsStr = array();
		foreach ($attrsRaw as $attr => $value) {
			$attrsStr[] = $attr . '="' . $value . '"';
		}
		
		return implode(' ', $attrsStr);
	}
	
	/**
	 * Render the "class" attribute's value
	 * 
	 * @return string
	 */
	protected function _renderClasses() {
		$classes = $this->getClasses();
		if (empty($classes)) return;
		
		return implode(' ', $classes);
	}
	
	/**
	 * Render the "style" attribute's value
	 * 
	 * @return string
	 */
	protected function _renderStyle() {
		$styleRaw = $this->getStyles();
		if (empty($styleRaw)) return;
		
		$styleStr = array();
		foreach ($styleRaw as $param => $value) {
			$styleStr[] = $param . ':' . $value . ';';
		}
		
		return implode(' ', $styleStr);
	}
	
	/**
	 * Render the tag
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}
	
	/**
	 * Render the tag
	 * 
	 * @param  string $html
	 * @return string
	 */
	public function __invoke($html = null) {
		return $this->render($html);
	}
	
	/**
	 * Set the attr
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {
		$this->setAttr($name, $value);
	}
	
	/**
	 * Get the attr
	 * 
	 * @param  string $name
	 * @return string | null
	 */
	public function __get($name) {
		return $this->getAttr($name);
	}
	
	/**
	 * Is the attr exists ?
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->hasAttr($name);
	}
	
	/**
	 * Remove the attr
	 * 
	 * @param string $name
	 */
	public function __unset($name) {
		$this->removeAttr($name);
	}
	
}