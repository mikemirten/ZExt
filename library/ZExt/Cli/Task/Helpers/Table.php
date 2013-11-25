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
 * Console table builder helper
 * 
 * @category   ZExt
 * @package    Cli
 * @subpackage Helpers
 * @author     Mike.Mirten
 * @version    1.0
 */
class Table extends HelperAbstract {
	
	/**
	 * Run the helper
	 * 
	 * @param array $data
	 * @param int   $cellPadding
	 */
	public function table(array $data, $cellPadding = 1) {
		if ($cellPadding < 0) {
			$cellPadding = 0;
		}
		
		// Dimensions calculation
		$columnsNumber = 0;
		$columnsWidths = [];
		
		foreach ($data as $row) {
			$columnsCount = count($row);
			
			if ($columnsCount > $columnsNumber) {
				$columnsNumber = $columnsCount;
			}
			
			foreach (array_values($row) as $key => $col) {
				$length = strlen($col);
				
				if (isset($columnsWidths[$key])) {
					if ($length > $columnsWidths[$key]) {
						$columnsWidths[$key] = $length;
					}
				} else {
					$columnsWidths[$key] = $length;
				}
			}
		}
		
		// Table rendering
		$tableRows = [];
		
		foreach ($data as $row) {
			$row      = array_values($row);
			$tableCol = '|';
			
			for ($colNm = 0; $colNm < $columnsNumber; ++ $colNm) {
				$colWidth = $columnsWidths[$colNm];
				$col      = isset($row[$colNm]) ? $row[$colNm] : '';
				$len      = strlen($col);
				
				if ($colWidth > $len) {
					$col .= str_repeat(' ', $colWidth - $len);
				}
				
				$paddings  = str_repeat(' ', $cellPadding);
				$tableCol .= $paddings . $col . $paddings . '|';
			}
			
			$tableRows[] = $tableCol;
		}
		
		$delimLine = '+';
		
		foreach ($columnsWidths as $width) {
			$delimLine .= str_repeat('-', $width + $cellPadding * 2) . '+';
		}
		
		$body = implode(PHP_EOL . $delimLine . PHP_EOL, $tableRows);
		
		echo $delimLine . PHP_EOL . $body . PHP_EOL . $delimLine;
	}
	
}