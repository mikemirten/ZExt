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

namespace ZExt\Highlighter\Languages;

use SplStack;

/**
 * Php highlighter
 * 
 * @category   ZExt
 * @package    Highlighter
 * @subpackage Languages
 * @author     Mike.Mirten
 * @version    1.0beta
 */
class Php implements LanguageInterface {

	// States
	const STATE_RUN = 0;
	const STATE_VAR = 1;
	const STATE_STR = 2;
	const STATE_STS = 3;
	const STATE_NUM = 4;
	const STATE_CMT = 5;
	const STATE_CML = 6;
	const STATE_CME = 7;
	const STATE_DOC = 8;
	const STATE_PNT = 9;
	const STATE_KEY = 10;
	
	static protected $keywords = [
		'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch',
		'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do',
		'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach',
		'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for',
		'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include',
		'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list',
		'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 
		'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait',
		'try', 'unset', 'use', 'var', 'while', 'xor', 'true', 'false', 'null',
		'self', 'bool', 'string', 'int', 'object', 'double', 'float', 'real', 'parent'
	];
	
	/**
	 * Highlight the single line
	 * 
	 * @param  string $source
	 * @return string
	 */
	public function highlightLine($source) {
		$source = htmlspecialchars($source);
		$length = mb_strlen($source, 'UTF-8');
		$result = '';
		$state  = self::STATE_RUN;
		$stack  = new SplStack();
		$tags   = new SplStack();
		
		for ($pos = 0; $pos < $length; ++ $pos) {
			$char = $source[$pos];
			
			// State: run
			if ($state === self::STATE_RUN) {
				// Whitespace
				if ($char === ' ') {
					$result .= '&nbsp;';
					continue;
				}

				// Tabulation
				if ($char === "\t") {
					$result .= '&nbsp;&nbsp;&nbsp;&nbsp;';
					continue;
				}

				// Variable
				if ($char === '$') {
					$result .= '<span class="zDumpVariable">';
					$tags[]  = '</span>';
					$result .= $char;
					$stack[] = self::STATE_RUN;
					$state   = self::STATE_VAR;
					continue;
				}
				
				// String
				if ($char === '\'') {
					$result .= '<span class="zDumpString">';
					$tags[]  = '</span>';
					$result .= $char;
					$stack[] = self::STATE_RUN;
					$state   = self::STATE_STR;
					continue;
				}
				
				// String with substitution
				if ($char === '"') {
					$result .= '<span class="zDumpString">';
					$tags[]  = '</span>';
					$result .= $char;
					$stack[] = self::STATE_RUN;
					$state   = self::STATE_STS;
					continue;
				}
				
				// Numeric
				if (preg_match('/[0-9]/', $char)) {
					$result .= '<span class="zDumpInteger">';
					$tags[]  = '</span>';
					$result .= $char;
					$stack[] = self::STATE_RUN;
					$state   = self::STATE_NUM;
					continue;
				}
				
				// Keyword
				if (preg_match('/[a-z]/i', $char)) {
					$keyword = $char;
					$state   = self::STATE_KEY;
					continue;
				}
				
				// Point
				if ($char === '.') {
					$state = self::STATE_PNT;
					continue;
				}
				
				// Comment start
				if ($char === '/') {
					$stack[] = self::STATE_RUN;
					$state   = self::STATE_CMT;
					continue;
				}
				
				// Comment special
				if ($char === '*' && trim(substr($source, 0, $pos)) === '') {
					$result .= '<span class="zDumpComment">*';
					$tags[]  = '</span>';
					$stack[] = self::STATE_RUN;
					$state   = self::STATE_CME;
					continue;
				}
			}
			
			// State: Variable
			if ($state === self::STATE_VAR) {
				if (preg_match('/[a-z0-9_]/i', $char)) {
					$result .= $char;
					continue;
				}
				
				-- $pos;
				$result .= $tags->pop();
				$state   = $stack->pop();
				continue;
			}
			
			// State: String
			if ($state === self::STATE_STR) {
				$result .= $char;
				
				if ($char === '\'' && $source[$pos - 1] !== '\\') {
					$result .= $tags->pop();
					$state   = $stack->pop();
				}
				
				continue;
			}
			
			// State: String with substitution
			if ($state === self::STATE_STS) {
				// Variable
				if ($char === '$') {
					$result .= '<span class="zDumpVariable">';
					$tags[]  = '</span>';
					$result .= $char;
					$stack[] = self::STATE_STS;
					$state   = self::STATE_VAR;
					continue;
				}
				
				$result .= $char;
				
				if ($char === '"' && $source[$pos - 1] !== '\\') {
					$result .= $tags->pop();
					$state   = $stack->pop();
				}
				
				continue;
			}
			
			// State: Point
			if ($state === self::STATE_PNT) {
				if (preg_match('/[0-9]/', $char)) {
					$result .= '<span class="zDumpInteger">.';
					$tags[]  = '</span>';
					$result .= $char;
					$stack[] = self::STATE_RUN;
					$state   = self::STATE_NUM;
					continue;
				}
				
				-- $pos;
				$state   = self::STATE_RUN;
				$result .= '.';
				continue;
			}
			
			// State: Numeric
			if ($state === self::STATE_NUM) {
				if (preg_match('/[0-9\.]/', $char)) {
					$result .= $char;
					continue;
				}
				
				-- $pos;
				$result .= $tags->pop();
				$state   = $stack->pop();
				continue;
			}
			
			// State: Comment start
			if ($state === self::STATE_CMT) {
				if ($char === '/') {
					$result .= '<span class="zDumpComment">//';
					$tags[]  = '</span>';
					$state   = self::STATE_CML;
					continue;
				}
				
				if ($char === '*') {
					$result .= '<span class="zDumpComment">/*';
					$tags[]  = '</span>';
					$state   = self::STATE_CME;
					continue;
				}
				
				-- $pos;
				$state   = $stack->pop();
				$result .= '/';
				$result .= $char;
				continue;
			}
			
			// State: Comment run
			if ($state === self::STATE_CME) {
				// Whitespace
				if ($char === ' ') {
					$result .= '&nbsp;';
					continue;
				}

				// Tabulation
				if ($char === "\t") {
					$result .= '&nbsp;&nbsp;&nbsp;&nbsp;';
					continue;
				}
				
				// End
				if ($char === '/' && $source[$pos - 1] === '*') {
					$state   = $stack->pop();
					$result .= '/';
					$result .= $tags->pop();
					continue;
				}
				
				// Variable
				if ($char === '$') {
					$result .= '<span class="zDumpVariable">';
					$tags[]  = '</span>';
					$result .= $char;
					$stack[] = self::STATE_CME;
					$state   = self::STATE_VAR;
					continue;
				}
				
				// Phpdoc
				if ($char === '@') {
					$result .= '<strong>';
					$tags[]  = '</strong>';
					$result .= $char;
					$stack[] = self::STATE_CME;
					$state   = self::STATE_DOC;
					continue;
				}
				
				$result .= $char;
				continue;
			}
			
			// State: Phpdoc
			if ($state === self::STATE_DOC) {
				if (preg_match('/[a-z\-]/i', $char)) {
					$result .= $char;
					continue;
				}
				
				-- $pos;
				$result .= $tags->pop();
				$state   = $stack->pop();
				continue;
			}
			
			// State: Keyword
			if ($state === self::STATE_KEY) {
				if (preg_match('/[a-z]/i', $char)) {
					$keyword .= $char;
					continue;
				}
				
				if (in_array($keyword, self::$keywords, true)) {
					$result .= '<span class="zDumpKeyword">' . $keyword . '</span>';
				} else {
					$result .= $keyword;
				}
				
				$result .= $char;
				$state   = self::STATE_RUN;
				continue;
			}
			
			$result .= $char;
		}
		
		// Unclosed tags
		if (! $tags->isEmpty()) {
			foreach ($tags as $tag) {
				$result .= $tag;
			}
		}
		
		return $result;
	}
	
}