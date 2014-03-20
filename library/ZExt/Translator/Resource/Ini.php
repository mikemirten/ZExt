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

namespace ZExt\Translator\Resource;

use ZExt\Translator\Resource\Exceptions\InvalidParameter;
use ZExt\Translator\Resource\Exceptions\ParseError;

/**
 * Translator INI-files resource
 * 
 * @category   ZExt
 * @package    Translator
 * @subpackage Resource
 * @author     Mike.Mirten
 * @version    1.0
 */
class Ini extends FilesBasedAbstract {
	
	const SECTIONS_LOCALE = 'locale';
	const SECTIONS_DOMAIN = 'domain';
	const SECTIONS_IGNORE = 'ignore';
	
	/**
	 * Filename extension
	 *
	 * @var string
	 */
	protected $filenameExtension = 'ini';
	
	/**
	 * INI sections treatment mode
	 *
	 * @var string
	 */
	protected $sectionsMode = self::SECTIONS_IGNORE;
	
	/**
	 * Initialized catalogs' list
	 *
	 * @var array
	 */
	protected $initializedCatalogs = [];
	
	/**
	 * Set the sections treatment mode
	 * See SECTIONS_* constants
	 * 
	 * @param  string $mode
	 * @return Ini
	 * @throws InvalidParameter
	 */
	public function setSectionsMode($mode) {
		if (! in_array($mode, [
			self::SECTIONS_LOCALE,
			self::SECTIONS_DOMAIN,
			self::SECTIONS_IGNORE
		], true)) {
			throw new InvalidParameter('Unknown mode of sections treatment: "' . $mode . '"');
		}
		
		$this->sectionsMode = $mode;
		
		return $this;
	}
	
	/**
	 * Get the sections treatment mode
	 * 
	 * @return string
	 */
	public function getSectionsMode() {
		return $this->sectionsMode;
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
		
		switch ($this->sectionsMode) {
			case self::SECTIONS_IGNORE:
				$this->initExact($locale, $domain);
				break;
			
			case self::SECTIONS_DOMAIN:
				$this->initManyDomains($locale, $domain);
				break;
			
			case self::SECTIONS_LOCALE:
				$this->initManyLocales($locale, $domain);
				break;
		}
		
		if (! isset($this->initializedCatalogs[$locale])) {
			$this->initializedCatalogs[$locale] = [];
		}

		$this->initializedCatalogs[$locale][$domain] = true;
	}
	
	/**
	 * Initialize the exact locale and domain
	 * 
	 * @param  string $locale
	 * @param  string $domain
	 * @throws ParseError
	 */
	protected function initExact($locale, $domain) {
		$source = $this->getCatalogContent($locale, $domain);
		
		if ($source !== false) {
			$catalog = parse_ini_string($source);
			
			if ($catalog === false) {
				throw new ParseError('Error has occurred while parsing INI file for the locale: "' . $locale . '" and the domain: "' . $domain . '"');
			}

			$this->catalogs = array_replace_recursive($this->catalogs, [
				$locale => [
					$domain => $catalog
				]
			]);
		}

		if (! isset($this->initializedCatalogs[$locale])) {
			$this->initializedCatalogs[$locale] = [];
		}

		$this->initializedCatalogs[$locale][$domain] = true;
	}
	
	/**
	 * Initialize all domains for the exact locale
	 * 
	 * @param  string $locale
	 * @param  string $domain
	 * @throws ParseError
	 */
	protected function initManyDomains($locale, $domain) {
		$source = $this->getCatalogContent($locale, $domain);
		
		if ($source !== false) {
			$domains = parse_ini_string($source, true);
			
			if ($domains === false) {
				throw new ParseError('Error has occurred while parsing INI file for the locale: "' . $locale . '"');
			}

			$this->catalogs = array_replace_recursive($this->catalogs, [$locale => $domains]);
		}

		if (! isset($this->initializedCatalogs[$locale])) {
			$this->initializedCatalogs[$locale] = [];
		}

		foreach (array_keys($domains) as $domain) {
			$this->initializedCatalogs[$locale][$domain] = true;
		}
	}
	
	/**
	 * Initialize all locales for the exact domain
	 * 
	 * @param  string $locale
	 * @param  string $domain
	 * @throws ParseError
	 */
	protected function initManyLocales($locale, $domain) {
		$source = $this->getCatalogContent($locale, $domain);
		
		if ($source !== false) {
			$locales = parse_ini_string($source, true);
			
			if ($locales === false) {
				throw new ParseError('Error has occurred while parsing INI file for the domain: "' . $domain . '"');
			}
			
			foreach ($locales as $locale => $catalog) {
				$this->catalogs = array_replace_recursive($this->catalogs, [
					$locale => [
						$domain => $catalog
					]
				]);
			}
		}

		if (! isset($this->initializedCatalogs[$locale])) {
			$this->initializedCatalogs[$locale] = [];
		}

		foreach (array_keys($locales) as $locale) {
			$this->initializedCatalogs[$locale][$domain] = true;
		}
	}
	
}