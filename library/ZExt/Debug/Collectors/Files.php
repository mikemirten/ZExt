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

namespace ZExt\Debug\Collectors;

use ZExt\Debug\Infosets\Infoset;

/**
 * Included files information collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    2.0
 */
class Files extends CollectorAbstract {
	
	/**
	 * Included files
	 *
	 * @var array
	 */
	protected $includedFiles;
	
	/**
	 * Additional APC information template
	 *
	 * @var string 
	 */
	static protected $apcInfo = '[special]Mem: [info]%s[/info], Hits: [success]%s[/success][/special]';
	
	/**
	 * Groups of the files by the paths
	 * 
	 * @var array 
	 */
	protected $groups = [];
	
	/**
	 * Set the groups of the files by the paths [title => path]
	 * 
	 * @param array $groups
	 */
	public function setGroups(array $groups) {
		foreach ($groups as $title => $path) {
			$this->setGroup($title, $path);
		}
	}
	
	/**
	 * Set the group of the files by the path
	 * 
	 * @param string $path
	 * @param string $title
	 */
	public function setGroup($title, $path) {
		$path = rtrim(realpath($path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		$this->groups[(string) $title] = $path;
	}
	
	/**
	 * Get the collected information
	 * 
	 * @return Infoset
	 */
	public function getInfo() {
		$info  = $this->createInfoset();
		$files = $this->getIncludedFiles();
		
		$info->setIcon('script')
		     ->setTitle(count($files) . ' Files');
		
		$this->createContent($info);
		
		return $info;
	}
	
	/**
	 * Create the content
	 * 
	 * @param Infoset $info
	 */
	protected function createContent(Infoset $info) {
		$this->includePathInfo($info);
		
		if (extension_loaded('apc')) {
			$apcInfo  = apc_cache_info();
			$apcFiles = [];
			
			foreach ($apcInfo['cache_list'] as $file) {
				$apcFiles[$file['filename']] = $file;
			}
			
			$this->apcInfo($info, $apcInfo);
		}
		
		$apcEnabled = ! empty($apcFiles);
		
		$groups = $this->includedFilesInfo($apcEnabled ? $apcFiles : null);
		
		foreach ($groups as $title => $group) {
			$table = $this->createTable();
			$table->setColsWidths([50, 50]);
			$table->setTitle($title);
			
			if ($apcEnabled) {
				$table->enableBbCodes();
			}
			
			foreach ($group as $file) {
				$table[] = $file;
			}
			
			$info[] = $table;
		}
	}
	
	/**
	 * Create the include path information
	 * 
	 * @param Infoset $info
	 */
	protected function includePathInfo(Infoset $info) {
		$list = $this->createList();
		$list->setTitle('Include path');
		
		$include = explode(PATH_SEPARATOR, get_include_path());
		
		foreach ($include as $path) {
			$list[] = $path;
		}
		
		$info[] = $list;
	}
	
	/**
	 * Create groups with information about the included files
	 * 
	 * @param  array $apcFiles
	 * @return array
	 */
	protected function includedFilesInfo(array $apcFiles = null) {
		foreach ($this->getIncludedFiles() as $file) {
			if ($apcFiles !== null && isset($apcFiles[$file])) {
				$apcMem   = $this->formatMemory($apcFiles[$file]['mem_size']);
				$apcHits  = $apcFiles[$file]['num_hits'];
				$fileInfo = sprintf(self::$apcInfo, $apcMem, $apcHits);
			} else {
				$fileInfo = '';
			}
			
			foreach ($this->groups as $title => $path) {
				if (strpos($file, $path) === 0) {
					if (! isset($groups[$title])) {
						$groups[$title] = [];
					}
					
					$groups[$title][] = [str_replace($path, '', $file), $fileInfo];
					continue 2;
				}
			}
			
			$other[] = [$file, $fileInfo];
		}
		
		if (! empty($other)) {
			if (empty($groups)) {
				$groups['Included files:'] = $other;
			} else {
				$groups['Other files:'] = $other;
			}
		}
		
		return $groups;
	}
	
	/**
	 * Collect the APC info
	 * 
	 * @param Infoset $infoset
	 * @param array   $apcInfo
	 */
	protected function apcInfo(Infoset $infoset, array $apcInfo) {
		$info = $this->createTable()
			->setColsWidths([50, 50])
			->setTitle('APC Info')
			->enableBbCodes();
		
		$infoset[] = $info;
		
		$info[] = ['Memory:', '[info]' . $this->formatMemory($apcInfo['mem_size']) . '[/info]'];

		if (isset($apcInfo['num_entries'])) {
			$info[] = ['Cache entries: ', '[success]' . $apcInfo['num_entries'] . '[/success]'];
		}
		else if (isset($apcInfo['nentries'])) {
			$info[] = ['Cache entries: ', '[success]' . $apcInfo['nentries'] . '[/success]'];
		}

		if (isset($apcInfo['num_hits'])) {
			$info[] = ['Cache hits: ', '[success]' . $apcInfo['num_hits'] . '[/success]'];
		}
		else if (isset($apcInfo['nhits'])) {
			$info[] = ['Cache hits: ', '[success]' . $apcInfo['nhits'] . '[/success]'];
		}

		if (isset($apcInfo['num_misses'])) {
			$info[] = ['Cache misses: ', '[success]' . $apcInfo['num_misses'] . '[/success]'];
		}
		else if (isset($apcInfo['nmisses'])) {
			$info[] = ['Cache misses: ', '[success]' . $apcInfo['nmisses'] . '[/success]'];
		}
	}
	
	/**
	 * Get included files
	 * 
	 * @return array
	 */
	protected function getIncludedFiles() {
		if ($this->includedFiles === null) {
			$this->includedFiles = get_included_files();
		}
		
		return $this->includedFiles;
	}
	
}