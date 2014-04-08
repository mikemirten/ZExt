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

namespace ZExt\I18n\Resource;

use ZExt\I18n\Resource\Exceptions\ParseError;

/**
 * Translator Csv-files resource
 * 
 * @category   ZExt
 * @package    I18n
 * @subpackage Resource
 * @author     Mike.Mirten
 * @version    1.0
 */
class Csv extends FilesBasedAbstract {
	
	/**
	 * Filename extension
	 *
	 * @var string
	 */
	protected $filenameExtension = 'csv';
	
	/**
	 * CSV-line delimiter
	 *
	 * @var string
	 */
	protected $csvDelimiter = ';';
	
	/**
	 * CSV-line enclosure
	 *
	 * @var string
	 */
	protected $csvEnclosure = '"';
	
	/**
	 * Initialized catalogs' list
	 *
	 * @var array
	 */
	protected $initializedCatalogs = [];
	
	/**
	 * Set the line delimiter
	 * 
	 * @param  string $delimiter
	 * @return Csv
	 */
	public function setDelimiter($delimiter) {
		$this->csvDelimiter = (string) $delimiter;
		
		return $this;
	}
	
	/**
	 * Get the line delimiter
	 * 
	 * @return string
	 */
	public function getDelimiter() {
		return $this->csvDelimiter;
	}
	
	/**
	 * Set the line enclosure
	 * 
	 * @param  string $enclosure
	 * @return Csv
	 */
	public function setEnclosure($enclosure) {
		$this->csvEnclosure = (string) $enclosure;
		
		return $this;
	}
	
	/**
	 * Get the line enclosure
	 * 
	 * @return string
	 */
	public function getEnclosure() {
		return $this->csvEnclosure;
	}
	
	/**
	 * Initialize the catalog for the locale and domain
	 * 
	 * @param string $locale
	 * @param string $domain
	 */
	protected function initCatalog($locale, $domain) {
		if (isset($this->initializedCatalogs[$locale][$domain])) {
			return;
		}
		
		$content = $this->getCatalogContent($locale, $domain);
		
		if ($content === false) {
			$this->markAsInitialized($locale, $domain);
			return;
		}
		
		$catalog = $this->parseContent($content);
		
		if (! isset($this->catalogs[$locale])) {
			$this->catalogs[$locale] = [];
		}
		
		$this->catalogs[$locale][$domain] = $catalog;
		
		$this->markAsInitialized($locale, $domain);
	}
	
	/**
	 * Parse the CSV-content
	 * 
	 * @param  string $content
	 * @return array
	 * @throws ParseError
	 */
	protected function parseContent($content) {
		$lines  = explode(PHP_EOL, $content);
		$result = [];
		
		foreach ($lines as $lineNm => $lineRaw) {
			if (empty($lineRaw)) {
				continue;
			}
			
			$line = str_getcsv($lineRaw, $this->csvDelimiter, $this->csvEnclosure);
			
			if (count($line) < 2) {
				$template = 'Error has occurred while parsing CSV file for the file: "%s" on line %s';
				throw new ParseError(sprintf($template, $this->lastFilePath, $lineNm));
			}
			
			$result[trim($line[0])] = trim($line[1]);
		}
		
		return $result;
	}
	
	/**
	 * Mark the resource as initialized
	 * 
	 * @param string $locale
	 * @param string $domain
	 */
	protected function markAsInitialized($locale, $domain) {
		if (! isset($this->initializedCatalogs[$locale])) {
			$this->initializedCatalogs[$locale] = [];
		}

		$this->initializedCatalogs[$locale][$domain] = true;
	}

}