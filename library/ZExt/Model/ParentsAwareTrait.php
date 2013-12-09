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

namespace ZExt\Model;

use ZExt\Datagate\DatagateInterface;

trait ParentsAwareTrait {
	
	/**
	 * Parental datagate
	 * 
	 * @var string 
	 */
	protected $_datagate;
	
	/**
	 * Force the insertion action at the save method calling
	 *
	 * @var bool 
	 */
	protected $_forceInsert = false;
	
	/**
	 * Set the datagate
	 *  
	 * @param  object $datagateName
	 * @return ModelAbstract
	 */
	public function setDatagate(DatagateInterface $datagate) {
		$this->_datagate = $datagate;
		
		return $this;
	}
	
	/**
	 * Get the datagate
	 * 
	 * @return object
	 */
	public function getDatagate() {
		if ($this->_datagate === null) {
			throw new Exception('Model isn\'t linked to a datagate');
		}
		
		return $this->_datagate;
	}
	
	/**
	 * Has the datagate
	 * 
	 * @return bool
	 */
	public function hasDatagate() {
		return $this->_datagate !== null;
	}
	
	/**
	 * Save a model or changes of a model
	 * 
	 * @return ModelAbstract
	 */
	public function save() {
		return $this->getDatagate()->save($this);
	}
	
	/**
	 * Force the insertion action at the save method calling
	 * 
	 * @return ModelAbstract
	 */
	public function forceInsert() {
		$this->_forceInsert = true;
		
		return $this;
	}
	
	/**
	 * Unforce the insertion action at the save method calling
	 * 
	 * @return ModelAbstract
	 */
	public function unforceInsert() {
		$this->_forceInsert = false;
		
		return $this;
	}
	
	/**
	 * Get data to save
	 * 
	 * @return array
	 */
	public function toSave() {
		return $this->_data;
	}
	
	/**
	 * Is insert has forced at the save method calling
	 * 
	 * @return bool
	 */
	public function isInsertForced() {
		return $this->_forceInsert;
	}
	
	/**
	 * Remove model
	 * 
	 * @return ModelAbstract
	 */
	public function remove() {
		return $this->getDatagate()->remove($this);
	}
	
	/**
	 * Get a service by a name
	 *
	 * @param  string $id
	 * @return mixed
	 */
	protected function getService($id) {
		return $this->getLocator()->get($id);
	}
	
}