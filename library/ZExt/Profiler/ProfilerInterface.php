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

use IteratorAggregate, Countable;

/**
 * Profiler interface
 * 
 * @category   ZExt
 * @package    Profiler
 * @subpackage Profiler
 * @author     Mike.Mirten
 * @version    1.1
 */
interface ProfilerInterface extends IteratorAggregate, Countable {
	
	/**
	 * Start an event
	 * 
	 * @param  string $title
	 * @param  int    $type
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startEvent($message, $type = ProfileInterface::TYPE_INFO, array $options = null);
	
	/**
	 * Start an info event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startInfo($message, array $options = null);
	
	/**
	 * Start a read event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startRead($message, array $options = null);
	
	/**
	 * Start a write event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startWrite($message, array $options = null);
	
	/**
	 * Start an isert event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startInsert($message, array $options = null);
	
	/**
	 * Start a delete event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startDelete($message, array $options = null);
	
	/**
	 * Stop a last event
	 * 
	 * @param int $status
	 */
	public function stopEvent($status = ProfileInterface::STATUS_SUCCESS);
	
	/**
	 * Stop a last event with success status
	 */
	public function stopSuccess();
	
	/**
	 * Stop a last event with notice status
	 */
	public function stopNotice();
	
	/**
	 * Stop a last event with warning status
	 */
	public function stopWarning();
	
	/**
	 * Stop a last event with error status
	 */
	public function stopError();
	
	/**
	 * Get profiler's results
	 * 
	 * @return ProfileInterface[]
	 */
	public function getProfiles();
	
	/**
	 * Get the total elapsed time of an events in seconds
	 * 
	 * @return int
	 */
	public function getTotalElapsedTime();
	
	/**
	 * Get total events has occurred
	 * 
	 * @return int
	 */
	public function getTotalEvents();
	
	/**
	 * Get the last event
	 * 
	 * @return ProfileInterface | null
	 */
	public function getLastEvent();
	
}