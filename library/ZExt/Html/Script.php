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
 * Script tag's abstraction
 * 
 * @package    Html
 * @subpackage Script
 * @author     Mike.Mirten
 * @version    1.0
 */
class Script extends Tag {
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'script';
	
	/**
	 * Tag's attributes
	 * 
	 * @var string[]
	 */
	protected $_attrs = ['type' => 'text/javascript'];
	
	/**
	 * Constructor
	 * 
	 * @param string         $content Script content
	 * @param string | array $attrs   Additional attributes
	 */
	public function __construct($content = null, $attrs = null) {
		parent::__construct(null, $content, $attrs);
	}
	
}