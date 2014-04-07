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

use IntlDateFormatter;

/**
 * Date formatter
 * 
 * @category   ZExt
 * @package    Formatter
 * @author     Mike.Mirten
 * @version    1.0
 */
class Date implements FormatterInterface {
	
	/**
	 * Intl date formatters instances for different locales and formats
	 *
	 * @var IntlDateFormatter 
	 */
	protected $formatters     = [];
	protected $formattersDay  = [];
	protected $formattersTime = [];
	
	/**
	 * Format the date
	 * 
	 * @param  int    $value
	 * @param  array  $params
	 * @param  string $locale
	 * @return string
	 */
	public function format($value, $params = null, $locale = null) {
		if (! isset($params['primary'])) {
			return $this->getFormatter($locale)->format($value);
		}
		
		if ($params['primary'] === 'day') {
			return $this->getFormatterDay($locale)->format($value);
		}
		
		if ($params['primary'] === 'time') {
			return $this->getFormatterTime($locale)->format($value);
		}
		
		return $this->getFormatter($locale)->format($value);
	}
	
	/**
	 * Get the date and time formatter
	 * 
	 * @return IntlDateFormatter
	 */
	protected function getFormatter($locale) {
		if (! isset($this->formatters[$locale])) {
			$this->formatters[$locale] = new IntlDateFormatter($locale, IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
		}
		
		return $this->formatters[$locale];
	}
	
	/**
	 * Get the date only formatter
	 * 
	 * @return IntlDateFormatter
	 */
	protected function getFormatterDay($locale) {
		if (! isset($this->formattersDay[$locale])) {
			$this->formattersDay[$locale] = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
		}
		
		return $this->formattersDay[$locale];
	}
	
	/**
	 * Get the time only formatter
	 * 
	 * @return IntlDateFormatter
	 */
	protected function getFormatterTime($locale) {
		if (! isset($this->formattersTime[$locale])) {
			$this->formattersTime[$locale] = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::MEDIUM);
		}
		
		return $this->formattersTime[$locale];
	}
	
	
}