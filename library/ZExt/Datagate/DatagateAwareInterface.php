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

namespace ZExt\Datagate;

/**
 * Datagate aware interface
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    1.0
 */
interface DatagateAwareInterface {
	
	/**
	 * Set the datagate
	 *  
	 * @param DatagateInterface $datagate
	 */
	public function setDatagate(DatagateInterface $datagate);
	
	/**
	 * Get the datagate datagate
	 * 
	 * @return DatagateInterface
	 */
	public function getDatagate();
	
	/**
	 * Has the datagate
	 * 
	 * @return bool
	 */
	public function hasDatagate();
	
	/**
	 * Save the data
	 * 
	 * @return bool
	 */
	public function save();
	
	/**
	 * Remove the data
	 * 
	 * @return bool
	 */
	public function remove();
	
	/**
	 * Force the insertion action at the save method calling
	 */
	public function forceInsert();
	
	/**
	 * Unforce the insertion action at the save method calling
	 */
	public function unforceInsert();
	
	/**
	 * Has been the "insert" method forsed for a model
	 * 
	 * @return bool
	 */
	public function isInsertForced();
	
	/**
	 * Get data for insert into a database
	 * 
	 * @return array
	 */
	public function getDataForInsert();
	
	/**
	 * Get data for update in a database
	 * 
	 * @return array
	 */
	public function getDataForUpdate();
	
	/**
	 * Model is empty check
	 * 
	 * @return bool
	 */
	public function isEmpty();
	
}