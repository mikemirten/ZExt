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

namespace ZExt\Debug\Collectors;

use ZExt\Debug\Infosets\Infoset,
    ZExt\Debug\Infosets\InfosetTable;

use ZExt\Profiler\ProfileInterface,
    ZExt\Profiler\ProfilerInterface,
    ZExt\Profiler\ProfilerExtendedInterface;

use ZExt\Debug\Collectors\Exceptions\NoProfiler;

/**
 * Profiler information collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    2.0
 */
class Profiler extends CollectorAbstract {
	
	const STATUS_SUCCESS = '[success]v[/success]';
	const STATUS_NOTICE  = '[warning]v[/warning]';
	const STATUS_WARNING = '[warning]![/warning]';
	const STATUS_ALERT   = '[alert]![/alert]';
	
	/**
	 * Profiler
	 * 
	 * @var ProfilerInterface 
	 */
	private $profiler;
	
	/**
	 * Get the collected information
	 * 
	 * @return InfoSet
	 */
	public function getInfo() {
		$info = $this->createInfoset();
		
		$this->handleIcon($info);
		$this->createTitle($info);
		$this->createContent($info);
		
		return $info;
	}
	
	/**
	 * Resolve the icon source
	 * 
	 * @param Infoset $info
	 */
	protected function handleIcon(Infoset $info) {
		$profiler = $this->getProfiler();
		
		if ($profiler instanceof ProfilerExtendedInterface) {
			$icon = $profiler->getIcon();
			
			if ($icon !== null) {
				if (strpos($icon, 'data:image') === 0) {
					$info->setIcon($icon, Infoset::ICON_BASE64);
					return;
				}
				
				$info->setIcon($icon);
				return;
			}
		}
		
		$info->setIcon('monitor');
	}
	
	/**
	 * Create the title
	 * 
	 * @return string
	 */
	protected function createTitle(Infoset $info) {
		$profiler = $this->getProfiler();
		$events   = $profiler->getTotalEvents();
		
		if ($events === 0) {
			$info->setTitle('No events');
			return;
		}
		
		$time = $profiler->getTotalElapsedTime();
		
		$info->setTitle($events . ' in ' . $this->formatTime($time));
	}
	
	/**
	 * Get a panel with full information
	 * 
	 * @return string
	 */
	protected function createContent(Infoset $info) {
		$profiler = $this->getProfiler();
		
		// Info
		if ($profiler instanceof ProfilerExtendedInterface) {
			$this->additionalInfo($info, $profiler);
		}
		
		if ($profiler->getTotalEvents() === 0) {
			return;
		}
		
		// Events
		$table = $this->createTable()
			->setColsWidths([1, 1, 1, 94])
			->setHeadContent(['', '', 'Time', 'Action'])
			->enableBbCodes()
			->setTitle('Events');
		
		$info[] = $table;
		$number = 1;
		
		foreach ($profiler->getProfiles() as $profile) {
			$status = $this->handleProfileStatus($profile);
			$time   = '[strong]' . $this->formatTime($profile->getElapsedTime()) . '[/strong]';
			
			$row = [$number, $status, $time, $profile->getMessage()];
			$this->handleProfileType($profile, $table, $row);
			
			++ $number;
		}
	}
	
	/**
	 * Resolve the profile status
	 * 
	 * @param  ProfileInterface $profile
	 * @return string
	 */
	protected function handleProfileStatus(ProfileInterface $profile) {
		switch($profile->getStatus()) {
			case ProfileInterface::STATUS_SUCCESS:
				return self::STATUS_SUCCESS;

			case ProfileInterface::STATUS_NOTICE:
				return self::STATUS_NOTICE;

			case ProfileInterface::STATUS_WARNING:
				return self::STATUS_WARNING;

			case ProfileInterface::STATUS_ERROR:
				return self::STATUS_ALERT;

			default:
				return '';
		}
	}
	
	/**
	 * Handle the table row by the type of the profile
	 * 
	 * @param ProfileInterface $profile
	 * @param InfosetTable     $table
	 * @param array            $row
	 */
	protected function handleProfileType(ProfileInterface $profile, InfosetTable $table, array $row) {
		switch ($profile->getType()) {
			case ProfileInterface::TYPE_READ:
				$table->pushSuccess($row);
				break;

			case ProfileInterface::TYPE_WRITE:
				$table->pushInfo($row);
				break;

			case ProfileInterface::TYPE_INSERT:
				$table->pushWarning($row);
				break;

			case ProfileInterface::TYPE_DELETE:
				$table->pushAlert($row);
				break;

			default:
				$table->pushContent($row);
		}
	}
	
	/**
	 * Create the additional info 
	 * 
	 * @param Infoset $info
	 */
	protected function additionalInfo(Infoset $info, ProfilerExtendedInterface $profiler) {
		$profilerInfo = $profiler->getAdditionalInfo();
			
		if (is_string($profilerInfo)) {
			$info[] = $profilerInfo;
			return;
		}
		
		if (is_array($profilerInfo)) {
			$infoTable = $this->createTable()
				->enableBbCodes()
				->setTitle('Info');

			$info[] = $infoTable;

			foreach ($profilerInfo as $key => $value) {
				$infoTable[] = [$key . ':', $value];
			}
		}
	}
	
	/**
	 * Set a profiler
	 * 
	 * @param ProfilerInterface $profiler
	 */
	public function setProfiler(ProfilerInterface $profiler) {
		$this->profiler = $profiler;
	}
	
	/**
	 * Get a profiler
	 * 
	 * @return ProfilerInterface
	 */
	public function getProfiler() {
		if ($this->profiler === null) {
			throw new NoProfiler('Profiler has not been supplied');
		}
		
		return $this->profiler;
	}
	
}