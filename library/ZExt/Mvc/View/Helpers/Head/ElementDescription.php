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

namespace ZExt\Mvc\View\Helpers\Head;

use ZExt\Html\Tag;

/**
 * Description element
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
class ElementDescription implements ElementInterface {
	
	/**
	 * Meta description
	 *
	 * @var string
	 */
	protected $description;
	
	/**
	 * Set the description
	 * 
	 * @param  string $description
	 * @return Head
	 */
	public function setDescription($description) {
		$this->description = trim($description);
		
		return $this;
	}
	
	/**
	 * Get the description
	 * 
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Is the description empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return $this->description === null;
	}

	/**
	 * Assemble the description tag
	 * 
	 * @param  string $description
	 * @return string
	 */
	public function assemble() {
		$descTag = new Tag('meta');
		$descTag->setClosed();

		$descTag->name    = 'description';
		$descTag->content = $this->description;

		return $descTag->render();
	}
	
}