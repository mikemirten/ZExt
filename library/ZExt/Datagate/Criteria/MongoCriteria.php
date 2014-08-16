<?php
namespace ZExt\Datagate\Criteria;

use ZExt\Datagate\MongoCollection;
use MongoRegex,	Exception;

/**
 * MongoDB conditions abstraction
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Criteria
 * @author     Mike.Mirten
 * @version    1.2
 */
class MongoCriteria implements CriteriaInterface {
	
	// Conditions
	const COND_EQUAL           = '=';
	const COND_LESS            = '<';
	const COND_MORE            = '>';
	const COND_LESS_EQUAL      = '<=';
	const COND_MORE_EQUAL      = '>=';
	const COND_NOT_EQUAL       = '!=';
	const COND_NOT_EQUAL_ALT   = '<>';
	const COND_IN              = 'in';
	const COND_NOT_IN          = 'not in';
	const COND_IN_ARRAY        = 'in array';
	const COND_EXISTS          = 'exists';
	const COND_TYPE            = 'type';
	const COND_IS_ARRAY        = 'is array';
	const COND_IS_STRING       = 'is string';
	const COND_IS_BOOL         = 'is bool';
	const COND_IS_INT          = 'is int';
	const COND_IS_NULL         = 'is null';
	const COND_REGEXP          = 'regexp';
	const COND_LIKE            = 'like';
	const COND_ARRAY_COUNT     = 'array count';
	const COND_ARRAY_COUNT_ALT = 'array size';
	
	// Sorts
	const SORT_ASC  = 'asc';
	const SORT_DESC = 'desc';
	
	// Mongo conditions
	const MONGO_IN         = '$in';
	const MONGO_LESS       = '$lt';
	const MONGO_MORE       = '$gt';
	const MONGO_LESS_EQUAL = '$lte';
	const MONGO_MORE_EQUAL = '$gte';
	const MONGO_NOT_EQUAL  = '$ne';
	const MONGO_NOT_IN     = '$nin';
	const MONGO_IN_ARRAY   = '$all';
	const MONGO_EXISTS     = '$exists';
	const MONGO_TYPE       = '$type';
	const MONGO_SIZE       = '$size';
	const MONGO_OR         = '$or';
	const MONGO_AND        = '$and';
	
	// Mongo datatypes
	const MONGO_TYPE_DOUBLE     = 1;
	const MONGO_TYPE_STRING     = 2;
    const MONGO_TYPE_OBJECT     = 3;
    const MONGO_TYPE_ARRAY      = 4;
    const MONGO_TYPE_BINARY     = 5;
    const MONGO_TYPE_OBJECT_ID  = 7;
    const MONGO_TYPE_BOOLEAN    = 8;
    const MONGO_TYPE_DATE       = 9;
    const MONGO_TYPE_NULL       = 10;
    const MONGO_TYPE_REGEXP     = 11;
    const MONGO_TYPE_JAVASCRIPT = 13;
    const MONGO_TYPE_SYMBOL     = 14;
    const MONGO_TYPE_JS_WSCOPE  = 15;
    const MONGO_TYPE_INT32      = 16;
    const MONGO_TYPE_TIMESTAMP  = 17;
    const MONGO_TYPE_INT64      = 18;
    const MONGO_TYPE_MIN_KEY    = 255;
    const MONGO_TYPE_MAXKEY     = 127;
	
	// Mongo sorts
	const MONGO_SORT_ASC  = 1;
	const MONGO_SORT_DESC = -1;
	
	/**
	 * Aggregate functions list
	 *
	 * @var array
	 */
	static protected $mongoAggregators = ['sum', 'avg', 'min', 'max', 'first', 'last', 'push'];
	
	/**
	 * Datagate
	 *
	 * @var MongoCollection 
	 */
	protected $_datagate;
	
	/**
	 * Select conditions
	 *
	 * @var array
	 */
	protected $_conditions = [];
	
	/**
	 * Sort conditions
	 *
	 * @var array
	 */
	protected $_sort = [];
	
	/**
	 * Records limit
	 *
	 * @var int
	 */
	protected $_limit;
	
	/**
	 * Records offset
	 *
	 * @var int
	 */
	protected $_offset;
	
	/**
	 * Collection name
	 *
	 * @var string
	 */
	protected $_collection;
	
	/**
	 * Properties list
	 *
	 * @var array
	 */
	protected $_properties = [];
	
	/**
	 * Group aggregate definition
	 *
	 * @var array
	 */
	protected $_groupDefinition = [];
	
	/**
	 * Group by property
	 *
	 * @var string
	 */
	protected $_groupBy;

