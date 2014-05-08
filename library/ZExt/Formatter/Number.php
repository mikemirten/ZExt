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

namespace ZExt\Formatter;

use NumberFormatter;

/**
 * Number formatter
 * 
 * @category   ZExt
 * @package    Formatter
 * @author     Mike.Mirten
 * @version    1.0
 */
class Number implements FormatterInterface {
	
	/**
	 * Intl NumberFormatter instances for each locale
	 *
	 * @var NumberFormatter 
	 */
	protected $formatters = [];
	
	/**
	 * Format the number
	 * 
	 * @param  int    $value
	 * @param  array  $params
	 * @param  string $locale
	 * @return string
	 */
	public function format($value, $params = null, $locale = null) {
		if ($locale === null) {
			$locale = 'en';
		}
		
		return $this->getFormatter($locale)->format($value);
	}
	
	/**
	 * Get the formatter for the locale
	 * 
	 * @param  string $locale
	 * @return NumberFormatter
	 */
	protected function getFormatter($locale) {
		if (! isset($this->formatters[$locale])) {
			$this->formatters[$locale] = new NumberFormatter($locale, NumberFormatter::DECIMAL);
		}
		
		return $this->formatters[$locale];
	}
	
}