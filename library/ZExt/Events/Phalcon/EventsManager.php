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

namespace ZExt\Events\Phalcon;

use ZExt\Events\EventsManagerInterface;

use Phalcon\Events\Manager;

use ZExt\Profiler\ProfilerInterface,
    ZExt\Profiler\ProfileableInterface,
    ZExt\Profiler\ProfileableTrait;

/**
 * Events manager based on the Phalcon\Events\Manager
 * 
 * @category   ZExt
 * @package    Events
 * @subpackage Manager
 * @author     Mike.Mirten
 * @version    1.0
 */
class EventsManager extends Manager implements EventsManagerInterface, ProfileableInterface {
	
	use ProfileableTrait;
	
	/**
	 * Trigger the event
	 * 
	 * @param  string $event
	 * @param  mixed  $target
	 * @param  array  $args
	 * @return EventsManager
	 */
	public function trigger($event, $target = null, $args = []) {
		if ($this->_profilerEnabled) {
			$profilerEvent = $this->getProfiler()->startEvent($event);
		}
		
		$this->fire($event, $target, $args);
		
		if ($this->_profilerEnabled) {
			$profilerEvent->stop();
		}
		
		return $this;
	}
	
	/**
	 * On profiler init callback
	 * 
	 * @param ProfilerInterface $profiler
	 */
	public function onProfilerInit(ProfilerInterface $profiler) {
		$profiler->setName('Phalcon based events manager');
	}
	
}