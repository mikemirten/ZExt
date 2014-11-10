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

namespace ZExt\File;

/**
 * File abstraction
 * 
 * @category   ZExt
 * @package    File
 * @subpackage File
 * @author     Mike.Mirten
 * @version    1.0
 */
class File implements FileInterface {
	
	/**
	 * Path to file
	 *
	 * @var string 
	 */
	protected $path;
	
	/**
	 * Constructor
	 * 
	 * @param string $path
	 */
	public function __construct($path) {
		$this->path = $path;
	}
	
	/**
	 * Get content of file
	 * 
	 * @return string
	 * @throws Exceptions\InvalidPath
	 */
	public function getContent() {
		$path = realpath($this->path);
		
		if ($path === false) {
			throw new Exceptions\InvalidPath('File "' . $path . '" doesn\'t exists or inaccsessible', null, null, $this->path);
		}
		
		$content = file_get_contents($path);
		
		if ($content === false) {
			throw new Exceptions\InvalidPath('File "' . $path . '" is unreadable', null, null, $this->path);
		}
		
		return $content;
	}
	
}