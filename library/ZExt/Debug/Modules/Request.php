<?php
namespace ZExt\Debug\Modules;

use ZExt\Html\Tag;
use ZExt\Dump\Html as Dump;

class Request extends ModuleAbstract {
	
	public function getTabIcon($size = null) {
		return 'data:image/png;base64,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';
	}
	
	public function renderTab() {
		return 'Request';
	}
	
	public function renderPanel() {
		$title = new Tag('h4');
		
		$info = $title->render('Compiled request data:');
		$info .= Dump::getDump($_REQUEST);
		
		$info .= $title->render('GET data:');
		$info .= Dump::getDump($_GET);
		
		$info .= $title->render('POST data:');
		$info .= Dump::getDump($_POST);
		
		$info .= $title->render('Session data:');
		$info .= Dump::getDump($_SESSION);
		
		$info .= $title->render('Cookies data:');
		$info .= Dump::getDump($_COOKIE);
		
		$info .= $title->render('Server data:');
		$info .= Dump::getDump($_SERVER);
		
		$info .= $title->render('Environment data:');
		$info .= Dump::getDump($_ENV);
		
		return $info;
	}
	
}