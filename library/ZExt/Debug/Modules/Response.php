<?php
namespace ZExt\Debug\Modules;
use ZExt\Html\Tag;

class Response extends ModuleAbstract {
	
	public function getTabIcon($size = null) {
		
	}
	
	public function renderTab() {
		$response = \Zend_Controller_Front::getInstance()->getResponse();
		$code     = $response->getHttpResponseCode();
		
		
		switch (true) {
			case $code >= 200 && $code < 400:
				$color = '#0e0';
				break;
				
			case $code >= 400:
				$color = '#f00';
				break;
			
			default:
				$color = '#fff';
		}
		
		$codeTag = new Tag('strong', $code);
		$codeTag->addStyle('color', $color);
		
		return $codeTag->render();
	}
	
}