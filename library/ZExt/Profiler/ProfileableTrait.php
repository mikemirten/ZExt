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
 * Profileable trait
 * 
 * @category   ZExt
 * @package    Profiler
 * @subpackage Profileable
 * @author     Mike.Mirten
 * @version    1.0
 */
trait ProfileableTrait {
	
	/**
	 * Profiler
	 * 
	 * @var ProfilerInterface
	 */
	private $_profiler;
	
	/**
	 * Is profiler enabled
	 * 
	 * @var bool
	 */
	protected $_profilerEnabled = false;
	
	/**
	 * Set a profiler
	 * 
	 * @var ProfilerInterface
	 */
	public function setProfiler(ProfilerInterface $profiler) {
		$this->_profiler = $profiler;
	}
	
	/**
	 * Get a profiler
	 * 
	 * @return ProfilerInterface
	 */
	public function getProfiler() {
		if ($this->_profiler === null) {
			$this->_profiler = new Profiler();
			$this->onProfilerInit($this->_profiler);
		}
		
		return $this->_profiler;
	}
	
	/**
	 * On profiler init callback
	 * 
	 * @param ProfilerInterface $profiler
	 */
	protected function onProfilerInit(ProfilerInterface $profiler){}
	
	/**
	 * Switch profiler on/off
	 * 
	 * @param bool $enabled
	 */
	public function setProfilerStatus($enabled = true) {
		$this->_profilerEnabled = (bool) $enabled;
	}
	
	/**
	 * Is profiler enabled
	 * 
	 * @return bool
	 */
	public function isProfilerEnabled() {
		return $this->_profilerEnabled;
	}
	
}