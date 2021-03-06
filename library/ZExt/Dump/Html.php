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

namespace ZExt\Dump;

use Closure, ReflectionObject, Exception;

use ZExt\Html\Tag;
use ZExt\Html\Table;

use ZExt\Events\EventsManagerAwareInterface;
use ZExt\Di\LocatorAwareInterface;
use ZExt\Log\LoggerAwareInterface;

use ZExt\Model\Collection;
use ZExt\Model\Iterator;
use ZExt\Model\Model;

use ZExt\Highlighter\Languages\Php as PhpHighlighter;

class Html {
	
	protected static $hasStyle = false;
	
	public static function dump($var, $recursion = 4) {
		echo self::getDump($var, $recursion);
	}
	
	public static function getDump($var, $recursion = 4) {
		self::addStyle();
		
		list ($varType, $varValue) = self::_dump($var, $recursion);
		
		if ($varValue === '') {
			$info = $varType;
		} else {
			$info = $varType . ' : ' . $varValue;
		}
		
		return new Tag('div', $info, 'zDumpContainer');
	}
	
	protected static function _dump($var, $recursion, $maxStrLength = 128) {
		$datatype = gettype($var);
		
		$typeTagInt  = new Tag('span', null, 'zDumpInteger');
		$typeTagStr  = new Tag('span', null, 'zDumpString');
		$typeTagBool = new Tag('span', null, 'zDumpBoolean');
		
		$encodingTag = new Tag('span', null, 'zDumpEncoding');
		
		switch ($datatype) {
			case 'NULL':
				$varType  = 'Null';
				$varValue = '';
				break;
			
			case 'boolean':
				$varType  = 'Boolean';
				$varValue = $typeTagBool->render($var ? 'True' : 'False');
				break;
			
			case 'integer':
			case 'float':
			case 'double':
				$varType  = ucfirst($datatype);
				$varValue = $typeTagInt->render($var);
				break;
			
			case 'string':
				$encoding = mb_detect_encoding($var);
				
				$varType  =  $encodingTag->render($encoding);
				$varType .= '&nbsp;(' . $typeTagInt->render(mb_strlen($var, $encoding)) . ')';
				
				$varValue = htmlspecialchars($var);
				
				if (mb_strlen($var) > $maxStrLength) {
					$typeTagStr->title = $varValue;	
					
					$varValue = substr($varValue, 0, $maxStrLength) . '...';
				} 
				
				$varValue = $typeTagStr->render('"' . $varValue . '"');
				
				break;
				
			case 'array':
				$count = count($var);
				if ($count > 0) {
					$varType  = 'Array&nbsp;(' . $typeTagInt->render($count) . ')';
					
					if ($recursion > 0) {
						$varValue = self::_dumpArray($var, $recursion - 1);
					} else {
						$varValue = '';
					}
				} else {
					$varType  = 'Array&nbsp;(empty)';
					$varValue = '';
				}
				break;
				
			case 'object':
				if ($var instanceof Closure) {
					$varType  = 'Function';
					$varValue = '';
				}
				else if ($var instanceof Exception) {
					$varType  = 'Exception';
					$varValue = self::_exceptionInfo($var, $recursion);
				}
				else if ($var instanceof Model) {
					$varType  = 'Model';
					$varValue = self::_modelInfo($var, $recursion);
				}
				else if ($var instanceof Collection) {
					$varType  = 'Collection';
					$varValue = self::_modelInfo($var, $recursion);
				}
				else if ($var instanceof Iterator) {
					$varType  = 'Iterator';
					$varValue = self::_modelInfo($var, $recursion);
				}
				else {
					$varType  = 'Object';
					$varValue = self::_objectInfo($var, $recursion);
				}
				break;
				
			case 'resource':
				$varType  = 'Resource';
				$varValue = self::_resourceInfo($var);
				break;
			
			default:
				$varType  = 'Seems to an unknown type';
				$varValue = $datatype;
		}
		
		return [$varType, $varValue];
	}
	
	protected static function _dumpArray($array, $recursion) {
		$typeTagInt = new Tag('span', null, 'zDumpInteger');
		$typeTagStr = new Tag('span', null, 'zDumpString');
		
		$partsList = new Table([], 'zDumpArrayTable');
		$partsList->getColgroup()->addElements([1, 1, 1, 1, 95]);
		
		foreach ($array as $key => $value) {
			$row = [];
			
			if (is_int($key)) {
				$row[0] = $typeTagInt->render($key);
			} else {
				$row[0] = $typeTagStr->render('"' . htmlspecialchars($key) . '"');
			}
			
			$varInfo = self::_dump($value, $recursion);
			
			$row[1] = '&nbsp=>&nbsp';
			$row[2] = (string) $varInfo[0];
			$row[3] = ':';
			$row[4] = (string) $varInfo[1];
			
			$partsList[] = $row;
		}
		
		return $partsList;
	}
	
