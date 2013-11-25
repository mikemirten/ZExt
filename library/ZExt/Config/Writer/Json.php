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
namespace ZExt\Config\Writer;

use Traversable;

/**
 * Jsonconfig writer
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage Writer
 * @author     Mike.Mirten
 * @version    1.0
 */
class Json implements WriterInterface {
	
	const OPTION_PRETTY = 'pretty';
	
	/**
	 * Default options
	 *
	 * @var array
	 */
	protected $defaultOptions = [
		self::OPTION_PRETTY => true
	];
	
	/**
	 * Assemble the config
	 * 
	 * @param  Traversable $config
	 * @param  array $options
	 * @return string
	 */
	public function assemble(Traversable $config, array $options = []) {
		$jsonOptions = 0;
		
		$options += $this->defaultOptions;
		
		if ($options[self::OPTION_PRETTY] === true) {
			$jsonOptions += JSON_PRETTY_PRINT;
		}
		
		return json_encode($this->iteratoToArray($config), $jsonOptions);
	}
	
	/**
	 * Recursive iterator to array
	 * 
	 * @param  Traversable $config
	 * @return array
	 */
	protected function iteratoToArray(Traversable $config) {
		$config = iterator_to_array($config);
		
		foreach ($config as &$value) {
			if ($value instanceof Traversable) {
				$value = $this->iteratoToArray($value);
			}
		}
		unset($value);
		
		return $config;
	}
	
}