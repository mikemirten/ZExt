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

use ZExt\Helper\HelperAbstract;

/**
 * Console color text helper
 * 
 * @category   ZExt
 * @package    Cli
 * @subpackage Helpers
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
class Color extends HelperAbstract {
	
	const COLOR_PATTERN = "\033[%sm";
	
	/**
	 * Color codes of the console font colors
	 *
	 * @var array
	 */
	protected $_fontColors = [
		'black'        => '0;30',
		'dark_gray'    => '1;30',
		'blue'         => '0;34',
		'light_blue'   => '1;34',
		'green'        => '0;32',
		'light_green'  => '1;32',
		'cyan'         => '0;36',
		'light_cyan'   => '1;36',
		'red'          => '0;31',
		'light_red'    => '1;31',
		'purple'       => '0;35',
		'light_purple' => '1;35',
		'brown'        => '0;33',
		'yellow'       => '1;33',
		'light_gray'   => '0;37',
		'white'        => '1;37'
	];
	
	/**
	 * Color codes of the console backgrounds
	 *
	 * @var array
	 */
	protected $_backgroundColors = [
		'black'      => '40',
		'red'        => '41',
		'green'      => '42',
		'yellow'     => '43',
		'blue'       => '44',
		'magenta'    => '45',
		'cyan'       => '46',
		'light_gray' => '47'
	];
	
	/**
	 * Run the helper
	 * 
	 * @param mixed $params
	 */
	public function color($string = null, $color = null, $background = null) {
		$result = '';
		
		if ($color !== null) {
			$result = sprintf(self::COLOR_PATTERN, $this->_fontColors[$color]);
		}
		
		if ($background !== null) {
			$result .= sprintf(self::COLOR_PATTERN, $this->_backgroundColors[$background]);
		}
		
		return $result . $string . sprintf(self::COLOR_PATTERN, 0);
	}
	
}