	protected static function _resourceInfo($resource) {
		$type = get_resource_type($resource);
		$info = $type;
		
		switch ($type) {
			case 'stream':
				$info .= ' (pos: ' . ftell($resource) . ')';
				break;
		}
		
		return $info;
	}
	
	protected static function _objectInfo($object, $recursion) {
		$reflection = new ReflectionObject($object);
		$classTag   = new Tag('span', null, 'zDumpClass');
		
		$info       = $classTag->render($reflection->getName());
		$properties = $reflection->getProperties();
		$propsCount = count($properties);
		
		if ($recursion > 0) {
			$propertiesInfo = [];
			foreach ($properties as $property) {
//				$modifiers = Reflection::getModifierNames($property->getModifiers());
//				implode(' ', $modifiers);
				
				$name = $property->getName();
				
				if ($property->isPublic()) {
					$propertiesInfo[$name] = $property->getValue($object);
				} else {
					$property->setAccessible(true);
					$propertiesInfo[$name] = $property->getValue($object);
					$property->setAccessible(false);
				}
			}

			$info .= self::_dumpArray($propertiesInfo, $recursion - 1);
		} else {
			$info .= ' (' . $propsCount . ' properties)';
		}
		
		return $info;
	}
	
	protected static function _modelInfo($model, $recursion) {
		$typeTagInt = new Tag('span', null, 'zDumpInteger');
		$typeTagStr = new Tag('span', null, 'zDumpString');
		$classTag   = new Tag('span', null, 'zDumpClass');
		$dataTitle  = new Tag('div', null, 'zDumpTitle');
		
		$count = $model->count();
		$info  = $classTag->render(get_class($model)) . ' (' . $typeTagInt->render($count) . ($count === 1 ? ' item' : ' items') . ')';
		$info .= $dataTitle->render('Info:');
		
		$partsList   = new Table([], 'zDumpArrayTable');
		$partsList[] = ['Parent datagate', ':', $model->hasDatagate() ? $classTag->render(get_class($model->getDatagate())) : 'No datagate'];
		
		if ($model instanceof LocatorAwareInterface) {
			$partsList[] = ['Has a services\' locator', ':', $model->hasLocator() ? 'Yes' : 'No'];
		}
		
		if ($model instanceof EventsManagerAwareInterface) {
			$partsList[] = ['Has an event\'s manager', ':', $model->hasEventsManager() ? 'Yes' : 'No'];
		}
		
		if ($model instanceof LoggerAwareInterface) {
			$partsList[] = ['Has a logger', ':', $model->hasLogger() ? 'Yes' : 'No'];
		}
		
		$partsList[] = ['Is insert forced', ':', $model->isInsertForced() ? $typeTagInt->render('Yes') : 'No'];

		if ($model instanceof Model) {
			if ($model->hasCollection()) {
				$collection = $model->getCollection();
				$countItems = $collection->count();
				$countInfo  = $typeTagInt->render($countItems) . ($countItems === 1 ? ' item' : ' items');

				$partsList[] = ['Belongs to collection', ':', $classTag->render(get_class($collection)) . ' (' . $countInfo . ')'];
			} else {
				$partsList[] = ['Belongs to collection', ':', 'No'];
			}
		} else if ($model instanceof Collection) {
			$primary = $model->getPrimary();
			
			$partsList[] = ['Models\' class', ':', $classTag->render($model->getModel())];
			$partsList[] = ['Primary ID', ':', ($primary === null) ? 'No primary ID' : $typeTagStr->render('"' . $primary . '"')];
		} else if ($model instanceof Iterator) {
			$partsList[] = ['Models\' class', ':', $classTag->render($model->getModel())];
		}

		if ($model->hasMetadata()) {
			$partsList[] = ['Metadata', ':', self::_modelInfo($model->getMetadata())];
		} else {
			$partsList[] = ['Metadata', ':', 'No metadata'];
		}
		
		if ($model->isEmpty()) {
			$partsList[] = ['Data', ':', 'No data'];
			$info .= $partsList->render();
		} else {
			$info .= $partsList->render();
			
			if ($model instanceof Model) {
				$dataCount = $model->count();
				$countInfo = $typeTagInt->render($dataCount) . ' ' . ($dataCount === 1 ? 'Item' : 'Items');
				
				$info .= $dataTitle->render('Data (' . $countInfo . '):');
				$info .= self::_dumpArray($model->getDataLinked(), $recursion - 1)->render();
			}
			else if ($model instanceof Collection) {
				foreach ($model->getDataLinked() as $key => $data) {
					if (is_int($key)) {
						$key = $typeTagInt->render($key);
					} else {
						$key = $typeTagStr->render('"' . $key . '"');
					}
					
					$dataCount = count($data);
					$countInfo = $typeTagInt->render($dataCount) . ' ' . ($dataCount === 1 ? 'Item' : 'Items');
					
					$info .= $dataTitle->render('Model ID: ' . $key . ' (' . $countInfo . '):');
					$info .= self::_dumpArray($data, $recursion - 1)->render();
				}
			}
		}
		
		return $info;
	}
	
