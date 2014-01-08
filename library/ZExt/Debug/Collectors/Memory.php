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
 * @version   2.0
 */

namespace ZExt\Debug\Collectors;

use ZExt\Debug\Infosets\Infoset;

/**
 * Memory usage information collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    2.0
 */
class Memory extends CollectorAbstract {
	
	const ICON = 'data:image/png;base64,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';
	
	/**
	 * Get the collected information
	 * 
	 * @return Infoset
	 */
	public function getInfo() {
		$info = $this->createInfoset();
		
		$memory = memory_get_peak_usage();
		$limit  = ini_get('memory_limit');
		
		$info->setIcon('chip')
		     ->setTitle($this->formatMemory($memory) . ' of ' . $limit);
		
		if ($limit / $memory < 0.25) {
			$info->setLevel(Infoset::LEVEL_WARNING);
		}
		
		return $info;
	}
	
}