	/**
	 * Constructor
	 * 
	 * @param MongoCollection $datagate
	 */
	public function __construct(MongoCollection $datagate) {
		$this->_datagate = $datagate;
	}
	
	/**
	 * Set the collection name [and properties list]
	 * 
	 * @param  string         $collection
	 * @param  string | array $properties
	 * @return MongoCriteria
	 */
	public function from($collection, $properties = null) {
		$this->_collection = $collection;
		
		if (is_string($properties) || is_array($properties)) {
			$this->_properties = (array) $properties;
		}
		
		return $this;
	}
	
	/**
	 * Set the properties list
	 * 
	 * @param  array $columns
	 * @return MongoCriteria
	 */
	public function columns($columns) {
		foreach ($columns as $property => $definition) {
			if (is_bool($definition)) {
				$this->_properties[$property] = $definition;
				continue;
			}
			
			$this->addAccumulator($property, $definition);
		}
		
		return $this;
	}
	
	/**
	 * Add accumulator to the group
	 * 
	 * @param  string $group
	 * @param  string $definition
	 * @throws Exceptions\InvalidDefinition
	 */
	protected function addAccumulator($group, $definition) {
		$definition = trim($definition);
		
		if (! preg_match('/^([a-z]+) *\( *([a-z_]+) *\)$/i', $definition, $matches)) {
			throw new Exceptions\InvalidDefinition('Invalid definition: "' . $definition . '"');
		}
		
		$function = strtolower(trim($matches[1]));
		$property = trim($matches[2]);
		
		if (! in_array($function, static::$mongoAggregators, true)) {
			throw new Exceptions\InvalidDefinition('Invalid aggregate function: "' . $function . '"');
		}
		
		$this->_groupDefinition[$group] = ['$' . $function => '$' . $property];
	}
	
	/**
	 * Group by property
	 * 
	 * @param  string $property
	 * @return MongoCriteria
	 */
	public function groupBy($property) {
		$this->_groupBy = (string) $property;
		
		return $this;
	}
	
	/**
	 * Add the condition
	 * 
	 * @param  string $condition
	 * @param  mixed  $value
	 * @param  string $type
	 * @return MongoCriteria
	 */
	public function where($condition, $value = null, $type = null) {
		if (strpos($condition, '||')) {
			$conditions = explode('||', $condition);
			$conditions = array_map('trim', $conditions);
			
			$conditionParts = [];
			
			foreach ($conditions as $condition) {
				list($property, $condition, $valueCond) = $this->_parseCondition($condition);
				if ($valueCond !== '?') {
					$value = is_numeric($valueCond) ? $this->_parseNumeric($valueCond) : $valueCond;
				}

				$conditionParts[] = $this->_buildCondition($property, $condition, $value);
			}
			
			$this->_addCondition([self::MONGO_OR => $conditionParts]);
		} else {
			list($property, $condition, $valueCond) = $this->_parseCondition($condition);
			
			if ($valueCond !== '?') {
				$value = is_numeric($valueCond) ? $this->_parseNumeric($valueCond) : $valueCond;
			}
			
			$mongoCondition = $this->_buildCondition($property, $condition, $value);
			$this->_addCondition($mongoCondition);
		}
		
		return $this;
	}
	
	public function orWhere($condition, $value = null, $type = null) {
		
	}
	
