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

namespace ZExt\Formatter;

/**
 * Memory formatter
 * 
 * @category   ZExt
 * @package    Formatter
 * @author     Mike.Mirten
 * @version    1.0
 */
class Memory implements FormatterInterface {
	
	/**
	 * Format the memory
	 * 
	 * @param  int    $memory Memory in bytes
	 * @param  string $locale
	 * @return string
	 */
	public function format($memory, $params = null, $locale = null) {
		if ($memory == 0) {
			return 0;
		}

		if ($memory < 1024) {
			return round($memory) . 'b';
		}

		if ($memory < 1048576) {
			return round($memory / 1024, 2) . 'K';
		}

		if ($memory < 10485760) {
			return round($memory / 1024, 1) . 'K';
		}

		if ($memory < 104857600) {
			return round($memory / 1048576, 2) . 'M';
		}

		if ($memory < 1073741824) {
			return round($memory / 1048576, 1) . 'M';
		}

		if ($memory < 10737418240) {
			return round($memory / 1073741824, 2) . 'G';
		}
		
		return round($memory / 1073741824, 1) . 'G';
	}
	
}