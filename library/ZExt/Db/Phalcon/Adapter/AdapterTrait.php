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
namespace ZExt\Db\Phalcon\Adapter;

use ZExt\Profiler\ProfileableTrait;
use Phalcon\Events\Manager;

/**
 * Phalcon DB adapter trait
 * Provides profiler
 * 
 * @package    Db
 * @subpackage Phalcon
 * @author     Mike.Mirten
 * @version    1.0beta
 */
trait AdapterTrait {
	
	use ProfileableTrait;
	
	/**
	 * Has profiler resolved
	 *
	 * @var bool
	 */
	protected $_profilerResolved = false;
	
	/**
	 * Handle the oprtions from a constructor
	 * 
	 * @param array $options
	 */
	protected function handleOptions(&$options) {
		if (is_array($options) && isset($options[self::PARAM_PROFILER])) {
			$this->setProfilerStatus($options[self::PARAM_PROFILER]);
			
			unset($options[self::PARAM_PROFILER]);
		}
	}
	
	/**
	 * Enable / disable the profiler
	 * 
	 * @param bool $enabled
	 */
	public function setProfilerStatus($enabled = true) {
		$this->_profilerEnabled = (bool) $enabled;
		
		if ($this->_profilerEnabled && ! $this->_profilerResolved) {
			$eventsManager = $this->getEventsManager();
			
			if ($eventsManager === null) {
				$eventsManager = new Manager();
				
				$this->setEventsManager($eventsManager);
			}
			
			$eventsManager->attach('db:beforeQuery', function($event, $db) {
				if ($this->_profilerEnabled) {
					$query    = $db->getSQLStatement();
					$profiler = $this->getProfiler();
					
					$action = substr($query, 0, strpos($query, ' '));
					
					if ($action === 'SELECT') {
						$profiler->startRead($query);
					}
					else if ($action === 'UPDATE') {
						$profiler->startWrite($query);
					}
					else if ($action === 'INSERT') {
						$profiler->startInsert($query);
					}
					else if ($action === 'DELETE') {
						$profiler->startDelete($query);
					}
					else {
						$profiler->startInfo($query);
					}
				}
			});
			
			$eventsManager->attach('db:afterQuery', function($event, $db) {
				if ($this->_profilerEnabled) {
					$this->getProfiler()->stopEvent();
				}
			});
			
			$this->_profilerResolved = true;
		}
	}
	
}