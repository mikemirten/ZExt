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
 * Logger based on the Zend_Log
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.0
 */
class Logger implements LoggerInterface {
	
	/**
	 * Logger to Zend_Log type map
	 *
	 * @var array
	 */
	protected static $typesMap = [
		self::TYPE_DEBUG     => Zend_Log::DEBUG,
		self::TYPE_INFO      => Zend_Log::INFO,
		self::TYPE_NOTICE    => Zend_Log::NOTICE,
		self::TYPE_WARNING   => Zend_Log::WARN,
		self::TYPE_ERROR     => Zend_Log::ERR,
		self::TYPE_ALERT     => Zend_Log::ALERT,
		self::TYPE_EMERGENCY => Zend_Log::EMERG,
		self::TYPE_CRITICAL  => Zend_Log::CRIT
	];
	
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