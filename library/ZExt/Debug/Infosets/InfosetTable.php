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

use ZExt\Debug\Infosets\Table\Row;

/**
 * Container for an information represented as a table
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage Infoset
 * @author     Mike.Mirten
 * @version    1.0
 */
class InfosetTable extends Infoset {

	/**
	 * Table head's row content
	 *
	 * @var array
	 */
	protected $tableHead;
	
	/**
	 * Table cols widths
	 *
	 * @var srrsy
	 */
	protected $tableColgroup;
	
	/**
	 * The information representation type
	 *
	 * @var string
	 */
	protected $contentType = self::TYPE_TABLE;
	
	/**
	 * Set the head's row content
	 * 
	 * @param  array $content
	 * @return InfosetTable
	 */
	public function setHeadContent(array $content) {
		$this->tableHead = $content;
		
		return $this;
	}
	
	/**
	 * Get the head's row content
	 */
	public function getHeadContent() {
		return $this->tableHead;
	}
	
	/**
	 * Set the columns' widths
	 * 
	 * @param  array $widths
	 * @return InfosetTable
	 */
	public function setColsWidths(array $widths) {
		$this->tableColgroup = $widths;
		
		return $this;
	}
	
	/**
	 * Get the columns' widths
	 * 
	 * @return array
	 */
	public function getColsWidths() {
		return $this->tableColgroup;
	}
	
	/**
	 * Push the content as an info
	 * 
	 * @param  mixed $content
	 * @return InfosetTable
	 */
	public function pushInfo($content) {
		$this->pushContent(new Row($content, Row::MARK_INFO));
		
		return $this;
	}
	
	/**
	 * Push the content as a success
	 * 
	 * @param  mixed $content
	 * @return InfosetTable
	 */
	public function pushSuccess($content) {
		$this->pushContent(new Row($content, Row::MARK_SUCCESS));
		
		return $this;
	}
	
	/**
	 * Push the content as a warning
	 * 
	 * @param  mixed $content
	 * @return InfosetTable
	 */
	public function pushWarning($content) {
		$this->pushContent(new Row($content, Row::MARK_WARNING));
		
		return $this;
	}
	
	/**
	 * Push the content as an alert
	 * 
	 * @param  mixed $content
	 * @return InfosetTable
	 */
	public function pushAlert($content) {
		$this->pushContent(new Row($content, Row::MARK_ALERT));
		
		return $this;
	}
	
}