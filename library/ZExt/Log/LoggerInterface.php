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

namespace ZExt\Log;

/**
 * Logger interface
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
interface LoggerInterface {
	
	const TYPE_DEBUG     = 0;
	const TYPE_INFO      = 1;
	const TYPE_NOTICE    = 2;
	const TYPE_WARNING   = 3;
	const TYPE_ERROR     = 4;
	const TYPE_ALERT     = 5;
	const TYPE_EMERGENCY = 6;
	const TYPE_CRITICAL  = 7;
	
	/**
	 * Log an info
	 * 
	 * @param string $message
	 */
	public function info($message);
	
	/**
	 * Log a notice
	 * 
	 * @param string $message
	 */
	public function notice($message);
	
	/**
	 * Log a warning
	 * 
	 * @param string $message
	 */
	public function warning($message);
	
	/**
	 * Log an error
	 * 
	 * @param string $message
	 */
	public function error($message);
	
	/**
	 * Log an alert
	 * 
	 * @param string $message
	 */
	public function alert($message);
	
	/**
	 * Log an emergency
	 * 
	 * @param string $message
	 */
	public function emergency($message);
	
	/**
	 * Log a critical
	 * 
	 * @param string $message
	 */
	public function critical($message);
	
	/**
	 * Log a debug
	 * 
	 * @param string $message
	 */
	public function debug($message);
	
}