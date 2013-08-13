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
use ZExt\Html\Tag;

class Files extends ModuleAbstract {
	
	protected $_applicationPath;
	protected $_libraryPath;
	protected $_includedFiles;
	
	public function setApplicationPath($path) {
		$this->_applicationPath = realpath($path);
		
		return $this;
	}
	
	public function getApplicationPath() {
		return $this->_applicationPath;
	}
	
	public function setLibraryPath($path) {
		$this->_libraryPath = realpath($path);
		
		return $this;
	}
	
	public function getLibraryPath() {
		return $this->_libraryPath;
	}
	
	public function getTabIcon($size = null) {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABGdBTUEAALGOfPtRkwAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wMCBUsCmplUDEAAAPwSURBVEjHlZbPjxRFFMc/VdXVP2eXxf3BrusmuAgh688DRk3AmGjCYePFcOFiTDwI8ceFf8EbnuDG2Qt4JOtmDWLYRKOGREVJIGFxgWwEYXZ2BoaZnu6q8oDdmVl2d+Alne7qdL/Pe9+qeq8EXXbu3LkPtNZvOedyAOccxd05R57nZFlGs9nUtVpt7siRIz/Qx7yuZ2mMOVSpVA4XjtdDrLXkeY5SilardWxubu6L2dnZk1sBZDcsyzKllEJKiRDi0Qfy0SfFWGuN7/suDEPiOD6+uLj4OSCeBIAQAqUUQgg6nQ7GmDLqPM9xzpFlGVJKEccxQohgenr6xMLCwqHNID0AKSVSSjzPI0kSPM/DWlvChRBorYnjGN/36XQ6bnJykpmZmTOLi4uf9QUU8hQQpVQ5B1prOp1OmYkxhrW1NXHhwgW3vLwMcHx+fv4xuR7LQClVRu6cIwxDkiQhiiKGh4fZtm0bvu8zOjrK2NgYxhjRbDapVqtBvV4/cerUqYObraIyeuccvu8jpaTVapFlGUpJ0rSNdQ5nLVJ6ZaZDQ0MYY1y73RZKqQ+BBcBtCSh0T5KQ1dUGl36/QbX6AGctg4MRe198lvGJUbKOwVqHtVYopfB9XwEa6GwIKHTP85yrV1aY+/ZPbq00iOII4W5jzDD16n1Wb9cY35Fw+KP97HtjN1pr/gfQVyKlBN+c/omLl+4ixQ0OvPMqcRRy+Y8rTEztYulanShpsXpnha++/If3Dx3g40/exTlFEARbryLP82jUm5z5+leS0OPm9V/4e9lw/rvTrK7l/HvvIUtXv2f7yCQ7pqaItzf57a+7LC2tlFn0zSCMAyqDMfdu3yLPLDevzZNnKeNDEzyoV/G8iEb9Og/u13jlzVnS3KOdNlFKbQ0QQiClpJIkfHrsPc6e/ZFnxg8SD+xESIVzFk8LdvuSeHAnQiakaZOZnTD9/FRPSdkQUERgjOHt/S8xPhbx88UlVu5YHrYdzknyDujoBZSCwbjB7pcHeX3fawwMDJQB9pWosD17djExMUq1epdabY1WKyfLM7QHAwMVnpucYnhkFGNMj48tAUWxK0p0klSI44S9exPA4ax7tNm6rkL3olj2lagwYwxKKdrtNo1GgyzLSJKENE2x1hJFEVpruvvHU0nk+z5pmpalw/M80jQlDEPCMMRaixDiyQHd/QAgCIKed0U3s9aSpmlZVor9sxFAbrRMi46mlCKKorI8F46CICDPc4wxOOfQWpf/9t1o6ycZYGRkpGdSnXNUKpWecXeQmwKCIAiKVDezbmfrDwVCCIJ1xajnVHH06NGTQRCcz/Nc8ZTmnMPzPNNuty93S/8filzctvM99ycAAAAASUVORK5CYII=';
	}
	
