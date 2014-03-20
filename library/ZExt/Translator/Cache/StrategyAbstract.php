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

namespace ZExt\Translator\Cache;

use ZExt\Cache\CacheAwareInterface;
use ZExt\Cache\CacheAwareTrait;

use ZExt\Di\LocatorAwareInterface;
use ZExt\Di\LocatorAwareTrait;

/**
 * Cache strategy interface
 * 
 * @category   ZExt
 * @package    Translator
 * @subpackage Cache
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class StrategyAbstract implements StrategyInterface, LocatorAwareInterface, CacheAwareInterface {
	
	use LocatorAwareTrait;
	use CacheAwareTrait;
	
	/**
	 * Lifetime in seconds
	 *
	 * @var int 
	 */
	protected $lifetime = 3600;
	
	/**
	 * Cache namespace
	 *
	 * @var string 
	 */
	protected $namespace = 'zext_translator';
	
	/**
	 * Constructor
	 * 
	 * @param mixed $locator
	 */
	public function __construct($locator = null) {
		if ($locator !== null) {
			$this->setLocator($locator);
		}
	}
	
	/**
	 * Set the lifetime in seconds (0 - permanent)
	 * 
	 * @param  int $lifetime
	 * @return StrategyAbstract
	 */
	public function setLifetime($lifetime) {
		$this->lifetime = (int) $lifetime;
		
		return $this;
	}
	
	/**
	 * Get the lifetime in seconds
	 * 
	 * @return int
	 */
	public function getLifetime() {
		return $this->lifetime;
	}
	
	/**
	 * Get the service name (need for caching)
	 * 
	 * @return string
	 */
	protected function getServiceName() {
		return $this->namespace;
	}
	
}



