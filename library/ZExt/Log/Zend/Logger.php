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

namespace ZExt\Log\Zend;

use ZExt\Log\LoggerInterface;
use Zend_Log;

/**
 * Logger based on Zend_Log
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.1
 */
class Logger implements LoggerInterface {
	
	/**
	 * Zend_Log instance
	 *
	 * @var Zend_Log
	 */
	protected $logger;
	
	/**
	 * Constructor
	 * 
	 * @param Zend_Log $logger
	 */
	public function __construct(Zend_Log $logger) {
		$this->logger = $logger;
	}
	
	/**
	 * Log an event
	 * 
	 * @param string $message
	 * @param int    $code
	 */
	public function log($message, $code = self::INFO) {
		$this->logger->log($message, $code);
	}
	
	/**
	 * Log an info
	 * 
	 * @param string $message
	 */
	public function info($message) {
		$this->logger->log($message, Zend_Log::INFO);
	}
	
	/**
	 * Log a notice
	 * 
	 * @param string $message
	 */
	public function notice($message) {
		$this->logger->log($message, Zend_Log::NOTICE);
	}
	
	/**
	 * Log an info
	 * 
	 * @param string $message
	 */
	public function warning($message) {
		$this->logger->log($message, Zend_Log::WARN);
	}
	
	/**
	 * Log an error
	 * 
	 * @param string $message
	 */
	public function error($message) {
		$this->logger->log($message, Zend_Log::ERR);
	}
	
	/**
	 * Log an alert
	 * 
	 * @param string $message
	 */
	public function alert($message) {
		$this->logger->log($message, Zend_Log::ALERT);
	}
	
	/**
	 * Log an info
	 * 
	 * @param string $message
	 */
	public function emergency($message) {
		$this->logger->log($message, Zend_Log::EMERG);
	}
	
	/**
	 * Log a critical
	 * 
	 * @param string $message
	 */
	public function critical($message) {
		$this->logger->log($message, Zend_Log::ALERT);
	}
	
	/**
	 * Log a debug
	 * 
	 * @param string $message
	 */
	public function debug($message) {
		$this->logger->log($message, Zend_Log::DEBUG);
	}
	
}