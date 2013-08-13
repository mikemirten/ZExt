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
use ZExt\Html\ListUnordered;
use ZExt\Html\ListOrdered;
use ZExt\Html\Tag;

/**
 * Debug bar's component
 * 
 * @package Debug
 * @subpackage CacheModule
 * @author Mike.Mirten
 * @version 1.0b
 */
class Cache extends ModuleAbstract {
	
	/**
	 * Zend cache adapter
	 * 
	 * @var \Zend_Cache_Core 
	 */
	protected $_adapter;
	
	/**
	 * Set an adapter of a database
	 * 
	 * @param \Zend_Cache_Core $adapter
	 * @return DatabaseSql
	 */
	public function setAdapter(\Zend_Cache_Core $adapter) {
		$this->_adapter = $adapter;
		
		return $this;
	}
	
	/**
	 * Get an adapter of a database
	 * 
	 * @return \Zend_Cache_Core
	 */
	public function getAdapter() {
		if ($this->_adapter === false) return;
		
		if ($this->_adapter === null) {
			$this->_adapter = \Zend_Db_Table::getDefaultMetadataCache();
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
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wMCBYnB/VmSx4AAAQtSURBVEjH3VXNS1xXFP/dz/fePGfUkapTnYrakIohRcaKqSUuukugFLrqomThLEpLKYFC/4IuC4UsJ91UV6UQCBK6Klk0FBltcKX5INP4FTODmTrOmzfX9969Xeg8VBINFLrogcN79+t3zvmdwznAfymXLl361xiJROLYmpy80NXV9cH09PRHJy+eJpRSsrKyslcsFn8AUDt6xls/T548wdDQEBkbG7t2+fLlvG3br22AEILR0VFVLBZ/B/BbPp/HzZs3D86WlpaQy+UAALOzs7l79+79mMlk3hVCnAlqjInXQRAEnuf9dP78+S9nZmYUABQKhQOKbt++/XZ7e/tnxpiZO3fuZIwxlDF2qgGtNQghsRpjzMDAAC5cuPCr1vpuoVD4fm5uLuIAsL29PSmE+Mq27fYoikij0QClFABgjIk9Pek1IQScczDGQAiBEAJhGH7YaDTecRznZwB/8cMkpWq1mpdIJNp3d3ejZrPJCSHHQI0xaO0dvgHnHFEUgfODVFar1TAIAtVoNPxz5851xQaCIIiUUnuMMXieh2azeWZipZSwLCs2VqlUiOM4EEJAKVXTWh9U0cTEBE8mkyOpVCqzv79PwjCknue9lPNWJEIIcM6htUYURajX6yiVShgaGqIA7FQqlU0mk28BWOJTU1NMCMEppcoYE/m+T5RSr6wcxhiEEDHvYRhifX0dlmXB8zwthACl1Hdd1wEAvrm5iVKpVM9ms77jOERrTVqPj3LNGAPnHFJKCCFgWRYopVhbWwOlFFJKBEFApJT00aNHL+r1+j4A8IsXLzpjY2PTrusOhGEISimRUsbALXBKKYQQkFKCcw5CCNbW1gAAQggYY0ApZWEYYnJy8r3t7e1xAL/wWq1myuVyNZ1O1wFwQojtOA4RQsRcU0qP0aOUQqVSiaNqlXOz2QyNMXRjY6O8t7fnASA8iiJdLpefd3R0+FLKTiklSSQSsactA4wxGGNQLpehlIqBW3JYxowxRqvVamV3d/fvnp4ewq5evfrG+Pj4d21tbb0PHz6sVCqVpOu6sCwLtm3DsiwwxtBsNrGzswOtdUwZY+yYAggHBwe9bDb7ZjKZ9Iwx89zzPLq1tfW8v7+/03Xddtd14TgOjDGIogi+76PRaCAIghj4SHs4FoUQgtu2bT979mzL9/294eFhyovForFtuzwyMjIqhHDq9bpRSpFW7QM46uGpYoxBZ2en3NnZiVZXV18sLi4acuPGjelcLne3VCot3bp1az2TyXz8ui36ZXulUqlw/fr1Twkhz+/fvz9Furu70xMTE+/Pz88v5fP5b/v6+r5+nVbdaoZHRSmFhYWFT54+fbre29ubePz48R/H3Lhy5crn6XT6G631/klArXUQRVF4RmDBgwcPvlheXv7zVSNTAmh72SgFEB3qqWMCgH/4/Z/IP1JI1NMaqNImAAAAAElFTkSuQmCC';
	}
	
	/**
	 * Tab with base information
	 * 
	 * @return string
	 */
	public function renderTab() {
		$adapter = $this->getAdapter();
		if (! $adapter) return 'No adapter';
		
		if (! method_exists($adapter, 'getProfiler')) return 'Profiler is unsupported';
		$profiler = $adapter->getProfiler();
		if (! $profiler) return 'No profiler';
		
		$numberQueries = $profiler->getTotalNumQueries();
		if (! $numberQueries) return 'No queries';
		
		$totalElapsed = $profiler->getTotalElapsedSecs();
		
		return $numberQueries . ' in ' . $this->formatTime($totalElapsed);
	}
	
	/**
	 * Panel with full information
	 * 
	 * @return string
	 */
	public function renderPanel() {
		$html = null;
		
		if (function_exists('apc_sma_info')) {
			$apcInfo = apc_sma_info();
			
			$apcTitle = new Tag('h4', 'APC');
			
			$apcInfoTag = new ListUnordered();
			$apcInfoTag->addClass('list-rows list-simple');
			
			$totalUsed = $apcInfo['seg_size'] * $apcInfo['num_seg'];
			
			$memUsed = $this->formatMemory($totalUsed);
			$memFree = $this->formatMemory($apcInfo['avail_mem'] + $totalUsed);
			
			$apcInfoTag->addElement('Used memory: ' . $memUsed . ' of ' . $memFree);
			
			$html = $apcTitle . $apcInfoTag;
		}
		
		$adapter = $this->getAdapter();
		if (! $adapter) return $html;
		
		if (! method_exists($adapter, 'getProfiler')) return $html;
		$profiler = $adapter->getProfiler();
		if (! $profiler || ! $profiler->getEnabled()) return $html;
		
		$backend = $adapter->getBackend();
		if ($backend instanceof \Zend_Cache_Backend_ExtendedInterface) {
			$filling = ' (' . $backend->getFillingPercentage() . '% filling)';
		} else {
			$filling = '';
		}
		
		$title = new Tag('h4', get_class($adapter) . $filling);
		
		$list = new ListOrdered();
		$list->addClass('list-rows');
		
		foreach ($profiler->getQueryProfiles() as $profile) {
			$time  = new Tag('strong', $this->formatTime($profile->getElapsedSecs()) . ': ');
			$query = $profile->getQuery();
			
			$list->addElement($time . ' ' . $query);
		}
		
		return $html . $title . $list;
	}
	
}