	/**
	 * Build the condition
	 * 
	 * @param  string $property
	 * @param  string $condition
	 * @param  mixed  $value
	 * @return array
	 * @throws Exception
	 */
	protected function _buildCondition($property, $condition, $value) {
		switch ($condition) {
			// var = ?
			case self::COND_EQUAL:
				return [$property => $value];
			
			// var in(?)
			case self::COND_IN:
				return [$property => [
					self::MONGO_IN => (array) $value
				]];
			
			// var not in(?)
			case self::COND_NOT_IN:
				return [$property => [
					self::MONGO_NOT_IN => (array) $value
				]];
			
			// var < ?
			case self::COND_LESS:
				return [$property => [
					self::MONGO_LESS => $value
				]];
			
			// var > ?
			case self::COND_MORE:
				return [$property => [
					self::MONGO_MORE => $value
				]];
			
			// var <= ?
			case self::COND_LESS_EQUAL:
				return array($property => array(
					self::MONGO_LESS_EQUAL => $value
				));
			
			// var >= ?
			case self::COND_MORE_EQUAL:
				return [$property => [
					self::MONGO_MORE_EQUAL => $value
				]];
			
			// var != ?
			case self::COND_NOT_EQUAL:
			case self::COND_NOT_EQUAL_ALT:
				return [$property => [
					self::MONGO_NOT_EQUAL => $value
				]];
			
			// var in array(?)
			case self::COND_IN_ARRAY:
				return [$property => [
					self::MONGO_IN_ARRAY => (array) $value
				]];
			
			// var exists(?)
			case self::COND_EXISTS:
				return [$property => [
					self::MONGO_EXISTS => $value
				]];
			
			// var type(?)
			case self::COND_TYPE:
				$this->_where($property, [self::MONGO_TYPE => $value]);
				
			// var is array()
			case self::COND_IS_ARRAY:
				return [$property => [
					self::MONGO_TYPE => self::MONGO_TYPE_ARRAY
				]];
			
			// var is int()
			case self::COND_IS_INT:
				return [$property => [
					self::MONGO_TYPE => self::MONGO_TYPE_INT32
				]];
			
			// var is string()
			case self::COND_IS_STRING:
				return [$property => [
					self::MONGO_TYPE => self::MONGO_TYPE_STRING
				]];
			
			// var is bool()
			case self::COND_IS_BOOL:
				return [$property => [
					self::MONGO_TYPE => self::MONGO_TYPE_BOOLEAN
				]];
			
			// var is null()
			case self::COND_IS_NULL:
				return [$property => [
					self::MONGO_TYPE => self::MONGO_TYPE_NULL
				]];
			
			// var regexp(?)
			case self::COND_REGEXP:
				return [$property => new MongoRegex($value)];
			
			// var like ?
			case self::COND_LIKE:
				$regex = preg_quote(trim($value, '%'));
				if ($value[0] !== '%') $regex = '^' . $regex;
				if ($value[strlen($value) - 1] !== '%') $regex .= '&';
				
				return [$property => new MongoRegex('/' . $regex . '/')];
				
			// var array size ?
			case self::COND_ARRAY_COUNT:
			case self::COND_ARRAY_COUNT_ALT:
				return [$property => [
					self::MONGO_SIZE => $value
				]];
			
			default:
				throw new Exception('Uncnown condition: "' . $condition . '"');
		}
	}
	
	/**
	 * Add the condition to the conditions' list
	 * 
	 * @param array $condition
	 */
	protected function _addCondition(array $condition) {
		$conditions = [];
		
		foreach ($condition as $key => $part) {
			if ($key === self::MONGO_OR) {
				if (isset($this->_conditions[self::MONGO_OR]) || isset($this->_conditions[self::MONGO_AND])) {
					// If the "$or" clause already exists, then converts it to the "$and" clause
					if (isset($this->_conditions[self::MONGO_OR])) {
						$mongoAnd = [
							[self::MONGO_OR => $this->_conditions[self::MONGO_OR]],
							[self::MONGO_OR => $part]
						];
						unset($this->_conditions[self::MONGO_OR]);
					}
					// Otherwise, just create the "$and" clause
					else {
						$mongoAnd = [[self::MONGO_OR => $part]];
					}
					
					// If the "$and" clause already exists, then merges it with the last clause
					if (isset($this->_conditions[self::MONGO_AND])) {
						$this->_conditions[self::MONGO_AND] = array_merge(
							$this->_conditions[self::MONGO_AND],
							$mongoAnd
						);
					}
					// Otherwise, put the created clause into the conditions
					else {
						$this->_conditions[self::MONGO_AND] = $mongoAnd;
					}
				} else {
					$conditions[$key] = $part;
				}
			} else {
				$conditions[$key] = $part;
			}
		}
		
		$this->_conditions = $conditions + $this->_conditions;
	}

	/**
	 * Set the limit and offset
	 * 
	 * @param  int $limit
	 * @param  int $offset
	 * @return MongoCriteria
	 */
	public function limit($limit, $offset = null) {
		$this->_limit = (int) $limit;
		
		if ($offset !== null) {
			$this->_offset = (int) $offset;
		}
		
		return $this;
	}
	
	/**
	 * Set the offset
	 * 
	 * @param  int $offset
	 * @return MongoCriteria
	 */
	public function offset($offset) {
		$this->_offset = (int) $offset;
		
		return $this;
	}
	
