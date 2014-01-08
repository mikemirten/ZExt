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
 * Request information collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    1.0
 */
class Request extends CollectorAbstract {
	
	/**
	 * Collecting request information
	 *
	 * @var array
	 */
	static protected $serverInfo = [
		'request' => [
			'Request method'   => 'REQUEST_METHOD',
			'Request protocol' => 'SERVER_PROTOCOL',
			'Request uri'      => 'REQUEST_URI',
			'Query string'     => 'QUERY_STRING',
			'Gateway protocol' => 'GATEWAY_INTERFACE',
			'Server software'  => 'SERVER_SOFTWARE',
			'Content type'     => 'CONTENT_TYPE',
			'Content length'   => 'CONTENT_LENGTH',
			'Script filename'  => 'SCRIPT_FILENAME'
		],
		'http' => [
			'Requested host'  => 'HTTP_HOST',
			'Accept data'     => 'HTTP_ACCEPT',
			'Accept encoding' => 'HTTP_ACCEPT_ENCODING',
			'Accept language' => 'HTTP_ACCEPT_LANGUAGE',
			'User agent'      => 'HTTP_USER_AGENT',
			'Cache control'   => 'HTTP_HOST',
			'Connection'      => 'HTTP_CONNECTION',
		],
		'server' => [
			'Remote IP'   => 'REMOTE_ADDR',
			'Remote port' => 'REMOTE_PORT',
			'Server IP'   => 'SERVER_ADDR',
			'Server port' => 'SERVER_PORT',
			'Server name' => 'SERVER_NAME'
		]
	];
	
	/**
	 * Get the collected information
	 * 
	 * @return Infoset
	 */
	public function getInfo() {
		$info = $this->createInfoset();
		$info->setIcon('arrowdown');
		
		if (isset($_SERVER['REQUEST_METHOD'])) {
			$info->setTitle(preg_replace('/[^a-z]+/i', '', $_SERVER['REQUEST_METHOD']));
		} else {
			$info->setTitle('Request');
		}
		
		$this->createContent($info);
		
		return $info;
	}
	
	/**
	 * Create the content
	 * 
	 * @param Infoset $info
	 */
	protected function createContent(Infoset $info) {
		$this->serverInfo($info);
		
		if (! empty($_REQUEST)) {
			$this->collectData($info, $_REQUEST, 'Request arguments');
		}
		
		if (! empty($_GET)) {
			$this->collectData($info, $_GET, 'Get arguments');
		}
		
		if (! empty($_POST)) {
			$this->collectData($info, $_POST, 'Post arguments');
		}
		
		if (! empty($_ENV)) {
			$this->collectData($info, $_ENV, 'Environment vars');
		}
		
		if (! empty($_COOKIE)) {
			$this->collectData($info, $_COOKIE, 'Cookies');
		}
	}
	
	/**
	 * Collect the server information
	 * 
	 * @param Infoset $info
	 */
	protected function serverInfo(Infoset $info) {
		$infoTable = $this->createTable();
		$infoTable->setTitle('Info');
		
		if (! empty($_SERVER['REQUEST_TIME'])) {
			$infoTable[] = ['Request time: ', date(DATE_RFC3339, $_SERVER['REQUEST_TIME'])];
		}
		
		foreach (self::$serverInfo as $groupName => $groupInfo) {
			foreach ($groupInfo as $title => $item) {
				if (empty($_SERVER[$item])) {
					continue;
				}
				
				$value = $_SERVER[$item];
				
				if (strlen($value) > 1024) {
					$value = substr($value, 0, 1024) . '...';
				}
				
				if ($groupName === 'http') {
					$infoTable->pushSuccess([$title . ':', $value]);
					continue;
				}
				
				if ($groupName === 'server') {
					$infoTable->pushInfo([$title . ':', $value]);
					continue;
				}
				
				$infoTable->pushContent([$title . ':', $value]);
			}
		}
		
		$info[] = $infoTable;
	}
	
	/**
	 * Collect the data
	 * 
	 * @param Infoset $info
	 * @param array   $source
	 * @param string  $title
	 */
	protected function collectData(Infoset $info, array $source, $title) {
		$infoTable = $this->createTable();
		$infoTable->setTitle($title);
		
		foreach ($source as $name => $value) {
			if (is_array($value)) {
				foreach ($value as $key => $val) {
					if (strlen($val) > 1024) {
						$val = substr($val, 0, 1024) . '...';
					}
					
					$infoTable[] = [$name . '[' . $key . ']:', $val];
				}
				
				continue;
			}
			
			if (strlen($value) > 1024) {
				$value = substr($value, 0, 1024) . '...';
			}
			
			$infoTable[] = [$name . ':', $value];
		}
		
		$info[] = $infoTable;
	}
	
}