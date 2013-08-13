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

/**
 * Modules' interface
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage ModuleInterface
 * @author     Mike.Mirten
 * @version    1.0
 */
interface ModuleInterface {
	
	/**
	 * Get a base64 encoded icon for a tab
	 * 
	 * @param size of an icon
	 * @return string 
	 */
	public function getTabIcon($size = null);
	
	/**
	 * Title of a tab
	 * 
	 * @return string | null if no title
	 */
	public function getTitle();

	/**
	 * Tab with base information
	 * 
	 * @return string | null if "do not render me this time"
	 */
	public function renderTab();
	
	/**
	 * Panel with full information
	 * 
	 * @return string | null if nothing, to show
	 */
	public function renderPanel();
	
}