<?php
namespace ZExt\Paginator\Adapter;
use ZExt\Datagate\SqlTableAbstract;

class SqlTableSelect extends \Zend_Paginator_Adapter_DbTableSelect {
	
	/**
	 * Datagate
	 * 
	 * @var SqlTableAbstract 
	 */
	protected $_datagate;
	
	/**
	 * Constructor
	 * 
	 * @param \Zend_Db_Select $select
	 * @param SqlTableAbstract $datagate
	 */
	public function __construct(\Zend_Db_Select $select, SqlTableAbstract $datagate = null) {
		parent::__construct($select);
		
		if ($datagate) $this->setDatagate($datagate);
	}
	
	/**
     * Returns a Collection of Objects for a page.
	 * 
	 * @see \ZExt\Model\Collection
	 * @see \ZExt\Model\Object
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return \ZExt\Model\Object[]
     */
	public function getItems($offset, $itemCountPerPage) {
		$data = parent::getItems($offset, $itemCountPerPage);
		
		$datagate = $this->getDatagate();
		if (! $datagate) return $data;
		
		$datagate->dataTypeConvertArray($data);
		
		return $datagate->createCollection($data);
	}
	
	/**
	 * Set the datagate
	 * 
	 * @param SqlTableAbstract $datagate
	 */
	public function setDatagate(SqlTableAbstract $datagate) {
		$this->_datagate = $datagate;
	}
	
	/**
	 * Get the datagate
	 * 
	 * @return SqlTableAbstract
	 */
	public function getDatagate() {
		return $this->_datagate;
	}
	
}