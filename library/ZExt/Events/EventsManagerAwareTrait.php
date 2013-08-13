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

namespace ZExt\Events;

use ZExt\Di\LocatorAwareInterface;
use ZExt\Events\Exceptions\NoEventsManager;

/**
 * Events manager aware trait
 * 
 * @category   ZExt
 * @package    Events
 * @subpackage Manager
 * @author     Mike.Mirten
 * @version    1.0
 */
trait EventsManagerAwareTrait {
	
	/**
	 * Instance of the EventsManagerInterface
	 *
	 * @var EventsManagerInterface 
	 */
	private $_eventsManager;
	
	/**
	 * Service's ID for an events manager
	 *
	 * @var string
	 */
	private $_eventsManagerServiceId = 'eventsManager';
	
	/**
	 * Trigger an event
	 * 
	 * @param mixed $event
	 */
	protected function triggerEvent($event, $source = null, $data = null) {
		$this->getEventsManager()->trigger($event, $source, $data);
	}
	
	/**
	 * Set service's ID for an events manager
	 * 
	 * @param string $id
	 */
	public function setEventsManagerServiceId($id) {
		$this->_eventsManagerServiceId = (string) $id;
	}
	
	/**
	 * Get service's ID for an events manager
	 * 
	 * @return string
	 */
	public function getEventsManagerServiceId() {
		return $this->_eventsManagerServiceId;
	}
	
	/**
	 * Set an events manager
	 * 
	 * @param EventsManagerInterface $eventsManager
	 */
	public function setEventsManager(EventsManagerInterface $eventsManager) {
		$this->_eventsManager = $eventsManager;
	}
	
	/**
	 * Get an events manager
	 * 
	 * @return EventsManagerInterface
	 */
	public function getEventsManager() {
		if ($this->_eventsManager === null) {
			if ($this instanceof LocatorAwareInterface && $this->hasLocator()) {
				$this->_eventsManager = $this->getLocator()->get($this->getEventsManagerServiceId());
				
				if (! $this->_eventsManager instanceof EventsManagerInterface) {
					throw new NoEventsManager('Event\'s manager must implement "EventsManagerInterface"');
				}
			} else {
				throw new NoEventsManager('No Events manager been supplied, also unable to obtain one through locator');
			}
		}
		
		return $this->_eventsManager;
	}
	
	/**
	 * Has an avent manager
	 * 
	 * @return bool
	 */
	public function hasEventsManager() {
		if ($this->_eventsManager !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface && $this->hasLocator()
			&& $this->getLocator()->has($this->getEventsManagerServiceId())) {
			return true;
		}
		
		return false;
	}
	
}