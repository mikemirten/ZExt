<?php
namespace ZExt\Profiler;

interface ProfileableInterface {
	
	/**
	 * Set a profiler
	 * 
	 * @var ProfilerInterface
	 */
	public function setProfiler(ProfilerInterface $profiler);
	
	/**
	 * Get a profiler
	 * 
	 * @return ProfilerInterface
	 */
	public function getProfiler();
	
	/**
	 * Switch a profiler on/off
	 * 
	 * @param bool $switch
	 */
	public function setProfilerStatus($enabled = true);
	
	/**
	 * Is enabled a profiler
	 * 
	 * @return bool
	 */
	public function isProfilerEnabled();
	
}