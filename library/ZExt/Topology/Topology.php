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

use ZExt\Di\Container;

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
	 * Tags' locator
	 *
	 * @var Container 
	 */
	protected $tagsLocator;
	
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
	protected function renderElement(Descriptor $descriptor) {
		$element = $this->renderElementInner($descriptor);
		
		if ($descriptor->hasChildren()) {
			$tagsLocator = $this->getTagsLocator();
			
			$children = $this->renderChildren($descriptor);
			$element  = $tagsLocator->link->render() . $element;
			
			return $children . $tagsLocator->wrapper->render($element);
		}
		
		return $element;
	}
	
	/**
	 * Render the element's children
	 * 
	 * @param  Descriptor $descriptor
	 * @return string
	 */
	protected function renderChildren(Descriptor $descriptor) {
		$counter = 1;
		$parts   = [];
		$total   = $descriptor->getChildrenNumber();
		
		$tagsLocator = $this->getTagsLocator();
		$linkUp      = $tagsLocator->linkUp->render();
		$linkDown    = $tagsLocator->linkDown->render();
		$wrapper     = $tagsLocator->wrapper;
		
		foreach ($descriptor as $linkTitle => $child) {
			$element = $this->renderChildElement($child, $linkTitle);
			
			if ($counter > 1) {
				$element .= $linkUp;
			}
			
			if ($counter < $total) {
				$element .= $linkDown;
			}
			
			if ($total > 1) {
				$element = $wrapper->render($element);
			}
			
			$parts[] = $element;
			
			++ $counter;
		}
		
		return $wrapper->render(implode('<br>', $parts));
	}
	
	/**
	 * Render the element as a child
	 * 
	 * @param  Descriptor   $descriptor
	 * @param  string | int $linkTitle
	 * @return string
	 */
	protected function renderChildElement(Descriptor $descriptor, $linkTitle) {
		$element = $this->renderElement($descriptor);
		$link    = $this->getTagsLocator()->get('link');

		if (is_string($linkTitle)) {
			return $element . $link->render($linkTitle);
		}

		return $element . $link->render();
	}
	
	/**
	 * Render the element's inner parts
	 * 
	 * @param  Descriptor $descriptor
	 * @return string
	 */
	protected function renderElementInner(Descriptor $descriptor) {
		$tagsLocator = $this->getTagsLocator();
		
		// Element's main
		$elementTag = new Tag();
		$elementTag->addClass('topology-element');
		
		$type = $descriptor->getType();
		
		if (isset(static::$types[$type])) {
			$elementTag->addClass(static::$types[$type]);
		}
		
		// Header
		$title = htmlspecialchars($descriptor->getTitle());
		$elementTag->appendHtml($tagsLocator->header->render($title));
		
		// Body
		if ($descriptor->hasPropertities()) {
			$content = $this->renderBodyContent($descriptor);
			$elementTag->appendHtml($tagsLocator->body->render($content));
		} else {
			$elementTag->appendHtml($tagsLocator->body->render());
		}
		
		// Footer
		$elementTag->appendHtml($tagsLocator->footer->render());
		
		return $elementTag->render();
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
		
		foreach ($descriptor->getProperties() as $property => $value) {
			$value = htmlspecialchars($value);
			
			if (is_numeric($property)) {
				$content[] = [$value, ''];
				continue;
			}
			
			$property  = htmlspecialchars($property);
			$content[] = [$property . ':', $value];
		}
		
		return $content->render();
	}
	
	/**
	 * Grt the tags' locator
	 * 
	 * @return Container
	 */
	protected function getTagsLocator() {
		if ($this->tagsLocator !== null) {
			return $this->tagsLocator;
		}
		
		$container = new Container();

		$container->header = function() {
			return new Tag('div', null, 'topology-header');
		};
		
		$container->body = function() {
			return new Tag('div', null, 'topology-body');
		};
		
		$container->footer = function() {
			return new Tag('div', null, 'topology-footer');
		};
		
		$container->link = function() {
			return new Tag('div', null, 'topology-link');
		};
		
		$container->linkUp = function() {
			return new Tag('div', null, 'topology-link-up');
		};
		
		$container->linkDown = function() {
			return new Tag('div', null, 'topology-link-down');
		};
		
		$container->wrapper = function() {
			return new Tag('div', null, 'topology-wrapper');
		};

		$this->tagsLocator = $container;
		return $container;
	}
	
}