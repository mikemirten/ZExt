<?php
namespace ZExt\Profiler;

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
	 */
	protected function onProfilerInit(){}
	
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