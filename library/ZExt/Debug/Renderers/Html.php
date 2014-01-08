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

use ZExt\Di\LocatorInterface,
    ZExt\Di\Container;

use ZExt\Debug\Infosets\Infoset,
    ZExt\Debug\Infosets\InfosetTable,
    ZExt\Debug\Infosets\Table\Row;

use ZExt\Debug\Assets\Icons;

use ZExt\Debug\Exceptions\InvalidPath,
    ZExt\Debug\Renderers\Exceptions\InvalidContent;

use ZExt\Html\Tag,
    ZExt\Html\Script,
    ZExt\Html\DescriptionList,
    ZExt\Html\ListUnordered,
    ZExt\Html\ListElement,
    ZExt\Html\Table;

use ZExt\Dump\Html as Dump;

use SplQueue, Traversable;

/**
 * HTML content renderer
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage Renderer
 * @author     Mike.Mirten
 * @version    1.0
 */
class Html implements RendererInterface {
	
	/**
	 * Information stack
	 *
	 * @var Infoset[] 
	 */
	protected $info;
	
	/**
	 * The assets path
	 *
	 * @var string
	 */
	protected $assetsPath;
	
	/**
	 * The tags instances' locator
	 *
	 * @var LocatorInterface
	 */
	protected $tagsLocator;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->info = new SplQueue();
	}
	
	/**
	 * Set the assets path
	 * 
	 * @param  string $path
	 * @throws InvalidPath
	 */
	public function setAssetsPath($path) {
		$path = realpath(rtrim($path, DIRECTORY_SEPARATOR));
		
		if ($path === false || ! is_readable($path)) {
			throw new InvalidPath('Path to the assets must exists and be readable');
		}
		
		$this->assetsPath = $path;
	}
	
	/**
	 * Add the information set
	 * 
	 * @param Infoset $infoset
	 */
	public function addInfo(Infoset $infoset) {
		if ($infoset->getLevel() > Infoset::LEVEL_ALERT) {
			$this->info[] = $infoset;
		} else {
			$this->info->unshift($infoset);
		}
	}
	
	/**
	 * Render the information
	 * 
	 * @return mixed
	 */
	public function render() {
		$tabsList = new ListUnordered();
		$tabsList->setSeparator('');
		$tabsList->addClass('debug-bar');
		$tabsList->id = 'debug-elements';
		
		$panels = '';
		
		foreach ($this->info as $info) {
			$tabElement = $this->createTab($info);
			
			if (! $info->isEmpty()) {
				$id = 'debug-panel-' . substr(md5(rand(0, 1000) . $info->getName()), 24);
			
				$tabElement->addClass('withpanel clickable');
				$tabElement->setAttr('data-panel-id', $id);
				
				$panelTag = $this->createPanel($info);
				$panelTag->id = $id;
				
				$panels .= $panelTag->render();
			}
			
			$tabsList->addElement($tabElement);
		}
		
		$styleTag  = new Tag('style', $this->getAsset('bar.css'));
		$scriptTag = new Script($this->getAsset('bar.js'));
		
		$wrapperTag = new Tag('div', $tabsList->render(), 'debug-bar-wrapper');
		$debugTag   = new Tag('div', $panels . $wrapperTag->render(), 'debug-main');
		
		return $styleTag->render() . $scriptTag->render() . $debugTag->render();
	}
	
	/**
	 * Create the tab element
	 * 
	 * @param  Infoset $info
	 * @return ListElement
	 */
	protected function createTab(Infoset $info) {
		$title   = $this->specialCharsHandle($info->getTitle());
		$title   = $this->bbCodesHandle($title);
		$infoTag = new Tag('span', $title, 'debug-info-holder');

		$tabElement = new ListElement($infoTag->render());
		$tabElement->addClass('debug-tab');
		
		$titleAttr = $info->getName() . ' :: ' . $info->getTitle();
		$titleAttr = $this->specialCharsHandle($titleAttr);
		$titleAttr = $this->stripBbCodes($titleAttr);
		$tabElement->title = $titleAttr;
		
		if ($info->getLevel() <= Infoset::LEVEL_ALERT) {
			$tabElement->addClass('alert');
		}
		
		if ($info->hasIcon()) {
			$this->handleIcon($info, $tabElement);
		}
		
		return $tabElement;
	}
	
	/**
	 * Handle the icon
	 * 
	 * @param  Infoset $info
	 * @param  Tag     $tabElement
	 */
	protected function handleIcon(Infoset $info, Tag $tabElement) {
		switch ($info->getIconType()) {
			case Infoset::ICON_ASSET:
				$icon = Icons::getIcon($info->getIconSource());
				$tabElement->addClass('hasicon');
				$tabElement->addStyle('background-image', "url($icon)");
				return;
			
			case Infoset::ICON_BASE64:
				$icon = $info->getIconSource();
				$tabElement->addClass('hasicon');
				$tabElement->addStyle('background-image', "url($icon)");
				return;
		}
	}
	
	/**
	 * Create the panel
	 * 
	 * @param  Infoset $info
	 * @return Tag
	 */
	protected function createPanel(Infoset $info) {
		$content = $this->createContent($info);
		
		$title = new Tag('h4', $info->getName(), 'debug-bar-wrapper');
		$panel = new Tag('div', $content, 'debug-panel');
		
		return new Tag('div', $title->render() . $panel->render(), 'debug-panel-wrapper');
	}
	
	/**
	 * Create the content of the panel
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createContent(Infoset $info) {
		$content = '';
		
		switch ($info->getContentType()) {
			case Infoset::TYPE_LIST:
				$content .= $this->createList($info);
				break;
			
			case Infoset::TYPE_TABLE:
				$content .= $this->createTable($info);
				break;
			
			case Infoset::TYPE_MULTI:
				foreach ($info as $part) {
					if (($title = $part->getTitle()) !== null) {
						$content .= $this->getTagsLocator()->get('title')->render($title);
					}
				
					$content .= $this->createContent($part);
				}
				break;
				
			case Infoset::TYPE_TEXT:
				foreach ($info as $part) {
					$content .= $this->specialCharsHandle($part);
				}
				break;
				
			case Infoset::TYPE_DESCLIST:
				$content .= $this->createDescriptionList($info);
				break;
			
			case Infoset::TYPE_DUMP:
				$content .= $this->createDump($info);
				break;
			
			default:
				throw new InvalidContent('Unknown content type: "' . $info->getContentType() . '"');
		}
		
		return $content;
	}
	
	/**
	 * Create the list
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createList(Infoset $info) {
		$list = new ListUnordered();
		$list->addClass('content-list');
		
		foreach ($info as $content) {
			if ($content instanceof Infoset) {
				$content = $this->createContent($content);
			}
			
			$content = $this->specialCharsHandle($content);
			
			if ($info->isContentBbCodes()) {
				$content = $this->bbCodesHandle($content);
			}
			
			$list[] = $content;
		}
		
		return $list->render();
	}
	
		/**
	 * Create the description list
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createDescriptionList(Infoset $info) {
		$list = new DescriptionList();
		$list->addClass('content-desclist');
		
		foreach ($info as $content) {
			if (! is_array($content) || ! isset($content[0], $content[1])) {
				throw new InvalidContent('Description list definition must be an array and content offsets: "0" and "1"');
			}
				
			list($term, $desc) = $content;
			
			if ($desc instanceof Infoset) {
				$desc = $this->createContent($desc);
			}
			
			$term = $this->specialCharsHandle($term);
			$desc = $this->specialCharsHandle($desc);
			
			if ($info->isContentBbCodes()) {
				$term = $this->bbCodesHandle($term);
				$desc = $this->bbCodesHandle($desc);
			}
			
			$list[] = [$term ,$desc];
		}
		
		return $list->render();
	}
	
	/**
	 * Create the table
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createTable(Infoset $info) {
		$table = new Table();
		$table->addClass('content-table');
		
		if ($info instanceof InfosetTable) {
			if (($widths = $info->getColsWidths()) !== null) {
				$table->getColgroup()->addElements($widths);
			}
			
			if (($headRow = $info->getHeadContent()) !== null) {
				$table->getHead()->addElement($headRow);
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
				
				$content = $this->specialCharsHandle($content);
				
				if ($info->isContentBbCodes()) {
					$content = $this->bbCodesHandle($content);
				}
				
				$cols[] = $content;
			}
			
			if ($row instanceof Row && $row->isMarked()) {
				$cols['_class_'] = $row->getMark();
			}
			
			$table[] = $cols;
		}
		
		return $table->render();
	}

	/**
	 * Create the dump of the exception
	 * 
	 * @param  Infoset $info
	 * @return string
	 */
	protected function createDump(Infoset $info) {
		$content = '';
		
		foreach ($info->getContent() as $part) {
			$content .= Dump::getDump($part);
		}
		
		return $content;
	}
	
	/**
	 * Get the asset
	 * 
	 * @param  string $name
	 * @return string
	 * @throws InvalidPath
	 */
	protected function getAsset($name) {
		$path  = $this->assetsPath . DIRECTORY_SEPARATOR . $name;
		$asset = file_get_contents($path);
		
		if ($asset === false) {
			throw new InvalidPath('Unable to get asset "' . $name . '" by path "' . $path . '"');
		}
		
		return $asset;
	}
	
	/**
	 * Special chars handle
	 * 
	 * @param  string $content
	 * @return string
	 */
	protected function specialCharsHandle($content) {
		return nl2br(htmlspecialchars($content));
	}
	
	/**
	 * Strip a BB codes from the content
	 * 
	 * @param  string $content
	 * @return string
	 */
	protected function stripBbCodes($content) {
		return preg_replace('~\[/?[a-z]+\]~', '', $content);
	}
	
	/**
	 * Handle the content's BB codes
	 * 
	 * @param  string $content
	 * @return string
	 */
	protected function bbCodesHandle($content) {
		$bbCodes = $this->getTagsLocator();
		
		return preg_replace_callback('~\[([a-z]+)\](.*)\[/\1\]~', function($match) use($bbCodes) {
			array_shift($match);
			list($tag, $value) = $match;
			
			if (preg_match('~\[[a-z]+\]~', $value)) {
				$value = $this->bbCodesHandle($value);
			}
			
			if ($bbCodes->has($tag)) {
				return $bbCodes->get($tag)->render($value);
			}
			
			return $value;
		}, $content);
	}
	
	/**
	 * Get the tags instances' locator
	 *
	 * @return LocatorInterface
	 */
	protected function getTagsLocator() {
		if ($this->tagsLocator === null) {
			$di = new Container();
			
			$di->keyword = function() {
				return new Tag('span', null, 'content-keyword');
			};
			
			$di->alert = function() {
				return new Tag('span', null, 'content-alert');
			};
			
			$di->warning = function() {
				return new Tag('span', null, 'content-warning');
			};
			
			$di->success = function() {
				return new Tag('span', null, 'content-success');
			};
			
			$di->info = function() {
				return new Tag('span', null, 'content-info');
			};
			
			$di->special = function() {
				return new Tag('span', null, 'content-special');
			};
			
			$di->strong = function() {
				return new Tag('strong');
			};
			
			$di->title = function() {
				return new Tag('h4', null, 'content-title');
			};
			
			$this->tagsLocator = $di;
		}
		
		return $this->tagsLocator;
	}

}