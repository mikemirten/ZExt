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

namespace ZExt\I18n\Plugin;

use ZExt\Di\InitializerInterface;
use ZExt\Di\InitializerNamespace;

use ZExt\I18n\PluralChooserInterface;
use ZExt\I18n\PluralChooser;

use ZExt\I18n\Plugin\Exceptions\NoRequiredParam;

use Closure;

/**
 * BB-codes handler
 * 
 * @category   ZExt
 * @package    I18n
 * @subpackage Plugin
 * @author     Mike.Mirten
 * @version    1.0
 */
class BBCodes implements PluginInterface {
	
	/**
	 * Parameters' definition pattern
	 *
	 * @var string
	 */
	protected $paramsPattern = '{{%s}}';
	
	/**
	 * Compiled search regular expression
	 *
	 * @var string
	 */
	protected $paramExpression;
	
	/**
	 * Actions' definitions
	 *
	 * @var Closure[]
	 */
	protected $actions = [];
	
	/**
	 * Formatters' initializer instance
	 *
	 * @var InitializerInterface 
	 */
	protected $formattersInitializer;
	
	/**
	 * Plural chooser
	 *
	 * @var PluralChooserInterface
	 */
	protected $pluralChooser;
	
	/**
	 * Delimiter of a plural rule variants
	 *
	 * @var string
	 */
	protected $pluralRuleDelimiter = ',';
	
	/**
	 * Set the parameters' definition pattern
	 * 
	 * @param  string $pattern
	 * @return Translator
	 */
	public function setParamsPattern($pattern) {
		$this->paramsPattern   = (string) $pattern;
		$this->paramExpression = null;
		
		return $this;
	}
	
	/**
	 * Get the parameters' definition pattern
	 * 
	 * @param  string $pattern
	 * @return Translator
	 */
	public function getParamsPattern() {
		return $this->paramsPattern;
	}
	
	/**
	 * Handle the translation and/or parameters
	 * 
	 * @param  string       $locale
	 * @param  string       $translation
	 * @param  array | null $params
	 * @return string
	 */
	public function handle($locale, $translation, $params = null) {
		return preg_replace_callback('~\[([a-z]+)(?:=([a-z0-9_]+))?\](.*?)\[/\1\]~', function($match) use($locale, $params) {
			list($origin, $bbcode, $bbargument, $value) = $match;
			
			// Plural
			if ($bbcode === 'plural') {
				if (empty($bbargument)) {
					throw new NoRequiredParam('Name of the plural number parameter must be specified in the BBCode');
				}
				
				if (! isset($params[$bbargument])) {
					throw new NoRequiredParam('Required parameter "' . $bbargument . '" is absent');
				}
				
				$result = $this->choosePlural($params[$bbargument], $value, $locale);
				
				return $this->handle($locale, $result, $params);
			}
			
			// Recursive BBCodes
			if (preg_match('~\[[a-z]+(?:=[a-z0-9_]+)?\]~', $value)) {
				$value = $this->handle($locale, $value, $params);
			}
			
			// Try the BBCode action callback
			if (isset($this->actions[$bbcode])) {
				return $this->actions[$bbcode]($value, $bbcode, $origin);
			}
			
			// Try the BBCode formatter
			$formatter = $this->getFormattersInitializer()->initialize(ucfirst($bbcode));
			
			$formatterParams = [];

			if (isset($bbargument[0])) {
				$formatterParams['primary'] = $bbargument;
			}

			if (preg_match($this->getParamExpression(), $value, $valueMatch)) {
				$value = trim($valueMatch[1]);

				if ($params === null || ! isset($params[$value])) {
					throw new NoRequiredParam('Required parameter "' . $value . '" is absent');
				}

				$paramPrefix = $value . '.';

				foreach ($params as $paramKey => $paramValue) {
					if (strpos($paramKey, $paramPrefix) === 0) {
						$formatterParams[str_replace($paramPrefix, '', $paramKey)] = $paramValue;
					}
				}

				return $formatter->format($params[$value], $formatterParams, $locale);
			}

			return $formatter->format($value, $formatterParams, $locale);
		}, $translation);
	}
	
	/**
	 * Choose the plural
	 * 
	 * @param int    $number
	 * @param string $rule
	 * @param string $locale
	 */
	protected function choosePlural($number, $rule, $locale) {
		$variants = explode($this->pluralRuleDelimiter, $rule);
		$variants = array_map('trim', $variants);
		
		return $this->getPluralChooser()->choose($number, $variants, $locale);
	}
	
	/**
	 * Set the delimiter of a plural rule variants
	 * 
	 * @param  string $delimiter
	 * @return BBCodes
	 */
	public function setPluralRuleDelimiter($delimiter) {
		$this->pluralRuleDelimiter = (string) $delimiter;
		
		return $this;
	}
	
	/**
	 * Set the delimiter of a plural rule variants
	 * 
	 * @return string
	 */
	public function getPluralRuleDelimiter() {
		return $this->pluralRuleDelimiter;
	}
	
	/**
	 * Set the plural chooser
	 * 
	 * @param  PluralChooserInterface $chooser
	 * @return BBCodes
	 */
	public function setPluralChooser(PluralChooserInterface $chooser) {
		$this->pluralChooser = $chooser;
		
		return $this;
	}
	
	/**
	 * Get the plural chooser
	 * 
	 * @return PluralChooserInterface
	 */
	public function getPluralChooser() {
		if ($this->pluralChooser === null) {
			$this->pluralChooser = new PluralChooser();
		}
		
		return $this->pluralChooser;
	}
	
	/**
	 * Get search expression
	 * 
	 * @return string
	 */
	protected function getParamExpression() {
		if ($this->paramExpression === null) {
			$expr = sprintf(preg_quote($this->paramsPattern, '~'), '(.*?)');
			
			$this->paramExpression = '~' . $expr . '~';
		}
		
		return $this->paramExpression;
	}
	
	/**
	 * Set the callback action for the code
	 * 
	 * Callback will be called with the arguments:
	 * - string $content BBcode's content
	 * - string $code    BBCode
	 * - string $origin  Original string from translation definition
	 * 
	 * Callback must return string value
	 * 
	 * @param  string | array $code
	 * @param  Closure        $callback
	 * @return BBCodes
	 */
	public function setBBCodeCallback($code, Closure $callback) {
		if (is_array($code)) {
			foreach ($code as $part) {
				$this->actions[$part] = $callback;
			}
			
			return $this;
		}
		
		$this->actions[$code] = $callback;
		
		return $this;
	}
	
	/**
	 * Set the formatters' initializer
	 * 
	 * @param  InitializerInterface $initializer
	 * @return BBCodes
	 */
	public function setFormattersInitializer(InitializerInterface $initializer) {
		$this->formattersInitializer = $initializer;
		
		return $this;
	}
	
	/**
	 * Get the formatters' initializer
	 * 
	 * @return InitializerInterface
	 */
	public function getFormattersInitializer() {
		if ($this->formattersInitializer === null) {
			$this->formattersInitializer = new InitializerNamespace('ZExt\Formatter');
		}
		
		return $this->formattersInitializer;
	}

	/**
	 * Set the callback action for the code
	 * 
	 * @param string  $name
	 * @param Closure $value
	 */
	public function __set($name, $value) {
		$this->setBBCodeCallback($name, $value);
	}
	
}