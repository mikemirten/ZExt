<?php
namespace ZExt\Paginator\Adapter;

use ZExt\Datagate\DatagateInterface;
use ZExt\Model\Collection;

use MongoCursor;

class MongoCursorAdapter implements AdapterInterface {
	
	/**
	 * Mongo cursor
	 *
	 * @var MongoCursor
	 */
	protected $cursor;
	
	/**
	 * Datagate
	 *
	 * @var DatagateInterface 
	 */
	protected $datagate;
	
	/**
	 * Constructor
	 * 
	 * @param MongoCursor       $cursor
	 * @param DatagateInterface $datagate
	 */
	public function __construct(MongoCursor $cursor = null, DatagateInterface $datagate = null) {
		if ($cursor !== null) {
			$this->setCursor($cursor);
		}
		
		if ($datagate !== null) {
			$this->setDatagate($datagate);
		}
	}
	
	/**
	 * Set a mongo cursor
	 * 
	 * @param  MongoCursor $cursor
	 * @return PaginatorAdapter
	 */
	public function setCursor(MongoCursor $cursor) {
		$this->cursor = $cursor;
		
		return $this;
	}
	
	/**
	 * Set a datagate
	 * 
	 * @param  DatagateInterface $datagate
	 * @return PaginatorAdapter
	 */
	public function setDatagate(DatagateInterface $datagate) {
		$this->datagate = $datagate;
		
		return $this;
	}
	
	/**
	 * Get an items of the page
	 * 
	 * @param  int $offset
	 * @param  int $limit
	 * @return Collection
	 */
	public function getItems($offset, $limit) {
		$this->cursor->skip($offset);
		$this->cursor->limit($limit);
		
		$data = iterator_to_array($this->cursor);

		if ($this->datagate === null) {
			return new Collection($data);
		} else {
			return $this->datagate->createCollection($data);
		}
	}
	
	/**
	 * Get the number of the all items
	 * 
	 * @return int
	 */
	public function count() {
		return $this->cursor->count();
	}
	
}