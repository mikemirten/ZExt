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

use ZExt\Components\OptionsTrait;
use Traversable;

use ZExt\Translator\Resource\Exceptions\InvalidParameter;
use ZExt\Translator\Resource\Exceptions\ReadError;
use ZExt\Translator\Resource\Exceptions\NoPath;

/**
 * Translator files-based resource abstract class
 * 
 * @category   ZExt
 * @package    Translator
 * @subpackage Resource
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class FilesBasedAbstract extends ResourceAbstract {
	
	use OptionsTrait;
	
	const TPL_LOCALE = 'locale';
	const TPL_DOMAIN = 'domain';
	
	/**
	 * Base path of the translation catalogs
	 *
	 * @var string
	 */
	protected $basePath;
	
	/**
	 * Parts of path template
	 *
	 * @var array
	 */
	protected $pathTemplate = [self::TPL_LOCALE];
	
	/**
	 * Parts of filename template
	 *
	 * @var array
	 */
	protected $filenameTemplate = [self::TPL_DOMAIN];
	
	/**
	 * Filename parts separator
	 *
	 * @var string
	 */
	protected $filenameSeparator = '.';
	
	/**
	 * Filename extension
	 *
	 * @var string
	 */
	protected $filenameExtension;
	
	/**
	 * Path to the last file requested
	 *
	 * @var string 
	 */
	protected $lastFilePath;
	
	/**
	 * Constructor
	 * 
	 * @param string $basePath Base path of the translations' catalogs | Options as an array or a traversable implementation
	 */
	public function __construct($basePath = null) {
		if ($basePath !== null) {
			if (is_array($basePath) || $basePath instanceof Traversable) {
				$this->setOptions($basePath);
			} else {
				$this->setBasePath($basePath);
			}
		}
	}
	
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
			$this->initCatalogsForSingleLocale($locale, $domain);
			return $this->getSingleCatalog($locale, $domain);
		}
		
		if (is_array($locale)) {
			$this->initCatalogsForManyLocales($locale, $domain);
			return $this->getManyCatalogs($locale, $domain);
		}
		
		throw new InvalidParameter('Locale must be a string or an array');
	}
	
	/**
	 * Get the path to the last file
	 * 
	 * @return string
	 */
	public function getLastFilePath() {
		return $this->lastFilePath;
	}
	
	/**
	 * Initialize the catalog for the exact locale and exact domain
	 * 
	 * @param  string $locale
	 * @param  string $domain
	 * @throws InvalidParameter
	 */
	protected function initCatalogsForSingleLocale($locale, $domain) {
		if (is_string($domain)) {
			$this->initCatalog($locale, $domain);
			return;
		}
			
		if (is_array($domain)) {
			$this->initCatalogForManyDomains($locale, $domain);
			return;
		}
		
		throw new InvalidParameter('Domain must be a string or an array');
	}
	
	/**
	 * Initialize the catalog for the many of the locales
	 * 
	 * @param type $locales
	 * @param type $domain
	 */
	protected function initCatalogsForManyLocales($locales, $domain) {
		foreach ($locales as $locale) {
			$this->initCatalogsForSingleLocale($locale, $domain);
		}
	}
	
	/**
	 * Initialize the catalog for the exact locale and many of the domains
	 * 
	 * @param type $locale
	 * @param type $domains
	 */
	protected function initCatalogForManyDomains($locale, $domains) {
		foreach ($domains as $domain) {
			$this->initCatalog($locale, $domain);
		}
	}
	
	/**
	 * Initialize the catalog for the locale and domain
	 * 
	 * @param string $locale
	 * @param string $domain
	 */
	abstract protected function initCatalog($locale, $domain);
	
	/**
	 * Set the path template (overrides the current one)
	 * 
	 * @param array $template
	 */
	public function setPathTemplate(array $template) {
		$this->resetPathTemplate();
		$this->addPathTemplateParts($template);
		
		return $this;
	}
	
	/**
	 * Add the parts of path template.
	 * Use TPL_* constants
	 * 
	 * @param  array $part
	 * @return FilesBasedAbstract
	 */
	public function addPathTemplateParts(array $parts) {
		foreach ($parts as $part) {
			$this->addPathTemplatePart($part);
		}
			
		return $this;
	}
	
	/**
	 * Add the part of path template.
	 * Use TPL_* constants
	 * 
	 * @param  string $part
	 * @return FilesBasedAbstract
	 */
	public function addPathTemplatePart($part) {
		if (! in_array($part, [
			self::TPL_DOMAIN,
			self::TPL_LOCALE
		], true)) {
			throw new InvalidParameter('Unknown part of the path template definition was given');
		}
		
		$this->pathTemplate[] = $part;
		
		return $this;
	}
	
	/**
	 * Reset the path template definition
	 * 
	 * @return FilesBasedAbstract
	 */
	public function resetPathTemplate() {
		$this->pathTemplate = [];
		
		return $this;
	}
	
	/**
	 * Get the path template definition
	 * 
	 * @return array
	 */
	public function getPathTemplate() {
		$this->pathTemplate;
	}
	
	/**
	 * Set the filename template (overrides the current one)
	 * 
	 * @param array $template
	 */
	public function setFilenameTemplate(array $template) {
		$this->resetFilenameTemplate();
		$this->addFilenameTemplateParts($template);
		
		return $this;
	}
	
	/**
	 * Add the parts of filename template.
	 * Use TPL_* constants
	 * 
	 * @param  array $part
	 * @return FilesBasedAbstract
	 */
	public function addFilenameTemplateParts(array $parts) {
		foreach ($parts as $part) {
			$this->addFilenameTemplatePart($part);
		}
			
		return $this;
	}
	
	/**
	 * Add the part of filename template.
	 * Use TPL_* constants
	 * 
	 * @param  string $part
	 * @return FilesBasedAbstract
	 */
	public function addFilenameTemplatePart($part) {
		if (! in_array($part, [
			self::TPL_DOMAIN,
			self::TPL_LOCALE
		], true)) {
			throw new InvalidParameter('Unknown part of the filename template definition was given');
		}
		
		$this->filenameTemplate[] = $part;
		
		return $this;
	}
	
	/**
	 * Reset the filename template definition
	 * 
	 * @return FilesBasedAbstract
	 */
	public function resetFilenameTemplate() {
		$this->filenameTemplate = [];
		
		return $this;
	}
	
	/**
	 * Get the filename template definition
	 * 
	 * @return array
	 */
	public function getFilenameTemplate() {
		return $this->filenameTemplate;
	}
	
	/**
	 * Set the base path
	 * 
	 * @param  string $path
	 * @return FilesBasedAbstract
	 */
	public function setBasePath($path) {
		$path = realpath($path);
		
		if ($path === false) {
			throw new NoPath('Path does not exists or is not accessible');
		}
		
		$this->basePath = rtrim($path, DIRECTORY_SEPARATOR);
		
		return $this;
	}
	
	/**
	 * Get the base path
	 * 
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}
	
	/**
	 * Set the separator of a filename parts
	 * 
	 * @param  string $separator
	 * @return FilesBasedAbstract
	 */
	public function setFilenamePartsSeparator($separator) {
		$this->filenameSeparator = (string) $separator;
		
		return $this;
	}
	
	/**
	 * Get the separator of a filename parts
	 * 
	 * @return string
	 */
	public function getFilenamePartsSeparator() {
		return $this->filenameSeparator;
	}
	
	/**
	 * Set the filename extension
	 * 
	 * @param  string $extension
	 * @return FilesBasedAbstract
	 */
	public function setFileExtension($extension) {
		$this->filenameExtension = (string) $extension;
		
		return $this;
	}
	
	/**
	 * Get the filename extension
	 * 
	 * @return string
	 */
	public function getFileExtension() {
		return $this->filenameExtension;
	}
	
	/**
	 * Get the catalog content
	 * 
	 * @param  string $locale
	 * @param  string $domain
	 * 
	 * @return string | bool
	 * @throws ReadError
	 * @throws NoPath
	 */
	protected function getCatalogContent($locale, $domain) {
		$path = $this->getCatalogPath($locale, $domain);
		
		if (! is_file($path)) {
			return false;
		}
		
		if (! is_readable($path)) {
			throw new ReadError('Catalog file: "' . $path . '" exists but can\'t be read');
		}
		
		$result = file_get_contents($path);
		
		if ($result === false) {
			throw new ReadError('Error has occurred while reading the file: "' . $path . '"');
		}
		
		$this->lastFilePath = $path;
		
		return $result;
	}
	
	/**
	 * Get the catalog path for the locale and domain
	 * 
	 * @param  string $locale
	 * @param  string $domain
	 * @return string
	 * @throws NoPath
	 */
	protected function getCatalogPath($locale, $domain) {
		if ($this->basePath === null) {
			throw new NoPath('Base path definition required');
		}
		
		// Path to the file resolve
		$pathParts = [$this->basePath];
		
		foreach ($this->pathTemplate as $part) {
			if ($part === self::TPL_LOCALE) {
				$pathParts[] = $locale;
				continue;
			}
			
			if ($part === self::TPL_DOMAIN) {
				$pathParts[] = $domain;
			}
		}
		
		// Filename resolve
		$flenameParts = [];
		
		foreach ($this->filenameTemplate as $part) {
			if ($part === self::TPL_LOCALE) {
				$flenameParts[] = $locale;
				continue;
			}
			
			if ($part === self::TPL_DOMAIN) {
				$flenameParts[] = $domain;
			}
		}
		
		$filename = implode($this->filenameSeparator, $flenameParts);
		
		// Extension resolve
		if ($this->filenameExtension !== null) {
			$filename .= '.' . $this->filenameExtension;
		}
		
		$pathParts[] = $filename;
		
		return implode(DIRECTORY_SEPARATOR, $pathParts);
	}
	
}