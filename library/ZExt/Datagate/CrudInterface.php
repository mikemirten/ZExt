<?php
namespace ZExt\Datagate;
use ZExt\Model\ModelInterface;

interface CrudInterface {
	
	/**
	 * Save a newly created or modified model
	 * 
	 * @param ModelInterface $model
	 */
	public function save(ModelInterface $model);
	
	/**
	 * Fetch a model by id
	 * 
	 * @param mixed $id
	 */
	public function fetch($id);
	
	/**
	 * Remove a model
	 * 
	 * @param ModelInterface $model
	 */
	public function remove(ModelInterface $model);
	
}