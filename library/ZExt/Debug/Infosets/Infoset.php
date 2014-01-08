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

namespace ZExt\Debug\Infosets;

use SplDoublyLinkedList, IteratorAggregate, ArrayAccess, Countable;

/**
 * Container for a collected information
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage Infoset
 * @author     Mike.Mirten
 * @version    1.0
 */
class Infoset implements IteratorAggregate, ArrayAccess, Countable {

	const LEVEL_ALERT   = 100;
	const LEVEL_WARNING = 200;
	const LEVEL_INFO    = 300;
	
	const TYPE_DUMP     = 'dump';
	const TYPE_MULTI    = 'multi';
	const TYPE_TEXT     = 'text';
	const TYPE_TABLE    = 'table';
	const TYPE_LIST     = 'list';
	const TYPE_DESCLIST = 'desc';
	
	const ICON_ASSET  = 'asset';
	const ICON_PATH   = 'url';
	const ICON_BASE64 = 'base64';
	
	/**
	 * The info importance level
	 *
	 * @var int 
	 */
	protected $level = self::LEVEL_INFO;
	
	/**
	 * The information representation type
	 *
	 * @var string
	 */
	protected $contentType = self::TYPE_MULTI;
	
	/**
	 * The icon source type
	 *
	 * @var string
	 */
	protected $iconType = self::ICON_ASSET;
	
	/**
	 * The icon source
	 *
	 * @var string
	 */
	protected $iconSource;
	
	/**
	 * The information title
	 *
	 * @var string
	 */
	protected $title;
	
	/**
	 * The module name
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * The collected information
	 *
	 * @var SplDoublyLinkedList
	 */
	protected $content;

	/**
	 * Type of the highlight
	 *
	 * @var string
	 */
	protected $highlight;
	
	/**
	 * Is the BB codes handling need ?
	 *
	 * @var bool 
	 */
	protected $bbCodes = false;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->content = new SplDoublyLinkedList();
	}
	
	/**
	 * Set the information importance level
	 * 
	 * @param  int $level
	 * @return InfoSet
	 */
	public function setLevel($level) {
		$this->level = (int) $level;
		
		return $this;
	}
	
	/**
	 * Get the information importance level
	 * 
	 * @return int
	 */
	public function getLevel() {
		return $this->level;
	}
	
	/**
	 * Set the information title
	 * 
	 * @param  string $title
	 * @return InfoSet
	 */
	public function setTitle($title) {
		$this->title = (string) $title;
		
		return $this;
	}
	
	/**
	 * Get the information title
	 * 
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Set the collector name
	 * 
	 * @param  string $name
	 * @return Infoset
	 */
	public function setName($name) {
		$this->name = (string) $name;
		
		return $this;
	}
	
	/**
	 * Get the collector name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Has the information title ?
	 * 
	 * @return bool
	 */
	public function hasTitle() {
		return $this->title !== null;
	}
	
	/**
	 * Set the information representation type
	 * 
	 * @param string $type
	 */
	public function setContentType($type) {
		$this->contentType = (string) $type;
		
		return $this;
	}
	
	/**
	 * Get the information representation type
	 * 
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}
	
	/**
	 * Set the content BB codes handling
	 * 
	 * @param  bool $using
	 * @return InfoSet
	 */
	public function enableBbCodes() {
		$this->bbCodes = true;
		
		return $this;
	}
	
	/**
	 * Is the content BB codes handling need ?
	 * 
	 * @return bool
	 */
	public function isContentBbCodes() {
		return $this->bbCodes;
	}

	/**
	 * Set the icon source and type
	 * 
	 * @param  string $type
	 * @param  string $source
	 * @return InfoSet
	 */
	public function setIcon($source, $type = self::ICON_ASSET) {
		$this->iconType   = (string) $type;
		$this->iconSource = (string) $source;
		
		return $this;
	}
	
	/**
	 * Has the icon specified ?
	 * 
	 * @return bool
	 */
	public function hasIcon() {
		return $this->iconSource !== null;
	}
	
	/**
	 * Get the icon type
	 * 
	 * @return string
	 */
	public function getIconType() {
		return $this->iconType;
	}
	
	/**
	 * Get the icon source
	 * 
	 * @return string
	 */
	public function getIconSource() {
		return $this->iconSource;
	}
	
	/**
	 * Set the highlight language if highlighting is needed
	 * 
	 * @param  string $language
	 * @return InfoSet
	 */
	public function setHighlight($language) {
		$this->highlight = (string) $language;
		
		return $this;
	}
	
	/**
	 * Get the highlight language
	 * 
	 * @return string | null if a highlight is not needed
	 */
	public function getHighlight() {
		return $this->highlight;
	}
	
	/**
	 * Get information content
	 * 
	 * @return \Iterator
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * 
	 * @param  mixed $content
	 * @return InfoSet
	 */
	public function pushContent($content) {
		$this->content[] = $content;
		
		return $this;
	}
	
	/**
	 * Get iterator
	 * 
	 * @return \Iterator
	 */
	public function getIterator() {
		return $this->content;
	}
	
	/**
	 * Is no content ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return $this->content->isEmpty();
	}
	
	/**
	 * Count the elements of the content
	 * 
	 * @return int
	 */
	public function count() {
		return $this->content->count();
	}
	
	public function offsetExists($offset) {
		return $this->content->offsetExists($offset);
	}
	
	public function offsetGet($offset) {
		return $this->content->offsetGet($offset);
	}
	
	public function offsetSet($offset, $value) {
		$this->content->offsetSet($offset, $value);
	}
	
	public function offsetUnset($offset) {
		$this->content->offsetUnset($offset);
	}
	
}