	public function renderTab() {
		$files = $this->_getIncludedFiles();
		
		return count($files) . ' Files';
	}
	
	public function renderPanel() {
		$filesApplication = array();
		$filesLibrary     = array();
		$filesOther       = array();
		
		$applicationPath = $this->getApplicationPath();
		$libraryPath     = $this->getLibraryPath();
		
		if (function_exists('apc_cache_info')) {
			$apcUsed = true;
			
			$apcInfo    = apc_cache_info();
			$apcInfoTag = new Tag('span', null, 'debug-info-holder');

			$apcFiles = array();
			foreach ($apcInfo['cache_list'] as $file) {
				$apcFiles[$file['filename']] = $file;
			}
		} else {
			$apcUsed = false;
		}
		
		foreach ($this->_getIncludedFiles() as $file) {
			if ($apcUsed === true && isset($apcFiles[$file])) {
				$apcMem  = ' Mem: ' . $this->formatMemory($apcFiles[$file]['mem_size']);
				$apcHits = ' Hits: ' . $apcFiles[$file]['num_hits'];
				$file .= $apcInfoTag->render('APC' . $apcMem . $apcHits);
			}
			
			if (strpos($file, $applicationPath) !== false) {
				$file = str_replace($applicationPath, '', $file);
				$filesApplication[] = ltrim($file, DIRECTORY_SEPARATOR);
				continue;
			}
			
			if (strpos($file, $libraryPath) !== false) {
				$file = str_replace($libraryPath, '', $file);
				$filesLibrary[] = ltrim($file, DIRECTORY_SEPARATOR);
				continue;
			}
			
			$filesOther[] = $file;
		}
		
		$includePath = explode(PATH_SEPARATOR, get_include_path());
		
		$html = $this->_createList($includePath, 'Include path:');
		
		if ($apcUsed) {
			$info = ['Memory: ' . $this->formatMemory($apcInfo['mem_size'])];
			
			if (isset($apcInfo['num_entries'])) {
				$info[] = 'Cache entries: ' . $apcInfo['num_entries'];
			} else if (isset($apcInfo['nentries'])) {
				$info[] = 'Cache entries: ' . $apcInfo['nentries'];
			}
			
			if (isset($apcInfo['num_hits'])) {
				$info[] = 'Cache hits: ' . $apcInfo['num_hits'];
			} else if (isset($apcInfo['nhits'])) {
				$info[] = 'Cache hits: ' . $apcInfo['nhits'];
			}
			
			if (isset($apcInfo['num_misses'])) {
				$info[] = 'Cache misses: ' . $apcInfo['num_misses'];
			} else if (isset($apcInfo['nmisses'])) {
				$info[] = 'Cache misses: ' . $apcInfo['nmisses'];
			}
			
			$html .= $this->_createList($info, 'APC');
		}
		
		if (! empty($filesApplication)) {
			sort($filesApplication);
			
			$count = count($filesApplication);
			$html .= $this->_createList($filesApplication, "Application files ($count):");
		}
		
		if (! empty($filesLibrary)) {
			sort($filesLibrary);
			
			$count = count($filesLibrary);
			$html .= $this->_createList($filesLibrary, "Library files ($count):");
		}
		
		if (! empty($filesOther)) {
			sort($filesOther);
			
			$count = count($filesOther);
			if (empty($filesApplication) && empty($filesLibrary)) {
				$title = "Files ($count):";
			} else {
				$title = "Other files ($count):";
			}
			
			
			$html .= $this->_createList($filesOther, $title);
		}
		
		return $html;
	}
	
	protected function _createList(array $list, $title) {
		$html  = new Tag('h4', $title);
		$html .= new ListUnordered($list, 'list-rows list-simple');
		
		$containerTag = new Tag('div', $html);
		
		return $containerTag->render();
	}
	
	protected function _getIncludedFiles() {
		if ($this->_includedFiles === null) {
			$this->_includedFiles = get_included_files();
		}
		
		return $this->_includedFiles;
	}
	
}