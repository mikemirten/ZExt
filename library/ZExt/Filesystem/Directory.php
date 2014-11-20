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

namespace ZExt\Filesystem;

use ZExt\Filesystem\Exceptions\InvalidPath;

/**
 * Directory abstraction
 * 
 * @category   ZExt
 * @package    File
 * @subpackage File
 * @author     Mike.Mirten
 * @version    1.0
 */
class Directory implements DirectoryInterface {
	
	/**
	 * Path to directory
	 *
	 * @var string 
	 */
	protected $path;
	
	/**
	 * Constructor
	 * 
	 * @param  string $path
	 * @throws InvalidPath
	 */
	public function __construct($path) {
		$this->path = $path;
	}
	
	/**
	 * Get a file from directory
	 * 
	 * @param  string $path Relative path
	 * @return File
	 * @throws InvalidPath
	 */
	public function getFile($path) {
		$dirPath = realpath($this->path);
		
		if ($dirPath === false) {
			throw new InvalidPath('Path "' . $this->path . '" doesn\'t exists or inaccsessible', null, null, $this->path);
		}
		
		return new File($dirPath . DIRECTORY_SEPARATOR . $path);
	}
	
}