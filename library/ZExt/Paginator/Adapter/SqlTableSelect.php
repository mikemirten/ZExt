<?php
namespace ZExt\Paginator\Adapter;

use ZExt\Datagate\SqlTableAbstract;
use Zend_Db_Select as Select;
use PDO;

class SqlTableSelect implements AdapterInterface {
	
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
			$select = clone $this->select;
			$select->reset(Select::COLUMNS)
				   ->reset(Select::LIMIT_OFFSET)
				   ->reset(Select::LIMIT_COUNT)
				   ->columns(['count' => 'count(*)']);

			$this->count = $select->query(PDO::FETCH_OBJ)->fetch()->count;
		}
		
		return $this->count;
	}
	
}