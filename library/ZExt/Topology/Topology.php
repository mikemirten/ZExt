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

namespace ZExt\Topology;

use ZExt\Html\Tag;
use ZExt\Html\Table;

/**
 * Topology builder
 * 
 * @category   ZExt
 * @package    Topology
 * @subpackage Topology
 * @author     Mike.Mirten
 * @version    1.0
 */
class Topology {
	
	static protected $types = [
		Descriptor::TYPE_DEFAULT => 'topology-default',
		Descriptor::TYPE_PRIMARY => 'topology-primary',
		Descriptor::TYPE_SUCCESS => 'topology-success',
		Descriptor::TYPE_WARNING => 'topology-warning',
		Descriptor::TYPE_ALERT   => 'topology-alert'
	];
	
	/**
	 * Render the topology
	 * 
	 * @param  Descriptor $descriptor
	 * @return string
	 */
	public function render(Descriptor $descriptor) {
		$style = file_get_contents(__DIR__ . '/Assets/style.css');
		
		$styleTag = new Tag('style', $style);
		
		$topologyTag = new Tag();
		$topologyTag->addClass('topology');
		
		echo $styleTag->render() . $topologyTag->render($this->renderElement($descriptor));
	}
	
	/**
	 * Render the element
	 * 
	 * @param  Descriptor $descriptor
	 * @param  bool       $isChild
	 * @param  string     $linkTitle
	 * @return string
	 */
	protected function renderElement(Descriptor $descriptor, $isChild = false, $linkTitle = null) {
		$elementTag = new Tag();
		$elementTag->addClass('topology-element');
		
		$type = $descriptor->getType();
		
		if (isset(static::$types[$type])) {
			$elementTag->addClass(static::$types[$type]);
		}
		
		$headerTag = new Tag();
		$headerTag->addClass('topology-header');
		$headerTag->setHtml(htmlspecialchars($descriptor->getTitle()));
		
		$bodyTag = new Tag();
		$bodyTag->addClass('topology-body');
		
		if ($descriptor->hasPropertities()) {
			$bodyTag->setHtml($this->renderBodyContent($descriptor));
		}
		
		$footerTag = new Tag();
		$footerTag->addClass('topology-footer');
		
		$parts = $headerTag->render() . $bodyTag->render() . $footerTag->render();
		
		if ($isChild) {
			$rightLinkTag = new Tag();
			$rightLinkTag->addClass('topology-link');
			
			if ($linkTitle !== null) {
				$rightLinkTag->setHtml($linkTitle);
			}
		}
		
		if ($descriptor->hasChildren()) {
			$children = $this->renderChildren($descriptor);
			
			$linkTag = new Tag();
			$linkTag->addClass('topology-link');
			
			if ($descriptor->getChildrenNumber() > 1) {
				$linkUpTag = new Tag();
				$linkUpTag->addClass('topology-link-up');
				
				$element = $linkUpTag->render() . $linkTag->render() . $elementTag->render($parts);
			} else {
				$element = $linkTag->render() . $elementTag->render($parts);
			}
			
			$elementWrapperTag = new Tag();
			$elementWrapperTag->addClass('topology-wrapper');
			
			if ($isChild) {
				return $children . $elementWrapperTag->render($element . $rightLinkTag->render());
			}
			
			return $children . $elementWrapperTag->render($element);
		}
		
		if ($isChild) {
			return $elementTag->render($parts) . $rightLinkTag->render();
		}
		
		return $elementTag->render($parts);
	}
	
	/**
	 * Render the element's children
	 * 
	 * @param  Descriptor $descriptor
	 * @return string
	 */
	protected function renderChildren(Descriptor $descriptor) {
		$parts = [];
		
		foreach ($descriptor->getChildren() as $linkTitle => $child) {
			$parts[] = $this->renderElement($child, true, is_numeric($linkTitle) ? null : $linkTitle);
		}
		
		$elementWrapperTag = new Tag();
		$elementWrapperTag->addClass('topology-wrapper');
		
		return $elementWrapperTag->render(implode('<br>', $parts));
	}
	
	/**
	 * Render the element's body content
	 * 
	 * @param  Descriptor $descriptor
	 * @return string
	 */
	protected function renderBodyContent(Descriptor $descriptor) {
		$content = new Table();
		$content->addClass('topology-props');
		
		foreach ($descriptor as $property => $value) {
			if (is_numeric($property)) {
				$content[] = [$value, ''];
				continue;
			}
			
			$content[] = [$property . ':', $value];
		}
		
		return $content->render();
	}
	
}