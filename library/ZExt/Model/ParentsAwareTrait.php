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

use ZExt\Datagate\CrudInterface;

trait ParentsAwareTrait {
	
	/**
	 * Parental service
	 * 
	 * @var string
	 */
	protected $_parentService;
	
	/**
	 * Parental datagate
	 * 
	 * @var string 
	 */
	protected $_parentDatagate;
	
	/**
	 * Force the insertion action at the save method calling
	 *
	 * @var bool 
	 */
	protected $_forceInsert = false;
	
	/**
	 * Set the parental service
	 * 
	 * @param  object $sevice
	 * @return ModelAbstract
	 */
	public function setParentService($sevice) {
		$this->_parentService = $sevice;
		
		return $this;
	}
	
	/**
	 * Get athe parent service
	 * 
	 * @return object
	 */
	public function getParentService() {
		return $this->_parentService;
	}
	
	/**
	 * Set the parental datagate
	 *  
	 * @param  object $datagateName
	 * @return ModelAbstract
	 */
	public function setParentDatagate($datagate) {
		$this->_parentDatagate = $datagate;
		
		return $this;
	}
	
	/**
	 * Get the parental datagate
	 * 
	 * @return object
	 */
	public function getParentDatagate() {
		return $this->_parentDatagate;
	}
	
	/**
	 * Save a model or changes of a model
	 * 
	 * @return ModelAbstract
	 */
	public function save() {
		$this->_getDatagateCrud()->save($this);
		
		return $this;
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
		$this->_getDatagateCrud()->remove($this);
		
		return $this;		
	}
	
	/**
	 * Get datagate for "CRUD" actions
	 * 
	 * @return CrudInterface
	 * @throws Exception
	 */
	protected function _getDatagateCrud() {
		$datagate = $this->getParentDatagate();
		
		if ($datagate === null) {
			throw new Exception('Model isn\'t linked to a datagate');
		}
		
		if (! $datagate instanceof CrudInterface) {
			throw new Exception('Datagate "' . get_class($datagate) . '" hasn\'t "CRUD" methods');
		}
		
		return $datagate;
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
	
	// Model to a parental service proxy
	public function __call($method, $arguments = array()) {
		$service = $this->getParentService();
		
		if ($service === null){
			throw new Exception('Proxying to a parental service isn\'t available (called method: "' . $method . '")');
		}	
		
		array_unshift($arguments, $this);
		return call_user_func_array(array($service, $method), $arguments);
	}
	
}