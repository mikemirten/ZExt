<?php
namespace ZExt\Paginator\Adapter;

use ZExt\Datagate\SqlTableAbstract;
use Zend_Db_Select as Select;
use PDO;

class SqlTableSelect implements AdapterInterface {
	
	const COUNT_COLUMN = 'zext_paginator_count';
	
	/**
	 * Datagate
	 * 
	 * @var SqlTableAbstract 
	 */
	protected $datagate;
	
	/**
	 * Zend db select
	 *
	 * @var Select
	 */
	protected $select;
	
	/**
	 * Number of the items
	 *
	 * @var int
	 */
	protected $count;
	
	/**
	 * Constructor
	 * 
	 * @param Select           $select
	 * @param SqlTableAbstract $datagate
	 */
	public function __construct(Select $select, SqlTableAbstract $datagate = null) {
		$this->select   = $select;
		$this->datagate = $datagate;
	}
	
	/**
	 * Get an items of the page
	 * 
	 * @param  int $offset
	 * @param  int $limit
	 * @return \Traversable
	 */
	public function getItems($offset, $limit) {
		$this->select->limit($limit, $offset);
		
		return $this->datagate->fetchAll($this->select);
	}
	
	/**
	 * Get the number of the all items
	 * 
	 * @return int
	 */
	public function count() {
		if ($this->count === null) {
			$groupPart = $this->select->getPart(Select::GROUP);
			
			$select = empty($groupPart)
				? $this->prepareSelect(clone $this->select)
				: $this->prepareSubquerySelect(clone $this->select);
			
			$this->count = (int) $select->query(PDO::FETCH_OBJ)->fetch()->{self::COUNT_COLUMN};
		}
		
		return $this->count;
	}
	
	/**
	 * Prepare the simple count select
	 * 
	 * @param  Select $select
	 * @return Select
	 */
	protected function prepareSelect(Select $select) {
		$select->reset(Select::COLUMNS)
		       ->reset(Select::LIMIT_OFFSET)
		       ->reset(Select::LIMIT_COUNT)
		       ->reset(Select::ORDER)
		       ->columns([self::COUNT_COLUMN => 'count(*)']);

		return $select;
	}
	
	/**
	 * Prepare the subquery based count select
	 * 
	 * @param  Select $select
	 * @return Select
	 */
	protected function prepareSubquerySelect(Select $select) {
		$select->reset(Select::LIMIT_OFFSET)
		       ->reset(Select::LIMIT_COUNT)
		       ->reset(Select::ORDER);

		$countSelect = $select->getAdapter()->select()
			->from($select)
			->reset(Select::COLUMNS)
			->columns([self::COUNT_COLUMN => 'count(1)']);

		return $countSelect;
	}
	
}