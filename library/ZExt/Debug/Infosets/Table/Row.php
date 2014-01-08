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

namespace ZExt\Debug\Infosets\Table;

use IteratorAggregate, ArrayIterator;

/**
 * Table's row information container
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage Infoset
 * @author     Mike.Mirten
 * @version    1.0
 */
class Row implements IteratorAggregate {
	
	const MARK_INFO    = 'info';
	const MARK_SUCCESS = 'success';
	const MARK_WARNING = 'warning';
	const MARK_ALERT   = 'alert';
	
	/**
	 * Row's content
	 *
	 * @var array
	 */
	protected $content;
	
	/**
	 * Row's special mark
	 *
	 * @var string
	 */
	protected $mark;
	
	/**
	 * Constructor
	 * 
	 * @param array  $content
	 * @param string $mark
	 */
	public function __construct(array $content = null, $mark = null) {
		if ($content !== null) {
			$this->setContent($content);
		}
		
		if ($mark !== null) {
			$this->markAs($mark);
		}
	}
	
	/**
	 * Set the content
	 * 
	 * @param  array $cells
	 * @return Row
	 */
	public function setContent(array $cells) {
		$this->content = $cells;
		
		return $this;
	}
	
	/**
	 * Get the content
	 * 
	 * @return array
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * Mar the content
	 * 
	 * @param  string $mark
	 * @return Row
	 */
	public function markAs($mark) {
		$this->mark = (string) $mark;
		
		return $this;
	}
	
	/**
	 * Is the content marked ?
	 * 
	 * @return bool
	 */
	public function isMarked() {
		return $this->mark !== null;
	}
	
	/**
	 * Get the mark
	 * 
	 * @return string
	 */
	public function getMark() {
		return $this->mark;
	}
	
	/**
	 * Get the content iterator
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator($this->content);
	}
	
}