	/**
	 * Sort by the property(ies)
	 * 
	 * @param  string | array $properties
	 * @return SelectInterface
	 * @throws Exception
	 */
	public function sort($properties) {
		if (is_string($properties)) {
			if (strpos($properties, ',') === false) {
				$properties = [$properties];
			} else {
				$properties = explode(',', $properties);
			}
		}
		
		if (is_array($properties)) {
			$properties = array_map('trim', $properties);
		} else {
			throw new Exception('Uncnown property type "' . gettype($properties) . '"');
		}
		
		foreach ($properties as $property) {
			if (strpos($property, ' ') === false) {
				$this->_sort[$property] = self::MONGO_SORT_ASC;
			} else {
				list ($property, $sort) = explode(' ', $property);
				
				$sort = strtolower($sort);
				
				if ($sort === self::SORT_ASC) {
					$this->_sort[$property] = self::MONGO_SORT_ASC;
				} else if ($sort === self::SORT_DESC) {
					$this->_sort[$property] = self::MONGO_SORT_DESC;
				} else {
					throw new Exception('Uncnown sort condition "' . $sort . '"');
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Count the number of found records
	 * 
	 * @return int
	 */
	public function count() {
		$cursor = $this->_adapter->find($this, null, null, true);
		
		return $cursor->count();
	}
	
	/**
	 * Get the limit
	 * 
	 * @return int
	 */
	public function getLimit() {
		return $this->_limit;
	}
	
	/**
	 * Set the offset
	 * 
	 * @return int
	 */
	public function getOffset() {
		return $this->_offset;
	}
	
	/**
	 * Get the collection name
	 * 
	 * @return string
	 */
	public function getCollectionName() {
		return $this->_collection;
	}
	
	/**
	 * Get the sort conditions
	 * 
	 * @return array
	 */
	public function getSortConditions() {
		return $this->_sort;
	}
	
	/**
	 * Get "group by" property
	 */
	public function getGroupBy() {
		return $this->_groupBy;
	}
	
	/**
	 * Assemble the query
	 * 
	 * @return array
	 */
	public function assemble() {
		return $this->_conditions;
	}
	
	/**
	 * Assemble the aggregate pipeline
	 * 
	 * @return array
	 */
	public function assemblePipeline() {
		$aggregate = [];
		
		// Conditions
		if (! empty($this->_conditions)) {
			$aggregate[] = ['$match' => $this->_conditions];
		}
		
		// Group
		if (! empty($this->_groupDefinition)) {
			$group = $this->_groupDefinition;
			
			$group['_id'] = ($this->_groupBy === null)
				? null
				: '$' . $this->_groupBy;
			
			$aggregate[] = ['$group' => $group];
		}
		
		// Sort
		if (! empty($this->_sort)) {
			$aggregate[] = ['$sort' => $this->_sort];
		}
		
		// Offset
		if ($this->_offset !== null) {
			$aggregate[] = ['$skip' => $this->_offset];
		}
		
		// Limit
		if ($this->_limit !== null) {
			$aggregate[] = ['$limit' => $this->_limit];
		}
		
		return $aggregate;
	}
	
	/**
	 * Find all records of a data
	 * 
	 * @return \ZExt\Model\Collection | \ZExt\Model\Iterator
	 */
	public function find() {
		return $this->_datagate->find($this);
	}

	/**
	 * Find a first record
	 * 
	 * @return \ZExt\Model\Model | null
	 */
	public function findFirst() {
		return $this->_datagate->findFirst($this);
	}
	
	/**
	 * Aggregate data
	 * 
	 * @param  bool $rawOutput
	 * @return \ZExt\Model\Collection | \ZExt\Model\Model
	 */
	public function aggregate($rawOutput = false) {
		return $this->_datagate->aggregate($this, $rawOutput);
	}
	
	/**
	 * Parse the condition into a parts
	 * 
	 * @param  string $condition
	 * @return array
	 * @throws Exception
	 */
	protected function _parseCondition($condition) {
		if (! preg_match('/
				([a-z0-9_\.]+) #property
				(?|
					(?:\s+([a-z0-9]+[\s_]?[a-z0-9]*)  #keyword
					(?:\(|\s+))|(?:\s*([!=\<\>]+)\s*) #condition
				)
				(.+) #value
			/ix', $condition, $matches)) {
			throw new Exception('Error in condition: "' . $condition . '"');
		}
		
		return [
			trim($matches[1]),
			strtolower(trim($matches[2])),
			trim($matches[3], "()\"' \t\r\n\0\x0B")
		];
	}
	
	/**
	 * Parse the string to a number
	 * 
	 * @param string $string
	 * @return int | float
	 */
	protected function _parseNumeric($string) {
		if (strpos($string, '.') === false) {
			return (int) $string;
		} else {
			return (float) $string;
		}
	}
	
	public function __sleep() {
		return [
			'_conditions',
			'_sort',
			'_limit',
			'_offset',
			'_collection',
			'_properties',
			'_groupDefinition',
			'_groupBy'
		];
	}

}