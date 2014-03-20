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

/**
 * Translator resource abstract class
 * 
 * @category   ZExt
 * @package    Translator
 * @subpackage Resource
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class ResourceAbstract implements ResourceInterface {
	
	/**
	 * Translation's catalogs
	 * [locale][domain][id]
	 *
	 * @var array 
	 */
	protected $catalogs = [];
	
	/**
	 * Get the translation's catalog for the locale(s) [and domain(s)]
	 * 
	 * @param  string | array $locale
	 * @param  string | array $domain
	 * @return array
	 * @throws InvalidParameter
	 */
	public function getCatalogs($locale = null, $domain = null) {
		if ($locale === null && $domain === null) {
			return $this->catalogs;
		}
		
		if (is_string($locale)) {
			return $this->getSingleCatalog($locale, $domain);
		}
		
		if (is_array($locale)) {
			return $this->getManyCatalogs($locale, $domain);
		}
		
		throw new InvalidParameter('Locale must be a string or an array');
	}
	
	/**
	 * Get the translation's catalog for the locale [and domain(s)]
	 * 
	 * @param  string         $locale
	 * @param  string | array $domain
	 * @return array
	 * @throws InvalidParameter
	 */
	protected function getSingleCatalog($locale, $domain = null) {
		if (! isset($this->catalogs[$locale])) {
				 return [];
		}

		if ($domain === null) {
			return [$locale => $this->catalogs[$locale]];
		}

		if (is_string($domain)) {
			if (! isset($this->catalogs[$locale][$domain])) {
				return [];
			}

			return [
				$locale => [
					$domain => $this->catalogs[$locale][$domain]
				]
			];
		}

		if (is_array($domain)) {
			return [$locale => array_intersect_key(
				$this->catalogs[$locale],
				array_flip($domain))
			];
		}
		
		throw new InvalidParameter('Domain must be a string or an array');
	}
	
	/**
	 * Get the many of translation's catalogs for the many locales [and domain(s)]
	 * 
	 * @param  array          $locale
	 * @param  string | array $domain
	 * @return array
	 * @throws InvalidParameter
	 */
	protected function getManyCatalogs(array $locale, $domain = null) {
		if ($domain === null) {
			return array_intersect_key(
				$this->catalogs,
				array_flip($locale)
			);
		}

		if (is_string($domain)) {
			$result = [];

			foreach ($locale as $part) {
				if (isset($this->catalogs[$part][$domain])) {
					$result[$part][$domain] = $this->catalogs[$part][$domain];
				}
			}

			return $result;
		}

		if (is_array($domain)) {
			$result = [];

			foreach ($locale as $part) {
				if (isset($this->catalogs[$part])) {
					$result[$part] = array_intersect_key(
						$this->catalogs[$part],
						array_flip($domain)
					);
				}
			}

			return $result;
		}

		throw new InvalidParameter('Domain must be a string or an array');
	}
	
}