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
use ZExt\Html\ListOrdered;
use ZExt\Html\Tag;

/**
 * Debug bar's component
 * 
 * @package Debug
 * @subpackage DatabaseSqlModule
 * @author Mike.Mirten
 * @version 1.0b
 */
class DatabaseSql extends ModuleAbstract {
	
	/**
	 * Zend database adapter
	 * 
	 * @var \Zend_Db_Adapter_Abstract
	 */
	protected $_adapter;
	
	public function init() {
		$adapter = $this->getAdapter();
		if ($adapter) $adapter->getProfiler()->setEnabled(true);
	}
	
	/**
	 * Set an adapter of a database
	 * 
	 * @param \Zend_Db_Adapter_Abstract $adapter
	 * @return DatabaseSql
	 */
	public function setAdapter(\Zend_Db_Adapter_Abstract $adapter) {
		$this->_adapter = $adapter;
		
		return $this;
	}
	
	/**
	 * Get an adapter of a database
	 * 
	 * @return \Zend_Db_Adapter_Abstract
	 */
	public function getAdapter() {
		if ($this->_adapter === false) return;
		
		if ($this->_adapter === null) {
			$this->_adapter = \Zend_Db_Table::getDefaultAdapter();
			if (! $this->_adapter) {
				$this->_adapter = false;
				return;
			}
		}
		
		return $this->_adapter;
	}
	
	/**
	 * Get a base64 encoded icon for a tab
	 * 
	 * @param size of an icon
	 * @return string 
	 */
	public function getTabIcon($size = null) {
		return 'data:image/png;base64,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';
	}
	
	/**
	 * Tab with base information
	 * 
	 * @return string
	 */
	public function renderTab() {
		$adapter = $this->getAdapter();
		if (! $adapter) return 'No adapter';
		
		$profiler = $adapter->getProfiler();
		if (! $profiler || ! $profiler->getEnabled()) return 'No profiler';
		
		$numberQueries = $profiler->getTotalNumQueries();
		if (! $numberQueries) return 'No queries';
		
		$totalElapsed  = $profiler->getTotalElapsedSecs();
		
		return $numberQueries . ' in ' . $this->formatTime($totalElapsed);
	}
	
	/**
	 * Panel with full information
	 * 
	 * @return string
	 */
	public function renderPanel() {
		$adapter = $this->getAdapter();
		if (! $adapter) return;
		
		$profiler = $adapter->getProfiler();
		if (! $profiler || ! $profiler->getEnabled()) return;
		
		$profiles = $profiler->getQueryProfiles();
		
		if (! empty($profiles)) {
			$list = new ListOrdered();
			$list->addClass('list-rows');

			foreach ($profiles as $profile) {
				$time  = new Tag('strong', $this->formatTime($profile->getElapsedSecs()) . ': ');
				$query = $this->highlightQuery($profile->getQuery());

				$list->addElement($time . ' ' . $query);
			}

			return $list->render();
		}
	}
	
	/**
	 * Highlight SQL Query
	 * 
	 * @param string $query
	 * @return string
	 */
	protected function highlightQuery($query) {
		$keywords = array(
			'describe', 'into',   'join',   'set', 'left',   'right',
			'insert',   'delete', 'select', 'asc', 'group',  'by',  'on',
			'from',     'limit',  'where',  'in',  'order',  'and', 'or',
			'update',   'create', 'desc',   'as',  'having', '||',  '&&'
		);
		
		$keywords = array_map('strtoupper', $keywords);
		$keywords = array_map('preg_quote', $keywords);
		$keywords = '/(' . implode('|', $keywords) . ')/';
		
		$replace = array(
			$keywords               => new Tag('span', '$1', 'debug-keyword'),
			'/(\'.+\')/i'           => new Tag('span', '$1', 'debug-string'),
			'/(`[a-z0-9_#@\$]+`)/i' => new Tag('span', '$1', 'debug-string2')
		);
		
		return preg_replace(array_keys($replace), array_values($replace), $query);
	}
	
}