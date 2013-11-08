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

namespace ZExt\Profiler;

/**
 * Profile interface
 * 
 * @category   ZExt
 * @package    Profiler
 * @subpackage Profile
 * @author     Mike.Mirten
 * @version    1.1
 */
interface ProfileInterface {
	
	// Base types of events
	const TYPE_INFO   = 1;
	const TYPE_READ   = 2;
	const TYPE_WRITE  = 3;
	const TYPE_INSERT = 4;
	const TYPE_DELETE = 5;
	
	// Base types of events' statuses
	const STATUS_SUCCESS = 1;
	const STATUS_NOTICE  = 2;
	const STATUS_WARNING = 3;
	const STATUS_ERROR   = 4;
	
	/**
	 * Constructor
	 * 
	 * @param string $message
	 * @param int    $type
	 * @param array  $options
	 */
	public function __construct($message, $type = self::TYPE_INFO, array $options = null);
	
	/**
	 * Start the event
	 */
	public function start();
	
	/**
	 * Stop the event
	 * 
	 * @param int $type Status of event end
	 */
	public function stop($type = self::STATUS_SUCCESS);
	
	/**
	 * Stop the event with the success status
	 */
	public function stopSuccess();
	
	/**
	 * Stop the event with the notice status
	 */
	public function stopNotice();
	
	/**
	 * Stop the event with the warning status
	 */
	public function stopWarning();
	
	/**
	 * Stop the event with the error status
	 */
	public function stopError();
	
	/**
	 * Has an event ended
	 * 
	 * @return bool
	 */
	public function hasEnded();
	
	/**
	 * Get elapsed time of an event in seconds
	 * 
	 * @return float
	 */
	public function getElapsedTime();
	
	/**
	 * Get used memory of an event
	 * 
	 * @return int
	 */
	public function getUsedMemory();
	
	/**
	 * Get message of an event
	 * 
	 * @return string
	 */
	public function getMessage();
	
	/**
	 * Get type of an event
	 * 
	 * @return int
	 */
	public function getType();
	
	/**
	 * Get status of end of an event
	 * 
	 * @return int
	 */
	public function getStatus();
	
}