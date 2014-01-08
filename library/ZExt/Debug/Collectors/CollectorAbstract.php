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

namespace ZExt\Debug\Collectors;

use ZExt\Components\OptionsTrait;

use ZExt\Debug\Infosets\Infoset,
    ZExt\Debug\Infosets\InfosetTable;

/**
 * Collector abstract
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class CollectorAbstract implements CollectorInterface {
	
	use OptionsTrait;
	
	/**
	 * Constructor
	 * 
	 * @param array $params
	 */
	public function __construct(array $options = null) {
		if ($options !== null) {
			$this->setOptions($options);
		}
		
		$this->init();
	}
	
	/**
	 * Init the collector
	 * For extensions use
	 */
	protected function init(){}
	
	/**
	 * Format the value as a time
	 * 
	 * @param  int | float $seconds Time in seconds
	 * @return string
	 */
	function formatTime($seconds) {
		switch (true) {
			case $seconds == 0:
				return 0;
			
			case $seconds < 0.01:
				$time = round($seconds * 1000, 2) . 'ms';
				break;
			
			case $seconds < 0.1:
				$time = round($seconds * 1000, 1) . 'ms';
				break;
			
			case $seconds < 1:
				$time = round($seconds * 1000) . 'ms';
				break;
			
			case $seconds < 10:
				$time = round($seconds, 2) . 's';
				break;
			
			default:
				$time = round($seconds, 1) . 's';
		}
		
		return $time;
	}
	
	/**
	 * Format the value as a memory
	 * 
	 * @param  int $memory
	 * @return string
	 */
	function formatMemory($memory) {
		switch (true) {
			case $memory == 0:
				return 0;
			
			case $memory < 1024:
				$memory = round($memory) . 'b';
				break;
			
			case $memory < 1048576:
				$memory = round($memory / 1024, 2) . 'K';
				break;
			
			case $memory < 10485760:
				$memory = round($memory / 1024, 1) . 'K';
				break;
			
			case $memory < 104857600:
				$memory = round($memory / 1024) . 'K';
				break;
			
			case $memory < 1073741824:
				$memory = round($memory / 1048576, 1) . 'M';
				break;
			
			default:
				$memory = round($memory / 1048576) . 'M';
		}
		
		return $memory;
	}
	
	/**
	 * Create the base information set (multipart)
	 * 
	 * @return Infoset
	 */
	protected function createInfoset() {
		return new Infoset();
	}
	
	/**
	 * Create the list information set
	 * 
	 * @return Infoset
	 */
	protected function createList() {
		$info = new Infoset();
		$info->setContentType(Infoset::TYPE_LIST);
		
		return $info;
	}
	
	/**
	 * Create the description list information set
	 * 
	 * @return Infoset
	 */
	protected function createDesclist() {
		$info = new Infoset();
		$info->setContentType(Infoset::TYPE_DESCLIST);
		
		return $info;
	}

	/**
	 * Create the table information set
	 * 
	 * @return InfosetTable
	 */
	protected function createTable() {
		$info = new InfosetTable();
		
		return $info;
	}
	
}