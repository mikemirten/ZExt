<?php
namespace ZExt\Html\DescriptionList;

use ZExt\Html\Tag;

class Term extends Tag {
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'dt';
	
	/**
	 * Constructor
	 * 
	 * @param string | numeric $content
	 * @param string | array   $attrs
	 */
	public function __construct($content = null, $attrs = null) {
		parent::__construct(null, $content, $attrs);
	}
	
}