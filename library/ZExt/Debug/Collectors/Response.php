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

use ZExt\Debug\Infosets\Infoset;

/**
 * Response information collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    1.0
 */
class Response extends CollectorAbstract {
	
	/**
	 * Get the collected information
	 * 
	 * @return Infoset
	 */
	public function getInfo() {
		$info = $this->createInfoset()
			->setName('Response info')
			->setIcon('arrowup');
		
		$this->createTitle($info);
		$this->createContent($info);
		
		return $info;
	}
	
	/**
	 * Create the title information
	 * 
	 * @param Infoset $info
	 */
	protected function createTitle(Infoset $info) {
		if (php_sapi_name() === 'cli') {
			$info->setTitle('[success]CLI[/success]');
			return;
		}
		
		$code = http_response_code();
		
		$title = ($code < 400)
			? '[success]' . $code . '[/success]'
			: '[alert]' . $code . '[/alert]';
		
		if (ob_get_level() > 0) {
			$title .= ' / ' . $this->formatMemory(ob_get_length());
		}
		
		$info->setTitle($title);
	}
	
	/**
	 * Create the content
	 * 
	 * @param Infoset $info
	 */
	protected function createContent(Infoset $info) {
		if (php_sapi_name() === 'cli') {
			return;
		}
		
		$headersList = $this->createList();
		$headersList->setTitle('Headers');
		
		$totalSize = 0;
		
		foreach (headers_list() as $header) {
			$totalSize += strlen($header);
			$headersList[] = $header;
		}
		
		$infoTable = $this->createTable()
			->enableBbCodes()
			->setTitle('Info');
		
		$contentSize = (ob_get_level() > 0)
			? $this->formatMemory(ob_get_length())
			: 'Unknown';
		
		$infoTable[] = ['Headers size:', '[info]' . $this->formatMemory($totalSize) . '[/info]'];
		$infoTable[] = ['Content size:', '[info]' . $contentSize . '[/info]'];
		
		$info[] = $infoTable;
		$info[] = $headersList;
	}
	
}