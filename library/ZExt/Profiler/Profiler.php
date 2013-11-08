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

use ArrayIterator;


/**
 * Profiler interface
 * 
 * @category   ZExt
 * @package    Profiler
 * @subpackage Profiler
 * @author     Mike.Mirten
 * @version    1.1
 */
class Profiler implements ProfilerExtendedInterface {
	
	/**
	 * Profiles
	 * 
	 * @var ProfileInterface[] 
	 */
	protected $_profiles = [];
	
	/**
	 * Name of a profiling object
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Icon of a profiling object
	 *
	 * @var string
	 */
	protected $_icon;
	
	/**
	 * Additional info about a profiling object
	 * 
	 * @var string | array
	 */
	protected $_info;
	
	/**
	 * Last started profile
	 *
	 * @var ProfileInterface
	 */
	protected $_lastProfile;
	
	/**
	 * Start an event
	 * 
	 * @param  string $title
	 * @param  array $options
	 * @return ProfileInterface
	 */
	public function startEvent($message, $type = ProfileInterface::TYPE_INFO, array $options = null) {
		$profile = new Profile($message, $type, $options);
		
		$this->_profiles[]  = $profile;
		$this->_lastProfile = $profile;
		
		$profile->start();
		
		return $profile;
	}
	
	/**
	 * Start an info event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startInfo($message, array $options = null) {
		return $this->startEvent($message, ProfileInterface::TYPE_INFO, $options);
	}
	
	/**
	 * Start a read event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startRead($message, array $options = null) {
		return $this->startEvent($message, ProfileInterface::TYPE_READ, $options);
	}
	
	/**
	 * Start a write event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startWrite($message, array $options = null) {
		return $this->startEvent($message, ProfileInterface::TYPE_WRITE, $options);
	}
	
	/**
	 * Start an isert event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startInsert($message, array $options = null) {
		return $this->startEvent($message, ProfileInterface::TYPE_INSERT, $options);
	}
	
	/**
	 * Start a delete event
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return ProfileInterface
	 */
	public function startDelete($message, array $options = null) {
		return $this->startEvent($message, ProfileInterface::TYPE_DELETE, $options);
	}
	
	/**
	 * Stop a last event
	 * 
	 * @return Profiler
	 * @throws Exception
	 */
	public function stopEvent($status = ProfileInterface::STATUS_SUCCESS) {
		if ($this->_lastProfile === null) {
			throw new Exception('No one event has been started');
		}
		
		$this->_lastProfile->stop($status);
		
		return $this;
	}
	
	/**
	 * Stop a last event with success status
	 * 
	 * @return Profiler
	 */
	public function stopSuccess() {
		$this->stopEvent(ProfileInterface::STATUS_SUCCESS);
		
		return $this;
	}
	
	/**
	 * Stop a last event with notice status
	 * 
	 * @return Profiler
	 */
	public function stopNotice() {
		$this->stopEvent(ProfileInterface::STATUS_NOTICE);
		
		return $this;
	}
	
	/**
	 * Stop a last event with warning status
	 * 
	 * @return Profiler
	 */
	public function stopWarning() {
		$this->stopEvent(ProfileInterface::STATUS_WARNING);
		
		return $this;
	}
	
	/**
	 * Stop a last event with error status
	 * 
	 * @return Profiler
	 */
	public function stopError() {
		$this->stopEvent(ProfileInterface::STATUS_ERROR);
		
		return $this;
	}
	
	/**
	 * Get profiles
	 * 
	 * @return ProfileInterface[]
	 */
	public function getProfiles() {
		return $this->_profiles;
	}
	
	/**
	 * Get the total elapsed time of an events
	 * 
	 * @return int
	 */
	public function getTotalElapsedTime() {
		$time = 0;
		
		foreach ($this->_profiles as $profile) {
			$time += $profile->getElapsedTime();
		}
		
		return $time;
	}
	
	/**
	 * Get the total events number
	 * 
	 * @return int
	 */
	public function getTotalEvents() {
		return count($this->_profiles);
	}
	
	/**
	 * Get the icon of a profiling object
	 * 
	 * @return string
	 */
	public function getIcon() {
		return $this->_icon;
	}
	
	/**
	 * Set the icon of a profiling object
	 * 
	 * @param  string $image
	 * @return Profiler
	 */
	public function setIcon($image) {
		$this->_icon = (string) $image;
		
		return $this;
	}
	
	/**
	 * Get a name of a profiling object
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Set a name of a profiling object
	 * 
	 * @param  string $name
	 * @return Profiler
	 */
	public function setName($name) {
		$this->_name = (string) $name;
		
		return $this;
	}
	
	/**
	 * Get an additional info about a profiling object
	 * 
	 * @return string | array
	 */
	public function getAdditionalInfo() {
		return $this->_info;
	}
	
	/**
	 * Set an additional info about a profiling object
	 * 
	 * @param  string | array $info
	 * @return Profiler
	 */
	public function setAdditionalInfo($info) {
		$this->_info = $info;
		
		return $this;
	}
	
	/**
	 * Get the last event
	 * 
	 * @return ProfileInterface | null
	 */
	public function getLastEvent() {
		return $this->_lastProfile;
	}
	
	/**
	 * Render the results as a formatted text
	 * 
	 * @return string
	 */
	public function render() {
		$totalEvents = $this->getTotalEvents();
		
		if ($totalEvents > 0) {
			$text  = 'Total: ' . $totalEvents . ' events in: ';
			$text .= round($this->getTotalElapsedTime(), 4) . 's ';
		} else {
			$text = 'No events';
		}
		
		$text .= PHP_EOL;
		
		$info = $this->getAdditionalInfo();
		
		if ($info !== null) {
			$text .= PHP_EOL;
			$text .= 'Info:' . PHP_EOL;
			
			if (is_string($info)) {
				$text .= $info . PHP_EOL;
			} else if (is_array($info)) {
				foreach ($info as $key => $part) {
					$text .= $key . ': ' . $part . PHP_EOL;
				}
			}
		}
		
		if ($totalEvents > 0) {
			$profiles = $this->getProfiles();
			$nm       = 1;
			
			$text .= PHP_EOL;
			$text .= 'Events:' . PHP_EOL;
			
			foreach ($profiles as $profile) {
				$text .= $nm ++ . '. ';
				$text .= $profile->getMessage() . ':';
				$text .= ' ' . round($profile->getElapsedTime(), 4) . 's';
				$text .= ' ' . $profile->getUsedMemory() . 'b';
				$text .= PHP_EOL;
			}
		}
		
		return $text;
	}
	
	/**
	 * Get the total events number
	 * 
	 * @return int
	 */
	public function count() {
		return $this->getTotalEvents();
	}
	
	/**
	 * Get the events iterator
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator($this->getProfiles());
	}
	
	/**
	 * Get the results as a formatted text
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}
	
}