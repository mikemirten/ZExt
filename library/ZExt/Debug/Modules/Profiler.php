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

namespace ZExt\Debug\Modules;

use ZExt\Profiler\ProfileInterface,
    ZExt\Profiler\ProfilerInterface,
    ZExt\Profiler\ProfilerExtendedInterface,
    ZExt\Html\ListOrdered,
    ZExt\Html\ListUnordered,
    ZExt\Image\Base64,
    ZExt\Html\Tag;

class Profiler extends ModuleAbstract {
	
	const COLOR_READ   = 'rgb(235, 250, 235)';
	const COLOR_WRITE  = 'rgb(220, 235, 250)';
	const COLOR_INSERT = 'rgb(255, 245, 235)';
	const COLOR_DELETE = 'rgb(250, 230, 230)';
	
	/**
	 * Profiler
	 * 
	 * @var ProfilerInterface 
	 */
	private $_profiler;
	
	/**
	 * Get a base64 encoded icon for a tab
	 * 
	 * @param  mixed size of an icon
	 * @return string 
	 */
	public function getTabIcon($size = null) {
		$profiler = $this->getProfiler();
		
		if ($profiler !== null && $profiler instanceof ProfilerExtendedInterface) {
			$icon = $profiler->getIcon();
			
			if ($icon !== null) {
				$iconEncoder = new Base64($icon);
				return $iconEncoder->render();
			}
		}
		
		return parent::getTabIcon($size);
	}
	
	public function renderTab() {
		$profiler = $this->getProfiler();
		
		if ($profiler === null) {
			return 'No profiler';
		}
		
		$events = $profiler->getTotalEvents();
		
		if (empty($events)) {
			return 'No events';
		}
		
		$time = $profiler->getTotalElapsedTime();
		
		return $events . ' in ' . $this->formatTime($time);
	}
	
	/**
	 * Get a panel with full information
	 * 
	 * @return string
	 */
	public function renderPanel() {
		$profiler = $this->getProfiler();
		
		if ($profiler === null) {
			return;
		}
		
		$content   = '';
		$strongTag = new Tag('strong');
		$titleTag  = new Tag('h4');
		
		if ($profiler instanceof ProfilerExtendedInterface) {
			$info = $profiler->getAdditionalInfo();
			
			if (is_string($info)) {
				$content .= new Tag('p', $info);
			} else if (is_array($info)) {
				$infoList = new ListUnordered();
				$infoList->addClass('list-rows list-simple');
				
				foreach ($info as $key => $value) {
					$infoList->addElement($strongTag->render($key . ': ') . $value);
				}
				
				$content .= $titleTag->render('Info:');
				$content .= $infoList->render();
			}
		}
		
		$events = $profiler->getTotalEvents();
		
		if (empty($events)) {
			return empty($content) ? null : $content;
		}
		
		$listTag = new ListOrdered();
		$listTag->addClass('list-rows');
		
		foreach ($profiler->getProfiles() as $profile) {
			$time   = $this->formatTime($profile->getElapsedTime());
			$memory = $this->formatMemory($profile->getUsedMemory());
			
			$usage = $strongTag->render($time . ' / ' . $memory . ': ');
			$query = $profile->getMessage();
			
			switch ($profile->getType()) {
				case ProfileInterface::TYPE_READ:
					$color = self::COLOR_READ;
					break;

				case ProfileInterface::TYPE_WRITE:
					$color = self::COLOR_WRITE;
					break;

				case ProfileInterface::TYPE_INSERT:
					$color = self::COLOR_INSERT;
					break;

				case ProfileInterface::TYPE_DELETE:
					$color = self::COLOR_DELETE;
					break;

				default:
					$color = null;
			}
			
			if ($color === null) {
				$options = null;
			} else {
				$options = array(
					'style' => array(
						'background' => $color
					)
				);
			}
			
			$listTag->addElement($usage . ' ' . $query, null, $options);
		}
		
		return $content . $titleTag->render('Events:') . $listTag->render();
	}
	
	/**
	 * Set a profiler
	 * 
	 * @param ProfilerInterface $profiler
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
		return $this->_profiler;
	}
	
}