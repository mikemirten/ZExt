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
 * Errors information collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    2.0
 */
class Errors extends CollectorAbstract {
	
	/**
	 * Error representation template
	 *
	 * @var string
	 */
	protected $errorTemplate = "[keyword]%s[/keyword]: %s\n[strong]File: [/strong]%s ([success]%s[/success])";
	
	/**
	 * Known types of an errors
	 *
	 * @var array
	 */
	static protected $errorTypes = [
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parse',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core error',
		E_CORE_WARNING       => 'Core warning',
		E_COMPILE_ERROR      => 'Compile error',
		E_COMPILE_WARNING    => 'Compile warning',
		E_USER_ERROR         => 'User error',
		E_USER_WARNING       => 'User warning',
		E_USER_NOTICE        => 'User notice',
		E_STRICT             => 'Strict',
		E_RECOVERABLE_ERROR  => 'Recoverable error',
		E_DEPRECATED         => 'Deprecated',
		E_USER_DEPRECATED    => 'User deprecated'
	];
	
	/**
	 * Handled errors
	 * 
	 * @var array
	 */
	protected $errors = [];
	
	/**
	 * Has an error more than warning
	 *
	 * @var bool
	 */
	protected $hasError = false;
	
	/**
	 * Init the collector
	 */
	protected function init() {
		$errorMask = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR |
		             E_PARSE | E_USER_ERROR | E_RECOVERABLE_ERROR;
		
		set_error_handler(function($type, $message, $file, $line) use($errorMask) {
			$this->errors[] = [
				'type'    => $type,
				'message' => $message,
				'file'    => $file,
				'line'    => $line
			];
			
			if ($errorMask & $type) {
				$this->hasError = true;
			}
			 
			return true;
		});
	}
	
	/**
	 * Get the collected information
	 * 
	 * @return InfoSet
	 */
	public function getInfo() {
		$info = $this->createInfoset();
		$info->setName('Occurred errors');
		
		$this->handleIcon($info);
		$this->createTitle($info);
		
		if (! empty($this->errors)) {
			$info->setContentType(Infoset::TYPE_LIST)
			     ->enableBbCodes();
			     
			$this->createContent($info);
		}
		
		return $info;
	}
	
	/**
	 * Handle the iconn
	 * 
	 * @param Infoset $info
	 */
	protected function handleIcon(Infoset $info) {
		if (empty($this->errors)) {
			$info->setIcon('ok');
			return;
		}
		
		if ($this->hasError) {
			$info->setIcon('alert');
			return;
		}
		
		$info->setIcon('warning');
	}


	/**
	 * Create the title
	 * 
	 * @param Infoset $info
	 */
	protected function createTitle(Infoset $info) {
		$errors = count($this->errors);
		
		if ($errors === 0) {
			$info->setTitle('No errors');
			return;
		}
		
		$info->setTitle($errors . ($errors > 1 ? ' Errors' : ' Error'));
	}
	
	/**
	 * Create the content
	 * 
	 * @param Infoset $info
	 */
	protected function createContent(Infoset $info) {
		foreach ($this->errors as $error) {
			if (isset(self::$errorTypes[$error['type']])) {
				$type = self::$errorTypes[$error['type']];
			} else {
				$type = 'Uncnown';
			}

			$info[] = sprintf($this->errorTemplate, $type, $error['message'], $error['file'], $error['line']);
		}
	}
	
}