	protected static function _exceptionInfo(Exception $exception, $recursion, $odd = false) {
		$typeTagInt = new Tag('span', null, 'zDumpInteger');
		$typeTagStr = new Tag('span', null, 'zDumpString');
		$classTag   = new Tag('span', null, 'zDumpClass');
		$dataTitle  = new Tag('div', null, 'zDumpTitle');
		$blockTag   = new Tag('div', null, 'zDumpBlock');
		
		$info  = $classTag->render(get_class($exception));
		$info .= ' (code: ' . $typeTagInt->render($exception->getCode()) . ')';
		
		$info .= $dataTitle->render('Message:');
		$info .= $blockTag->render($typeTagStr->render($exception->getMessage()));
		
		$info .= $dataTitle->render('Info:');
		
		$partsList = new Table([], 'zDumpArrayTable');
		$partsList->getColgroup()->addElements([1, 1, 99]);
		
		$filePath = $exception->getFile();
		$fileLine = $exception->getLine();
		
		$fileInfo  = $filePath;
		$fileInfo .= ' (line: ' . $typeTagStr->render($fileLine) . ')';
		
		$partsList[] = ['File', ':', $fileInfo];
		$info .= $partsList->render();
		
		if (is_readable($filePath)) {
			$info .= $dataTitle->render('Code listing:');
			$info .= self::dumpPhpScript($filePath, $fileLine);
		}
		
		$trace = $exception->getTrace();
		
		if (! empty($trace)) {
			$info .= $dataTitle->render('Trace:');

			$traceList = new Table([], 'zDumpArrayTable');

			$traceList[] = ['File', 'Line', '', 'Call'];

			foreach ($trace as $part) {
				if (isset($part['file'])) {
					$fileInfo = $part['file'];
					$lineInfo = $typeTagStr->render($part['line']);
				} else {
					$fileInfo = 'Internal function';
					$lineInfo = '';
				}

				$callInfo = '';

				if (isset($part['class'])) {
					$callInfo .= $classTag->render($part['class']);
					$callInfo .= $part['type'];
				}

				$callInfo .= $part['function'] . '(';

				if (! empty($part['args'])) {
					$argsInfo = [];

					foreach ($part['args'] as $arg) {
						switch (gettype($arg)) {
							case 'NULL':
								$argInfo = 'Null';
								break;

							case 'object':
								$argInfo = $classTag->render(get_class($arg));
								break;

							case 'array':
								$argInfo = 'Array(' . $typeTagInt->render(count($arg)) . ' items)';
								break;

							default:
								$argsCount = count($part['args']);
								$maxLength = (int) round(160 / $argsCount);

								if ($maxLength < 16) {
									$maxLength = 16;
								}

								$argInfo = self::_dump($arg, 0, $maxLength)[1];
						}

						$argsInfo[] = $argInfo;
					}

					$callInfo .= implode(', ', $argsInfo);
				}

				$callInfo .= ')';

				$traceList[] = [$fileInfo, $lineInfo, ':', $callInfo];

			}

			$info .= $traceList->render();
		}
		
		$previous = $exception->getPrevious();
		
		if ($previous !== null) {
			$info .= $dataTitle->render('Previous:');
			
			if ($recursion > 0) {
				if ($odd) {
					$blockTag->addStyle('background', '#fff');
					$odd = false;
				} else {
					$blockTag->addStyle('background', '#f9f8f7');
					$odd = true;
				}
				
				$info .= $blockTag->render(self::_exceptionInfo($previous, $recursion - 1, $odd));
			} else {
				$previousInfo  = $classTag->render(get_class($previous));
				$previousInfo .= ' (code: ' . $typeTagInt->render($exception->getCode()) . ')';
				
				$info .= $blockTag->render($previousInfo);
			}
		}
		
		return $info;
	}
	
	public static function dumpPhpScript($path, $line, $range = 8) {
		$minLine = $line - $range;
		$maxLine = $line + $range;

		if ($minLine < 1) {
			$minLine = 1;
		}

		$dumpTable = new Table([], 'zDumpCodeTable');
		$dumpTable->getColgroup()->addElements([1, 99]);

		$file = fopen($path, 'r');
		$currentline = 1;

		$highlighter = new PhpHighlighter();
		
		while ($currentline <= $maxLine && ! feof($file) && ($string = fgets($file)) !== false) {
			if ($currentline >= $minLine) {
				$string = $highlighter->highlightLine($string);

				if ($currentline === $line) {
					$dumpTable[] = [$currentline, $string, '_class_' => 'zDumpLineError'];
				} else {
					$dumpTable[] = [$currentline, $string];
				}
			}

			++ $currentline;
		}

		return $dumpTable->render();
	}

	protected static function addStyle() {
		if (! self::$hasStyle) {
			echo new Tag('style', file_get_contents(__DIR__ . '/Html/Style.css'));
			self::$hasStyle = true;
		}
	}
	
	public function __invoke($var, $recursion = 4) {
		self::dump($var, $recursion);
	}
	
}