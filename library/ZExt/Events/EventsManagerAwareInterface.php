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

/**
 * Events manager aware trait
 * 
 * @category   ZExt
 * @package    Events
 * @subpackage Manager
 * @author     Mike.Mirten
 * @version    1.0
 */
interface EventsManagerAwareInterface {
	
	/**
	 * Set an events manager
	 * 
	 * @param EventsManagerInterface $eventsManager
	 */
	public function setEventsManager(EventsManagerInterface $eventsManager);
	
	/**
	 * Get an events manager
	 * 
	 * @return EventsManagerInterface
	 */
	public function getEventsManager();
	
	/**
	 * Has an avent manager
	 * 
	 * @return bool
	 */
	public function hasEventsManager();
	
	/**
	 * Set service's ID for an events manager
	 * 
	 * @param string $id
	 */
	public function setEventsManagerServiceId($id);
	
	/**
	 * Get service's ID for an events manager
	 * 
	 * @return string
	 */
	public function getEventsManagerServiceId();
	
}