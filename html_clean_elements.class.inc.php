<?php


class html_element extends html_clean
{
	public $el_name = '';
	public $children = array();
	public $type = '' // head , block , inline
	public $end = true;
	public $self_close = false;

/**
 * @var $attributes array three dimensional array - each array
 * element is keyed with the attribute name which in turn has two
 * elements, type and value which is an array of pre-defined values
 * or a string identifying the value type - e.g. 'string', 'numeric'
 * etc.
 *
 * e.g.	array(
 		'href' => array(
			'type' => 'minimum',
			'value' => 'string'
		),
		'hreflang' => array(
			'type' => 'accessibility',
			'value' => array(
				fr, de, it, nl, el, es, pt, ar, he, ru, zh, ja, hi, ur, sa
			)
		)
 *	)
 */
	protected $attributes = array()
	protected $delete = false;
	protected $current_attr = '';


	public function __construct( $element , $attributes , $closing )
	{
		$this->get_element_info();
	}
	
	protected function check_attributes()
	{
		if($attributes != '')
		{
			$regex = '/[\r\n\t\ ]+([-a-z]+)=(?:([\'"])([^\2]*)\2|([^\ \t]))/is';
			$attributes = preg_replace_callback( $regex , 'CLEAN_ATTRIBUTES_CALLBACK' , $attributes
		};
	}

	private function CLEAN_ATTRIBUTES_CALLBACK($matches)
	{
		$attribute = strtolower($matches[1]);
		$value = isset($matches[3])?$matches[3]:'';
		$value .= isset($matches[4])?$matches[4]:'';
		if(isset($this->current_tag->attribute[$attribute]))

	}

}

