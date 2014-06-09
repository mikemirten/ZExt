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

namespace ZExt\Debug\Renderers;

use SplQueue, Traversable;

use ZExt\Debug\Infosets\Infoset,
    ZExt\Debug\Infosets\InfosetTable;

use ZExt\Helper\HelpersBrokerAwareInterface,
    ZExt\Helper\HelpersBrokerAwareTrait;

use ZExt\Debug\Renderers\Exceptions\InvalidContent;

use ZExt\Version\Version;

/**
 * CLI content renderer
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage Renderer
 * @author     Mike.Mirten
 * @version    0.1
 */
class Cli implements RendererInterface, HelpersBrokerAwareInterface {
	
	use HelpersBrokerAwareTrait;
	
	/**
	 * Information stack
	 *
	 * @var Infoset[] 
	 */
	protected $info;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->info = new SplQueue();
	}
	
	/**
	 * Set the information set
	 * 
	 * @param InfoSet $infoset
	 */
	public function addInfo(Infoset $infoset) {
		if ($infoset->getLevel() > Infoset::LEVEL_ALERT) {
			$this->info[] = $infoset;
		} else {
			$this->info->unshift($infoset);
		}
	}
	
	/**
	 * Get CLI parameters
	 * 
	 * @return array
	 */
	protected function getCliParams() {
		if (empty($_SERVER['argv'])) {
			return [];
		}
		
		$params = preg_grep('/^\-\-zdebug:/', $_SERVER['argv']);
		
		if (empty($params)) {
			return [];
		}
		
		return array_map(function($in) {
			return str_replace('--zdebug:', '', $in);
		}, $params);
	}
	
	/**
	 * Render the information
	 * 
	 * @return mixed
	 */
	public function render() {
		$rows = [];
		
		$rows[] = $this->color('ZExt framework ' . Version::getVersion(), 'yellow') . ' | Debug info:' . PHP_EOL;

		$brief = array_search('brief', $this->getCliParams());
		
		foreach ($this->info as $info) {
			$row = ' ' . $info->getName();
			
			if ($info->hasTitle()) {
				$row .= ': ' . $this->bbCodesHandle($info->getTitle());
			}
			
			$rows[] = $this->colorLine($row, null, 'dark_gray') . PHP_EOL;
			
			if ($brief || $info->isEmpty()) {
				continue;
			}
			
			$rows[] = $this->createContent($info) . PHP_EOL;
		}
		
		return implode(PHP_EOL, $rows) . PHP_EOL;
	}
	
	/**
	 * Create the content of the panel
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createContent(Infoset $info) {
		switch ($info->getContentType()) {
			case Infoset::TYPE_LIST:
				return $this->createList($info);
			
			case Infoset::TYPE_TABLE:
				return $this->createTable($info);
			
			case Infoset::TYPE_MULTI:
				$content = [];
				
				foreach ($info as $part) {
					$content[] = $this->createContent($part);
				}
				
				return implode(PHP_EOL . PHP_EOL, $content);
				
			case Infoset::TYPE_TEXT:
				$content = iterator_to_array($info);
				
				return implode(PHP_EOL, $content);
				
			case Infoset::TYPE_DESCLIST:
				return $this->createDescriptionList($info);
			
			case Infoset::TYPE_DUMP:
				return;
			
			case Infoset::TYPE_TOPOLOGY:
				return;
		}
		
		throw new InvalidContent('Unknown content type: "' . $info->getContentType() . '"');
	}
	
	/**
	 * Create the list
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createList(Infoset $info) {
		$list = [];
		
		if ($info->hasTitle()) {
			$list[] = $info->getTitle() . ':';
		}
		
		foreach ($info as $content) {
			if ($content instanceof Infoset) {
				$content = $this->createContent($content);
			}
			
			if ($info->isContentBbCodes()) {
				$content = $this->bbCodesHandle($content);
			}
			
			$list[] = ' - ' . $content;
		}

		return implode(PHP_EOL, $list);
	}
	
	/**
	 * Create the table
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createTable(Infoset $info) {
		$table = [];
		
		if ($info instanceof InfosetTable) {
			if (($headRow = $info->getHeadContent()) !== null) {
				$table[] = $headRow;
			}
		}
		
		foreach ($info as $row) {
			if (! is_array($row) && ! $row instanceof Traversable) {
				throw new InvalidContent('Table row content must be an array or a Traversable implementation');
			}
			
			$cols = [];
			
			foreach ($row as $content) {
				if ($content instanceof Infoset) {
					$content = $this->createContent($content);
				}
				
				if ($info->isContentBbCodes()) {
					$content = $this->bbCodesHandle($content);
				}
				
				$cols[] = $content;
			}
			
//			if ($row instanceof Row && $row->isMarked()) {
//				$cols['_class_'] = $row->getMark();
//			}
			
			$table[] = $cols;
		}
		
		$title = $info->getTitle();
		
		return $this->table($table, $title ?: $title);
	}
	
	/**
	 * Create the description list
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createDescriptionList(Infoset $info) {
		$list = [];
		
		$list[] = $info->getTitle() . ':' . PHP_EOL;
		
		foreach ($info as $content) {
			if (! is_array($content) || ! isset($content[0], $content[1])) {
				throw new InvalidContent('Description list definition must be an array and content offsets: "0" and "1"');
			}
				
			list($term, $desc) = $content;
			
			if ($desc instanceof Infoset) {
				$desc = $this->createContent($desc);
			}
			
			if ($info->isContentBbCodes()) {
				$term = $this->bbCodesHandle($term);
				$desc = $this->bbCodesHandle($desc);
			}
			
			$list[] = $term;
			$list[] = $this->color($desc, 'light_gray') . PHP_EOL;
		}
		
		return implode(PHP_EOL, $list);
	}
	
	/**
	 * Handle the content's BB codes
	 * 
	 * @param  string $content
	 * @return string
	 */
	protected function bbCodesHandle($content) {
		return preg_replace_callback('~\[([a-z]+)\](.*?)\[/\1\]~', function($match) use($bbCodes) {
			array_shift($match);
			list($tag, $value) = $match;
			
			if (preg_match('~\[[a-z]+\]~', $value)) {
				$value = $this->bbCodesHandle($value);
			}
			
			if ($tag === 'keyword') {
				return $this->color($value, 'cyan');
			}
			
			if ($tag === 'alert') {
				return $this->color($value, 'red');
			}
			
			if ($tag === 'success') {
				return $this->color($value, 'green');
			}
			
			if ($tag === 'warning') {
				return $this->color($value, 'yellow');
			}
			
			if ($tag === 'info') {
				return $this->color($value, 'blue');
			}
			
			return $value;
		}, $content);
	}
	
}