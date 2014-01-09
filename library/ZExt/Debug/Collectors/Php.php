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
 * @version   2.0
 */

namespace ZExt\Debug\Collectors;

use ZExt\Debug\Infosets\Infoset;

/**
 * Php engine informarion collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    2.0
 */
class Php extends CollectorAbstract {
	
	/**
	 * Get the collected information
	 * 
	 * @return Infoset
	 */
	public function getInfo() {
		$info = $this->createInfoset()
			->setName('Php engine')
			->setIcon('elephant');
		
		preg_match('/([0-9\.]+)/i', phpversion(), $matches);
		$info->setTitle('PHP ' . $matches[1]);
		
		$this->engineInfo($info);
		$this->extensionsInfo($info);
		
		return $info;
	}
	
	/**
	 * Collect information about engine
	 * 
	 * @param Infoset $info
	 */
	protected function engineInfo(Infoset $info) {
		$engine = $this->createTable()
			->setColsWidths([1, 99])
			->enableBbCodes()
			->setTitle('Engine');
		
		$engine[] = ['PHP:', '[success]' . phpversion() . '[/success]'];
		$engine[] = ['Zend:', '[success]' . zend_version() . '[/success]'];
		$engine[] = ['OS:', php_uname()];
		$engine[] = ['User:', get_current_user()];
		
		$info[] = $engine;
	}
	
	/**
	 * Collect information about extensions
	 * 
	 * @param Infoset $info
	 */
	protected function extensionsInfo(Infoset $info) {
		$extensions = $this->createTable()
			->setHeadContent(['Name', 'Version'])
			->setColsWidths([1, 99])
			->enableBbCodes()
			->setTitle('Extensions');
		
		$info[] = $extensions;
		
		foreach (get_loaded_extensions() as $extension) {
			$version   = phpversion($extension);
			$extension = ucfirst($extension);
			
			if (empty($version)) {
				$extensions[] = [$extension, ''];
				continue;
			}
			
			$extensions[] = [$extension, ' [success]' . $version . '[/success]'];
		}
	}
	
}