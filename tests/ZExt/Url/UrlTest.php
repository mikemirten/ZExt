<?php

use ZExt\Url\Url;

class UrlTest extends PHPUnit_Framework_TestCase {
	
	public function testInstanceWithChema() {
		$url = new Url('schema');
		
		$this->assertTrue($url->hasScheme());
		$this->assertSame('schema', $url->getScheme());
		
		$this->assertFalse($url->hasUsername());
		$this->assertFalse($url->hasPassword());
		$this->assertFalse($url->hasHost());
		$this->assertFalse($url->hasPort());
		$this->assertFalse($url->hasPath());
		$this->assertFalse($url->hasQuery());
	}
	
	public function testInstanceWithFullUrl() {
		$url = new Url('schema://username:password@host.com:80/path/?param1=12&param2=22#fragment');
		
		$this->assertTrue($url->hasScheme());
		$this->assertSame('schema', $url->getScheme());
		
		$this->assertTrue($url->hasUsername());
		$this->assertSame('username', $url->getUsername());
		
		$this->assertTrue($url->hasPassword());
		$this->assertSame('password', $url->getPassword());
		
		$this->assertTrue($url->hasHost());
		$this->assertSame('host.com', $url->getHost());
		
		$this->assertTrue($url->hasPort());
		$this->assertSame(80, $url->getPort());
		
		$this->assertTrue($url->hasPath());
		$this->assertSame('/path/', $url->getPath());
		
		$this->assertTrue($url->hasQuery());
		$this->assertSame('param1=12&param2=22', $url->getQueryRaw());
		$this->assertSame(['param1' => '12', 'param2' => '22'], $url->getQueryParams());
		
		$this->assertTrue($url->hasQueryParam('param1'));
		$this->assertSame('12', $url->getQueryParam('param1'));
		
		$this->assertFalse($url->hasQueryParam('paramN'));
		
		$this->assertTrue(isset($url->param1));
		$this->assertSame('12', $url->param1);
	}
	
	public function testAssemble() {
		$url = new Url();
		
		$url->setScheme('http')
		    ->setUsername('john')
		    ->setPassword('123')
		    ->setHost('localhost')
		    ->setPort(1000)
			->setPath('/images/a')
			->setFragment('test');
		
		$url->param1 = 'qwerty';
		$url->param2 = 'asdfgh';
		
		$this->assertSame('http://john:123@localhost:1000/images/a?param1=qwerty&param2=asdfgh#test', $url->assemble());
	}
	
}