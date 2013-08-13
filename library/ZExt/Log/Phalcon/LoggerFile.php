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

namespace ZExt\Log\Phalcon;

use ZExt\Log\LoggerInterface;

use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;

/**
 * Logger based on Phalcon\Logger\Adapter\File;
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.0
 */
class LoggerFile extends File implements LoggerInterface {
	
	/**
	 * Log a critical
	 * 
	 * @param string $message
	 */
	public function critical($message) {
		$this->log($message, Logger::CRITICAL);
		
		return $this;
	}
	
	/**
	 * Log an emergency
	 * 
	 * @param string $message
	 */
	public function emergency($message) {
		$this->log($message, Logger::EMERGENCE);
		
		return $this;
	}
	
}