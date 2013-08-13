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

namespace ZExt\Debug\Modules;
use ZExt\Html\ListUnordered;
use ZExt\Html\ListOrdered;
use ZExt\Html\Tag;

class Errors extends ModuleAbstract {
	
	protected $_errorTypes = array(
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parse',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core error',
		E_CORE_WARNING       => 'Core warning',
		E_COMPILE_ERROR      => 'Compile error',
		E_COMPILE_WARNING    => 'Compile warning',
		E_USER_ERROR         => 'User error',
		E_USER_WARNING       => 'User warning',
		E_USER_NOTICE        => 'User notice',
		E_STRICT             => 'Strict',
		E_RECOVERABLE_ERROR  => 'Recoverable error',
		E_DEPRECATED         => 'Deprecated',
		E_USER_DEPRECATED    => 'User deprecated'
	);
	
	protected $_errors = array();
	
	protected $_hasZend = false;
	
	public function init() {
		set_error_handler(array($this, 'errorHandler'));
		
		$this->_hasZend = class_exists('Zend_Controller_Front', false);
	}
	
	public function getTabIcon($size = null) {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wMCBUwMqQQtfIAAAN6SURBVEjHxVbPS2NXFP7uu3FejHlNDGVIKoFqqY+IFqIFF6HFFgouZgp2VbALXRVFuikudCP+A+66KEJnk5WLFoOVuHBTGBAdMeKPiGQhz5jA06BPY0zy3tzThclrGpK4aIc5cOBy77nfd+853/0BvC8jov8lljWbwNjj0NHR0dd+v/87WZaHnE7ncwBSuVy+LhaL+4Zh/Nnd3f07AFE750k7ODhwn52d/fDw8JClJ6xcLhcymczc9vZ2Z0vQtbU1AEAqlRrK5/Ova0GEEEREpGkaHR4ekmma9lC1USgUUpqmvQCAvb29f4PPz88DALa2ttRSqXT5D66wAbLZLM3OztLU1BRtbm5S3QIEEZFlWeVkMvkCAOLx+CP4wsICAGB0dPTZ/f19pn5lVUsmkzQ9PU0TExO0srLSKFuCiMg0TdrY2PikWktpcXERABCNRn9xuVyBSjAjIlQdADweDzjn4JzD4/HYYqhxRkTkcDgQiUSiAMAYgwQA6+vrXR6P59sKGGukKp/PB845HA4HvF5vU1USETmdzv5UKvUFgEeC/v7+MBG5hRBo5rIsQ5ZlcM7R2dmJFrGMiFyKonxuE3DOnxNRW92WUZ+mrq4uMMbg9XrxRKzEGPvQJigUClZFCC0JgsGgnS4hRNNYIQRKpdJbAHAAwMXFhRYIBMoA2pqdbsYYAoEAFEUB5xymaTa9OYQQlmEYmk0wMjLy5vLyUu/o6OgBQI1ILMtCKBSy263uH8uy7ldXV18DAK/0lfv6+ooDAwMvmymJc45oNIqdnR2oqor29vaGq5ckie3v70fHx8d/s2sAAJOTk68SicQfRMQqaqCqMogI6XQau7u7OD8/t6+CegUREctkMslIJDJbxZVq2M2xsbGfjo+PV6u7qBw6CCHg8/kwODiI3t5eqKpaW2SqpkvTtDczMzPfA7hpelB8Pt9Hy8vLC7quW3d3d2QYBhmGIQzDoFwuR7quV/vIMAy6vb2lm5sbisViy6FQ6NOatLc0lyzL4Xg8/iqdTl/oun57dXVVzOVyZi6XM6+vr6niIpFI/BUOh78CoDQqfKsXggGQAfjn5uY+6+np+VhRlA8kSeLBYHBIVdWXRGSdnJzMRCKRX//rCypVtt4G4Jnb7fYvLS39nM1m356env74rp7t9uHh4S9jsdg37/JvwABI+Xz+vX1O8Dfr2Ptz93zHrAAAAABJRU5ErkJggg==';
	}
	
	public function renderTab() {
		$errors = count($this->_errors);
		
		if ($this->_hasZend) {
			$response = \Zend_Controller_Front::getInstance()->getResponse();
			if ($response && $response->isException()) {
				$errors += count($response->getException());
			}
		}
		
		if (empty($errors)) {
			return 'No errors';
		} else {
			return $errors . ($errors > 1 ? ' Errors' : ' Error');
		}
	}
	
	public function renderPanel() {
		$hasZendException = false;
		
		if ($this->_hasZend) {
			$response = \Zend_Controller_Front::getInstance()->getResponse();
			if ($response && $response->isException()) {
				$hasZendException = true;
			}
		}
		
		if (empty($this->_errors) && ! $hasZendException) return;
		
		$partTag    = new Tag('p');
		$typeTag    = new Tag('span', null, 'debug-keyword');
		$lineTag    = new Tag('span', null, 'debug-line');
		$fileTag    = new Tag('strong', 'File:');
		$messageTag = new Tag('span', null, 'debug-string');
		
		if (! empty($this->_errors)) {
			$errors = array();
			
			foreach ($this->_errors as $error) {
				if (isset($this->_errorTypes[$error['type']])) {
					$type = $this->_errorTypes[$error['type']];
				} else {
					$type = 'Uncnown';
				}
				
				$message = $error['message'];
				
				if (strpos($message, 'Uncaught exception') === 0) {
					$tracePos = strpos($message, 'Stack trace:');
					
					if ($tracePos !== false) {
						$traceRaw = substr($message, $tracePos + 12, strlen($message) - $tracePos - 21);
						$message  = substr($message, 0, $tracePos - 1);
						
						$message = preg_replace('/exception \'(.*)\' with/', 'exception "' . $typeTag->render('$1') . '" with', $message);
						$message = preg_replace('/message \'(.*)\' in .*/', 'message "' . $messageTag->render('$1') . '".', $message);
						
						$trace = preg_split('/#\d+/', $traceRaw);
						array_shift($trace);
						$message .= $partTag->render('Stack trace: ');
						$message .= new ListOrdered($trace, 'list-rows');
					}
				}
				
				$errors[] =
				$partTag->render($typeTag->render($type) . ': ' . $message) .
				$partTag->render($fileTag . ' ' . $error['file'] . ' (' . $lineTag->render($error['line']) . ')');
			}
		}
		
		if ($hasZendException) {
			foreach ($response->getException() as $exception) {
				$trace = array();
				foreach ($exception->getTrace() as $event) {					
					$trace[] =
					$partTag->render($event['class'] . $event['type'] . $event['function'] . ' (' . $lineTag->render($event['line']) . ')') .
					$partTag->render($fileTag . ' ' . $event['file'] . ', ');
							
				}
				
				$errors[] =
				$partTag->render($typeTag->render(get_class($exception) . ':') . ' (' . $exception->getCode() . ') "' . $exception->getMessage() . '"') .
				$partTag->render($fileTag . ' ' . $exception->getFile() . ' (' . $lineTag->render($exception->getLine()) . ')') .
				$partTag->render(new ListOrdered($trace));
			}
		}
		
		$listTag = new ListUnordered($errors, 'list-rows list-simple');
		
		return $listTag->render();
	}
	
	public function errorHandler($type, $message, $file, $line) {
        $this->_errors[] = array(
			'type'    => $type,
			'message' => $message,
			'file'    => $file,
			'line'    => $line
		);
        
        return true;
    }
	
}
