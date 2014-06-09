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

namespace ZExt\Cli\Task\Helpers;

/**
 * Console color line helper
 * 
 * @category   ZExt
 * @package    Cli
 * @subpackage Helpers
 * @author     Mike.Mirten
 * @version    1.0
 */
class ColorLine extends Color {
	
	static private $colsSize;
	
	public function colorLine($string = null, $color = null, $background = null) {
		$string .= str_repeat(' ', self::getColsSize() - strlen($string));
		
		return $this->color($string, $color, $background);
	}
	
	static protected function getColsSize() {
		if (self::$colsSize === null) {
			self::$colsSize = (int) exec('tput cols');
		}
		
		return self::$colsSize;
	}
	
}