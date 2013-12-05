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

use ZExt\Html\Table\TablePart;
use ZExt\Html\Table\TableColgroup;

/**
 * Html table's abstraction
 * 
 * @package    Html
 * @subpackage Table
 * @author     Mike.Mirten
 * @version    2.0
 */
class Table extends Tag {
	
	const TAG_TABLE = 'table';
	const TAG_HEAD  = 'thead';
	const TAG_BODY  = 'tbody';
	const TAG_FOOT  = 'tfoot';
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = self::TAG_TABLE;
	
	/**
	 * The table's colgroup
	 *
	 * @var TableColgroup
	 */
	protected $_colgroup;
	
	/**
	 * The table's head
	 *
	 * @var TablePart 
	 */
	protected $_head;
	
	/**
	 * The table's body
	 *
	 * @var TablePart 
	 */
	protected $_body;
	
	/**
	 * The table's foot
	 *
	 * @var TablePart 
	 */
	protected $_foot;
	
	/**
	 * Constructor
	 * 
	 * @param array $elements
	 * @param array $attrs
	 */
	public function __construct(array $elements = null, $attrs = null) {
		parent::__construct(null, null, $attrs);
		
		if ($elements !== null) {
			$this->getBody()->addElements($elements);
		}
	}
	
	/**
	 * Get the table's head
	 * 
	 * @return TablePart;
	 */
	public function getHead() {
		if ($this->_head === null) {
			$this->_head = new TablePart();
			$this->_head->setTag(self::TAG_HEAD);
		}
		
		return $this->_head;
	}
	
	/**
	 * Get the table's body
	 * 
	 * @return TablePart;
	 */
	public function getBody() {
		if ($this->_body === null) {
			$this->_body = new TablePart();
			$this->_body->setTag(self::TAG_BODY);
		}
		
		return $this->_body;
	}
	
	/**
	 * Get the table's foot
	 * 
	 * @return TablePart;
	 */
	public function getFoot() {
		if ($this->_foot === null) {
			$this->_foot = new TablePart();
			$this->_foot->setTag(self::TAG_FOOT);
		}
		
		return $this->_foot;
	}
	
	/**
	 * Render the table
	 * 
	 * @param  string $html
	 * @return string
	 */
	public function render($html = null) {
		$parts = [];
		
		if ($this->_colgroup !== null) {
			$parts[] = $this->_colgroup->render();
		}
		
		if ($this->_head !== null) {
			$parts[] = $this->_head->render();
		}
		
		if ($this->_body !== null) {
			$parts[] = $this->_body->render();
		}
		
		if ($this->_foot !== null) {
			$parts[] = $this->_foot->render();
		}
		
		return parent::render(implode($this->getSeparator(), $parts));
	}
	
}