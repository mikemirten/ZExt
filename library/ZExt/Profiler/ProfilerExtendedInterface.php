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

namespace ZExt\Profiler;

/**
 * Profiler extended interface
 * 
 * @category   ZExt
 * @package    Profiler
 * @subpackage Profiler
 * @author     Mike.Mirten
 * @version    1.0
 */
interface ProfilerExtendedInterface extends ProfilerInterface {
	
	/**
	 * Get icon of a profiling object
	 * 
	 * @return string
	 */
	public function getIcon();
	
	/**
	 * Get icon of a profiling object
	 * 
	 * @param string $image
	 */
	public function setIcon($image);
	
	/**
	 * Get a name of a profiling object
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
	 * Set a name of a profiling object
	 * 
	 * @param string $name
	 */
	public function setName($name);
	
	/**
	 * Get an additional info about a profiling object
	 * 
	 * @return string | array
	 */
	public function getAdditionalInfo();
	
	/**
	 * Set an additional info about a profiling object
	 * 
	 * @param string $info
	 */
	public function setAdditionalInfo($info);
	
}