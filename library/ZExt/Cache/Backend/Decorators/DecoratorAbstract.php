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

namespace ZExt\Cache\Backend\Decorators;

use ZExt\Cache\Backend\BackendInterface;
use ZExt\Cache\Backend\Exceptions\NoBackend;

/**
 * Decorator abstract
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Decorators
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class DecoratorAbstract implements DecoratorInterface {
	
	/**
	 * Backend
	 *
	 * @var BackendInterface 
	 */
	protected $backend;
	
	/**
	 * Constructor
	 * 
	 * @param BackendInterface $backend
	 */
	public function __construct(BackendInterface $backend = null) {
		if ($backend !== null) {
			$this->setBackend($backend);
		}
	}
	
	/**
	 * Set the backend
	 * 
	 * @param BackendInterface $backend
	 */
	public function setBackend(BackendInterface $backend) {
		$this->backend = $backend;
	}
	
	/**
	 * Get the backend
	 * 
	 * @return BackendInterface
	 */
	public function getBackend() {
		if ($this->backend === null) {
			throw new NoBackend('Backend wasn\'t been supplied');
		}
		
		return $this->backend;
	}
	
}